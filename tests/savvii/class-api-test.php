<?php

/**
 * Class APITest
 */
class APITest extends Warpdrive_UnitTestCase {

    function setUp() {
        putenv( 'WARPDRIVE_ACCESS_TOKEN=Foo42Bar' );
        putenv( 'WARPDRIVE_SYSTEM_NAME=FooBar' );
    }

    function test_varnish_cache_flush() {
        // Arrange
        $token   = 'Foo42Bar';
        $wp_http = $this->getMockBuilder( 'WP_Http' )
            ->setMethods( [ 'request' ] )
            ->getMock();
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
        $api->varnish_cache_flush();
        // Assert
    }

    function test_varnish_cache_flush_domain() {
        // Arrange
        $token   = 'Foo42Bar';
        $domain  = 'example.org';
        $wp_http = $this->getMockBuilder( 'WP_Http' )
            ->setMethods( [ 'request' ] )
            ->getMock();
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
                        'body' => '{"domains":["example.org"]}',
                    ]
                )
            );
        $api = new Savvii\Api();
        $this->setProtectedProperty( $api, 'http_client', $wp_http );
        // Act
        $api->varnish_cache_flush( $domain );
        // Assert
    }

    function test_varnish_cache_is_enabled() {
        // Arrange
        $token   = 'Foo42Bar';
        $wp_http = $this->getMockBuilder( 'WP_Http' )
            ->setMethods( [ 'request' ] )
            ->getMock();
        $wp_http->expects( $this->once() )
            ->method( 'request' )
            ->with(
                $this->equalTo( Savvii\Options::api_location() . "/v2/caches/{$token}" ),
                $this->equalTo(
                    [
                        'method' => 'GET',
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
        $api->varnish_cache_is_enabled();
        // Assert
    }

    function test_sucuri_cache_flush() {
        // Arrange
        $token   = 'Foo42Bar';
        $wp_http = $this->getMockBuilder( 'WP_Http' )
            ->setMethods( [ 'request' ] )
            ->getMock();
        $wp_http->expects( $this->once() )
            ->method( 'request' )
            ->with(
                $this->equalTo( Savvii\Options::api_location() . "/v2/sucuricaches/{$token}" ),
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
        $api->sucuri_cache_flush();
        // Assert
    }

    function test_sucuri_cache_flush_domain() {
        // Arrange
        $token   = 'Foo42Bar';
        $domain  = 'example.org';
        $wp_http = $this->getMockBuilder( 'WP_Http' )
            ->setMethods( [ 'request' ] )
            ->getMock();
        $wp_http->expects( $this->once() )
            ->method( 'request' )
            ->with(
                $this->equalTo( Savvii\Options::api_location() . "/v2/sucuricaches/{$token}" ),
                $this->equalTo(
                    [
                        'method' => 'DELETE',
                        'httpversion' => '1.1',
                        'sslverify' => true,
                        'headers' => [
                            'Authorization' => 'Token token="' . $token . '"',
                            'Content-Type' => 'application/json',
                        ],
                        'body' => '{"domains":["example.org"]}',
                    ]
                )
            );
        $api = new Savvii\Api();
        $this->setProtectedProperty( $api, 'http_client', $wp_http );
        // Act
        $api->sucuri_cache_flush( $domain );
        // Assert
    }

    function test_sucuri_cache_is_enabled() {
        // Arrange
        $token   = 'Foo42Bar';
        $wp_http = $this->getMockBuilder( 'WP_Http' )
            ->setMethods( [ 'request' ] )
            ->getMock();
        $wp_http->expects( $this->once() )
            ->method( 'request' )
            ->with(
                $this->equalTo( Savvii\Options::api_location() . "/v2/sucuricaches/{$token}" ),
                $this->equalTo(
                    [
                        'method' => 'GET',
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
        $api->sucuri_cache_is_enabled();
        // Assert
    }

    function test_warpdrive_api_has_an_http_client() {
        $token = 'Foo42Bar';

        $sa = new \Savvii\Api();
        $this->assertInstanceOf( \WP_Http::class, $this->getProtectedProperty( $sa, 'http_client' ) );
    }

    function test_warpdrive_api_has_an_token() {
        $token = 'Foo42Bar';

        $sa = new \Savvii\Api();
        $this->assertEquals( $token, $this->getProtectedProperty( $sa, 'token' ) );
    }
}
