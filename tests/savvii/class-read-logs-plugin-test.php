<?php

use \Savvii\ReadLogs;
use \Savvii\ReadLogsPlugin;

class ReadLogsPluginTest extends Warpdrive_UnitTestCase {

    /**
     * @var ReadLogs
     */
    private $read_logs;
    /**
     * @var ReadLogsPlugin
     */
    private $plugin;

    function setUp() {
        parent::setUp();
        $this->read_logs = $this->getMockBuilder( 'Savvii\ReadLogs' )
            ->setMethods([ 'clean_log_name', 'clean_lines', 'get_log_lines' ] )
            ->getMock();
        $this->plugin = new ReadLogsPlugin();
        $this->plugin->read_logs = $this->read_logs;
    }

    // __construct
    // ----------------------------------------------------------------------

    function test_construct_adds_dashboard_to_admin_bar_menu_at_top_position() {
        $this->_setRole( 'administrator' );

        $wp_admin_bar = $this->getMockBuilder( 'stdClass' )
            ->setMethods( [ 'add_menu' ] )
            ->getMock();
        $wp_admin_bar
            ->method( 'add_menu' )
            ->withConsecutive(
                [ $this->callback( [ $this, '_test_admin_bar_add_menu_warpdrive_access_log' ] ) ],
                [ $this->callback( [ $this, '_test_admin_bar_add_submenu_warpdrive_access_log_10_lines' ] ) ],
                [ $this->callback( [ $this, '_test_admin_bar_add_submenu_warpdrive_access_log_100_lines' ] ) ],
                [ $this->callback( [ $this, '_test_admin_bar_add_menu_warpdrive_error_log' ] ) ],
                [ $this->callback( [ $this, '_test_admin_bar_add_submenu_warpdrive_error_log_10_lines' ] ) ],
                [ $this->callback( [ $this, '_test_admin_bar_add_submenu_warpdrive_error_log_100_lines' ] ) ]
            );
        // @codingStandardsIgnoreLine, we need to ignore because we set a global
        $GLOBALS['wp_admin_bar'] = $wp_admin_bar;
        new ReadLogsPlugin();
        do_action( 'admin_bar_menu', [ &$wp_admin_bar ] );
        $this->addToAssertionCount( 1 );
    }

    function _test_admin_bar_add_menu_warpdrive_access_log( $subject ) {
        return 'warpdrive_top_menu' === $subject['parent'] && 'warpdrive_access_log' === $subject['id'];
    }

    function _test_admin_bar_add_submenu_warpdrive_access_log_10_lines( $subject ) {
        return 'warpdrive_access_log' === $subject['parent'] && 'warpdrive_access_log_10_lines' === $subject['id'];
    }

    function _test_admin_bar_add_submenu_warpdrive_access_log_100_lines( $subject ) {
        return 'warpdrive_access_log' === $subject['parent'] && 'warpdrive_access_log_100_lines' === $subject['id'];
    }

    function _test_admin_bar_add_menu_warpdrive_error_log( $subject ) {
        return 'warpdrive_top_menu' === $subject['parent'] && 'warpdrive_error_log' === $subject['id'];
    }

    function _test_admin_bar_add_submenu_warpdrive_error_log_10_lines( $subject ) {
        return 'warpdrive_error_log' === $subject['parent'] && 'warpdrive_error_log_10_lines' === $subject['id'];
    }

    function _test_admin_bar_add_submenu_warpdrive_error_log_100_lines( $subject ) {
        return 'warpdrive_error_log' === $subject['parent'] && 'warpdrive_error_log_100_lines' === $subject['id'];
    }

    function test_construct_add_admin_menu_action() {
        global $submenu;
        $this->_setRole( 'administrator' );
        $this->assertTrue( $this->_action_added( 'admin_menu' ) );
        do_action( 'admin_menu' );

        // Test if there are submenu's registered for warpdrive_dashboard
        $this->assertTrue( isset( $submenu['warpdrive_dashboard'] ), 'Expected warpdrive_dashboard to exist in $submenu' );
        // Search the readlogs submenu
        $submenu_index = array_search( 'warpdrive_readlogs', array_column( $submenu['warpdrive_dashboard'], 2 ), true );
        $this->assertTrue( false !== $submenu_index, 'Expected submenu[warpdrive_dashboard] to contain warpdrive_readlogs' );
    }

    // _g
    // ----------------------------------------------------------------------

    function test__g_from_get() {
        $_GET['Foo'] = 'Bar';
        $this->assertEquals( 'Bar', $this->plugin->_g( 'Foo' ) );
    }

    function test__g_default_null() {
        $this->assertFalse( isset( $_GET['Foo'] ) );
        $this->assertEquals( null, $this->plugin->_g( 'Foo' ) );
    }

    function test__g_default_passed() {
        $this->assertFalse( isset( $_GET['Foo'] ) );
        $this->assertEquals( 'Bar', $this->plugin->_g( 'Foo', 'Bar' ) );
    }

    // readlogs page
    // ----------------------------------------------------------------------

    function test_readlogs_page() {
        $_REQUEST['_wpnonce'] = wp_create_nonce( 'warpdrive_readlogs' );
        $_SERVER['HTTP_REFERER'] = admin_url();

        $this->read_logs->expects( $this->once() )
            ->method( 'clean_log_name' )
            ->with( $this->equalTo( 'error' ) )
            ->will( $this->returnValue( 'error' ) );
        $this->read_logs->expects( $this->once() )
            ->method( 'clean_lines' )
            ->with( $this->equalTo( 10 ) )
            ->will( $this->returnValue( 10 ) );
        $this->read_logs->expects( $this->once() )
            ->method( 'get_log_lines' )
            ->will( $this->returnValue( [ 'Line 1' ] ) );
        $this->expectOutputRegex( '~Line 1~' );

        $_GET['log']   = 'error';
        $_GET['lines'] = '10';
        $this->plugin->readlogs_page();
    }

    /**
     * @expectedException WPDieException
     */
    function test_readlogs_page_incorrect_nonce() {
        $_REQUEST['_wpnonce'] = 'failure';
        $_SERVER['HTTP_REFERER'] = admin_url();

        $_GET['log']   = 'error';
        $_GET['lines'] = '10';
        $this->plugin->readlogs_page();
    }
}
