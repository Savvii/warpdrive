<?php

namespace Savvii;

class CdnLinkRewriterPlugin {

    /**
     * Constructor
     */
    function __construct() {
        // If we can ob, we should
        if ( $this->can_ob() ) {
            // Register CDN ob callbacks
            add_action( 'wp',        [ $this, 'start' ], -999999 );
            add_action( 'wp_footer', [ $this, 'end' ],    999999 );
        }
    }

    function can_ob() {
        // Skip certain cases
        if ( is_admin()                                 // Skip if admin
            || defined( 'DOING_AJAX' )                  // Skip if ajax
            || defined( 'DOING_CRON' )                  // Skip if cron
            || defined( 'APP_REQUEST' )                 // Skip if APP request
            || defined( 'XMLRPC_REQUEST' )              // Skip if XML RPC request
            || ( defined( 'SHORTINIT' ) && SHORTINIT )  // Skip if WPMU's and WP's 3.0 short init is detected
        ) {
            return false;
        }

        return true;
    }

    function start() {
        ob_start( [ $this, 'process' ] );
    }

    function end() {
        ob_end_flush();
    }

    function process( $buffer ) {
        $rewriter = new CdnLinkRewriter();
        return $rewriter->rewrite( $buffer );
    }
}
