<?php

namespace Savvii;

/**
 * Class CdnLinkRewriter
 * Rewrites a buffer to Savvii CDN links
 * @package Savvii
 */
class CdnLinkRewriter {

    /**
     * Constructor
     */
    function __construct() { }

    /**
     * Rewrite links in $buffer to cdn qualified links
     * @param string $buffer
     * @return string
     */
    function rewrite( $buffer = '' ) {
        // We need this because PHP does not allow string interpolation with constants
        $wpinc = WPINC;

        // Determine wp-content directory
        $wp_content_dir = str_replace( ABSPATH, '', WP_CONTENT_DIR );

        // Prepare link regexp
        $scheme     = 'https?://';
        $domain     = $this->get_domain_regexp();
        $paths      = "{$this->get_site_path_regexp()}(wp-content|{$wp_content_dir}|{$wpinc})/";
        $basename   = '[^"\']+?';
        $extensions = '\.(css|js|gif|png|jpg|ico|ttf|otf|woff)(?!\.)';
        $version    = '(\?[a-zA-Z0-9=\.\-_&]+)?';

        // Regular expression to match links
        $regexp = "~({$scheme})({$domain})({$paths}{$basename}{$extensions})({$version})~";

        // Replacement
        $replace = '$1' . Options::cdn_domain() . '$3';

        return preg_replace( $regexp, $replace, $buffer );
    }

    /**
     * Get the domain as regular expression
     * @return string
     */
    function get_domain_regexp() {
        $domain_re = '';

        $parse_url = wp_parse_url( site_url() );
        if ( is_array( $parse_url ) && isset( $parse_url['host'] ) ) {
            $domain_re = preg_quote( $parse_url['host'] );
        }

        return $domain_re;
    }

    /**
     * Get the site path as regular expression
     * @return string
     */
    function get_site_path_regexp() {
        $site_path_re = '/';

        $parse_url = wp_parse_url( site_url() );
        if ( is_array( $parse_url ) && isset( $parse_url['path'] ) ) {
            $trimmed_path = trim( $parse_url['path'], '/' );
            $site_path_re = '/' . ( strlen( $trimmed_path ) ? preg_quote( $trimmed_path . '/' ) : '' );
        }

        return $site_path_re;
    }
}
