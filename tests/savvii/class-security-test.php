<?php

namespace Tests;

use \stdClass;
use \WP_Error;
use \Savvii\Options;
use \Savvii\Security;
use \Savvii\SecurityPlugin;

class SavviiSecurityTest extends \Warpdrive_UnitTestCase {

    // Ip address
    const IP = '127.0.0.1';
    const IP_PROXY = '127.0.0.2';

    /**
     * @var \Savvii\Security
     */
    private $security;

    function setUp() {
        parent::setUp();
        putenv( 'WARPDRIVE_SYSTEM_NAME=bar-baz' );
        $this->security = $this->getMockBuilder( 'Savvii\Security' )
            ->setMethods( [ 'write_syslog' ] )
            ->getMock();
    }

    /**
     * Setup the ip address
     */
    function setup_ip_address() {
        $_SERVER['REMOTE_ADDR'] = self::IP;
    }

    // can_login_header_show_message
    // ----------------------------------------------------------------------

    function test_clhsm_true_by_default() {
        $this->assertTrue( $this->security->can_login_header_show_message() );
    }

    function test_clhsm_false_for_reset_password() {
        $_GET['key'] = '123';
        $this->assertFalse( $this->security->can_login_header_show_message() );
    }

    function test_clhsm_false_for_password_actions() {
        // Lost password
        $_REQUEST['action'] = 'lostpassword';
        $this->assertFalse( $this->security->can_login_header_show_message() );
        // Retrieve password
        $_REQUEST['action'] = 'retrievepassword';
        $this->assertFalse( $this->security->can_login_header_show_message() );
        // Reset pass
        $_REQUEST['action'] = 'resetpass';
        $this->assertFalse( $this->security->can_login_header_show_message() );
        $_REQUEST['action'] = 'rp';
        $this->assertFalse( $this->security->can_login_header_show_message() );
        // Register
        $_REQUEST['action'] = 'register';
        $this->assertFalse( $this->security->can_login_header_show_message() );
    }

    // get_ip_address
    // ----------------------------------------------------------------------

    function test_gia_returns_string() {
        unset( $_SERVER['REMOTE_ADDR'] );
        $ip = $this->security->get_ip_address();
        $this->assertInternalType( 'string', $ip );
        $this->assertSame( '', $ip );
    }

    function test_gia_returns_direct_ip() {
        // Set ip
        $_SERVER['REMOTE_ADDR'] = self::IP;
        // Check if ip is returned
        $this->assertEquals( self::IP, $this->security->get_ip_address() );
    }

    // get_system_name
    // ----------------------------------------------------------------------
    function test_gsn_when_set() {
        $this->assertEquals( 'bar-baz', $this->security->get_system_name() );
    }

    function test_gsn_when_not_set() {
        $this->assertEquals( 'bar-baz', $this->security->get_system_name() );
    }

    // clear_auth_cookie
    // ----------------------------------------------------------------------

    function test_clear_auth_cookie() {
        // Setup cookie
        $_COOKIE[ AUTH_COOKIE ]        = 'non-empty';
        $_COOKIE[ SECURE_AUTH_COOKIE ] = 'non-empty';
        $_COOKIE[ LOGGED_IN_COOKIE ]   = 'non-empty';
        // Create object
        $this->security->clear_auth_cookie();
        // Check cookie
        $this->assertEmpty( $_COOKIE[ AUTH_COOKIE ] );
        $this->assertEmpty( $_COOKIE[ SECURE_AUTH_COOKIE ] );
        $this->assertEmpty( $_COOKIE[ LOGGED_IN_COOKIE ] );
    }

    // cookie_failed_log
    // ----------------------------------------------------------------------

    function test_cookie_failed_log_writes_syslog() {
        $this->security->expects( $this->once() )
            ->method( 'write_syslog' );

        $this->security->cookie_failed_log( '' );
    }

    function test_cookie_failed_log_uses_ip_address() {
        $this->security = $this->getMockBuilder( 'Savvii\Security' )
            ->setMethods( [ 'get_ip_address' ] )
            ->getMock();
        $this->security->expects( $this->once() )
            ->method( 'get_ip_address' );

        $this->security->cookie_failed_log( '' );
    }

    function test_cookie_failed_log_uses_username() {
        $expected = 'FooBar';
        $this->security->expects( $this->once() )
            ->method( 'write_syslog' )
            ->with( $this->stringContains( $expected ) );

        $this->security->cookie_failed_log( $expected );
    }
}
