<?php

/**
 * Class CacheFlusherTest
 */
class CdnLinkRewriterPluginTest extends Warpdrive_UnitTestCase {

    function test_cdn_disabled_when_in_admin() {
        set_current_screen( 'dashboard' );
        $cp = new Savvii\CdnLinkRewriterPlugin();
        $this->assertEquals( false, $cp->can_ob() );
    }

    function test_cdn_allowed_when_not_in_admin() {
        set_current_screen( 'front' );
        $cp = new Savvii\CdnLinkRewriterPlugin();
        $this->assertEquals( true, $cp->can_ob() );
    }

    function test_cdn_allowed_on_normal_start() {
        $cp = new Savvii\CdnLinkRewriterPlugin();
        $this->assertTrue( $cp->can_ob() );
    }

    function test_construct_sets_and_calls_ob_actions_on_can_ob() {
        $stub = $this->getMock( 'Savvii\CdnLinkRewriterPlugin', [ 'start', 'end' ] );
        $stub->expects( $this->once() )
            ->method( 'start' );
        $stub->expects( $this->once() )
            ->method( 'end' );
        $this->assertTrue( $this->_action_added( 'wp' ) );
        $this->assertTrue( $this->_action_added( 'wp_footer' ) );
        do_action( 'wp' );
        do_action( 'wp_footer' );
    }

    function test_construct_sets_no_ob_actions_on_no_can_ob() {
        set_current_screen( 'dashboard' );
        $stub = $this->getMock( 'Savvii\CdnLinkRewriterPlugin', [ 'start', 'end' ] );
        $stub->expects( $this->never() )
            ->method( 'start' );
        $stub->expects( $this->never() )
            ->method( 'end' );
        $this->assertFalse( $this->_action_added( 'wp' ) );
        $this->assertFalse( $this->_action_added( 'wp_footer' ) );
        do_action( 'wp' );
        do_action( 'wp_footer' );
    }

    function test_process_called_when_page_rendered() {
        set_current_screen( 'front' );
        $stub = $this->getMock( 'Savvii\CdnLinkRewriterPlugin', [ 'process' ] );
        $stub->expects( $this->once() )
            ->method( 'process' )
            ->with( '' )
            ->will( $this->returnValue( '' ) );
        $this->expectOutputString( '' );
        do_action( 'wp' );
        do_action( 'wp_footer' );
    }

    function test_process_returns_string() {
        $cp  = new Savvii\CdnLinkRewriterPlugin();
        $str = 'This is a test string';
        $this->expectOutputString( $str );
        do_action( 'wp' );
        echo esc_html( $cp->process( $str ) );
        do_action( 'wp_footer' );
    }
}
