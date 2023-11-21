<?php
define( 'WP_CACHE', true ); // Added by WP Rocket

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress' );

/** Database username */
define( 'DB_USER', 'wordpress' );

/** Database password */
define( 'DB_PASSWORD', 'wordpress' );

/** Database hostname */
define( 'DB_HOST', 'db' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'a221452a7d29045fb095ba2e54e2a2acfd2ea38bfafb2e50dc7ac3b370273546' );
define( 'SECURE_AUTH_KEY',  '8fd444006dd0f2b6f0e1895c93d8d2f11a630e13f1d7abc35c583ceeec7d66b3' );
define( 'LOGGED_IN_KEY',    '9f6b0594b91bd8108cf067c12d12e372376ce6829392696c7daa542395772521' );
define( 'NONCE_KEY',        '86217c8762a33709f6641f2cedc1b424c842cd778ab4d7505c17c88ff8ff5118' );
define( 'AUTH_SALT',        '4e5cdd602f759f9e4f48b83ef964748d558104bb1e2cb977f464a2687a334eeb' );
define( 'SECURE_AUTH_SALT', '72365554205874bbaf33121575576920ccada85a04b61695f85d88aaaf8c2994' );
define( 'LOGGED_IN_SALT',   '7459afcdb8ba7b85688c63ffabac1b60dd1e4a21b86c7a2e08f37d5f0797fbd3' );
define( 'NONCE_SALT',       '1aea37567ed0339b07151f6314045598f5db3a912d4fa5b7d326912ba04717cd' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', true );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
