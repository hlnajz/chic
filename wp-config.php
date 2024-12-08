<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', '' );

/** Database username */
define( 'DB_USER', '' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', '' );

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
define( 'AUTH_KEY',         'xdelvswiqfo4gff2lzprw5bvnzsprtbqzvlcam3cv0susxuimsuj3jqwgser5z49' );
define( 'SECURE_AUTH_KEY',  'lrac2zdlvjylaloqpm8x6eseuf0il3c1klyozaiuvs1qy7w1frnfvsb6tofeddzj' );
define( 'LOGGED_IN_KEY',    'qgychlkzvlsc7oyxzwucr9exn4njschdzk60eub6b8skhsbdovgwx47trhpnct0a' );
define( 'NONCE_KEY',        'zkzulklxvooucpvu5xjhg3vlnrblu6cawl0pthl9nq5biolsnj9qxklyn34iblfi' );
define( 'AUTH_SALT',        'cydrxl6bey01rzj7q3zlxfhyvp3agdc0h6tlxko1dguo36wzgchgmicfjhq3jvip' );
define( 'SECURE_AUTH_SALT', 'ymyfjhhjn8xwnlzjmwqtphrb1fmpaa6mm4b2cosypmwg0jyoo3okaaesaw6xlptk' );
define( 'LOGGED_IN_SALT',   'pwn4dqkwqkht1p3mar54q5brawj4lsasjo8hdpzhmddumfaac2hwoz2dxxdwygkr' );
define( 'NONCE_SALT',       'zokhxjtwble8lumljdkxlqj8x6obyfoyey7zxrjdwf8gsgr8aydkepyb6k021ekn' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wpy3_';

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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
