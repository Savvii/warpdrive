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
            'View database size',
            'View database size',
            'manage_options',
            'warpdrive_viewdatabasesize',
            [ $this, 'viewdatabasesize_page']
        );
    }

    function admin_bar_menu() {
        global $wp_admin_bar;

        if ( is_super_admin() ) {
            $wp_admin_bar->add_menu([
                'parent' => 'warpdrive_top_menu',
                'id' => 'warpdrive_viewdatabasesize',
                'title' => 'View database size',
                'href' => wp_nonce_url( admin_url( 'admin.php?page=warpdrive_viewdatabasesize' ), 'warpdrive_viewdatabasesize' ),
            ]);
        }
    }

    function viewdatabasesize_page() {
        check_admin_referer( 'warpdrive_viewdatabasesize' );
        $systemname = Options::system_name();

        $results = Database::get_wp_table_sizes();
        ?>
        <h2>View database table sizes</h2>
        <table>
            <tr>
                <th>Database</th>
                <th>Table</th>
                <th>Size (MB)</th>
            </tr>
        <?php

        foreach($results as $row)
        {
        ?>
            <tr>
            <?php
            echo "<td style='text-align:center;'>" . $row['database'] . "</td>";
            echo "<td style='text-align:center;'>" . $row['table']    . "</td>";
            echo "<td style='text-align:center;'>" . $row['size']     . "</td>";
            ?>
            </tr>
        <?php
        }
        ?>
        </table>
        <?php
    }
}