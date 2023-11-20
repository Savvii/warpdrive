<?php

use \Savvii\Options;
use \Savvii\CacheFlusherPlugin;

/**
 * Class CacheFlusherPluginTest
 */
class CacheFlusherPluginTest extends Warpdrive_UnitTestCase {

    function setUp() {
        parent::setUp();
        wp_set_current_user( 0 );
        $_REQUEST = [];
    }

    function tearDown() {
        parent::tearDown();
        unset( $GLOBALS['wp_admin_bar'] );
    }

    function test_init_sets_correct_actions_for_events_for_style_aggressive() {
        // Setup
        update_option( Options::CACHING_STYLE, CacheFlusherPlugin::CACHING_STYLE_AGRESSIVE );
        // Act
        $cfp = new CacheFlusherPlugin();
        // Assert
        $events = $cfp->register_events;
        foreach ( $events['agressive'] as $event ) {
            $this->assertTrue( $this->_action_added( $event ) );
        }
        foreach ( $events['normal'] as $event ) {
            $this->assertFalse( $this->_action_added( $event ) );
        }
    }

    function test_init_sets_correct_actions_for_events_for_style_normal() {
        // Setup
        update_option( Options::CACHING_STYLE, CacheFlusherPlugin::CACHING_STYLE_NORMAL );
        // Act
        $cfp = new CacheFlusherPlugin();
        // Assert
        $events = $cfp->register_events;
        foreach ( $events['agressive'] as $event ) {
            $this->assertTrue( $this->_action_added( $event ) );
        }
        foreach ( $events['normal'] as $event ) {
            $this->assertTrue( $this->_action_added( $event ) );
        }
    }

    function test_init_sets_no_actions_for_events_for_style_none() {
        // Setup
        update_option( Options::CACHING_STYLE, 'none' );
        // Act
        $cfp = new CacheFlusherPlugin();
        // Assert
        $events = $cfp->register_events;
        foreach ( $events['agressive'] as $event ) {
            $this->assertFalse( $this->_action_added( $event ) );
        }
        foreach ( $events['normal'] as $event ) {
            $this->assertFalse( $this->_action_added( $event ) );
        }
    }

    function test_sets_action_for_menu_bar() {
        $wp_admin_bar = $this->getMockBuilder( 'stdClass' )
            ->setMethods( ['add_menu'] )
            ->getMock();
        $wp_admin_bar->expects( $this->any() )
            ->method( 'add_menu' );
        // @codingStandardsIgnoreLine, we need to ignore because we set a global
        $GLOBALS['wp_admin_bar'] = $wp_admin_bar;
        $cfp = new CacheFlusherPlugin();
        do_action( 'admin_bar_menu', [ &$wp_admin_bar ] );
        $this->addToAssertionCount( 1 );
    }

    private $admin_bar_menu_flush_title_regex = '/^' . CacheFlusherPlugin::TEXT_FLUSH;
    private $admin_bar_menu_domainflush_title_regex = '/^' . CacheFlusherPlugin::TEXT_DOMAINFLUSH;

    function _test_admin_bar_add_menu_flush( $subject ) {
        return
            'warpdrive_cache_delete' === $subject['id']
            && preg_match( $this->admin_bar_menu_flush_title_regex, $subject['title'] );
    }

    function _test_admin_bar_add_menu_domainflush( $subject ) {
        return
            'warpdrive_sitecache_delete' === $subject['id']
            && preg_match( $this->admin_bar_menu_domainflush_title_regex, $subject['title'] );
    }

    function _test_admin_bar_add_menu_do_action() {
        $this->_setRole( 'administrator' );
        $call_amount = is_multisite() ? 2 : 1;
        $wp_admin_bar = $this->getMockBuilder( 'stdClass' )
            ->setMethods( ['add_menu'] )
            ->getMock();
        $wp_admin_bar->expects( $this->exactly( $call_amount ) )
            ->method( 'add_menu' )
            ->withConsecutive(
                [ $this->callback( [ $this, '_test_admin_bar_add_menu_flush' ] ) ],
                [ $this->callback( [ $this, '_test_admin_bar_add_menu_domainflush' ] ) ]
            );
        // @codingStandardsIgnoreLine, we need to ignore because we set a global
        $GLOBALS['wp_admin_bar'] = $wp_admin_bar;
        $cfp = new CacheFlusherPlugin();
        do_action( 'admin_bar_menu', [ &$wp_admin_bar ] );
    }

    function test_sets_flush_result_for_menu_bar() {
        $this->admin_bar_menu_flush_title_regex .= '$/';
        $this->admin_bar_menu_domainflush_title_regex .= '$/';

        $this->_test_admin_bar_add_menu_do_action();
    }

    function test_sets_flush_result_for_menu_bar_flush_failed() {
        $this->admin_bar_menu_flush_title_regex .= '.*' . CacheFlusherPlugin::TEXT_FLUSH_RESULT_FAILED . '.*$/';
        $this->admin_bar_menu_domainflush_title_regex .= '$/';
        $_REQUEST[ CacheFlusherPlugin::NAME_FLUSH_RESULT ] = CacheFlusherPlugin::NAME_FLUSH_RESULT_FAILED;

        $this->_test_admin_bar_add_menu_do_action();
    }

    function test_sets_flush_result_for_menu_bar_domainflush_failed() {
        $this->admin_bar_menu_flush_title_regex .= '$/';
        $this->admin_bar_menu_domainflush_title_regex .= '.*' . CacheFlusherPlugin::TEXT_FLUSH_RESULT_FAILED . '.*$/';
        $_REQUEST[ CacheFlusherPlugin::NAME_DOMAINFLUSH_RESULT ] = CacheFlusherPlugin::NAME_FLUSH_RESULT_FAILED;

        $this->_test_admin_bar_add_menu_do_action();
    }

    function test_sets_flush_result_for_menu_bar_flush_success() {
        $this->admin_bar_menu_flush_title_regex .= '.*' . CacheFlusherPlugin::TEXT_FLUSH_RESULT_SUCCESS . '.*$/';
        $this->admin_bar_menu_domainflush_title_regex .= '$/';
        $_REQUEST[ CacheFlusherPlugin::NAME_FLUSH_RESULT ] = CacheFlusherPlugin::NAME_FLUSH_RESULT_SUCCESS;

        $this->_test_admin_bar_add_menu_do_action();
    }

    function test_sets_flush_result_for_menu_bar_domainflush_success() {
        $this->admin_bar_menu_flush_title_regex .= '$/';
        $this->admin_bar_menu_domainflush_title_regex .= '.*' . CacheFlusherPlugin::TEXT_FLUSH_RESULT_SUCCESS . '.*$/';
        $_REQUEST[ CacheFlusherPlugin::NAME_DOMAINFLUSH_RESULT ] = CacheFlusherPlugin::NAME_FLUSH_RESULT_SUCCESS;

        $this->_test_admin_bar_add_menu_do_action();
    }

    function test_sets_flush_result_for_menu_bar_flush_unknown() {
        $this->admin_bar_menu_flush_title_regex .= '$/';
        $this->admin_bar_menu_domainflush_title_regex .= '$/';
        $_REQUEST[ CacheFlusherPlugin::NAME_FLUSH_RESULT ] = 'unknown';

        $this->_test_admin_bar_add_menu_do_action();
    }

    function test_sets_flush_result_for_menu_bar_domainflush_unknown() {
        $this->admin_bar_menu_flush_title_regex .= '$/';
        $this->admin_bar_menu_domainflush_title_regex .= '$/';
        $_REQUEST[ CacheFlusherPlugin::NAME_DOMAINFLUSH_RESULT ] = 'unknown';

        $this->_test_admin_bar_add_menu_do_action();
    }

    function test_not_show_flush_if_privilege_is_not_publish_posts() {
        $user_id = $this->factory()->user->create( [ 'role' => 'contributor' ] );
        wp_set_current_user( $user_id );
        $wp_admin_bar = $this->getMockBuilder( 'stdClass' )
            ->setMethods( ['add_menu'] )
            ->getMock();
        $wp_admin_bar->expects( $this->never() )
            ->method( 'add_menu' );
        // @codingStandardsIgnoreLine, we need to ignore because we set a global
        $GLOBALS['wp_admin_bar'] = $wp_admin_bar;
        $cfp = new CacheFlusherPlugin();
        do_action( 'admin_bar_menu', [ &$wp_admin_bar ] );
    }

    function test_not_shows_flush_if_user_not_admin() {
        $user_id = $this->factory()->user->create( [ 'role' => 'author' ] );
        wp_set_current_user( $user_id );
        $this->assertTrue( current_user_can( 'publish_posts' ) );
        $wp_admin_bar = $this->getMockBuilder( 'stdClass' )
            ->setMethods( ['add_menu'] )
            ->getMock();
        $wp_admin_bar->expects( $this->never() )
            ->method( 'add_menu' );
        // @codingStandardsIgnoreLine, we need to ignore because we set a global
        $GLOBALS['wp_admin_bar'] = $wp_admin_bar;
        $cfp = new CacheFlusherPlugin();
        do_action( 'admin_bar_menu', [ &$wp_admin_bar ] );
    }

    function test_on_non_widgets_page_widgets_notice_is_not_shown() {
        set_current_screen( 'dashboard' );
        $cfp = new CacheFlusherPlugin();
        ob_start();
        do_action( 'admin_notices' );
        $size = ob_get_length();
        ob_end_clean();
        $this->assertEquals( 0, $size );
    }

    function test_on_widgets_page_widgets_notice_is_shown() {
        set_current_screen( 'widgets' );
        $cfp = new CacheFlusherPlugin();
        ob_start();
        do_action( 'admin_notices' );
        $size = ob_get_length();
        ob_end_clean();
        $this->assertGreaterThan( 0, $size );
    }

    function test_flush_called_on_forced_flush() {
        // Setup
        $action = CacheFlusherPlugin::NAME_FLUSH_NOW;
        $_REQUEST[ $action ] = '1';
        $_REQUEST['_wpnonce'] = wp_create_nonce( $action );
        $_SERVER['HTTP_REFERER'] = admin_url();
        // Act
        $this->assertNotNull( $_REQUEST[ CacheFlusherPlugin::NAME_FLUSH_NOW ] );
        $this->assertEquals( 1, wp_verify_nonce( $_REQUEST['_wpnonce'], $action ) );
        $stub = $this->getMockBuilder( 'Savvii\CacheFlusherPlugin' )
            ->setMethods( ['flush'] )
            ->getMock();
        $stub->expects( $this->once() )
            ->method( 'flush' );
        $stub->init();
    }

    function test_domainflush_called_on_forced_flush() {
        // Setup
        $action = CacheFlusherPlugin::NAME_DOMAINFLUSH_NOW;
        $_REQUEST[ $action ] = '1';
        $_REQUEST['_wpnonce'] = wp_create_nonce( $action );
        $_SERVER['HTTP_REFERER'] = admin_url();
        // Act
        $this->assertNotNull( $_REQUEST[ CacheFlusherPlugin::NAME_DOMAINFLUSH_NOW ] );
        $this->assertEquals( 1, wp_verify_nonce( $_REQUEST['_wpnonce'], $action ) );
        $stub = $this->getMockBuilder( 'Savvii\CacheFlusherPlugin' )
            ->setMethods( ['domainflush'] )
            ->getMock();
        $stub->expects( $this->once() )
            ->method( 'domainflush' );
        $stub->init();
    }

    function test_flusher_returns_if_flushed_all_is_set() {
        $cfp = new CacheFlusherPlugin();
        $mock_flusher = $this->getMockBuilder( 'Savvii\CacheFlusher' )
            ->setMethods( ['flush'] )
            ->getMock()
            ->expects( $this->never() )
            ->method( 'flush' );
        $this->setProtectedProperty( $cfp, 'cache_flusher', $mock_flusher );
        $cfp->flushed_all = true;

        $cfp->flush();
    }

    function prepare_flush_result_and_safe_redirect( $flush_domain, $flush_result, $expect_safe_redirect, $expect_result ) {
        // Determine with method is used to flush
        $flush_method = $flush_domain ? 'flush_domain' : 'flush';
        // Determine which name is used for the result
        $name_result = $flush_domain ? CacheFlusherPlugin::NAME_DOMAINFLUSH_RESULT : CacheFlusherPlugin::NAME_FLUSH_RESULT;

        // Mock the flusher, let it return the requested result
        $flusher_mock = $this->getMockBuilder( 'stdClass' )
            ->setMethods( [ $flush_method ] )
            ->getMock();
        $flusher_mock->expects( $this->once() )
            ->method( $flush_method )
            ->will( $this->returnValue( $flush_result ) );

        // Mock the CacheFlusherPlugin, this way we can mock the flusher and check the calls to safe_redirect
        $cfp = $this->getMockBuilder( 'Savvii\CacheFlusherPlugin' )
            ->setMethods( [ 'safe_redirect' ] )
            ->getMock();

        // Add mocked flusher
        $this->setProtectedProperty( $cfp, 'cache_flusher', $flusher_mock );

        // Set expectation on safe_redirect, do we want to have it called or not?
        if ( $expect_safe_redirect ) {
            // We want to have safe_redirect called, check the redirect itself as well
            $cfp->expects( $this->once() )
                ->method( 'safe_redirect' )
                ->with( $this->equalTo( 'http://example.org/wp-admin/?' . $name_result . '=' . $expect_result ), $this->equalTo( 302 ) );
        } else {
            // We do not want safe_redirect to be called
            $cfp->expects( $this->never() )
                ->method( 'safe_redirect' );
        }

        return $cfp;
    }

    function expect_flush( $flush_result, $expect_safe_redirect, $expect_result = 'UNKONWN' ) {
        return $this->prepare_flush_result_and_safe_redirect( false, $flush_result, $expect_safe_redirect, $expect_result );
    }

    function expect_flush_domain( $flush_result, $expect_safe_redirect, $expect_result = 'UNKNOWN' ) {
        return $this->prepare_flush_result_and_safe_redirect( true, $flush_result, $expect_safe_redirect, $expect_result );
    }

    function test_flush_generates_no_redirect_if_not_admin() {
        set_current_screen( 'front' );
        $cfp = $this->expect_flush( false, false );
        $cfp->flush( true );
    }

    function test_flush_already_flushed_wont_redirect() {
        $cfp = $this->expect_flush( false, false );
        $cfp->flushed = true;
        $cfp->flush( true );
    }

    function test_flush_failure_generates_302_redirect_with_warning_if_admin() {
        set_current_screen( 'dashboard' );
        $cfp = $this->expect_flush( false, true, 'failed' );
        $cfp->flush( true );
    }

    function test_flush_failure_generates_no_redirect_if_not_admin() {
        set_current_screen( 'front' );
        $cfp = $this->expect_flush( false, false );
        $cfp->flush( true );
    }

    function test_flush_success_generates_302_redirect_with_success_if_admin() {
        set_current_screen( 'dashboard' );
        $cfp = $this->expect_flush( true, true, 'success' );
        $cfp->flush( true );
    }

    function test_flush_success_generates_no_redirect_if_not_admin() {
        set_current_screen( 'front' );
        $cfp = $this->expect_flush( true, false );
        $cfp->flush( true );
    }

    function test_flush_without_redirect_argument_generates_no_redirect() {
        set_current_screen( 'dashboard' );
        $cfp = $this->expect_flush( true, false );
        $cfp->flush();
    }

    function test_domainflush_generates_no_redirect_if_not_admin() {
        set_current_screen( 'front' );
        $cfp = $this->expect_flush_domain( true, false );
        $cfp->domainflush( true );
    }

    function test_domainflush_failing_request_generates_302_redirect_with_warning_if_admin() {
        set_current_screen( 'dashboard' );
        $cfp = $this->expect_flush_domain( false, true, 'failed' );
        $cfp->domainflush( true );
    }

    function test_domainflush_failing_request_generates_no_redirect_if_not_admin() {
        set_current_screen( 'front' );
        $cfp = $this->expect_flush_domain( false, false );
        $cfp->domainflush( true );
    }

    function test_domainflush_successful_request_generates_302_redirect_with_success_if_admin() {
        set_current_screen( 'dashboard' );
        $cfp = $this->expect_flush_domain( true, true, 'success' );
        $cfp->domainflush( true );
    }

    function test_domainflush_successful_request_generates_no_redirect_if_not_admin() {
        set_current_screen( 'front' );
        $cfp = $this->expect_flush_domain( true, false );
        $cfp->domainflush( true );
    }

    function test_domainflush_without_redirect_argument_generates_no_redirect() {
        set_current_screen( 'dashboard' );
        $cfp = $this->expect_flush_domain( false, false );
        $cfp->domainflush();
    }

    function test_get_default_style_returns_one_of_all_styles() {
        $styles = CacheFlusherPlugin::get_cache_styles();
        $this->assertGreaterThan( 0, count( $styles ) );
        $default = CacheFlusherPlugin::get_default_cache_style();
        $this->assertContains( $default, $styles );
    }

    function test_post_save_non_published_does_not_trigger_flush() {
        $this->assertFalse( $this->_action_added( 'clean_post_cache' ) );
        $mock = $this->getMockBuilder( 'Savvii\CacheFlusherPlugin' )
            ->setMethods( [ 'domainflush' ] )
            ->getMock();
        $this->assertTrue( $this->_action_added( 'clean_post_cache' ) );

        $mock->expects( $this->never() )
            ->method( 'domainflush' );

        $post_id = self::factory()->post->create( array( 'post_status' => 'concept' ) );
        do_action( 'clean_post_cache', $post_id, get_post( $post_id ) );
    }

    function test_post_save_published_triggers_flush() {
        $post_id = self::factory()->post->create( array( 'post_status' => 'publish' ) );
        $this->assertFalse( $this->_action_added( 'clean_post_cache' ) );
        $mock = $this->getMockBuilder( 'Savvii\CacheFlusherPlugin' )
            ->setMethods( [ 'domainflush' ] )
            ->getMock();
        $this->assertTrue( $this->_action_added( 'clean_post_cache' ) );

        $mock->expects( $this->once() )
            ->method( 'domainflush' );

        do_action( 'clean_post_cache', $post_id, get_post( $post_id ) );
    }

    function test_flush_on_publish_future_post() {
        $mock = $this->getMockBuilder( 'Savvii\CacheFlusherPlugin' )
            ->setMethods( [ 'domainflush' ] )
            ->getMock();
        $mock->expects( $this->once() )
            ->method( 'domainflush' );

        do_action( 'publish_future_post', [ null, null ] );
    }

    function test_flush_on_post_status_future_to_publish() {
        $post_id = self::factory()->post->create( array( 'post_status' => 'publish' ) );
        $mock = $this->getMockBuilder( 'Savvii\CacheFlusherPlugin' )
            ->setMethods( [ 'domainflush' ] )
            ->getMock();
        $mock->expects( $this->once() )
            ->method( 'domainflush' );

        do_action( 'transition_post_status', 'publish', 'future', get_post( $post_id ) );
    }

    function test_no_flush_on_post_status_other_than_future_to_publish() {
        $post_id = self::factory()->post->create( array( 'post_status' => 'publish' ) );
        $mock = $this->getMockBuilder( 'Savvii\CacheFlusherPlugin')
            ->setMethods( [ 'domainflush' ] )
            ->getMock();
        $mock->expects( $this->never() )
            ->method( 'domainflush' );

        do_action( 'transition_post_status', 'publish', 'concept', get_post( $post_id ) );
        do_action( 'transition_post_status', 'concept', 'future', get_post( $post_id ) );
    }

    function test_single_flush_on_multiple_triggers() {
        $post_id = self::factory()->post->create( array( 'post_status' => 'future' ) );
        $mock = $this->getMockBuilder( 'Savvii\CacheFlusher' )
            ->setMethods( [ 'flush_domain' ] )
            ->getMock();
        $mock->expects( $this->once() )
            ->method( 'flush_domain' );

        $cfp = new CacheFlusherPlugin();
        $this->setProtectedProperty( $cfp, 'cache_flusher', $mock );

        $this->assertFalse( $cfp->flushed_all );
        $this->assertEmpty( $cfp->flushed_domains );
        do_action( 'publish_future_post', $post_id );
        $this->assertFalse( $cfp->flushed_all );
        $this->assertEquals( $cfp->flushed_domains, [ 'example.org' ] );
        do_action( 'transition_post_status', 'publish', 'future', get_post( $post_id ) );
        $this->assertFalse( $cfp->flushed_all );
        $this->assertEquals( $cfp->flushed_domains, [ 'example.org' ] );
    }

    function test_domain_flush_after_flush_all_wont_run() {
        $mock_flusher = $this->getMockBuilder( 'Savvii\CacheFlusher' )
            ->setMethods( [ 'flush', 'flush_domain' ] )
            ->getMock();
        $mock_flusher->expects( $this->once() )
            ->method( 'flush' );
        $mock_flusher->expects( $this->never() )
            ->method( 'flush_domain' );

        $cfp = new CacheFlusherPlugin();
        $this->setProtectedProperty( $cfp, 'cache_flusher', $mock_flusher );

        $this->assertFalse( $cfp->flushed_all );
        $this->assertEmpty( $cfp->flushed_domains );

        do_action( 'warpdrive_cache_flush' );
        $this->assertTrue( $cfp->flushed_all );
        $this->assertEmpty( $cfp->flushed_domains );

        do_action( 'publish_future_post', [ null, null ] );
        $this->assertTrue( $cfp->flushed_all );
        $this->assertEmpty( $cfp->flushed_domains );
    }

    function test_domain_flush_after_flush_all_wont_run_with_old_action() {
        $mock_flusher = $this->getMockBuilder( 'Savvii\CacheFlusher' )
            ->setMethods( [ 'flush', 'flush_domain' ] )
            ->getMock();
        $mock_flusher->expects( $this->once() )
            ->method( 'flush' );
        $mock_flusher->expects( $this->never() )
            ->method( 'flush_domain' );

        $cfp = new CacheFlusherPlugin();
        $this->setProtectedProperty( $cfp, 'cache_flusher', $mock_flusher );

        $this->assertFalse( $cfp->flushed_all );
        $this->assertEmpty( $cfp->flushed_domains );

        do_action( 'savvii_cache_flush' );
        $this->assertTrue( $cfp->flushed_all );
        $this->assertEmpty( $cfp->flushed_domains );

        do_action( 'publish_future_post', [ null, null ] );
        $this->assertTrue( $cfp->flushed_all );
        $this->assertEmpty( $cfp->flushed_domains );
    }

    function test_flush_all_after_domain_flush_will_run() {
        $mock_flusher = $this->getMockBuilder( 'Savvii\CacheFlusher' )
            ->setMethods( [ 'flush', 'flush_domain' ] )
            ->getMock();
        $mock_flusher->expects( $this->once() )
            ->method( 'flush' );
        $mock_flusher->expects( $this->once() )
            ->method( 'flush_domain' );

        $cfp = new CacheFlusherPlugin();
        $this->setProtectedProperty( $cfp, 'cache_flusher', $mock_flusher );

        $this->assertFalse( $cfp->flushed_all );
        $this->assertEmpty( $cfp->flushed_domains );

        do_action( 'publish_future_post', [ null, null ] );
        $this->assertFalse( $cfp->flushed_all );
        $this->assertEquals( [ 'example.org' ], $cfp->flushed_domains );

        do_action( 'warpdrive_cache_flush' );
        $this->assertTrue( $cfp->flushed_all );
        $this->assertEquals( [ 'example.org' ], $cfp->flushed_domains );
    }

    function test_flush_all_after_domain_flush_will_run_with_old_action() {
        $mock_flusher = $this->getMockBuilder( 'Savvii\CacheFlusher' )
            ->setMethods( [ 'flush', 'flush_domain' ] )
            ->getMock();
        $mock_flusher->expects( $this->once() )
            ->method( 'flush' );
        $mock_flusher->expects( $this->once() )
            ->method( 'flush_domain' );

        $cfp = new CacheFlusherPlugin();
        $this->setProtectedProperty( $cfp, 'cache_flusher', $mock_flusher );

        $this->assertFalse( $cfp->flushed_all );
        $this->assertEmpty( $cfp->flushed_domains );

        do_action( 'publish_future_post', [ null, null ] );
        $this->assertFalse( $cfp->flushed_all );
        $this->assertEquals( [ 'example.org' ], $cfp->flushed_domains );

        do_action( 'savvii_cache_flush' );
        $this->assertTrue( $cfp->flushed_all );
        $this->assertEquals( [ 'example.org' ], $cfp->flushed_domains );
    }

    function test_flush_domain_runs_for_different_domains() {
        $mock_flusher = $this->getMockBuilder( 'Savvii\CacheFlusher' )
            ->setMethods( [ 'flush', 'flush_domain' ] )
            ->getMock();
        $mock_flusher->expects( $this->never() )
            ->method( 'flush' );
        $mock_flusher->expects( $this->exactly( 2 ) )
            ->method( 'flush_domain' );

        $cfp = new CacheFlusherPlugin();
        $this->setProtectedProperty( $cfp, 'cache_flusher', $mock_flusher );

        $this->assertFalse( $cfp->flushed_all );
        $this->assertEmpty( $cfp->flushed_domains );

        update_option( 'siteurl', 'http://site1.example.com' );
        do_action( 'warpdrive_domain_flush' );
        $this->assertFalse( $cfp->flushed_all );
        $this->assertEquals( [ 'site1.example.com' ], $cfp->flushed_domains );

        update_option( 'siteurl', 'http://site2.example.com' );
        do_action( 'warpdrive_domain_flush' );
        $this->assertFalse( $cfp->flushed_all );
        $this->assertEquals( [ 'site1.example.com', 'site2.example.com' ], $cfp->flushed_domains );
    }

    function test_flush_domain_runs_for_different_domains_with_old_action() {
        $mock_flusher = $this->getMockBuilder( 'Savvii\CacheFlusher' )
            ->setMethods( [ 'flush', 'flush_domain' ] )
            ->getMock();
        $mock_flusher->expects( $this->never() )
            ->method( 'flush' );
        $mock_flusher->expects( $this->exactly( 2 ) )
            ->method( 'flush_domain' );

        $cfp = new CacheFlusherPlugin();
        $this->setProtectedProperty( $cfp, 'cache_flusher', $mock_flusher );

        $this->assertFalse( $cfp->flushed_all );
        $this->assertEmpty( $cfp->flushed_domains );

        update_option( 'siteurl', 'http://site1.example.com' );
        do_action( 'savvii_domain_flush' );
        $this->assertFalse( $cfp->flushed_all );
        $this->assertEquals( [ 'site1.example.com' ], $cfp->flushed_domains );

        update_option( 'siteurl', 'http://site2.example.com' );
        do_action( 'savvii_domain_flush' );
        $this->assertFalse( $cfp->flushed_all );
        $this->assertEquals( [ 'site1.example.com', 'site2.example.com' ], $cfp->flushed_domains );
    }

    function test_flush_on_custom_trigger() {
        $mock = $this->getMockBuilder( 'Savvii\CacheFlusher' )
            ->setMethods( [ 'flush' ] )
            ->getMock();
        $mock->expects( $this->once() )
            ->method( 'flush' );

        $cfp = new CacheFlusherPlugin();
        $this->setProtectedProperty( $cfp, 'cache_flusher', $mock );

        $this->assertFalse( $cfp->flushed_all );
        do_action( 'warpdrive_cache_flush' );
        $this->assertTrue( $cfp->flushed_all );
    }

    function test_flush_on_custom_trigger_with_old_action() {
        $mock = $this->getMockBuilder( 'Savvii\CacheFlusher' )
            ->setMethods( [ 'flush' ] )
            ->getMock();
        $mock->expects( $this->once() )
            ->method( 'flush' );

        $cfp = new CacheFlusherPlugin();
        $this->setProtectedProperty( $cfp, 'cache_flusher', $mock );

        $this->assertFalse( $cfp->flushed_all );
        do_action( 'savvii_cache_flush' );
        $this->assertTrue( $cfp->flushed_all );
    }

    function test_domainflush_on_custom_trigger() {
        $mock = $this->getMockBuilder( 'Savvii\CacheFlusher' )
            ->setMethods( [ 'flush_domain' ] )
            ->getMock();
        $mock->expects( $this->once() )
            ->method( 'flush_domain' );

        $cfp = new CacheFlusherPlugin();
        $this->setProtectedProperty( $cfp, 'cache_flusher', $mock );

        $this->assertEmpty( $cfp->flushed_domains );
        do_action( 'warpdrive_domain_flush' );
        $this->assertEquals( [ 'example.org' ], $cfp->flushed_domains );
    }

    function test_domainflush_on_custom_trigger_with_old_action() {
        $mock = $this->getMockBuilder( 'Savvii\CacheFlusher' )
            ->setMethods( [ 'flush_domain' ] )
            ->getMock();
        $mock->expects( $this->once() )
            ->method( 'flush_domain' );

        $cfp = new CacheFlusherPlugin();
        $this->setProtectedProperty( $cfp, 'cache_flusher', $mock );

        $this->assertEmpty( $cfp->flushed_domains );
        do_action( 'savvii_domain_flush' );
        $this->assertEquals( [ 'example.org' ], $cfp->flushed_domains );
    }

    function test_get_flusher_constructs_a_cache_flusher() {
        $sd = new CacheFlusherPlugin();
        $this->assertInstanceOf( \Savvii\CacheFlusher::class, $this->getProtectedProperty( $sd, 'cache_flusher' ) );
    }
}
