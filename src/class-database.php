<?php

namespace Savvii;

/**
 * Class Database
 * @package Savvii
 */
class Database {
    public static function get_wp_table_sizes( $limit = -1 ) {
        global $wpdb;

        $systemname = Options::system_name();

        if ( $limit == -1) {
            $sql = $wpdb->prepare("
              SELECT
                table_schema as `database`,
                table_name as `table`,
                (data_length + index_length) `size`
              FROM information_schema.TABLES
              WHERE table_schema = %s
              ORDER BY (data_length + index_length) DESC;", $systemname);
        } else {
            $sql = $wpdb->prepare("
              SELECT
                table_schema as `database`,
                table_name as `table`,
                (data_length + index_length) `size`
              FROM information_schema.TABLES
              WHERE table_schema = %s
              ORDER BY (data_length + index_length) DESC
              LIMIT %d;", $systemname, $limit);
        }

        $results = $wpdb->get_results( $sql, $output=ARRAY_A );
        return $results;
    }

    public static function get_wp_database_size() {
        global $wpdb;

        $systemname = Options::system_name();

        $sql = $wpdb->prepare("
            SELECT 
                table_schema AS `database`,
                SUM(data_length + index_length) AS `size`
            FROM information_schema.TABLES 
            WHERE table_schema = %s
            GROUP BY table_schema LIMIT 1", $systemname);

        $rows = $wpdb->get_results( $sql, $output=ARRAY_A );

        if (count($rows) == 1) {
            return $rows[0];
        }
        return array();
    }

}
