<?php

/**
 * In WP 4.4.0 wp_parse_url was introduced, to support WP verions before 4.4.0
 * we need to make a stub for wp_parse_url to maintain compatibility.
 */
if ( ! function_exists( 'wp_parse_url' ) ) :
    function wp_parse_url( $url ) {
        return @parse_url( $url );
    }
endif;

/**
 * In WP 4.1.0 wp_json_encode was introduced, to support WP verions before 4.1.0
 * we need to make a stub for wp_json_encode to maintain compatibility.
 */
if ( ! function_exists( 'wp_json_encode' ) ) :
    function wp_json_encode( $data, $options = 0, $depth = 512 ) {
        return @json_encode( $data, $options, $depth );
    }
endif;

if ( is_multisite() ) :
    /**
     * In WP 4.6.0 get_sites was introduced, to support WP verions before 4.6.0
     * we need to make a stub for get_sites to maintain compatibility.
     */
    if ( ! function_exists( 'get_sites' ) ) :
        function get_sites( $args = [] ) {
            $sites = wp_get_sites( $args );
            foreach ( $sites as $key => $site ) {
                $sites[ $key ] = (object) $site;
            }
            return $sites;
        }
    endif;
endif;
