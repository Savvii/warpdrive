<?php
/**
* Get the sizes of all tables in the Database
* and display them
* @author Matthias <matthias@savvii.com>
*/

namespace Savvii;

class DatabaseSizePlugin {

    function __construct() {
        add_action( 'admin_menu', [ $this, 'admin_menu_init' ]);
        add_action( 'admin_bar_menu', [ $this, 'admin_bar_menu' ], 90);
    }

    function admin_menu_init() {
        add_submenu_page(
            'warpdrive_dashboard',
            'Database information',
            'Database information',
            'manage_options',
            'warpdrive_databaseinfo',
            [ $this, 'databaseinfo_page']
        );
    }

    function admin_bar_menu() {
        global $wp_admin_bar;

        if ( is_super_admin() ) {
            $wp_admin_bar->add_menu([
                'parent' => 'warpdrive_top_menu',
                'id' => 'warpdrive_databaseinfo',
                'title' => 'Database information',
                'href' => wp_nonce_url( admin_url( 'admin.php?page=warpdrive_databaseinfo' ), 'warpdrive_databaseinfo' ),
            ]);
        }
    }

    function databaseinfo_page() {
        check_admin_referer( 'warpdrive_databaseinfo' );

        $tables = Database::get_wp_table_sizes();

        // get and check the database info
        $databaseinfo = Database::get_wp_database_size();
        $errormsg = '';
        $databasename = '';
        $databasesize = 0;
        if (
                !array_key_exists('database', $databaseinfo) ||
                !array_key_exists('size', $databaseinfo) ||
                empty($tables)
        ) {
            $errormsg = 'Could not retrieve database information, please contact <a href="mailto:support@savvii.com">support</a>';
        } else {
            $databasename = $databaseinfo['database'];
            $databasesize = $databaseinfo['size'];
        }

        ?>
        <style>
            .savvii .postbox .main .activity-block label.checkbox { display: block; width: 100%; clear: both; }
            .savvii .postbox .main .activity-block label { margin-bottom: 10px; }
            .savvii .postbox .main .activity-block select { width: 100%; }
            .savvii .postbox .main .activity-block { border: none; }
            .savvii .postbox .main ul { list-style-type: square; }
            .savvii .postbox .main ul.clear { list-style-type: none; }
            .savvii .postbox .main li { position: relative; left: 2em; }
            .savvii .postbox .main li.sm { position: relative; left: 1em; }
            .savvii .postbox .button {text-align: center; width: 100%;}
            .savvii dt, dd { float: left }
            .savvii dt {width: 75px; clear:both}
            .savvii td {padding-right: 2em;}

            @media screen and ( min-width: 600px ) {
            .savvii #dashboard-widgets .postbox-container {
                    min-width: 510px;
                }
            }
        </style>
        <div class="savvii dashboard-widgets-wrap">
            <div id="dashboard-widgets" class="metabox-holder">
                <div class="postbox-container">
                <!-- Database size -->
                    <div class="postbox" style="min-height: 130px;">
                        <h2 class="hndle">Database Information</h2>
                        <div class="inside">
                            <div class="main">
                                <?php if (!empty($errormsg)) : ?>
                                    An error occurred: <?php echo $errormsg; ?>
                                <?php else: ?>
                                    <h3>Database</h3>
                                        <table>
                                            <tr>
                                                <td>Name:</td><td><?php echo $databasename; ?></td>
                                            </tr>
                                            <tr>
                                                <td>Size:</td><td><?php echo $this->readableBytes($databasesize); ?></td>
                                            </tr>
                                        </table>
                                        &nbsp;
                                    <h3>Tables</h3>
                                        <table>
                                        <?php foreach ($tables as $table) : ?>
                                        <tr>
                                            <td><?php echo $table['table']; ?></td>
                                            <td><?php echo $this->readableBytes($table['size']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                        </table>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Converts a long string of bytes into a readable format e.g KB, MB, GB, TB, YB
     *
     * @param {Int} num The number of bytes.
     */
    function readableBytes($bytes) {
        $i = floor(log($bytes) / log(1024));
        $sizes = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');

        return sprintf('%.02F', $bytes / pow(1024, $i)) * 1 . ' ' . $sizes[$i];
    }
}
