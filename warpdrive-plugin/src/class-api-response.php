<?php

namespace Savvii;

/**
* ApiResponse
*/
class ApiResponse {

    /**
     * Threshold for http response status code failure level
     * @private int failure_level
     */
    private $failure_level = 300;

    /**
     * API response array
     * @private array response
     */
    private $response;

    /**
     * Constructor
     */
    function __construct( $response = [] ) {
        $this->response = $response;
    }

    /**
     * Return the API response
     */
    function get_response() {
        return $this->response;
    }

    /**
     * Check if the API response was successfull
     */
    function success() {
        return is_array( $this->response ) && ( ! empty( $this->response['response']['code'] ) && $this->response['response']['code'] < $this->failure_level );
    }

    /**
     * Returns the body from the API call
     *
     * @return string
     */
    function get_body() {
        return (is_array($this->response) && array_key_exists('body', $this->response)) ? $this->response['body'] : '';
    }
}
