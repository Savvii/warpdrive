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
        $this->read_logs = $this->getMock(
            '\Savvii\ReadLogs',
            [ 'clean_log_name', 'clean_lines', 'get_log_lines' ]
        );
        $this->plugin = new ReadLogsPlugin();
        $this->plugin->read_logs = $this->read_logs;
    }

    // __construct
    // ----------------------------------------------------------------------

    function test_construct_add_admin_menu_action() {
        global $submenu;
        $this->_setRole( 'administrator' );
        $this->assertTrue( $this->_action_added( 'admin_menu' ) );
        do_action( 'admin_menu' );

        // Test if there are submenu's registered for warpdrive_dashboard
        $this->assertTrue( isset( $submenu['warpdrive_dashboard'] ), 'Expected warpdrive_dashboard to exist in $submenu' );
        // Search the readlogs submenu
        $submenu_index = array_search( 'savvii_readlogs', array_column( $submenu['warpdrive_dashboard'], 2 ), true );
        $this->assertTrue( false !== $submenu_index, 'Expected submenu[warpdrive_dashboard] to contain savvii_readlogs' );
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
        $_REQUEST['_wpnonce'] = wp_create_nonce( 'savvii_readlogs' );
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
