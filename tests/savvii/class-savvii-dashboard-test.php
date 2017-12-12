<?php

use \Savvii\Options;
use \Savvii\CacheFlusherPlugin;
use \Savvii\SavviiDashboard;

/**
 * Class SavviiSavviiDashboardTest
 */
class SavviiSavviiDashboardTest extends Warpdrive_UnitTestCase {

    const OPT_CACHE_STYLE    = Options::CACHING_STYLE;
    const OPT_CDN_ENABLE     = Options::CDN_ENABLE;

    const FORM_CACHE_STYLE   = SavviiDashboard::FORM_CACHE_STYLE;
    const FORM_CACHE_DEFAULT   = SavviiDashboard::FORM_CACHE_DEFAULT;
    const FORM_CDN_ENABLE    = SavviiDashboard::FORM_CDN_ENABLE;
    const FORM_CDN_DEFAULT    = SavviiDashboard::FORM_CDN_DEFAULT;
    const FORM_CDN_HOME_URL  = SavviiDashboard::FORM_CDN_HOME_URL;

    function setUp() {
        parent::setUp();
        $_REQUEST = [];
    }

    function test_construct_adds_dashboard_to_admin_menu_at_top_position() {
        global $menu, $wp_styles;
        // Create dashboard
        $sd = new SavviiDashboard();
        // Check actions set
        $this->assertTrue( $this->_action_added( 'admin_menu' ) );
        $this->assertTrue( $this->_action_added( 'custom_menu_order' ) );
        $this->assertTrue( $this->_action_added( 'menu_order' ) );
        $this->assertTrue( apply_filters( 'custom_menu_order', false ) );
        // Check menu ordering
        $given    = [ 'menu_1', $sd::MENU_NAME, 'menu_2' ];
        $expected = [ $sd::MENU_NAME, 'menu_1', 'menu_2' ];
        $this->assertEquals( $expected, apply_filters( 'menu_order', $given ) );
        // Check menu page added
        do_action( 'admin_menu' );
        // Search the menu item
        $menu_index = array_search( $sd::MENU_NAME, array_column( $menu, 2 ), true );
        $this->assertTrue( false !== $menu_index, 'Expected menu to contain ' . $sd::MENU_NAME );
    }

    function test_page_dashboard_shows_caching_normal_when_option_does_not_exist() {
        // Create dashboard
        $sd = new SavviiDashboard();
        // Remove options
        delete_option( self::OPT_CACHE_STYLE );
        delete_site_option( self::OPT_CACHE_STYLE );
        // Request page
        ob_start();
        $sd->page_dashboard();
        $output = ob_get_clean();
        $this->assertContains( '<input type="hidden" name="savvii_cache_default" value="normal" />', $output, '', true );
    }

    function test_page_dashboard_shows_caching_agressive_when_option_is_set() {
        // Create dashboard
        $sd = new SavviiDashboard();
        update_option( self::OPT_CACHE_STYLE, CacheFlusherPlugin::CACHING_STYLE_AGRESSIVE );
        // Request page
        ob_start();
        $sd->page_dashboard();
        $output = ob_get_clean();
        $this->assertContains( '<option value="agressive" selected="selected">Flush on post/page edit or publish</option>', $output, '', true );
    }

    function test_page_dashboard_shows_cdn_disabled_when_option_does_not_exist() {
        // Create dashboard
        $sd = new SavviiDashboard();
        // Remove options
        delete_option( self::OPT_CDN_ENABLE );
        delete_site_option( self::OPT_CDN_ENABLE );
        // Request page
        ob_start();
        $sd->page_dashboard();
        $output = ob_get_clean();
        if ( is_multisite() ) {
            return $this->assertContains( '<input type="checkbox" name="savvii_cdn_use_default" checked="checked" />', $output, '', true );
        }
        $this->assertContains( '<input type="checkbox" name="savvii_cdn_enable"  />', $output, '', true );
    }

    function test_page_dashboard_shows_cdn_enabled_when_option_true() {
        // Create dashboard
        $sd = new SavviiDashboard();
        update_option( self::OPT_CDN_ENABLE, true );
        // Request page
        ob_start();
        $sd->page_dashboard();
        $output = ob_get_clean();
        $this->assertContains( '<input type="checkbox" name="savvii_cdn_enable" checked="checked" />', $output, '', true );
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

    function test_maybe_update_cdn_enable_when_old_same_as_new() {
        // Setup
        $value   = 'enable';
        $enabled = true;
        update_option( self::OPT_CDN_ENABLE, $enabled );
        $_POST[ self::FORM_CDN_ENABLE ] = $value;
        $_POST[ self::FORM_CDN_DEFAULT ] = '';
        $_REQUEST['_wpnonce'] = wp_create_nonce( Options::CDN_ENABLE );
        // Dashboard
        $sd = $this->prepare_dashboard_with_flusher( true );
        ob_start();
        $sd->maybe_update_cdn_enable();
        $output = ob_get_clean();
        $this->assertEquals( $enabled, get_option( self::OPT_CDN_ENABLE ) );
        $this->assertContains( 'saved and performed cache flush', $output, '', true );
    }

    /**
     * @expectedException WPDieException
     */
    function test_maybe_update_cdn_enable_nonce_failure() {
        // Setup
        $value   = 'enable';
        $enabled = false;
        update_option( self::OPT_CDN_ENABLE, $enabled );
        $_POST[ self::FORM_CDN_ENABLE ] = $value;
        $_POST[ self::FORM_CDN_DEFAULT ] = '';
        $_REQUEST['_wpnonce'] = 'failure';
        // Dashboard
        $sd = new SavviiDashboard();
        $sd->maybe_update_cdn_enable();
        $this->assertEquals( $enabled, get_option( self::OPT_CDN_ENABLE ) );
    }

    function prepare_dashboard_with_flusher( $result ) {
        $flusher_mock = $this->getMock( 'stdClass', [ 'flush' ] );
        $flusher_mock->expects( $this->once() )
            ->method( 'flush' )
            ->will( $this->returnValue( $result ) );

        $sd = new SavviiDashboard();
        $this->setProtectedProperty( $sd, 'cache_flusher', $flusher_mock );

        return $sd;
    }

    function test_maybe_update_cdn_enable_when_no_change() {
        // Setup
        $value   = true;
        update_option( self::OPT_CDN_ENABLE, $value );
        $action = Options::CDN_ENABLE;
        $_REQUEST['_wpnonce'] = wp_create_nonce( $action );
        // Dashboard
        $sd = new SavviiDashboard();
        ob_start();
        $sd->maybe_update_cdn_enable();
        $output = ob_get_clean();
        $this->assertEquals( $value, get_option( self::OPT_CDN_ENABLE ) );
        $this->assertEquals( '', $output );
    }

    function test_maybe_update_cdn_enable_when_enabling_and_flush_succeedes() {
        // Setup
        $old   = false;
        $new   = true;
        $value = 'enable';
        update_option( self::OPT_CDN_ENABLE, $old );
        $_POST[ self::FORM_CDN_ENABLE ] = $value;
        $_POST[ self::FORM_CDN_DEFAULT ] = '';
        $action = Options::CDN_ENABLE;
        $_REQUEST['_wpnonce'] = wp_create_nonce( $action );
        // Dashboard
        $sd = $this->prepare_dashboard_with_flusher( true );
        ob_start();
        $sd->maybe_update_cdn_enable();
        $output = ob_get_clean();
        $this->assertEquals( $new, get_option( self::OPT_CDN_ENABLE ) );
        $this->assertContains( 'saved and performed cache flush', $output, '', true );
    }

    function test_maybe_update_cdn_enable_when_enabling_and_flush_fails() {
        // Setup
        $old   = false;
        $new   = true;
        $value = 'enable';
        update_option( self::OPT_CDN_ENABLE, $old );
        $_POST[ self::FORM_CDN_ENABLE ] = $value;
        $_POST[ self::FORM_CDN_DEFAULT ] = '';
        $action = Options::CDN_ENABLE;
        $_REQUEST['_wpnonce'] = wp_create_nonce( $action );
        // Dashboard
        $sd = $this->prepare_dashboard_with_flusher( false );
        ob_start();
        $sd->maybe_update_cdn_enable();
        $output = ob_get_clean();
        $this->assertEquals( $new, get_option( self::OPT_CDN_ENABLE ) );
        $this->assertContains( 'saved but could not perform cache flush', $output, '', true );
    }

    function test_page_dashboard_with_cdn_and_caching_disabled() {
        update_option( self::OPT_CDN_ENABLE, 0 );
        update_option( self::OPT_CACHE_STYLE, 'normal' );

        $sd = new SavviiDashboard();
        ob_start();
        $sd->page_dashboard();
        $output = ob_get_clean();
        $this->assertContains( '<option value="normal" selected="selected">', $output, '', true );
        $this->assertContains( '<input type="checkbox" name="savvii_cdn_enable"  />', $output, '', true );
    }

    function test_page_dashboard_with_cdn_and_caching_enabled() {
        update_option( self::OPT_CDN_ENABLE, true );
        update_option( self::OPT_CACHE_STYLE, 'agressive' );

        $sd = new SavviiDashboard();
        ob_start();
        $sd->page_dashboard();
        $output = ob_get_clean();
        $this->assertContains( '<option value="agressive" selected="selected">', $output, '', true );
        $this->assertContains( '<input type="checkbox" name="savvii_cdn_enable" checked="checked" />', $output, '', true );
    }

    function test_page_dashboard_display_cdn_warning_on_ssl() {
        $_SERVER['HTTPS'] = 'on';

        $sd = new SavviiDashboard();
        ob_start();
        $sd->page_dashboard();
        $output = ob_get_clean();
        $this->assertContains( 'Our CDN does not work in combination with SSL.', $output, '', true );
    }

    function test_page_dashboard_methods_post_not_empty_check() {
        $mock_methods = [
            'maybe_update_caching_style',
            'maybe_update_cdn_enable',
            'maybe_update_default_caching_style',
            'maybe_update_default_cdn_enable',
        ];

        $dash_mock = $this->getMock( 'Savvii\SavviiDashboard', $mock_methods );
        foreach ( $mock_methods as $method ) {
            $dash_mock->expects( $this->once() )
                ->method( $method )
                ->will( $this->returnValue( true ) );
        }

        $_POST = [ 'foo' => 'bar' ];

        ob_start();
        $dash_mock->page_dashboard();
        ob_get_clean();
    }

    function test_get_flusher_constructs_a_cache_flusher() {
        $sd = new SavviiDashboard();
        $this->assertInstanceOf( \Savvii\CacheFlusher::class, $this->getProtectedProperty( $sd, 'cache_flusher' ) );
    }
}
