<?php

class SavviiSecurityPluginTest extends Warpdrive_UnitTestCase {

    // Ip address
    const IP = '127.0.0.1';

    /**
     * @var \Savvii\SecurityPlugin
     */
    private $security_plugin;
    /**
     * @var \Savvii\Security
     */
    private $security;

    function setUp() {
        parent::setUp();
        putenv( 'WARPDRIVE_SYSTEM_NAME=FooBar' );
        // Create SecurityPlugin mock
        $this->security_plugin = $this->getMockBuilder( 'Savvii\SecurityPlugin' )
            ->setMethods( [ 'FooBar' ] )
            ->getMock();

        // Create Security mock
        $this->security = $this->getMockBuilder( 'Savvii\Security' )
            ->setMethods( [ 'write_syslog', 'can_login_header_show_message', 'clear_auth_cookie', 'cookie_failed_log', 'forbidden', 'login_failed' ] )
            ->getMock();
        // Inject Security mock into SecurityPlugin mock
        $this->security_plugin->security = $this->security;
        // Set IP address
        $_SERVER['REMOTE_ADDR'] = self::IP;
    }

    function test_security_is_set() {
        $s = $this->getMockBuilder( 'Savvii\SecurityPlugin' )
            ->setMethods( [ 'foo' ] )
            ->getMock();
        $this->assertInstanceOf( '\Savvii\Security', $s->security );
    }

    // login_errors, clean_login_error_messages
    // ----------------------------------------------------------------------

    private function prepare_clem( $can_login_header_show_message, $has_send_credentials = null ) {
        $this->security->expects( $this->once() )
            ->method( 'can_login_header_show_message' )
            ->will( $this->returnValue( $can_login_header_show_message ) );
        if ( ! is_null( $has_send_credentials ) ) {
            $this->security->has_send_credentials = $has_send_credentials;
        }
    }

    function test_login_errors_returns_content() {
        $expected = 'Test';
        $this->prepare_clem( false );

        $this->assertEquals(
            $expected,
            apply_filters( 'login_errors', $expected )
        );
    }

    function test_login_errors_returns_errors_when_no_credentials_are_send() {
        // Prepare
        $messages = "Test1<br />\nTest2<br />\n";
        $expected = "Test1<br />\n<br />\nTest2";
        $this->prepare_clem( true, false );

        $this->assertEquals(
            $expected,
            apply_filters( 'login_errors', $messages )
        );
    }

    function test_login_errors_returns_error_when_no_credentials_are_send() {
        // Prepare
        $messages = "Test1<br />\n";
        $expected = "Test1<br />\n";
        $this->prepare_clem( true, false );

        $this->assertEquals(
            $expected,
            apply_filters( 'login_errors', $messages )
        );
    }

    function test_login_errors_strips_information_leak_when_credentials_are_send() {
        // Prepare
        $messages = "Test1<br />\n";
        $expected = '<strong>ERROR</strong>: Incorrect username or password';
        $this->prepare_clem( true, true );

        $this->assertEquals(
            $expected,
            apply_filters( 'login_errors', $messages )
        );
    }

    function test_login_errors_string_information_leak() {
        // Prepare
        $messages = "Test1<br />\nTest2<br />\n";
        $expected = '<strong>ERROR</strong>: Incorrect username or password';
        $this->prepare_clem( true, true );

        $this->assertContains(
            $expected,
            apply_filters( 'login_errors', $messages )
        );
    }

    // wp_authenticate, track_credentials_state
    // ----------------------------------------------------------------------

    function test_wp_authenticate_without_credentials() {
        do_action( 'wp_authenticate', null, null );
        $this->assertFalse( $this->security->has_send_credentials );
    }

    function test_wp_authenticate_with_credentials() {
        do_action( 'wp_authenticate', 'username', 'password' );
        $this->assertTrue( $this->security->has_send_credentials );
    }

    function test_wp_authenticate_with_only_username() {
        do_action( 'wp_authenticate', 'username', null );
        $this->assertFalse( $this->security->has_send_credentials );
    }

    function test_wp_authenticate_with_only_password() {
        do_action( 'wp_authenticate', null, 'password' );
        $this->assertFalse( $this->security->has_send_credentials );
    }

    // filter_authenticate
    // ----------------------------------------------------------------------

    function test_filter_authenticate_with_correct_user() {
        $user_id = self::factory()->user->create();
        $this->security->expects( $this->never() )
            ->method( 'login_failed' );

        $user = new WP_User( $user_id );
        $user_result = apply_filters( 'authenticate', $user, '', '' );
        $this->assertEquals( $user, $user_result );
    }

    function test_filter_authenticate_with_null_user() {
        $this->security->expects( $this->once() )
            ->method( 'login_failed' )
            ->with( 'FooBar' );

        apply_filters( 'authenticate', null, 'FooBar', '_' );
    }

    function test_filter_authenticate_with_wp_error() {
        $this->security->expects( $this->once() )
            ->method( 'login_failed' );

        $user = new WP_Error;
        $user_result = apply_filters( 'authenticate', $user, '', '' );
        $this->assertEquals( $user, $user_result );
    }

    function test_filter_authenticate_with_ignored_error() {
        $this->security->expects( $this->never() )
            ->method( 'login_failed' );

        $user = new WP_Error;
        $user->add( 'empty_username', '' );
        $user_result = apply_filters( 'authenticate', $user, '', '' );
        $this->assertEquals( $user, $user_result );
    }

    // wp_login_success, login_success
    // ----------------------------------------------------------------------

    function test_wp_login_success_writes_to_log() {
        $this->security->expects( $this->once() )
            ->method( 'write_syslog' )
            ->with( 'Authentication success on FooBar for Admin from 127.0.0.1' );
        do_action( 'wp_login', 'Admin' );
    }

    function test_wp_login_success_with_utf8_username() {
        $this->security->expects( $this->once() )
            ->method( 'write_syslog' )
            ->with( 'Authentication success on FooBar for Ædmįñ from 127.0.0.1' );
        do_action( 'wp_login', 'Ædmįñ' );
    }

    // auth_cookie_valid, cookie_success
    // ----------------------------------------------------------------------

    function test_acv_clears_meta() {
        // Prepare meta data
        $user_id = self::factory()->user->create();
        wp_set_current_user( $user_id );
        update_user_meta( $user_id, 'warpdrive_prev_cookie', 'cookie' );

        do_action( 'auth_cookie_valid', [], wp_get_current_user() );
        $this->assertCount( 0, get_user_meta( $user_id, 'warpdrive_prev_cookie' ) );
    }

    // auth_cookie_malformed, auth_cookie_bad_hash, auth_cookie_bad_username,
    // cookie_failed
    // ----------------------------------------------------------------------

    function test_acbh_clears_cookie() {
        $cookie = [];
        $this->security->expects( $this->once() )
            ->method( 'clear_auth_cookie' );

        do_action( 'auth_cookie_bad_hash', $cookie );
    }

    function test_acbh_logs_attempt() {
        $username = 'FooBar';
        $cookie   = [ 'username' => $username ];
        $this->security->expects( $this->once() )
            ->method( 'cookie_failed_log' )
            ->with( $username );

        do_action( 'auth_cookie_bad_hash', $cookie );
    }

    function test_acbh_logs_unknown_when_no_user_set() {
        $this->security->expects( $this->once() )
            ->method( 'cookie_failed_log' )
            ->with( 'Unknown' );

        do_action( 'auth_cookie_bad_hash', [] );
    }

    function test_acbh_sets_prev_cookie_on_valid_user() {
        $user_login = 'FooBar';
        $cookie = [ 'username' => $user_login ];

        $user_id = self::factory()->user->create( [ 'user_login' => $user_login ] );
        wp_set_current_user( $user_id );

        do_action( 'auth_cookie_bad_hash', $cookie );

        $this->assertEquals( $cookie, get_user_meta( $user_id, 'warpdrive_prev_cookie', true ) );
    }

    function test_acbh_does_not_log_on_prev_cookie() {
        $user_login = 'FooBar';
        $cookie = [ 'username' => $user_login ];

        $user_id = self::factory()->user->create( [ 'user_login' => $user_login ] );
        wp_set_current_user( $user_id );
        update_user_meta( $user_id, 'warpdrive_prev_cookie', $cookie );

        $this->security->expects( $this->once() )
            ->method( 'clear_auth_cookie' );
        $this->security->expects( $this->never() )
            ->method( 'cookie_failed_log' );

        do_action( 'auth_cookie_bad_hash', $cookie );
    }

    function test_acbu_logs() {
        $this->security->expects( $this->once() )
            ->method( 'cookie_failed_log' );

        do_action( 'auth_cookie_bad_username', [] );
    }

    // redirect_canonical, redirect_canonical
    // ----------------------------------------------------------------------

    function test_rc_normal_url() {
        $this->security->expects( $this->never() )
            ->method( 'write_syslog' );
        $this->security->expects( $this->never() )
            ->method( 'forbidden' );

        $expected = 'FooBar';
        $result   = apply_filters( 'redirect_canonical', $expected, $expected );

        $this->assertEquals( $expected, $result );
    }

    function test_rc_logs_on_numeric_author() {
        $this->security->expects( $this->once() )
            ->method( 'write_syslog' )
            ->with( $this->stringContains( 'Blocked author enumeration' ) );
        $this->security->expects( $this->once() )
            ->method( 'forbidden' );

        $_GET['author'] = 1;
        apply_filters( 'redirect_canonical', 'FooBar', 'FooBar' );
    }

    // xmlrpc pingback
    // ----------------------------------------------------------------------

    function test_xmlrpc_pingback() {
        $this->security->expects( $this->once() )
            ->method( 'write_syslog' )
            ->with( $this->stringContains( 'XMLRPC pingback on' ) );

        do_action( 'xmlrpc_call', 'pingback.ping' );
    }

    function test_xmlrpc_non_pingback() {
        $this->security->expects( $this->never() )
            ->method( 'write_syslog' );

        do_action( 'xmlrpc_call', 'login' );
    }
}
