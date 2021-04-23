<?php

namespace Savvii;

/**
 * Class Database
 * @package Savvii
 */
class Database {
    public static function get_wp_table_sizes( $limit = -1 ) {
        global $wpdb;

        //check_admin_referer( 'warpdrive_viewdatabasesize' );
        $systemname = Options::system_name();

        if ( $limit == -1) {
            $sql = $wpdb->prepare("
              SELECT
                table_schema as `database`,
                table_name as `table`,
                round(((data_length + index_length) / 1024 / 1024), 2) `size`
              FROM information_schema.TABLES
              WHERE table_schema = %s
              ORDER BY (data_length + index_length) DESC;", $systemname);
        } else {
            $sql = $wpdb->prepare("
              SELECT
                table_schema as `database`,
                table_name as `table`,
                round(((data_length + index_length) / 1024 / 1024), 2) `size`
              FROM information_schema.TABLES
              WHERE table_schema = %s
              ORDER BY (data_length + index_length) DESC
              LIMIT %d;", $systemname, $limit);
        }

        $results = $wpdb->get_results( $sql, $output=ARRAY_A );
        return $results;
    }
}