<?php

namespace Savvii;

/**
 * Class CacheFlusherPlugin
 * @package Savvii
 * Plugin part of cache flushing which runs the cache flusher
 * depending on cache agression set by an administrator.
 */
class CacheFlusherPlugin {

    const CACHING_STYLE_NORMAL    = 'normal';
    const CACHING_STYLE_AGRESSIVE = 'agressive';

    const NAME_FLUSH_NOW = 'warpdrive_flush_now';
    const NAME_DOMAINFLUSH_NOW = 'warpdrive_domainflush_now';
    const NAME_FLUSH_RESULT = 'warpdrive_flush_result';
    const NAME_DOMAINFLUSH_RESULT = 'warpdrive_domainflush_result';
    const NAME_FLUSH_RESULT_FAILED = 'failed';
    const NAME_FLUSH_RESULT_SUCCESS = 'success';

    const TEXT_FLUSH = 'Flush all cache';
    const TEXT_DOMAINFLUSH = 'Flush site cache';
    const TEXT_FLUSH_RESULT_FAILED = '(failed)';
    const TEXT_FLUSH_RESULT_SUCCESS = '(cache flushed)';

    /**
     * Events that can be used to flush the cache
     * @var array
     */
    var $register_events = [
        'agressive' => [
            /// Pages
            // Run when a page is published, or if it is edited and its status is "published".
            'publish_page',
            /// Posts
            // Run when a post is published, or if it is edited and its status is "published".
            'publish_post',
            // Run just after a post or page is trashed.
            'trashed_post',
            // Run after publish of future post
            'publish_future_post',
        ],
        'normal' => [
            /// Pages
            /// Posts
            /// Comments
            // Run after comment changed state (approved, unapproved, trash, spam)
            'transition_comment_status',
            // Run after comment is edited
            'edit_comment',
            /// Other
            // Run just before an attached file is deleted from the database.
            'deleted_attachment',
            // Run when an attached file is edited/updated to the database.
            'edit_attachment',
            // Run when the blog's theme is changed.
            'switch_theme',
        ],
    ];

    /**
     * Is the cache already flushed?
     * @public boolean flushed_all
     */
    public $flushed_all = false;

    /**
     * Keep a list of flushed domains
     * @public array flushed_domains
     */
    public $flushed_domains = [];

    /**
     * A CacheFlusher instance
     * @protected CacheFlusher cache_flusher
     */
    protected $cache_flusher;

    /**
     * Static access
     */
    static function get_cache_styles() {
        return [
            self::CACHING_STYLE_AGRESSIVE,
            self::CACHING_STYLE_NORMAL,
        ];
    }
    static function get_default_cache_style() {
        return self::CACHING_STYLE_NORMAL;
    }

    /**
     * Constructor
     */
    function __construct() {
        $default_cache_stype = get_site_option( Options::CACHING_STYLE, self::get_default_cache_style() );
        // Register flush event for set aggression level
        switch ( get_option( Options::CACHING_STYLE, $default_cache_stype ) ) {
            case self::CACHING_STYLE_AGRESSIVE:
                foreach ( $this->register_events[ self::CACHING_STYLE_AGRESSIVE ] as $event ) {
                    add_action( $event, [ $this, 'domainflush' ], 10, 2 );
                }

                // Register custom_post_type edit/publish flush action
                // only in CACHING_STYLE_AGRESSIVE, CACHING_STYLE_NORMAL flushes on all posts types
                $current_checked_custom_post_types = get_option(Options::CACHING_CUSTOM_POST_TYPES, array());

                foreach ($current_checked_custom_post_types as $post_type => $enabled) {

                    $publish_event = "publish_$post_type";
                    $trashed_event  = "trashed_$post_type";
                    if ($enabled) {
                        add_action( $publish_event, [ $this, 'domainflush' ], 10, 2);
                        add_action( $trashed_event, [ $this, 'domainflush'], 10, 2);
                    }
                }

                break;
            case self::CACHING_STYLE_NORMAL:
                foreach ( $this->register_events[ self::CACHING_STYLE_AGRESSIVE ] as $event ) {
                    add_action( $event, [ $this, 'domainflush' ], 10, 2 );
                }
                foreach ( $this->register_events[ self::CACHING_STYLE_NORMAL ] as $event ) {
                    add_action( $event, [ $this, 'domainflush' ], 10, 2 );
                }
                // Run after a post changed status
                add_action( 'transition_post_status', [ $this, 'prepare_flush_transition_post_status' ], 10, 3 );
                // Run after cache for post is cleared
                add_action( 'clean_post_cache', [ $this, 'prepare_post_flush' ], 10, 2 );
                break;
            default:
                break;
        }

        // Add custom direct flush hook
        add_action( 'warpdrive_cache_flush', [ $this, 'flush' ], 10, 0 );
        add_action( 'warpdrive_domain_flush', [ $this, 'domainflush' ], 10, 0 );

        // Backward compatibility
        add_action( 'savvii_cache_flush', [ $this, 'flush' ], 10, 0 );
        add_action( 'savvii_domain_flush', [ $this, 'domainflush' ], 10, 0 );

        // init
        add_action( 'init', [ $this, 'init' ] );

        // Add flush button to top bar
        add_action( 'admin_bar_menu', [ $this, 'admin_bar_menu' ], 90 );
        // Add notification to widgets
        add_action( 'admin_notices', [ $this, 'admin_notices_widgets' ] );

        $this->cache_flusher = new CacheFlusher();
    }

    function init() {
        // Do we need to flush now?
        if ( isset( $_REQUEST[ self::NAME_FLUSH_NOW ] ) && check_admin_referer( self::NAME_FLUSH_NOW ) ) {
            $this->flush( true );
        } elseif ( isset( $_REQUEST[ self::NAME_DOMAINFLUSH_NOW ] ) && check_admin_referer( self::NAME_DOMAINFLUSH_NOW ) ) {
            $this->domainflush( true );
        }
    }

    public function prepare_post_flush( $post_id, $post ) {
        if ( 'publish' === $post->post_status ) {
            $this->domainflush();
        }
    }

    public function prepare_flush_transition_post_status( $new_status, $old_status, $post ) {
        if ( 'future' === $old_status && 'publish' === $new_status ) {
            $this->domainflush();
        }
    }

    function flush( $do_redirect = false ) {
        // Only flush a single time per request
        if ( $this->flushed_all ) {
            return; // Already flushed
        }
        $this->flushed_all = true;

        if ( $this->cache_flusher->flush() ) {
            $result = self::NAME_FLUSH_RESULT_SUCCESS;
        } else {
            $result = self::NAME_FLUSH_RESULT_FAILED;
        }

        // Send user back where they came from. Add query parameter so we can show a 'success' notification.
        if ( true === $do_redirect && is_admin_bar_showing() ) {
            $referer = wp_get_referer();

            if ( ! $referer ) {
                $referer = get_admin_url();
            }

            // Remove all related parameters to prevent conflicts or redirect loops
            $redirect_url = remove_query_arg([
                self::NAME_FLUSH_RESULT,
                self::NAME_DOMAINFLUSH_RESULT,
                self::NAME_FLUSH_NOW,
                self::NAME_DOMAINFLUSH_NOW,
            ], $referer);
            $redirect_url = add_query_arg( self::NAME_FLUSH_RESULT, $result, $redirect_url );

            $this->safe_redirect( $redirect_url, 302 );
        } else {
            return $result;
        }
    }

    function domainflush( $do_redirect = false ) {
        // debug
        //error_log(__FUNCTION__ . "Called by " . print_r(current_filter(), true));

        // Retrieve domain to flush
        $domain_parts = wp_parse_url( get_site_url() );
        $domain = isset( $domain_parts['host'] ) ? $domain_parts['host'] : '';

        // Only flush a single time per request per domain
        if ( $this->flushed_all || empty( $domain ) || in_array( $domain, $this->flushed_domains, true ) ) {
            return; // Already flushed or empty
        }
        array_push( $this->flushed_domains, $domain );

        if ( $this->cache_flusher->flush_domain() ) {
            $result = self::NAME_FLUSH_RESULT_SUCCESS;
        } else {
            $result = self::NAME_FLUSH_RESULT_FAILED;
        }

        // Send user back where they came from. Add query parameter so we can show a 'success' notification.
        if ( true === $do_redirect && is_admin_bar_showing() ) {
            $referer = wp_get_referer();

            if ( ! $referer ) {
                $referer = get_admin_url();
            }

            // Remove all related parameters to prevent conflicts or redirect loops
            $redirect_url = remove_query_arg([
                self::NAME_FLUSH_RESULT,
                self::NAME_DOMAINFLUSH_RESULT,
                self::NAME_FLUSH_NOW,
                self::NAME_DOMAINFLUSH_NOW,
            ], $referer);
            $redirect_url = add_query_arg( self::NAME_DOMAINFLUSH_RESULT, $result, $redirect_url );

            $this->safe_redirect( $redirect_url, 302 );
        }
    }

    /**
     * Wrapper for wp_safe_redirect
     * @codeCoverageIgnore
     */
    function safe_redirect( $location, $status = 302 ) {
        wp_safe_redirect( $location, $status );
    }

    function admin_bar_menu() {
        global $wp_admin_bar;

        $flush_result = null;
        if ( isset( $_REQUEST[ self::NAME_FLUSH_RESULT ] ) ) {
            switch ( $_REQUEST[ self::NAME_FLUSH_RESULT ] ) {
                case self::NAME_FLUSH_RESULT_FAILED:
                    $flush_result .= ' <span style="color:#FF0000">' . self::TEXT_FLUSH_RESULT_FAILED . '</span>';
                    break;
                case self::NAME_FLUSH_RESULT_SUCCESS:
                    $flush_result .= ' <span style="color:#00FF00">' . self::TEXT_FLUSH_RESULT_SUCCESS . '</span>';
                    break;
                default:
                    break;
            }
        }

        $domainflush_result = null;
        if ( isset( $_REQUEST[ self::NAME_DOMAINFLUSH_RESULT ] ) ) {
            switch ( $_REQUEST[ self::NAME_DOMAINFLUSH_RESULT ] ) {
                case self::NAME_FLUSH_RESULT_FAILED:
                    $domainflush_result .= ' <span style="color:#FF0000">' . self::TEXT_FLUSH_RESULT_FAILED . '</span>';
                    break;
                case self::NAME_FLUSH_RESULT_SUCCESS:
                    $domainflush_result .= ' <span style="color:#00FF00">' . self::TEXT_FLUSH_RESULT_SUCCESS . '</span>';
                    break;
                default:
                    break;
            }
        }

        //Add option to menu bar
        if ( current_user_can( 'manage_options' ) ) {
            $wp_admin_bar->add_menu([
                'parent' => 'warpdrive_top_menu',
                'id' => 'warpdrive_cache_delete',
                'title' => self::TEXT_FLUSH . $flush_result,
                'href' => wp_nonce_url( admin_url( '?warpdrive_flush_now' ), self::NAME_FLUSH_NOW ),
            ]);

            if ( is_multisite() ) {
                $wp_admin_bar->add_menu([
                    'parent' => 'warpdrive_top_menu',
                    'id' => 'warpdrive_sitecache_delete',
                    'title' => self::TEXT_DOMAINFLUSH . $domainflush_result,
                    'href' => wp_nonce_url( admin_url( '?warpdrive_domainflush_now' ), self::NAME_DOMAINFLUSH_NOW ),
                ]);
            }
        }
    }

    function admin_notices_widgets() {
        $page = get_current_screen();
        if ( 'widgets' !== $page->id ) {
            return;
        }

        ?>
        <div class="updated">
            <p><strong>Note: </strong>When changing widgets, cache is not automatically cleared! Use \'Flush cache\' in admin bar when you finished editing widgets.</p>
        </div>
        <?php
    }
}
