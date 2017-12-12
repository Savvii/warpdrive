<?php

use \Savvii\Options;
use \Savvii\SavviiDashboard;
use \Savvii\CacheFlusherPlugin;

/**
 * Class SavviiSavviiDashboardTestMultisite
 */
class SavviiSavviiDashboardTestMultisite extends Warpdrive_UnitTestCase {

    const OPT_CACHE_STYLE           = Options::CACHING_STYLE;
    const OPT_CDN_ENABLE            = Options::CDN_ENABLE;

    const FORM_CACHE_STYLE          = SavviiDashboard::FORM_CACHE_STYLE;
    const FORM_CACHE_USE_DEFAULT    = SavviiDashboard::FORM_CACHE_USE_DEFAULT;
    const FORM_CDN_ENABLE           = SavviiDashboard::FORM_CDN_ENABLE;
    const FORM_CDN_HOME_URL         = SavviiDashboard::FORM_CDN_HOME_URL;
    const FORM_CDN_USE_DEFAULT      = SavviiDashboard::FORM_CDN_USE_DEFAULT;

    function setUp() {
        parent::setUp();
        $_REQUEST = [];

        // Add a second site to test multisite
        $this->factory->blog->create();
    }

    function test_blog_is_added() {
        $blogs = get_sites();
        $this->assertCount( 2, $blogs );
    }

    function test_page_dashboard_display_log_options_for_super_admin() {
        wp_set_current_user( self::factory()->user->create( [
            'role' => 'administrator',
        ] ) );
        grant_super_admin( get_current_user_id() );

        $sd = new SavviiDashboard();
        ob_start();
        $sd->page_dashboard();
        $output = ob_get_clean();
        $this->assertContains( '<dt>Access log:</dt>', $output, '', true );
    }

    function test_page_dashboard_hide_log_options_for_non_super_admin() {
        wp_set_current_user( self::factory()->user->create( [
            'role' => 'administrator',
        ] ) );

        $sd = new SavviiDashboard();
        ob_start();
        $sd->page_dashboard();
        $output = ob_get_clean();
        $this->assertContains( '\'Read server logs\' shows data for all subsites.', $output, '', true );
    }

    function test_site_cache_settings_not_equal() {
        $blogs = get_sites();
        $this->sites_set_different_cache_site_options();
        $this->assertFalse( get_blog_option( $blogs[0]->blog_id, self::OPT_CACHE_STYLE ) === get_blog_option( $blogs[1]->blog_id, self::OPT_CACHE_STYLE ) );
    }

    function test_site_cdn_settings_not_equal() {
        $blogs = get_sites();
        $this->sites_set_different_cdn_site_options();
        $this->assertFalse( get_blog_option( $blogs[0]->blog_id, self::OPT_CDN_ENABLE ) === get_blog_option( $blogs[1]->blog_id, self::OPT_CDN_ENABLE ) );
    }

    function test_maybe_update_default_cache_normal() {
        // Setup
        $old_style = CacheFlusherPlugin::CACHING_STYLE_AGRESSIVE;
        $new_style = CacheFlusherPlugin::CACHING_STYLE_NORMAL;
        update_site_option( self::OPT_CACHE_STYLE, $old_style );
        $_POST[ SavviiDashboard::FORM_CACHE_SET_DEFAULT ] = $new_style;
        $_REQUEST['_wpnonce'] = wp_create_nonce( Options::CACHING_STYLE );
        // Dashboard
        $sd = new SavviiDashboard();
        $sd->maybe_update_default_caching_style();
        $this->assertEquals( $new_style, get_site_option( self::OPT_CACHE_STYLE ) );
    }

    function test_maybe_update_default_cache_agressive() {
        // Setup
        $old_style = CacheFlusherPlugin::CACHING_STYLE_NORMAL;
        $new_style = CacheFlusherPlugin::CACHING_STYLE_AGRESSIVE;
        update_site_option( self::OPT_CACHE_STYLE, $old_style );
        $_POST[ SavviiDashboard::FORM_CACHE_SET_DEFAULT ] = $new_style;
        $_REQUEST['_wpnonce'] = wp_create_nonce( Options::CACHING_STYLE );
        // Dashboard
        $sd = new SavviiDashboard();
        $sd->maybe_update_default_caching_style();
        $this->assertEquals( $new_style, get_site_option( self::OPT_CACHE_STYLE ) );
    }

    function test_maybe_update_default_cache_no_update() {
        // Setup
        $old_style = CacheFlusherPlugin::CACHING_STYLE_NORMAL;
        update_site_option( self::OPT_CACHE_STYLE, $old_style );
        $_POST[ SavviiDashboard::FORM_CACHE_SET_DEFAULT ] = null;
        $_REQUEST['_wpnonce'] = wp_create_nonce( Options::CACHING_STYLE );
        // Dashboard
        $sd = new SavviiDashboard();
        $sd->maybe_update_default_caching_style();
        $this->assertEquals( $old_style, get_site_option( self::OPT_CACHE_STYLE ) );
    }

    function test_maybe_update_default_cdn_disable() {
        // Setup
        $enabled = true;
        update_site_option( self::OPT_CDN_ENABLE, $enabled );
        $_POST[ SavviiDashboard::FORM_CACHE_SET_DEFAULT ] = '';
        $_REQUEST['_wpnonce'] = wp_create_nonce( Options::CACHING_STYLE );
        // Dashboard
        $sd = new SavviiDashboard();
        ob_start();
        $sd->maybe_update_default_cdn_enable();
        $output = ob_get_clean();
        $this->assertEmpty( get_site_option( self::OPT_CDN_ENABLE ) );
        $this->assertContains( 'saved but could not perform cache flush', $output, '', true );
    }

    function test_maybe_update_default_cdn_enable() {
        // Setup
        $value   = 'on';
        $enabled = false;
        update_site_option( self::OPT_CDN_ENABLE, $enabled );
        $_POST[ SavviiDashboard::FORM_CDN_SET_DEFAULT ] = $value;
        $_POST[ SavviiDashboard::FORM_CACHE_SET_DEFAULT ] = '';
        $_REQUEST['_wpnonce'] = wp_create_nonce( Options::CACHING_STYLE );
        // Dashboard
        $sd = new SavviiDashboard();
        ob_start();
        $sd->maybe_update_default_cdn_enable();
        $output = ob_get_clean();
        $this->assertTrue( get_site_option( self::OPT_CDN_ENABLE ) );
        $this->assertContains( 'saved but could not perform cache flush', $output, '', true );
    }

    function test_maybe_update_default_cdn_no_update() {
        // Setup
        $value   = 'on';
        $enabled = false;
        update_site_option( self::OPT_CDN_ENABLE, $enabled );
        $_POST[ SavviiDashboard::FORM_CDN_SET_DEFAULT ] = $value;
        $_POST[ SavviiDashboard::FORM_CACHE_SET_DEFAULT ] = null;
        $_REQUEST['_wpnonce'] = wp_create_nonce( Options::CACHING_STYLE );
        // Dashboard
        $sd = new SavviiDashboard();
        ob_start();
        $sd->maybe_update_default_cdn_enable();
        $output = ob_get_clean();
        $this->assertFalse( get_site_option( self::OPT_CDN_ENABLE ) );
    }

    function test_maybe_update_cdn_to_use_default() {
        // Setup
        update_site_option( self::OPT_CDN_ENABLE, true );
        $_POST[ SavviiDashboard::FORM_CDN_USE_DEFAULT ] = 'on';
        $_POST[ SavviiDashboard::FORM_CDN_DEFAULT ] = 'on';
        $_REQUEST['_wpnonce'] = wp_create_nonce( Options::CDN_ENABLE );
        // Dashboard
        $sd = new SavviiDashboard();
        ob_start();
        $sd->maybe_update_cdn_enable();
        $output = ob_get_clean();
        $this->assertTrue( get_site_option( self::OPT_CDN_ENABLE ) );
        $this->assertEquals( null, get_option( self::OPT_CDN_ENABLE, null ) );
        $this->assertContains( 'saved but could not perform cache flush', $output, '', true );
    }

    function test_maybe_update_cdn_to_not_use_default() {
        // Setup
        update_site_option( self::OPT_CDN_ENABLE, true );
        $_POST[ SavviiDashboard::FORM_CDN_ENABLE ] = 'on';
        $_POST[ SavviiDashboard::FORM_CDN_DEFAULT ] = 'on';
        $_REQUEST['_wpnonce'] = wp_create_nonce( Options::CDN_ENABLE );
        // Dashboard
        $sd = new SavviiDashboard();
        ob_start();
        $sd->maybe_update_cdn_enable();
        $output = ob_get_clean();
        $this->assertTrue( get_site_option( self::OPT_CDN_ENABLE ) );
        $this->assertEquals( true, get_option( self::OPT_CDN_ENABLE ) );
        $this->assertContains( 'saved but could not perform cache flush', $output, '', true );
    }

    function test_maybe_update_cache_to_use_default() {
        // Setup
        update_site_option( self::OPT_CACHE_STYLE, true );
        $_POST[ SavviiDashboard::FORM_CACHE_DEFAULT ] = '';
        $_POST[ SavviiDashboard::FORM_CACHE_USE_DEFAULT ] = 'on';
        $_REQUEST['_wpnonce'] = wp_create_nonce( Options::CACHING_STYLE );
        // Dashboard
        $sd = new SavviiDashboard();
        $sd->maybe_update_caching_style();

        $this->assertTrue( get_site_option( self::OPT_CACHE_STYLE ) );
        $this->assertEquals( null, get_option( self::OPT_CACHE_STYLE, null ) );
    }

    function test_maybe_update_cache_to_not_use_default() {
        // Setup
        $cache_style = CacheFlusherPlugin::CACHING_STYLE_AGRESSIVE;
        $cache_style_global = CacheFlusherPlugin::CACHING_STYLE_NORMAL;
        update_site_option( self::OPT_CACHE_STYLE, $cache_style_global );
        $_POST[ SavviiDashboard::FORM_CACHE_STYLE ] = $cache_style;
        $_POST[ SavviiDashboard::FORM_CACHE_DEFAULT ] = '';
        $_REQUEST['_wpnonce'] = wp_create_nonce( Options::CACHING_STYLE );
        // Dashboard
        $sd = new SavviiDashboard();
        $sd->maybe_update_caching_style();

        $this->assertEquals( $cache_style_global, get_site_option( self::OPT_CACHE_STYLE ) );
        $this->assertEquals( $cache_style, get_option( self::OPT_CACHE_STYLE ) );
    }

    function sites_set_different_cache_site_options() {
        $blogs = get_sites();
        $blogs_count = count( $blogs );
        for ( $i = 0; $i < $blogs_count; $i++ ) {
            update_blog_option( $blogs[ $i ]->blog_id, self::OPT_CACHE_STYLE, ($i % 2 ? CacheFlusherPlugin::CACHING_STYLE_AGRESSIVE : CacheFlusherPlugin::CACHING_STYLE_NORMAL) );
        }
    }

    function sites_set_different_cdn_site_options() {
        $blogs = get_sites();
        $blogs_count = count( $blogs );
        for ( $i = 0; $i < $blogs_count; $i++ ) {
            update_blog_option( $blogs[ $i ]->blog_id, self::OPT_CDN_ENABLE, $i % 2 );
        }
    }

    function prepare_dashboard_with_flusher( $result, $method = 'flush' ) {
        $flusher_mock = $this->getMock( 'stdClass', [ $method ] );
        $flusher_mock->expects( $this->once() )
            ->method( $method )
            ->will( $this->returnValue( $result ) );

        $sd = new SavviiDashboard();
        $this->setProtectedProperty( $sd, 'cache_flusher', $flusher_mock );

        return $sd;
    }
}
