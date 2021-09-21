<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'blog3' );

/** MySQL database username */
define( 'DB_USER', 'fonkoua' );

/** MySQL database password */
define( 'DB_PASSWORD', 'magboy17' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'l_]xvC[%}Vy/^j7vA`~PSR^+iZJ@GsY+YCS$k3Z,{.,4?OL!loMTDKbR9AxSK,<;' );
define( 'SECURE_AUTH_KEY',  '6ZFY|w3V4~c@TWr75?Oe!<P#N2&2KhB_PyCY8~5/TE[sd=<fKp/??bn:B>Yq{sDc' );
define( 'LOGGED_IN_KEY',    '%uzGTgZuhGF&iGrrDIgB;kU9_Q_bqGb|gLbY$Dzm.PN`g(CkEzJ{AydnQJ7UACQL' );
define( 'NONCE_KEY',        'p2 c+Q^vslIrpBb65d$VTp5|#$U2q=6s7C~H{X390?i$rhLZvE*n:|F7PV~Iv<%C' );
define( 'AUTH_SALT',        'z9fV3t3/U3bwf0u8;Q4oTga7ZHUU<#0P-_Y-a3}X4x.M^>7ESndFnXHCF:&/@q-|' );
define( 'SECURE_AUTH_SALT', '08rhj+b<cPD>^:Zc?QylgckB_THy^c`.}d^PxHmA6,tCi2OK@dfiN53!BFklBKxk' );
define( 'LOGGED_IN_SALT',   'JL7ig ezM[+SG;w*r{RM{6T?NQ XfF8!G>|V<B5D?I#]?g7 O]1=C&&[J8(B^NQd' );
define( 'NONCE_SALT',       'Q0x%=yyc,;RZOhRV=^})(SV8]@U%I{mRaJ<qtctPt$J3PZ|Fv4~9i9dLl_ya`-O>' );

/**#@-*/

/**
 * WordPress Database Table prefix.
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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
