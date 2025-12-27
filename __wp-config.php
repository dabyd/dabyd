<?php
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
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'breathworkbcn' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

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
define( 'AUTH_KEY',          'G0g-g-bRO <Vv:(d!1yqW/~MU2cq1<QRsL=}S/aUJYy^NT|7XH7XTq6pGNMC[PB4' );
define( 'SECURE_AUTH_KEY',   '{E *;QiZt^F+t5<i5q&dO^E9G8P?ua{$qS<-GOEl/6^bW9-J84w30tdRF/&2P v<' );
define( 'LOGGED_IN_KEY',     'uT,65ECp6cuni90LT7u6GAgF>q$^VrfPn$g6s22.0-ntbv+^=}rF!gwOfdN(Lxt5' );
define( 'NONCE_KEY',         '9[=KWWJJC$SG4sf@BAvjXWgp@sLs@.O!tD#?mvNf4PJ>]WL=`YlF^.^,^vT3iub/' );
define( 'AUTH_SALT',         '~!b$j?{}3`&,>o@L{+7tpc.-jx&*i7tt<->U:C [<o+Mz]og(Uy8_-(;:1T C?*}' );
define( 'SECURE_AUTH_SALT',  '[T7mMJU#NaQ1kChV=<YQ)pL~bodLw<[9;yT!-3{ZV3S7vhRuQk,),s6M:tb4g3zy' );
define( 'LOGGED_IN_SALT',    'kN#b)9WNBXKwO4Z~41Ea.mFaGdiB406$S4k4fDMp=o,[,~NG2&c0>M!*.7I|WwFh' );
define( 'NONCE_SALT',        'ikdgpFH2oirv82;:FzWxiD0Nl77ND/n+5WZ<5>l90[BOlG&ujf`,A!M>,!cTj:3$' );
define( 'WP_CACHE_KEY_SALT', '}iki[MS3S!5kv3[+h,pX+ I=,^UUmTruqqxQ]#wFC:M|UxF~(5qPM#k&HR)Jh3[.' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



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
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', true );
}

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
