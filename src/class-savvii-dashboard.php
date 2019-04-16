<?php

namespace Savvii;

/**
 * Class SavviiDashboard
 * @package Savvii
 */
class SavviiDashboard {

    const MENU_NAME = 'warpdrive_dashboard';
    const FORM_CACHE_STYLE = 'warpdrive_cache_style';
    const FORM_CACHE_DEFAULT = 'warpdrive_cache_default';
    const FORM_CACHE_SET_DEFAULT = 'warpdrive_cache_set_default';
    const FORM_CACHE_USE_DEFAULT = 'warpdrive_cache_use_default';
    const FORM_DEFAULT_CACHE_STYLE = 'warpdrive_default_cache_style';

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
        // Add flush button to top bar
        add_action( 'admin_bar_menu', [ $this, 'admin_bar_menu' ], 90 );

        $this->cache_flusher = new CacheFlusher();
    }

    function admin_bar_menu() {
        global $wp_admin_bar;

        //Add option to menu bar
        if ( current_user_can( 'manage_options' ) ) {
            $wp_admin_bar->add_menu([
                'id' => 'warpdrive_top_menu',
                'title' => 'Savvii',
                'href' => wp_nonce_url( admin_url( 'options-general.php?page=warpdrive_dashboard' ) ),
            ]);
        }
    }

    function admin_menu_register() {
        add_options_page( 'Savvii', 'Savvii', 'manage_options', self::MENU_NAME, [ $this, 'warpdrive_dashboard' ] );
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

    function maybe_update_default_caching_style() {
        if ( ! isset( $_POST[ self::FORM_CACHE_SET_DEFAULT ] ) ) {
            return;
        }

        check_admin_referer( Options::CACHING_STYLE );

        update_site_option( Options::CACHING_STYLE, ( CacheFlusherPlugin::CACHING_STYLE_NORMAL === $_POST[ self::FORM_CACHE_SET_DEFAULT ] ? CacheFlusherPlugin::CACHING_STYLE_NORMAL : CacheFlusherPlugin::CACHING_STYLE_AGRESSIVE ) );
    }

    function warpdrive_dashboard() {
        if ( ! empty( $_POST ) ) {
            // Update settings when needed
            $this->maybe_update_caching_style();
            // Update default settings when needed
            $this->maybe_update_default_caching_style();
        }

        // Get settings from options
        $default_cache_style = get_site_option( Options::CACHING_STYLE, CacheFlusherPlugin::get_default_cache_style() );
        $cache_style = get_option( Options::CACHING_STYLE, $default_cache_style );

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
            <?php if ( is_multisite() && is_super_admin() ) : ?>
                    <!-- Caching defaults -->
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
                                    <?php wp_nonce_field( Options::CACHING_STYLE ); ?>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- /Caching defaults -->
            <?php endif; ?>
                    <!-- Read server logs -->
                    <div class="postbox" style="min-height: 150px;">
                        <h2 class="hndle">Read server logs</h3>
                        <div class="inside"><?= is_super_admin() ? 'Please use the Savvii top menu for reading logs.' : '
                            \'Read server logs\' shows data for all subsites. Because of this, only users with the \'Super Admin\' role are able to read the server logs.'; ?>
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
