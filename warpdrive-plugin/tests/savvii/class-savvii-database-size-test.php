<?php

use \Savvii\Options;
use \Savvii\DatabaseSizePlugin;

/**
 * Class SavviiDatabaseSizeTest
 */
class SavviiDatabaseSizeTest extends Warpdrive_UnitTestCase {

    function setUp() {
        parent::setUp();
        $_REQUEST = [];
    }

    function test_construct_adds_database_to_admin_bar_menu() {
        $this->_setRole( 'administrator' );

        $wp_admin_bar = $this->getMockBuilder( 'stdClass' )
            ->setMethods( [ 'add_menu' ] )
            ->getMock();
        $wp_admin_bar->method( 'add_menu' )
            ->withConsecutive(
                [ $this->callback( [ $this, '_test_admin_bar_add_warpdrive_database_menu' ] ) ]
            );
        $GLOBALS['wp_admin_bar'] = $wp_admin_bar;
        new DatabaseSizePlugin();
        do_action( 'admin_bar_menu', [ &$wp_admin_bar ] );
        $this->addToAssertionCount( 1 );
    }

    function _test_admin_bar_add_warpdrive_database_menu( $subject ) {
        return 'warpdrive_databaseinfo' === $subject['id'];
    }

    function test_construct_adds_databaseinfo_to_dashboard_menu() {
        global $_wp_submenu_nopriv;
        // Create dashboard
        new DatabaseSizePlugin();
        // Check actions set
        $this->assertTrue( $this->_action_added( 'admin_menu' ) );
        do_action( 'admin_menu' );
        $this->assertArrayHasKey('options-general.php', $_wp_submenu_nopriv);
        $this->assertArrayHasKey('warpdrive_dashboard', $_wp_submenu_nopriv['options-general.php']);
        $this->assertArrayHasKey('warpdrive_databaseinfo', $_wp_submenu_nopriv['warpdrive_dashboard']);
    }

    /**
     * @expectedException WPDieException
     */
    function test_databaseinfo_page_nonce_failure() {
        // Setup wrong nonce
        $_REQUEST['_wpnonce'] = wp_create_nonce('waprdrive_databaseinfo');

        // access page
        $dp = new DatabaseSizePlugin();
        $dp->databaseinfo_page();
    }

    function test_databaseinfo_page_nonce_success() {
        // Setup
        $_REQUEST['_wpnonce'] = wp_create_nonce('warpdrive_databaseinfo');

        // Database overview
        $dp = new DatabaseSizePlugin();
        ob_start();
        $dp->databaseinfo_page();
        $output = ob_get_clean();
        $this->assertContains('Database Information', $output);
    }
}
