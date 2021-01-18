<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Warpdrive
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
    $_tests_dir = '/tmp/wordpress-tests-lib';
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually add autoloader to prevent load_modules from Warpdrive
 */
spl_autoload_register( function( $class_name ) {
    if ( strpos( $class_name, 'Savvii\\' ) === 0 ) {
        $file = __DIR__ . str_replace( 'savvii\\', '/../src/class-', strtolower( preg_replace( '/([a-zA-Z])(?=[A-Z])/', '$1-', $class_name ) ) ) . '.php';
        if ( file_exists( $file ) ) {
            require $file;
        }
    } else if ( strpos( $class_name, 'Mock\\' ) === 0 ) {
        $file = __DIR__ . str_replace( 'mock\\', '/mock/class-', strtolower( preg_replace( '/([a-zA-Z])(?=[A-Z])/', '$1-', $class_name ) ) ) . '.php';
        if ( file_exists( $file ) ) {
            require $file;
        }
    }
});

/**
 * Override of WordPress pluggables
 */
// Override wp_clear_auth_cookie to prevent setcookie to send headers
if ( ! function_exists( 'wp_clear_auth_cookie' ) ) :
    function wp_clear_auth_cookie() {}
endif;

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
require __DIR__ . '/warpdrive-unittestcase.php';
require __DIR__ . '/../src/compatibility.php';

// WP <4.3 compatibility Fixes
if ( version_compare( $wp_version, '4.3', '<' ) ) {
    // Make sure we have a wp_scripts object
    // @codingStandardsIgnoreLine, we need to ignore because we set a global
    $GLOBALS['wp_scripts'] = new WP_Scripts();

    // Disable deprecation errors, fixes:
    // PasswordHash has a deprecated constructor
    error_reporting( E_ALL & ~E_DEPRECATED );
}
