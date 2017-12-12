<?php

/**
 * Class CacheFlusherTest
 */
class CacheFlusherTest extends Warpdrive_UnitTestCase {

    function test_successful_flush_returns_true() {
        // Arrange
        $cf = $this->build_cache_flusher( true );
        // Act and assert
        $this->assertTrue( $cf->flush() );
    }

    function test_failed_flush_returns_false() {
        // Arrange
        $cf = $this->build_cache_flusher( false );
        // Act and assert
        $this->assertFalse( $cf->flush() );
    }

    function test_successful_flush_domain_returns_true() {
        // Arrange
        $domain  = 'example.org';
        $cf = $this->build_cache_flusher( true );
        // Act and assert
        $this->assertTrue( $cf->flush_domain( $domain ) );
    }

    function test_failed_flush_domain_returns_false() {
        // Arrange
        $domain  = 'example.org';
        $cf = $this->build_cache_flusher( false );
        // Act and assert
        $this->assertFalse( $cf->flush_domain( $domain ) );
    }

    function build_cache_flusher( $success ) {
        $response_code = $success ? 200 : 400;
        $wp_http = $this->getMock( 'WP_Http', [ 'request' ] );
        $wp_http->expects( $this->once() )
            ->method( 'request' )
            ->will(
                $this->returnValue( [ 'response' => [ 'code' => $response_code ] ] )
            );
        $cf = new \Savvii\CacheFlusher();

        $api = new \Savvii\Api();
        $this->setProtectedProperty( $api, 'http_client', $wp_http );
        $this->setProtectedProperty( $cf, 'api', $api );

        return $cf;
    }
}
