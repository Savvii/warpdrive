<?php

/**
 * Class APIResponseTest
 */
class APIResponseTest extends Warpdrive_UnitTestCase {

    function test_response_get_response_equals_input() {
        $response = $this->build_response_object( true );

        $this->assertEquals( [ 'response' => [ 'code' => 200 ], 'body' => 'OK' ], $response->get_response() );
    }

    function test_response_success_returns_true() {
        $response = $this->build_response_object( true );

        $this->assertTrue( $response->success() );
    }

    function test_response_success_returns_false() {
        $response = $this->build_response_object( false );

        $this->assertFalse( $response->success() );
    }

    function test_response_get_body() {
        // create a body
        $bodyObject = new stdClass();
        $bodyObject->result = 'ok';
        $bodyJson = json_encode($bodyObject);

        $response = $this->build_response_object(true, $bodyJson);
        $this->assertEquals($bodyJson, $response->get_body());
    }

    function build_response_object( $success, $body ='OK' ) {
        $status_code = $success ? 200 : 400;

        return new \Savvii\ApiResponse( [ 'response' => [ 'code' => $status_code] , 'body' => $body ] );
    }

    function test_api_response_success_returns_false_with_wp_error() {
        $response = new \Savvii\ApiResponse( ( new WP_Error() ) );

        $this->assertFalse( $response->success() );
    }

}
