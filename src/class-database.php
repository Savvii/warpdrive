<?php

namespace Savvii;

/**
 * Class Database
 * @package Savvii
 */
class Database {
    function get_wp_table_sizes() {
      global $wpdb;

      check_admin_referer( 'warpdrive_viewdatabasesize' );
      $systemname = Options::system_name();

      $results = $wpdb->get_results( $wpdb->prepare( "
        SELECT
          table_schema as `database`,
          table_name AS `table`,
          round(((data_length + index_length) / 1024 / 1024), 2) `size`
        FROM information_schema.TABLES
        WHERE table_schema = %s
        ORDER BY (data_length + index_length) DESC;", $systemname), $output=ARRAY_A);

        return $results;
    }
}
