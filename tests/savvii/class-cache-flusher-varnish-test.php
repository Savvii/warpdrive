<?php

/**
 * Class CacheFlusherVarnishTest
 */
class CacheFlusherVarnishTest extends CacheFlusherSucuriTest {

    function build_cache_flusher( $success, $enabled = true ) {
        $response_code = $success ? 200 : 400;
        $wp_http = $this->getMockBuilder( 'WP_Http' )
            ->setMethods( [ 'request' ] )
            ->getMock();
        $wp_http->method( 'request' )
            ->will(
                $this->returnValue(
                    [
                        'response' => [
                            'code' => $response_code
                        ],
                        'body' => json_encode($enabled)
                    ] )
            );
        $cf = new \Savvii\CacheFlusherVarnish();

        $api = new \Savvii\Api();
        $this->setProtectedProperty( $api, 'http_client', $wp_http );
        $this->setProtectedProperty( $cf, 'api', $api );

        return $cf;
    }
}
