<?php

/**
 * Class APITest
 */
class APITest extends Warpdrive_UnitTestCase {

    function setUp() {
        putenv( 'WARPDRIVE_ACCESS_TOKEN=Foo42Bar' );
        putenv( 'WARPDRIVE_SYSTEM_NAME=FooBar' );
    }

    function test_cache_flush() {
        // Arrange
        $token   = 'Foo42Bar';
        $wp_http = $this->getMock( 'WP_Http', [ 'request' ] );
        $wp_http->expects( $this->once() )
            ->method( 'request' )
            ->with(
                $this->equalTo( Savvii\Options::api_location() . "/v2/caches/{$token}" ),
                $this->equalTo(
                    [
                        'method' => 'DELETE',
                        'httpversion' => '1.1',
                        'sslverify' => true,
                        'headers' => [
                            'Authorization' => 'Token token="' . $token . '"',
                        ],
                    ]
                )
            );
        $api = new Savvii\Api();
        $this->setProtectedProperty( $api, 'http_client', $wp_http );
        // Act
        $api->cache_flush();
        // Assert
    }

    function test_cache_flush_domain() {
        // Arrange
        $token   = 'Foo42Bar';
        $domain  = 'example.org';
        $wp_http = $this->getMock( 'WP_Http', [ 'request' ] );
        $wp_http->expects( $this->once() )
            ->method( 'request' )
            ->with(
                $this->equalTo( Savvii\Options::api_location() . "/v2/caches/{$token}" ),
                $this->equalTo(
                    [
                        'method' => 'DELETE',
                        'httpversion' => '1.1',
                        'sslverify' => true,
                        'headers' => [
                            'Authorization' => 'Token token="' . $token . '"',
                            'Content-Type' => 'application/json',
                        ],
                        'body' => '{"domains":["example.org","cdn.FooBar.savviihq.com"]}',
                    ]
                )
            );
        $api = new Savvii\Api();
        $this->setProtectedProperty( $api, 'http_client', $wp_http );
        // Act
        $api->cache_flush( $domain );
        // Assert
    }

    function test_savvii_api_has_an_http_client() {
        $token = 'Foo42Bar';

        $sa = new \Savvii\Api();
        $this->assertInstanceOf( \WP_Http::class, $this->getProtectedProperty( $sa, 'http_client' ) );
    }

    function test_savvii_api_has_an_token() {
        $token = 'Foo42Bar';

        $sa = new \Savvii\Api();
        $this->assertEquals( $token, $this->getProtectedProperty( $sa, 'token' ) );
    }
}
