<?php

namespace Savvii;

/**
 * Class Security
 * @package Savvii
 */
class Security {

    /**
     * Options used within Security
     * @var Options
     */
    var $options;

    /**
     * Has the user sent credentials?
     * @var boolean
     */
    var $has_send_credentials = false;

    /**
     * Constructor - Set actions and filters
     */
    function __construct() {
    }

    // ----------------------------------------------------------------------------------------------------------------
    // Clean login error message

    /**
     * Clean login error messages because we do not want to show any error
     * message like unknown user or empty password, unless this was the attempt
     * that locked us out. Messages like 'invalid username' or 'invalid password'
     * leak information regarding usernames.
     */
    function clean_login_error_message( $content ) {
        // Should a message be shown?
        if ( ! $this->can_login_header_show_message() ) {
            return $content;
        }

        // Explode messages on <br />\n
        $messages = explode( "<br />\n", $content );
        // Remove last item if it's empty
        if ( 0 === strlen( end( $messages ) ) ) {
            array_pop( $messages );
        }

        // Filter information leaks
        $count_messages = count( $messages );
        if ( $this->has_send_credentials && $count_messages ) {
            // Replace error message
            return '<strong>ERROR</strong>: Incorrect username or password';
        } elseif ( $count_messages <= 1 ) {
            return $content;
        }

        $new = '';
        while ( $count_messages-- > 0 ) {
            $new .= array_shift( $messages ) . "<br />\n<br />\n";
        }
        return preg_replace( '~<br />\n<br />\n$~', '', $new );
    }

    /**
     * Can we set a login header message?
     * @return bool True if we can set a login header message
     */
    function can_login_header_show_message() {
        // De we want to reset a password?
        if ( isset( $_GET['key'] ) ) {
            return false;
        }
        // Check action
        $action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';
        return ! in_array(
            $action,
            [ 'lostpassword', 'retrievepassword', 'resetpass', 'rp', 'register' ],
            true
        );
    }

    // ----------------------------------------------------------------------------------------------------------------
    // Track credentials state

    /**
     * Keep track whether a user has filled in credentials.
     * Used to filter errors correctly.
     * @param $username
     * @param $password
     */
    function track_credentials_state( $username, $password ) {
        $this->has_send_credentials = ! empty( $username ) && ! empty( $password );
    }

    // ----------------------------------------------------------------------------------------------------------------
    // Login failed

    /**
     * Failed login attempt
     * @param $username
     */
    function login_failed( $username ) {
        $this->write_syslog( "Authentication failure on {$this->get_system_name()} for {$username} from {$this->get_ip_address()}" );
    }

    // ----------------------------------------------------------------------------------------------------------------
    // Login success

    function login_success( $username ) {
        $this->write_syslog( "Authentication success on {$this->get_system_name()} for {$username} from {$this->get_ip_address()}" );
    }

    // ----------------------------------------------------------------------------------------------------------------
    // Cookie success

    /**
     * Successful cookie login.
     * Clear any stored cookie from user meta.
     * @param $user
     */
    function cookie_success( $user ) {
        if ( get_user_meta( $user->ID, 'warpdrive_prev_cookie' ) ) {
            delete_user_meta( $user->ID, 'warpdrive_prev_cookie' );
        }
    }

    // ----------------------------------------------------------------------------------------------------------------
    // Cookie failed

    function cookie_failed_log( $username ) {
        $this->write_syslog( "Authentication failure -cookie- on {$this->get_system_name()} for {$username} from {$this->get_ip_address()}" );
    }

    /**
     * Failed cookie login hash
     * Make sure same invalid cookie is not counted more than once.
     */
    function cookie_failed( $cookie ) {
        // Clear current cookie contents
        $this->clear_auth_cookie();

        // Get username from cookie
        $username = isset( $cookie['username'] ) ? $cookie['username'] : 'Unknown';

        // Check if we have a valid user
        $user = get_user_by( 'login', $username );
        if ( $user ) {
            // Check if the cookie matches the previous cookie
            $prev_cookie = get_user_meta( $user->ID, 'warpdrive_prev_cookie', true );
            if ( $prev_cookie && $prev_cookie === $cookie ) {
                // Identical cookies, ignore this attempt
                return;
            }
            // Store cookie
            update_user_meta( $user->ID, 'warpdrive_prev_cookie', $cookie );
        }

        // Report fail
        $this->cookie_failed_log( $username );
    }

    /**
     * Clear authentication cookie
     */
    function clear_auth_cookie() {

        // only call wp_clear_cookie() the first time we are here
        // it seems that sometimes we end up in a loop, this will prevent that.
        $cookie_already_cleared = wp_cache_get('warpdrive_cookie_already_cleared');
        if ($cookie_already_cleared === false) {
            wp_cache_set('warpdrive_cookie_already_cleared', true);
            wp_clear_auth_cookie();
        }

        if ( ! empty( $_COOKIE[ AUTH_COOKIE ] ) ) {
            $_COOKIE[ AUTH_COOKIE ] = '';
        }
        if ( ! empty( $_COOKIE[ SECURE_AUTH_COOKIE ] ) ) {
            $_COOKIE[ SECURE_AUTH_COOKIE ] = '';
        }
        if ( ! empty( $_COOKIE[ LOGGED_IN_COOKIE ] ) ) {
            $_COOKIE[ LOGGED_IN_COOKIE ] = '';
        }
    }

    // ----------------------------------------------------------------------------------------------------------------
    // Redirect canonical

    function redirect_canonical( $redirect_url ) {
        if ( isset( $_GET['author'] ) ) {
            $this->write_syslog( "Blocked author enumeration on {$this->get_system_name()} from {$this->get_ip_address()}" );
            $this->forbidden();
        }

        return $redirect_url;
    }

    // ----------------------------------------------------------------------------------------------------------------
    // XMLRPC Pingback

    function xmlrpc_pingback() {
          $this->write_syslog( "XMLRPC pingback on {$this->get_system_name()} from {$this->get_ip_address()}" );
    }

    // ----------------------------------------------------------------------------------------------------------------
    // Helpers

    /**
     * Get ip address of client. We used to check the X-Forwarded-For header
     * here, but that can be spoofed by the client. Since our stack always
     * puts the real client IP in REMOTE_ADDR, we just use that directly.
     * @return string
     */
    function get_ip_address() {
        if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
            return $_SERVER['REMOTE_ADDR'];
        }
        return ''; // Failsafe
    }

    /**
     * Get system name of site, returns server name when not known
     * @return string
     */
    function get_system_name() {
        return Options::system_name();
    }

    /**
     * Generate a system log message in a specific log file
     * Messages are logged with priority LOG_INFO
     * @link http://php.net/manual/en/function.syslog.php
     * @param string $message As documented in \syslog
     * @return bool true on success or false on failure.
     */
    function write_syslog( $message ) {
        \openlog( 'warpdrive', LOG_NDELAY | LOG_PID , LOG_AUTH );
        return \syslog( LOG_INFO, $message );
    }

    /**
     * Clean buffer and send Forbidden headers
     * @codeCoverageIgnore
     */
    function forbidden() {
        ob_end_clean();
        header( 'HTTP/1.0 403 Forbidden' );
        header( 'Content-Type: text/plain' );
        exit( 'Forbidden' );
    }
}
