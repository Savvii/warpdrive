<?php

namespace Savvii;

/**
 * Class SavviiDashboard
 * @package Savvii
 */
class SavviiDashboard {

    const MENU_NAME = 'savvii_dashboard';
    const FORM_CACHE_STYLE = 'savvii_cache_style';
    const FORM_CACHE_DEFAULT = 'savvii_cache_default';
    const FORM_CACHE_SET_DEFAULT = 'savvii_cache_set_default';
    const FORM_CACHE_USE_DEFAULT = 'savvii_cache_use_default';
    const FORM_DEFAULT_CACHE_STYLE = 'savvii_default_cache_style';
    const FORM_CDN_ENABLE = 'savvii_cdn_enable';
    const FORM_CDN_DEFAULT = 'savvii_cdn_default';
    const FORM_CDN_SET_DEFAULT = 'savvii_cdn_set_default';
    const FORM_CDN_USE_DEFAULT = 'savvii_cdn_use_default';
    const FORM_CDN_HOME_URL = 'savvii_cdn_home_url';

    /**
     * CacheFlusher instance
     * @protected CacheFlusher cache_flusher
     */
    protected $cache_flusher;

    /**
     * Constructor
     */
    function __construct() {
        // Add menu to menu bar
        add_action( 'admin_menu', [ $this, 'admin_menu_register' ] );
        
        $this->cache_flusher = new CacheFlusher();
    }

    function admin_menu_register() {
        // Put menu page between other plugins (usually prio 99)        
        add_menu_page( 'Savvii', 'Savvii', 'manage_options', self::MENU_NAME, [ $this, 'page_dashboard' ], '', 99 );
    }

    function maybe_update_caching_style() {
        if ( ! isset( $_POST[ self::FORM_CACHE_DEFAULT ] ) ) {
            return;
        }

        check_admin_referer( Options::CACHING_STYLE );

        if ( isset( $_POST[ self::FORM_CACHE_USE_DEFAULT ] ) ) {
            return delete_option( Options::CACHING_STYLE );
        }

        update_option( Options::CACHING_STYLE, ( CacheFlusherPlugin::CACHING_STYLE_NORMAL === $_POST[ self::FORM_CACHE_STYLE ] ? CacheFlusherPlugin::CACHING_STYLE_NORMAL : CacheFlusherPlugin::CACHING_STYLE_AGRESSIVE ) );
    }

    function maybe_update_cdn_enable() {
        if ( ! isset( $_POST[ self::FORM_CDN_DEFAULT ] ) ) {
            return;
        }

        check_admin_referer( Options::CDN_ENABLE );

        if ( isset( $_POST[ self::FORM_CDN_USE_DEFAULT ] ) ) {
            delete_option( Options::CDN_ENABLE );
        }

        if ( ! isset( $_POST[ self::FORM_CDN_USE_DEFAULT ] ) ) {
            update_option( Options::CDN_ENABLE, isset( $_POST[ self::FORM_CDN_ENABLE ] ) ? true : 0 );
        }

        if ( $this->cache_flusher->flush() ) {
            ?><div class="updated"><p>Content Delivery Network option saved and performed cache flush.</p></div><?php
        } else {
            ?><div class="error"><p>Content Delivery Network option saved but could not perform cache flush.</p></div><?php
        }
    }

    function maybe_update_default_caching_style() {
        if ( ! isset( $_POST[ self::FORM_CACHE_SET_DEFAULT ] ) ) {
            return;
        }

        check_admin_referer( Options::CACHING_STYLE );

        update_site_option( Options::CACHING_STYLE, ( CacheFlusherPlugin::CACHING_STYLE_NORMAL === $_POST[ self::FORM_CACHE_SET_DEFAULT ] ? CacheFlusherPlugin::CACHING_STYLE_NORMAL : CacheFlusherPlugin::CACHING_STYLE_AGRESSIVE ) );
    }

    function maybe_update_default_cdn_enable() {
        if ( ! isset( $_POST[ self::FORM_CACHE_SET_DEFAULT ] ) ) {
            return;
        }

        check_admin_referer( Options::CACHING_STYLE );

        update_site_option( Options::CDN_ENABLE, isset( $_POST[ self::FORM_CDN_SET_DEFAULT ] ) ? true : 0 );

        if ( $this->cache_flusher->flush() ) {
            ?><div class="updated"><p>Default Content Delivery Network option saved and performed cache flush.</p></div><?php
        } else {
            ?><div class="error"><p>Default Content Delivery Network option saved but could not perform cache flush.</p></div><?php
        }
    }

    function page_dashboard() {
        if ( ! empty( $_POST ) ) {
            // Update settings when needed
            $this->maybe_update_caching_style();
            $this->maybe_update_cdn_enable();
            // Update default settings when needed
            $this->maybe_update_default_caching_style();
            $this->maybe_update_default_cdn_enable();
        }

        // Get settings from options
        $default_cache_style = get_site_option( Options::CACHING_STYLE, CacheFlusherPlugin::get_default_cache_style() );
        $default_cdn_enabled = get_site_option( Options::CDN_ENABLE, false );
        $cache_style = get_option( Options::CACHING_STYLE, $default_cache_style );
        $cdn_enabled = get_option( Options::CDN_ENABLE, $default_cdn_enabled );

        $caching_styles = [
            CacheFlusherPlugin::CACHING_STYLE_AGRESSIVE => 'Flush on post/page edit or publish',
            CacheFlusherPlugin::CACHING_STYLE_NORMAL => 'Flush on post/page edit or publish, comment changes, attachment changes',
        ];

        ?>
        <style>
            .savvii .postbox .main .activity-block label.checkbox { display: block; width: 100%; clear: both; }
            .savvii .postbox .main .activity-block label { margin-bottom: 10px; }
            .savvii .postbox .main .activity-block select { width: 100%; }
            .savvii .postbox .main .activity-block { border: none; }
            .savvii .postbox .button {text-align: center; width: 100%;}
            .savvii dt, dd { float: left }
            .savvii dt {width: 75px; clear:both}

            @media screen and ( min-width: 600px ) {
                .savvii #dashboard-widgets .postbox-container {
                    min-width: 510px;
                }
            }
        </style>
        <div class="savvii dashboard-widgets-wrap">
            <div id="dashboard-widgets" class="metabox-holder">
                <div class="postbox-container">
                    <!-- Caching -->
                    <div class="postbox" style="min-height: 130px;">
                        <h2 class="hndle">Caching</h2>
                        <div class="inside">
                            <div class="main">
                                <form action="" method="post">
                                    <div class="activity-block">
                                        <div class="button-group-vertical" style="width: 100%;">
            <?php if ( is_multisite() ) : ?>
                                            <label class="checkbox"><input type="checkbox" name="<?php echo esc_attr( self::FORM_CACHE_USE_DEFAULT ); ?>" <?php echo is_null( get_option( Options::CACHING_STYLE, null ) ) ? 'checked="checked"' : ''; ?> /> Use default network value</label>
            <?php endif; ?>
            <?php if ( ! is_multisite() || ! is_null( get_option( Options::CACHING_STYLE, null ) ) ) : ?>
                                            <select name="<?php echo esc_attr( self::FORM_CACHE_STYLE ); ?>">
            <?php foreach ( $caching_styles as $style => $text ) : ?>
                                                <option value="<?php echo esc_attr( $style ); ?>" <?php echo $style === $cache_style ? 'selected="selected"' : ''; ?>><?php echo esc_attr( $text ); ?></option>
            <?php endforeach; ?>
                                            </select>
            <?php endif; ?>
                                        </div>
                                    </div>
                                    <input type="hidden" name="<?php echo esc_attr( self::FORM_CACHE_DEFAULT ); ?>" value="<?php echo esc_attr( $cache_style ); ?>" />
                                    <?php wp_nonce_field( Options::CACHING_STYLE ); ?>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- /Caching -->
                    <!-- CDN -->
                    <div class="postbox" style="min-height: 130px;">
                        <h2 class="hndle">Content Delivery Network</h2>
                        <div class="inside">
                            <div class="main">
                                <form action="" method="post">
                                    <div class="activity-block">
                                        <div class="button-group-vertical" style="width: 100%;">
            <?php if ( is_multisite() ) : ?>
                                            <label class="checkbox"><input type="checkbox" name="<?php echo esc_attr( self::FORM_CDN_USE_DEFAULT ); ?>" <?php echo is_null( get_option( Options::CDN_ENABLE, null ) ) ? 'checked="checked"' : ''; ?> /> Use default network value</label>
            <?php endif; ?>
            <?php if ( ! is_multisite() || ! is_null( get_option( Options::CDN_ENABLE, null ) ) ) : ?>
                                            <label class="checkbox"><input type="checkbox" name="<?php echo esc_attr( self::FORM_CDN_ENABLE ); ?>" <?php echo $cdn_enabled ? 'checked="checked"' : ''; ?> /> CDN enabled</label>
            <?php endif; ?>
                                        </div>
                                    </div>
                                    <input type="hidden" name="<?php echo esc_attr( self::FORM_CDN_DEFAULT ); ?>" value="<?php echo esc_attr( $cdn_enabled ); ?>" />
                                    <?php wp_nonce_field( Options::CDN_ENABLE ); ?>
                                </form>
                            </div>
            <?php if ( is_ssl() ) : ?>
                            <div class="sub">
                                Our CDN does not work in combination with SSL. <a href="https://www.savvii.eu/blog/what-does-the-savvii-content-delivery-network-do/#cdnssl" title="Savvii CDN and SSL" target="_blank">Read here why</a>.
                            </div>
            <?php endif; ?>
                        </div>
                    </div>
                    <!-- /CDN -->
            <?php if ( is_multisite() && is_super_admin() ) : ?>
                    <!-- CDN and caching defaults -->
                    <div class="postbox" style="min-height: 130px;">
                        <h2 class="hndle">Multisite default values (only visible to super admin)</h2>
                        <div class="inside">
                            <div class="main">
                                <form action="" method="post">
                                    <h3>Cache</h3>
                                    <div class="activity-block" style="margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #eee;">
                                        <div class="button-group-vertical" style="width: 100%;">
                                            <select name="<?php echo esc_attr( self::FORM_CACHE_SET_DEFAULT ); ?>">
            <?php foreach ( $caching_styles as $style => $text ) : ?>
                                                <option value="<?php echo esc_attr( $style ); ?>" <?php echo $style === $default_cache_style ? 'selected="selected"' : ''; ?>><?php echo esc_attr( $text ); ?></option>
            <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <h3>Content Delivery Network</h3>
                                    <div class="activity-block">
                                        <div class="button-group-vertical" style="width: 100%;">
                                            <label class="checkbox"><input type="checkbox" name="<?php echo esc_attr( self::FORM_CDN_SET_DEFAULT ); ?>" <?php echo $default_cdn_enabled ? 'checked="checked"' : ''; ?> /> CDN enabled</label>
                                        </div>
                                    </div>
                                    <?php wp_nonce_field( Options::CACHING_STYLE ); ?>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- /CDN and caching defaults -->
            <?php endif; ?>
                    <!-- Read server logs -->
                    <div class="postbox" style="min-height: 150px;">
                        <h2 class="hndle">Read server logs</h3>
                        <div class="inside">
            <?php if ( is_super_admin() ) : ?>
                            <dl>
                                <dt>Access log:</dt>
                                <dd>
                                    <a href="<?php echo esc_attr( wp_nonce_url( admin_url( 'admin.php?page=savvii_readlogs&log=access&lines=10' ), 'savvii_readlogs' ) ); ?>" class="log-button">show 10 lines</a>,
                                    <a href="<?php echo esc_attr( wp_nonce_url( admin_url( 'admin.php?page=savvii_readlogs&log=access&lines=100' ), 'savvii_readlogs' ) ); ?>" class="log-button">show 100 lines</a>
                                </dd>
                                <dt>Error log:</dt>
                                <dd>
                                    <a href="<?php echo esc_attr( wp_nonce_url( admin_url( 'admin.php?page=savvii_readlogs&log=error&lines=10' ), 'savvii_readlogs' ) ); ?>" class="log-button">show 10 lines</a>,
                                    <a href="<?php echo esc_attr( wp_nonce_url( admin_url( 'admin.php?page=savvii_readlogs&log=error&lines=100' ), 'savvii_readlogs' ) ); ?>" class="log-button">show 100 lines</a>
                                </dd>
                            </dl>
            <?php else : ?>
                            'Read server logs' shows data for all subsites. Because of this, only users with the 'Super Admin' role are able to read the server logs.
            <?php endif; ?>
                        </div>
                    </div>
                    <!-- /Read server logs -->
                </div>
            </div>
        </div>
        <script type="text/javascript">
            jQuery( document ).ready( function() {
                jQuery( 'div.savvii.dashboard-widgets-wrap input[type=checkbox], div.savvii.dashboard-widgets-wrap select' ).on( 'change', function () {
                    jQuery( this ).closest( 'form' ).submit();
                });
            });
        </script>
        <?php
    }
}
