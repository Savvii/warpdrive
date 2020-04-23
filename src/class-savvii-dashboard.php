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
        // Add stylesheet
        add_action( 'admin_enqueue_scripts', array( $this, 'register_plugin_styles' ) );
        $this->cache_flusher = new CacheFlusher();
    }

    function register_plugin_styles() {
        wp_register_style( 'warpdrive-dashboard', plugins_url( 'warpdrive/src/css/savvii-dashboard.css' ) );
        wp_enqueue_style( 'warpdrive-dashboard' );
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

        $logreader = new ReadLogs();

        ?>
        <div class="savvii dashboard-widgets-wrap">
            <div id="dashboard-widgets" class="metabox-holder">
                <div class="postbox-container">
                    <!-- Caching -->
                    <div class="postbox">
                        <h2 class="hndle">Caching</h2>
                        <div class="inside">
                            <div class="main">
                                <form action="" method="post">
                                    <div class="activity-block">
                                        <div class="button-group-vertical">
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
                    <div class="postbox">
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
                    <div class="postbox">
                        <h2 class="hndle">Read server logs</h3>
                        <div class="inside">
                            <?php
                            if (is_super_admin()) {
                            ?>
                                <div class='row'>  
                                    <div class='column'>
                                        <h3>Access Log preview</h3>
                                        <div class='code'>
                                            <?php $logreader->print_lines( 'access', 10 ); ?>
                                        </div>
                                    </div>
                                    <div class='column'>
                                        <h3>Error log preview</h3>
                                        <div class='code'>
                                            <?php $logreader->print_lines( 'error', 10 ); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php
                            } else {
                                echo "<p>Only users with the Super Admin role are allowed to read server logs</p>";
                            }
                            ?>
                        </div>
                    </div>
                    <!-- /Read server logs -->
                    <!-- Statistics -->
                    <div class="postbox">
                        <h2 class="hndle">Statistics</h2>
                        <div class="inside">
                            <div class="row">
                                <?php
                                $errors = $logreader->find_match_in_log( 'access', '/HTTP\/(?:1\.1|2)\"\s+(50[24])/i');
                                $errors_502 = 0;
                                $errors_504 = 0;
                                foreach ($errors as $error) {
                                    if (strpos($error, '502')) {
                                        $errors_502++;
                                    } elseif (strpos($error, '504')) {
                                        $errors_504++;
                                    }
                                }

                                //$errors_502 = $logreader->find_match_in_log( 'access', '/HTTP\/(?:1\.1|2)\"\s+502/i');
                                //$errors_504 = $logreader->find_match_in_log( 'access', '/HTTP\/(?:1\.1|2)\"\s+504/i');
                                ?>
                                <div class="column">
                                    <p>WarpDrive detected <?php echo ' ' . $errors_502 . ' ';?> recent 502 (Bad Gateway) error(s)</p>
                                    <p>WarpDrive detected <?php echo ' ' . $errors_504 . ' ';?> recent 504 (Gateway Timeout) error(s)</p>
                                </div>
                                <?php
                                $results = Database::get_wp_table_sizes(10);
                                ?>
                                <div class="column">
                                    <table>
                                        <tr>
                                            <th>Table</th>
                                            <th>Size (MB)</th>
                                        </tr>
                                    <?php foreach($results as $row) { ?>
                                        <tr>
                                            <td><?php echo $row['table']; ?></td>
                                            <td><?php echo $row['size']; ?></td>
                                        </tr>
                                    <?php } ?>
                                    </table>
                                <a href=<?php echo wp_nonce_url( admin_url( 'admin.php?page=warpdrive_viewdatabasesize' ), 'warpdrive_viewdatabasesize' ) ?> > View all tables...</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /Statistics -->
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
