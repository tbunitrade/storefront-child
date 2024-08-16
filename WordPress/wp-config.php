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
define( 'DB_NAME', 'storefront' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

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
define( 'AUTH_KEY',         'hM03/k|ZzjUH(pB^6l^pVw6M,dHsST|t=pS)ea&K`O:!PT b6wYS0G+JHnAvVvq$' );
define( 'SECURE_AUTH_KEY',  '<Nb;b`L2Q)Z^ %R0=naRq554@Nq/Toi#} :T;M%rC]B_n|t)@Q!I {tNz_q_jGn~' );
define( 'LOGGED_IN_KEY',    '4m522:s^u2O1Yy7]F|SB0lJ%}qw]G:0<F R8Jfk<l.TlvBlCNv ]`:Fi@:/zt*Gj' );
define( 'NONCE_KEY',        '|T/be!K[+]K^P:mqYytutzijRIhHvc8h#4g#=qP>H~dGxeK:N`JwKdW$9;32ELm-' );
define( 'AUTH_SALT',        '%}zMU=xSN6CSR5o$:E/c@8ojcq6;#@T`gp]|%:gw9g_6y!zTAzC}n&/&~:B78*, ' );
define( 'SECURE_AUTH_SALT', 'psFLg tn<V2FLT.6Guzi#1vHxrw_uoW;]m~LDVH dM 7dBlzx%khabG%;UG`PRH2' );
define( 'LOGGED_IN_SALT',   'KKN~d7NRQ&BGYD]v%Pel9g>;q?NV$}C%-7wA~7pG5h]lq0$6MXCNhusNLZ6!$.*@' );
define( 'NONCE_SALT',       'W@UpmA,n;PgK,o/y6CS:<$`X)VZ8?q9;rXl!,YTqDOZ$A,xGc*G]jK}V:kv!&3J0' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'ch_';

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
//define( 'WP_DEBUG', false );

define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false); // Отключает отображение ошибок на экране


/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
