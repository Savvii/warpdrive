<?php

/**
 * Class UpdaterTest
 */
class UpdaterTest extends Warpdrive_UnitTestCase {
    private $plugin_info_mock_data = array(
        'Name'        => 'Plugin Name',
        'PluginURI'   => 'Plugin URI',
        'Version'     => '2.0.0',
        'Description' => 'Description',
        'Author'      => 'Author',
        'AuthorURI'   => 'Author URI',
        'TextDomain'  => 'Text Domain',
        'DomainPath'  => 'Domain Path',
        'Network'     => 'Network',
        '_sitewide'   => 'Site Wide Only',
    );

    function test_register_filters_registers_filters() {
        Savvii\Updater::create_instance();
        $this->addToAssertionCount( 1 );
    }

    function test_http_request_sslverify_contains_location() {
        $updater = $this->instantiate_updater();

        $args = $updater->http_request_sslverify( [ 'sslverify' => false ], 'https://github.com/Savvii/warpdrive' );
        $this->assertTrue( $args['sslverify'] );
    }

    function test_http_request_sslverify_not_contains_location() {
        $updater = $this->instantiate_updater();

        $args = $updater->http_request_sslverify( [ 'sslverify' => false ], 'https://example.org' );
        $this->assertFalse( $args['sslverify'] );
    }

    function test_api_check_has_new_release() {
        wp_set_current_user( self::factory()->user->create( [
            'role' => 'administrator',
        ] ) );

        if ( is_multisite() ) {
            grant_super_admin( get_current_user_id() );
        }

        $repository_url = Savvii\Options::REPO_RELEASES_LOCATION;

        $updater = $this->getMockBuilder( 'UpdaterMock' )
            ->setMethods( [ 'remote_get', 'get_plugin_data' ] )
            ->getMock();
        $updater->expects( $this->once() )
            ->method( 'remote_get' )
            ->with( $this->equalTo( $repository_url ) )
            ->will( $this->returnValue( [
                    'response' => [ 'code' => 200 ],
                    'body' => '{"tag_name": "99.0.0", "published_at": "", "zipball_url": "https://github.com/Savvii/warpdrive/releases/latest/download/package.zip"}',
            ] ) );
        $updater->expects( $this->once() )
            ->method( 'get_plugin_data' )
            ->will( $this->returnValue( $this->plugin_info_mock_data )
            );

        $transient = new \stdClass();
        $transient->response = [];

        $transient = $updater->api_check( $transient );
        $this->assertFalse( empty( $transient->response ) );
    }

    function test_api_check_has_new_release_empty_response() {
        wp_set_current_user( self::factory()->user->create( [
            'role' => 'administrator',
        ] ) );

        if ( is_multisite() ) {
            grant_super_admin( get_current_user_id() );
        }

        $repository_url = Savvii\Options::REPO_RELEASES_LOCATION;

        $updater = $this->getMockBuilder( 'UpdaterMock' )
            ->setMethods( [ 'remote_get', 'get_plugin_data' ] )
            ->getMock();
        $updater->expects( $this->once() )
            ->method( 'remote_get' )
            ->with( $this->equalTo( $repository_url ) )
            ->will( $this->returnValue( null ) );
        $updater->expects( $this->once() )
            ->method( 'get_plugin_data' )
            ->will( $this->returnValue( $this->plugin_info_mock_data )
            );

        $transient = new \stdClass();
        $transient->response = [];

        $transient = $updater->api_check( $transient );
        $this->assertEquals( $transient->response, [] );
    }

    function test_api_check_has_no_new_release() {
        wp_set_current_user( self::factory()->user->create( [
            'role' => 'administrator',
        ] ) );

        if ( is_multisite() ) {
            grant_super_admin( get_current_user_id() );
        }

        $updater = $this->instantiate_updater_with_remote_get( '1.0.0' );

        $transient = new \stdClass();
        $transient->response = [];

        $transient = $updater->api_check( $transient );
        $this->assertTrue( empty( $transient->response ) );
    }

    function test_api_check_already_checked() {
        wp_set_current_user( self::factory()->user->create( [
            'role' => 'administrator',
        ] ) );

        if ( is_multisite() ) {
            grant_super_admin( get_current_user_id() );
        }

        $updater = $this->instantiate_updater_with_remote_get( '1.0.0' );

        $transient_checked = new \stdClass();
        $transient_checked->checked = [
            Savvii\Updater::NAME => '99.0.0',
        ];

        $transient = $updater->api_check( $transient_checked );
        $this->assertEquals( $transient, $transient_checked );
    }

    function test_api_check_has_transient() {
        wp_set_current_user( self::factory()->user->create( [
            'role' => 'administrator',
        ] ) );

        if ( is_multisite() ) {
            grant_super_admin( get_current_user_id() );
        }

        $transient_data = (object) [
            'response' => [
                Savvii\Updater::NAME => (object) [
                    'new_version' => '99.0.0',
                    'slug' => Savvii\Updater::SLUG,
                    'plugin' => Savvii\Updater::NAME,
                    'url' => 'https://github.com/Savvii/warpdrive',
                    'package' => 'https://github.com/Savvii/warpdrive/releases/latest/download/package.zip',
                ],
            ],
        ];

        $updater = $this->instantiate_updater( '1.0.0' );

        $api_response_data = [
            'tag_name' => '99.0.0',
            'published_at' => '',
            'zipball_url' => 'https://github.com/Savvii/warpdrive/releases/latest/download/package.zip',
        ];

        set_site_transient( 'warpdrive_github_api_response', $api_response_data );

        $transient_response = $updater->api_check( new stdClass );

        $this->assertEquals( $transient_response, $transient_data );
    }

    function test_get_plugin_info() {
        $updater = $this->instantiate_updater_with_remote_get();

        $response = new \stdClass;
        $response->slug = 'warpdrive';

        $updater_response = $updater->get_plugin_info( null, null, $response );

        $response = new \stdClass;
        $response->slug = 'warpdrive';
        $response->name = 'Plugin Name';
        $response->plugin_name = 'warpdrive';
        $response->version = '1.0.0';
        $response->author = 'Author';
        $response->homepage = 'https://github.com/Savvii/warpdrive';
        $response->requires = '4.0';
        $response->downloaded = '0';
        $response->last_updated = '';
        $response->sections = [
            'description' => 'Description',
        ];
        $response->download_link = 'https://github.com/Savvii/warpdrive/releases/latest/download/package.zip';

        $this->assertEquals( $response, $updater_response );
    }

    function test_get_plugin_info_no_release_response() {
        $updater = $this->instantiate_updater_with_remote_get( '1.0.0', 300 );

        $response = new \stdClass;
        $response->slug = 'warpdrive';

        $updater_response = $updater->get_plugin_info( null, null, $response );

        $this->assertTrue( $updater_response );
    }

    function test_get_plugin_info_assert_null() {
        $updater = $this->instantiate_updater();

        $args = new \stdClass;

        $this->assertEquals( $updater->get_plugin_info( null, null, $args ), null );
    }

    function test_upgrader_post_install() {
        global $wp_filesystem;

        $destination = Savvii\Updater::DIR;

        $wp_filesystem = $this->getMockBuilder( 'WP_Filesystem_Base' )
            ->setMethods( [ 'move' ] )
            ->getMock();
        $wp_filesystem->expects( $this->once() )
            ->method( 'move' )
            ->with(
                $this->equalTo( $destination ),
                $this->equalTo( $destination )
            );

        $updater = $this->instantiate_updater();
        $proper_destination = $updater->upgrader_post_install( true, [ 'plugin' => Savvii\Updater::NAME ], [ 'destination' => $destination ] );

        $this->assertEquals( [ 'destination' => $destination ] , $proper_destination );
    }

    private function instantiate_updater() {
        $updater = $this->getMockBuilder( 'UpdaterMock' )
            ->setMethods( [ 'get_plugin_data', 'activate_plugin' ] )
            ->getMock();
        $updater->expects( $this->any() )
            ->method( 'get_plugin_data' )
            ->will( $this->returnValue( $this->plugin_info_mock_data ) );
        $updater->expects( $this->any() )
            ->method( 'activate_plugin' )
            ->will( $this->returnValue( true ) );

        return $updater;
    }

    private function instantiate_updater_with_remote_get( $tag_name = '1.0.0', $response_code = 200 ) {
        $repository_url = Savvii\Options::REPO_RELEASES_LOCATION;

        $updater = $this->getMockBuilder( 'UpdaterMock' )
            ->setMethods( [ 'remote_get', 'get_plugin_data', 'activate_plugin' ] )
            ->getMock();
        $updater->expects( $this->once() )
            ->method( 'remote_get' )
            ->with( $this->equalTo( $repository_url ) )
            ->will( $this->returnValue( [
                    'response' => [ 'code' => $response_code ],
                    'body' => '{"tag_name": "' . $tag_name . '", "published_at": "", "zipball_url": "https://github.com/Savvii/warpdrive/archive/latest"}',
            ] ) );
        $updater->expects( $this->any() )
            ->method( 'get_plugin_data' )
            ->will( $this->returnValue( $this->plugin_info_mock_data ) );
        $updater->expects( $this->any() )
            ->method( 'activate_plugin' )
            ->will( $this->returnValue( true ) );

        return $updater;
    }
}

class UpdaterMock extends Savvii\Updater {
    public function __construct() {
        parent::__construct();
    }
}
