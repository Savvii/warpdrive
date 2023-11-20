<?php

use \Savvii\Options;
use \Savvii\CacheFlusherPlugin;
use \Savvii\SavviiDashboard;

/**
 * Class SavviiSavviiDashboardTest
 */
class SavviiSavviiDashboardTest extends Warpdrive_UnitTestCase {

    const OPT_CACHE_STYLE    = Options::CACHING_STYLE;

    const FORM_CACHE_STYLE   = SavviiDashboard::FORM_CACHE_STYLE;
    const FORM_CACHE_DEFAULT   = SavviiDashboard::FORM_CACHE_DEFAULT;

    function setUp() {
        parent::setUp();
        $_REQUEST = [];
    }

    function test_construct_adds_dashboard_to_admin_bar_menu_at_top_position() {
        $this->_setRole( 'administrator' );

        $wp_admin_bar = $this->getMockBuilder( 'stdClass' )
            ->setMethods( [ 'add_menu' ] )
            ->getMock();
        $wp_admin_bar->expects( $this->exactly( 1 ) )
            ->method( 'add_menu' )
            ->withConsecutive(
                [ $this->callback( [ $this, '_test_admin_bar_add_warpdrive_top_menu' ] ) ]
            );
        $GLOBALS['wp_admin_bar'] = $wp_admin_bar;
        new SavviiDashboard();
        do_action( 'admin_bar_menu', [ &$wp_admin_bar ] );
        $this->addToAssertionCount( 1 );
    }

    function _test_admin_bar_add_warpdrive_top_menu( $subject ) {
        return 'warpdrive_top_menu' === $subject['id'];
    }

    function test_construct_adds_dashboard_to_admin_menu_at_top_position() {
        global $_wp_submenu_nopriv;
        // Create dashboard
        new SavviiDashboard();
        // Check actions set
        $this->assertTrue( $this->_action_added( 'admin_menu' ) );
        do_action( 'admin_menu' );
        $this->assertArrayHasKey('options-general.php', $_wp_submenu_nopriv);
        $this->assertArrayHasKey('warpdrive_dashboard', $_wp_submenu_nopriv['options-general.php']);
    }

    function test_warpdrive_dashboard_shows_caching_normal_when_option_does_not_exist() {
        // Create dashboard
        $sd = new SavviiDashboard();
        // Remove options
        delete_option( self::OPT_CACHE_STYLE );
        delete_site_option( self::OPT_CACHE_STYLE );
        // Request page
        ob_start();
        $sd->warpdrive_dashboard();
        $output = ob_get_clean();
        $this->assertContains( '<input type="hidden" name="warpdrive_cache_default" value="normal" />', $output, '', true );
    }

    function test_warpdrive_dashboard_shows_caching_agressive_when_option_is_set() {
        // Create dashboard
        $sd = new SavviiDashboard();
        update_option( self::OPT_CACHE_STYLE, CacheFlusherPlugin::CACHING_STYLE_AGRESSIVE );
        // Request page
        ob_start();
        $sd->warpdrive_dashboard();
        $output = ob_get_clean();
        $this->assertContains( '<option value="agressive" selected="selected">Flush on (custom) post/page edit or publish</option>', $output, '', true );
    }

    function test_maybe_update_cache_style_when_old_same_as_new() {
        // Setup
        $style = CacheFlusherPlugin::CACHING_STYLE_NORMAL;
        update_option( self::OPT_CACHE_STYLE, $style );
        $_POST[ self::OPT_CACHE_STYLE ] = $style;
        $_REQUEST['_wpnonce'] = wp_create_nonce( OPTIONS::CACHING_STYLE );
        // Dashboard
        $sd = new SavviiDashboard();
        $sd->maybe_update_caching_style();
        $this->assertEquals( $style, get_option( self::OPT_CACHE_STYLE ) );
    }

    /**
     * @expectedException WPDieException
     */
    function test_maybe_update_cache_style_nonce_failure() {
        // Setup
        $style = CacheFlusherPlugin::CACHING_STYLE_AGRESSIVE;
        update_option( self::OPT_CACHE_STYLE, $style );
        $_POST[ self::FORM_CACHE_STYLE ] = CacheFlusherPlugin::CACHING_STYLE_NORMAL;
        $_POST[ self::FORM_CACHE_DEFAULT ] = '';
        $_REQUEST['_wpnonce'] = 'failure';
        // Dashboard
        $sd = new SavviiDashboard();
        $sd->maybe_update_caching_style();
        $this->assertEquals( $style, get_option( self::OPT_CACHE_STYLE ) );
    }

    function test_maybe_update_cache_style_updates_style() {
        // Setup
        $old_style = CacheFlusherPlugin::CACHING_STYLE_NORMAL;
        $new_style = CacheFlusherPlugin::CACHING_STYLE_AGRESSIVE;
        update_option( self::OPT_CACHE_STYLE, $old_style );
        $_POST[ self::FORM_CACHE_STYLE ] = $new_style;
        $_POST[ self::FORM_CACHE_DEFAULT ] = '';
        $_REQUEST['_wpnonce'] = wp_create_nonce( Options::CACHING_STYLE );
        // Dashboard
        $sd = new SavviiDashboard();
        $sd->maybe_update_caching_style();
        $this->assertEquals( $new_style, get_option( self::OPT_CACHE_STYLE ) );
    }

    /**
     * @expectedException WPDieException
     */
    function test_update_custom_post_types_nonce_failure() {
        // Setup
        $post_types_enabled = array('movies' => true);
        $post_types_disabled = array('movies' => false);
        update_option(  Options::CACHING_CUSTOM_POST_TYPES , $post_types_disabled);
        $_POST[ Options::CACHING_CUSTOM_POST_TYPES ] = $post_types_enabled;
        $_POST[ SavviiDashboard::FORM_CACHE_DEFAULT ] = CacheFlusherPlugin::CACHING_STYLE_AGRESSIVE;
        $_POST[ SavviiDashboard::FORM_CACHE_STYLE ] = CacheFlusherPlugin::CACHING_STYLE_AGRESSIVE;
        $_REQUEST['_wpnonce'] = 'fail';
        // Dashboard
        $sd = new SavviiDashboard();
        $sd->update_custom_post_types();
        $this->assertEquals( $post_types_enabled, get_option( Options::CACHING_CUSTOM_POST_TYPES ) );
    }

    function test_update_custom_post_types() {
        // Setup
        $post_types_enabled = array('movies' => true);
        $post_types_disabled = array('movies' => false);
        update_option(  Options::CACHING_CUSTOM_POST_TYPES , $post_types_disabled);
        $_POST[ Options::CACHING_CUSTOM_POST_TYPES ] = $post_types_enabled;
        $_POST[ SavviiDashboard::FORM_CACHE_DEFAULT ] = CacheFlusherPlugin::CACHING_STYLE_AGRESSIVE;
        $_POST[ SavviiDashboard::FORM_CACHE_STYLE ] = CacheFlusherPlugin::CACHING_STYLE_AGRESSIVE;
        $_REQUEST['_wpnonce'] = wp_create_nonce( Options::CACHING_STYLE );
        // Dashboard
        $sd = new SavviiDashboard();
        $sd->update_custom_post_types();
        $this->assertEquals( $post_types_enabled, get_option( Options::CACHING_CUSTOM_POST_TYPES ) );
    }

    function test_warpdrive_dashboard_methods_post_not_empty_check() {
        $mock_methods = [
            'maybe_update_caching_style',
            'maybe_update_default_caching_style',
            'update_custom_post_types',
        ];

        $dash_mock = $this->getMockBuilder( 'Savvii\SavviiDashboard' )
            ->setMethods( $mock_methods )
            ->getMock();

        foreach ( $mock_methods as $method ) {
            $dash_mock->expects( $this->once() )
                ->method( $method )
                ->will( $this->returnValue( true ) );
        }

        $_POST = [ 'foo' => 'bar' ];

        ob_start();
        $dash_mock->warpdrive_dashboard();
        ob_get_clean();
    }

    function test_get_flusher_constructs_a_cache_flusher() {
        $sd = new SavviiDashboard();
        $this->assertInstanceOf( \Savvii\CacheFlusher::class, $this->getProtectedProperty( $sd, 'cache_flusher' ) );
    }
}
