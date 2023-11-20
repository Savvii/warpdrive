<?php
/**
 * Read logs
 * Allows administrators to read the access and error log
 *
 * @author Ferdi van der Werf <ferdi@savvii.nl>
 */

namespace Savvii;

class ReadLogsPlugin {

    /**
     * @var ReadLogs
     */
    var $read_logs;

    /**
     * Constructor
     */
    function __construct() {
        // Set ReadLogs
        $this->read_logs = new ReadLogs();
        // Add module menu items to warpdrive menu
        add_action( 'admin_menu', [ $this, 'admin_menu_init' ] );
        add_action( 'admin_bar_menu', [ $this, 'admin_bar_menu' ], 90 );
    }

    function admin_menu_init() {
        add_submenu_page(
            'warpdrive_dashboard', // Parent slug
            'Read server logs', // Page title
            'Read server logs', // Menu title
            'manage_options', // Capability
            'warpdrive_readlogs', // Menu slug
            [ $this, 'readlogs_page' ] // Callback
        );
    }

    function admin_bar_menu() {
       global $wp_admin_bar;

       if ( is_super_admin() ) {
            $wp_admin_bar->add_menu([
                'parent' => 'warpdrive_top_menu',
                'id' => 'warpdrive_access_log',
                'title' => 'Access log',
                'href' => '#',
            ]);

            $wp_admin_bar->add_menu([
                'parent' => 'warpdrive_access_log',
                'id' => 'warpdrive_access_log_10_lines',
                'title' => '10 lines',
                'href' => wp_nonce_url( admin_url( 'admin.php?page=warpdrive_readlogs&log=access&lines=10' ), 'warpdrive_readlogs' ),
            ]);


            $wp_admin_bar->add_menu([
                'parent' => 'warpdrive_access_log',
                'id' => 'warpdrive_access_log_100_lines',
                'title' => '100 lines',
                'href' => wp_nonce_url( admin_url( 'admin.php?page=warpdrive_readlogs&log=access&lines=100' ), 'warpdrive_readlogs' ),
            ]);


            $wp_admin_bar->add_menu([
                'parent' => 'warpdrive_top_menu',
                'id' => 'warpdrive_error_log',
                'title' => 'Error log',
                'href' => '#',
            ]);

            $wp_admin_bar->add_menu([
                'parent' => 'warpdrive_error_log',
                'id' => 'warpdrive_error_log_10_lines',
                'title' => '10 lines',
                'href' => wp_nonce_url( admin_url( 'admin.php?page=warpdrive_readlogs&log=error&lines=10' ), 'warpdrive_readlogs' ),
            ]);

            $wp_admin_bar->add_menu([
                'parent' => 'warpdrive_error_log',
                'id' => 'warpdrive_error_log_100_lines',
                'title' => '100 lines',
                'href' => wp_nonce_url( admin_url( 'admin.php?page=warpdrive_readlogs&log=error&lines=100' ), 'warpdrive_readlogs' ),
            ]);
       }
    }

    function _g( $var, $default = null ) {
        return isset( $_GET[ $var ] ) ? $_GET[ $var ] : $default;
    }

    function readlogs_page() {
        check_admin_referer( 'warpdrive_readlogs' );

        // Get variables from GET
        $log   = $this->read_logs->clean_log_name( $this->_g( 'log' ) );
        $lines = $this->read_logs->clean_lines( $this->_g( 'lines' ) );
        ?>
        <h2>Read logs</h2>
        <style>
            .savvii dt, dd { float: left }
            .savvii dt {width: 75px; clear:both}
        </style>
        <dl class="savvii">
            <dt>Access log:</dt>
            <dd>
                <a href="<?php echo esc_attr( wp_nonce_url( admin_url( 'admin.php?page=warpdrive_readlogs&log=access&lines=10' ), 'warpdrive_readlogs' ) ); ?>" class="log-button">show 10 lines</a>,
                <a href="<?php echo esc_attr( wp_nonce_url( admin_url( 'admin.php?page=warpdrive_readlogs&log=access&lines=100' ), 'warpdrive_readlogs' ) ); ?>" class="log-button">show 100 lines</a>
            </dd>
            <dt>Error log:</dt>
            <dd>
                <a href="<?php echo esc_attr( wp_nonce_url( admin_url( 'admin.php?page=warpdrive_readlogs&log=error&lines=10' ), 'warpdrive_readlogs' ) ); ?>" class="log-button">show 10 lines</a>,
                <a href="<?php echo esc_attr( wp_nonce_url( admin_url( 'admin.php?page=warpdrive_readlogs&log=error&lines=100' ), 'warpdrive_readlogs' ) ); ?>" class="log-button">show 100 lines</a>
            </dd>
        </dl>
        <div style="clear: both;"></div>
        <h3><?php echo esc_html( ucfirst( $log ) ) ?> log</h3>
        <ol>
        <?php
        $log_lines = $this->read_logs->get_log_lines( $log, $lines );
        foreach ( $log_lines as $line ) {
            ?><li><?php echo esc_html( $line ) ?></li><?php
        }
        ?>
        </ol>
        <?php
    }
}
