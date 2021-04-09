<?php

/**
 * Class CacheFlusherSucuriTest
 */
class CacheFlusherSucuriTest extends Warpdrive_UnitTestCase {

    function test_successful_flush_returns_true() {
        // Arrange
        $cf = $this->build_cache_flusher( true );
        // Act and assert
        $this->assertTrue( $cf->flush() );
    }

    function test_failed_enabled_flush_returns_false() {
        // Arrange
        $cf = $this->build_cache_flusher( false );
        $this->setProtectedProperty($cf, 'inTest', true);
        $this->setProtectedProperty($cf, 'overrideIsEnabled', true);
        $this->setProtectedProperty($cf, 'overrideIsEnabledResult', true);

        // Act and assert
        $this->assertTrue( $cf->is_enabled());
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
        $this->setProtectedProperty($cf, 'inTest', true);
        $this->setProtectedProperty($cf, 'overrideIsEnabled', true);
        $this->setProtectedProperty($cf, 'overrideIsEnabledResult', true);

        // Act and assert
        $this->assertFalse( $cf->flush_domain( $domain ) );
    }

    function test_disabled_failed_flush_returns_true() {
        // Arrange
        $cf = $this->build_cache_flusher( false );
        $this->setProtectedProperty($cf, 'inTest', true);
        $this->setProtectedProperty($cf, 'overrideIsEnabled', true);
        $this->setProtectedProperty($cf, 'overrideIsEnabledResult', false);

        // Act and assert
        $this->assertTrue( $cf->flush() );
    }

    function test_disabled_failed_flush_domain_returns_true() {
        // Arrange
        $cf = $this->build_cache_flusher( false );
        $this->setProtectedProperty($cf, 'inTest', true);
        $this->setProtectedProperty($cf, 'overrideIsEnabled', true);
        $this->setProtectedProperty($cf, 'overrideIsEnabledResult', false);

        // Act and assert
        $this->assertTrue( $cf->flush_domain( 'example.com' ) );
    }

    /**
     * Check is_enabled() based on the status code
     */
    public function test_success_statuscode_is_enabled()
    {
        // Arrange
        $cf = $this->build_cache_flusher( true );
        $this->assertTrue($cf->is_enabled());
    }

    /**
     * Check is_enabled() (false) based on the status code
     */
    public function test_fail_statuscode_is_enabled()
    {
        // Arrange
        $cf = $this->build_cache_flusher( false );
        $this->assertFalse($cf->is_enabled());
    }

    /**
     * Check is_enabled() true based on the response
     */
    public function test_succesfull_enabled_response_is_enabled()
    {
        // Arrange
        $cf = $this->build_cache_flusher( true, true );
        $this->assertTrue($cf->is_enabled());
    }

    /**
     * Check is_enabled() false based on the response
     */
    public function test_fail_enabled_response_is_enabled()
    {
        // Arrange
        $cf = $this->build_cache_flusher( true, false );
        $this->assertFalse($cf->is_enabled());
    }

    function build_cache_flusher( $success, $enabled = true ) {
        $response_code = $success ? 200 : 400;
        $wp_http = $this->getMockBuilder( 'WP_Http' )
            ->setMethods( [ 'request' ] )
            ->getMock();
        $wp_http->method( 'request' )
            ->will(
                $this->returnValue( [
                    'response' => [
                        'code' => $response_code
                    ],
                    'body' => json_encode($enabled)
                ])
            );
        $cf = new \Savvii\CacheFlusherSucuri();

        $api = new \Savvii\Api();
        $this->setProtectedProperty( $api, 'http_client', $wp_http );
        $this->setProtectedProperty( $cf, 'api', $api );

        return $cf;
    }

}
