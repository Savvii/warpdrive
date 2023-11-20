<?php

namespace Savvii;

/**
 * Class SecurityPlugin
 */
class SecurityPlugin {

    /**
     * @var Security
     */
    var $security;

    /**
     * Constructor - Set actions and filters
     */
    function __construct() {
        // Create new security class
        $this->security = new Security();
        // Errors above login form
        add_filter( 'login_errors', [ $this, 'filter_login_errors' ] );
        // Check if a login failed
        add_filter( 'authenticate', [ $this, 'filter_authenticate' ], 90, 3 );
        // Track if credentials are used, we need this to strip login errors
        add_action( 'wp_authenticate', [ $this, 'action_wp_authenticate' ], 10, 2 );
        // Login success
        add_action( 'wp_login', [ $this, 'action_wp_login' ] );
        // Cookie authentication success
        add_action( 'auth_cookie_valid', [ $this, 'action_auth_cookie_valid' ], 10, 2 );
        // Cookie authentication failed
        add_action( 'auth_cookie_bad_hash', [ $this, 'action_auth_cookie_failed' ] );
        add_action( 'auth_cookie_bad_username', [ $this, 'action_auth_cookie_failed' ] );
        // Redirect canonical
        add_filter( 'redirect_canonical', [ $this, 'filter_redirect_canonical' ], 10, 2 );
        // Check XML-RPC calls to register a pingback
        add_action( 'xmlrpc_call', [ $this, 'action_xmlrpc_call' ] );
    }

    function filter_login_errors( $content ) {
        return $this->security->clean_login_error_message( $content );
    }

    function filter_authenticate( $user, $username, $password ) {
        $ignore_codes = array( 'empty_username', 'empty_password' );

        if ( null === $user || ( is_wp_error( $user ) && ! in_array( $user->get_error_code(), $ignore_codes, true ) ) ) {
            $this->security->login_failed( $username );
        }

        return $user;
    }

    function action_wp_authenticate( $username, $password ) {
        $this->security->track_credentials_state( $username, $password );
    }

    function action_wp_login( $username ) {
        $this->security->login_success( $username );
    }

    function action_auth_cookie_valid( $cookie, $user ) {
        $this->security->cookie_success( $user );
    }

    function action_auth_cookie_failed( $cookie ) {
        $this->security->cookie_failed( $cookie );
    }

    function filter_redirect_canonical( $redirect ) {
        return $this->security->redirect_canonical( $redirect );
    }

    function action_xmlrpc_call( $method ) {
        if ( 'pingback.ping' === $method ) {
            $this->security->xmlrpc_pingback();
        }
    }
}
