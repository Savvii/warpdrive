<?php

namespace Savvii;

/**
 * Warpdrive class
 */
class Updater {
    const SLUG = 'warpdrive';
    const NAME = 'warpdrive/warpdrive.php';
    const DIR = WP_PLUGIN_DIR . '/' . Updater::SLUG;
    const FILE = WP_PLUGIN_DIR . '/' . Updater::NAME;
    const WP_VERSION_REQUIRED = '4.0';

    /**
     * Register the required filters for Warpdrive to overwrite specific WordPress update logic
     */
    protected function __construct() {
        // Hook into the site plugin update checks, this gets called once for every plugin the site has
        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'api_check' ) );
        // Hook into the plugin details screen
        add_filter( 'plugins_api', array( $this, 'get_plugin_info' ), 10, 3 );
        // Hook into the post install to rename the downloaded folder and activate the plugin again
        add_filter( 'upgrader_post_install', array( $this, 'upgrader_post_install' ), 10, 3 );
        // Set sslverify for zip download
        add_filter( 'http_request_args', array( $this, 'http_request_sslverify' ), 10, 2 );
    }

    /**
     * Instantiate the Updater
     */
    public static function create_instance() {
        new Updater();
    }

    /**
     * Add the sslverify flag to requests
     * @see https://developer.wordpress.org/reference/hooks/http_request_args/
     *
     * @param array $args http request arguments
     * @param string $url request url
     * @return array
     */
    public function http_request_sslverify( $args, $url ) {
        if ( 0 === strpos( $url, Options::REPO_LOCATION ) ) {
            $args['sslverify'] = true;
        }

        return $args;
    }

    /**
     * Check if there is an available update
     * @see http://hookr.io/filters/pre_set_site_transient_update_plugins/
     *
     * @param object $transient wp update transient
     * @return object
     */
    public function api_check( $transient ) {
        $new_release = $this->check_for_new_release();
        if ( ! empty( $new_release ) ) {
            $transient->response[ Updater::NAME ] = (object) [
                'new_version' => $new_release['tag_name'],
                'slug' => Updater::SLUG,
                'plugin' => Updater::NAME,
                'url' => Options::REPO_LOCATION,
                'package' => Options::REPO_RELEASES_LATEST_ZIPBALL,
            ];
        }

        return $transient;
    }

    /**
     * Get the plugin info and overwrite the plugin information WordPress displays when the call is for the Warpdrive plugin
     * @see https://developer.wordpress.org/reference/hooks/plugins_api/
     *
     * @param mixed $result result object or array, or false if no override should take place
     * @param string $action the type of information being requested, possible values: query_plugins, plugin_information, hot_tags or hot_categories
     * @param object $args plugin api arguments
     * @return mixed
     */
    public function get_plugin_info( $result, $action, $args ) {
        // Check if this API call is for the right plugin
        if ( ! isset( $args->slug ) || Updater::SLUG !== $args->slug ) {
            return $result;
        }

        $latest_release = $this->get_latest_release();
        if ( ! $latest_release ) {
            return true;
        }

        $plugin_info = $this->get_plugin_data();

        return (object) [
            'slug' => Updater::SLUG,
            'name' => $plugin_info['Name'],
            'plugin_name' => Updater::SLUG,
            'version' => $latest_release['tag_name'],
            'author' => $plugin_info['Author'],
            'homepage' => Options::REPO_LOCATION,
            'requires' => Updater::WP_VERSION_REQUIRED,
            'downloaded' => 0,
            'last_updated' => $latest_release['published_at'],
            'sections' => [
                'description' => $plugin_info['Description'],
            ],
            'download_link' => Options::REPO_RELEASES_LATEST_ZIPBALL,
        ];
    }

    /**
     * Rename the downloaded folder to the plugin basename
     * @see https://developer.wordpress.org/reference/hooks/upgrader_post_install/
     *
     * @param bool $response installation response
     * @param array $hook_extra extra arguments passed to hooked filters
     * @param array $result installation result data
     * @return array
     */
    public function upgrader_post_install( $response, $hook_extra, $result ) {
        global $wp_filesystem;

        // Skip if not Warpdrive
        if (
            empty( $hook_extra ) ||
            !array_key_exists('plugin', $hook_extra) ||
            Updater::NAME !== $hook_extra['plugin']
        ) {
            return $result;
        }

        // Move & Activate
        $wp_filesystem->move( $result['destination'], Updater::DIR );
        $result['destination'] = Updater::DIR;
        $this->activate_plugin( Updater::FILE );

        return $result;
    }

    /**
     * Get the latest Warpdrive release from Github
     * @return array
     */
    private function get_latest_release() {
        $response = get_site_transient( 'warpdrive_github_api_response' );
        if ( ! empty( $response ) ) {
            return $response;
        }

        $response = $this->remote_get( Options::REPO_RELEASES_LOCATION );
        if ( ! is_array( $response ) || 200 < $response['response']['code'] ) {
            return [];
        }

        $body = json_decode( $response['body'], true );
        set_site_transient( 'warpdrive_github_api_response', $body, 60 * 60 * 6 );

        return $body;
    }

    /**
     * Check if there is a new Warpdrive version available
     * @return bool
     */
    private function check_for_new_release() {
        $new_release = $this->get_latest_release();
        $plugin_data = $this->get_plugin_data();

        if ( empty( $new_release ) ) {
            return false;
        }

        if ( ! empty( $new_release['tag_name'] ) && version_compare( $new_release['tag_name'], $plugin_data['Version'], '>' ) ) {
            return $new_release;
        }

        return false;
    }

    /**
     * Wrapper function for testing
     * Activate the plugin
     * @param string $plugin plugin slug
     * @return bool
     */
    protected function activate_plugin( $plugin ) {
        return activate_plugin( $plugin );
    }

    /**
     * Wrapper function for testing
     * Get the plugin data from the Warpdrive plugin file
     * @return array
     */
    protected function get_plugin_data() {
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

        return get_plugin_data( Updater::FILE );
    }

    /**
     * Wrapper function for testing
     * Get the remote data
     * @param string $url the url to request the data from
     * @return object
     */
    protected function remote_get( $url ) {
        return wp_remote_get( $url );
    }
}
