<?php

namespace Savvii;

/**
* Api
*/
class Api {

    /**
     * WP_Http instance for sending http requests
     * @protected WP_Http http_client
     */
    protected $http_client;

    /**
     * Token used for API authentication
     * @protected string token
     */
    protected $token;

    /**
     * Constructor
     */
    function __construct() {
        $this->http_client = new \WP_Http();
        $this->token = Options::access_token();
    }

    /**
     * Flush the cache of the specified domain, if no domain given it flushes the cache of all domains
     * @param string @domain Domain name
     */
    function cache_flush( $domain = '' ) {
        // Build the request
        $request = [
            'method' => 'DELETE',
        ];

        // If there is a domain, add it to the request
        if ( '' !== $domain ) {
            $request['headers']['Content-Type'] = 'application/json';
            $request['body'] = wp_json_encode( [
                'domains' => [
                    $domain,
                ],
            ] );
        }

        // Call API
        return $this->call_api( [
            'request' => $request,
            'api_route' => '/v2/caches/' . $this->token,
        ] );
    }

    /**
    * Make an API call to the given route
    * @param array $args Options
    */
    private function call_api( $args = [] ) {
        // Default request headers
        $request = [
            'httpversion' => '1.1',
            'sslverify' => true,
            'headers' => [
                'Authorization' => 'Token token="' . $this->token . '"',
            ],
        ];

        // Merge request args to base arguments
        $request = empty( $args['request'] ) ? $request : array_replace_recursive( $args['request'], $request );

        // Call API and return the response object
        return new ApiResponse( $this->http_client->request( Options::api_location() . $args['api_route'], $request ) );
    }
}
