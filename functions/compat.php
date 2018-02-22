<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

/** --------------------------------------------------------------------------------------------- */
/** PHP ========================================================================================= */
/** --------------------------------------------------------------------------------------------- */

/**
 * Polyfill for the SPL autoloader. In PHP 5.2 (but not 5.3 and later), SPL can
 * be disabled, and PHP 7.2 raises notices if the compiler finds an __autoload()
 * function declaration. Function availability is checked here, and the
 * autoloader is included only if necessary.
 */
if ( ! function_exists( 'spl_autoload_register' ) ) :
	require_once IMAGIFY_TOOLS_FUNCTIONS_PATH . 'compat-spl-autoload.php';
endif;

/** --------------------------------------------------------------------------------------------- */
/** WORDPRESS =================================================================================== */
/** --------------------------------------------------------------------------------------------- */

if ( ! function_exists( 'wp_doing_ajax' ) ) :
	/**
	 * Determines whether the current request is a WordPress Ajax request.
	 *
	 * @since  1.0
	 * @since  WP 4.7.0
	 * @source WordPress
	 *
	 * @return bool True if it's a WordPress Ajax request, false otherwise.
	 */
	function wp_doing_ajax() {
		/**
		 * Filters whether the current request is a WordPress Ajax request.
		 *
		 * @since WP 4.7.0
		 *
		 * @param bool $wp_doing_ajax Whether the current request is a WordPress Ajax request.
		 */
		return apply_filters( 'wp_doing_ajax', defined( 'DOING_AJAX' ) && DOING_AJAX );
	}
endif;

if ( ! function_exists( 'wp_normalize_path' ) ) :

	/**
	 * Normalize a filesystem path.
	 *
	 * On windows systems, replaces backslashes with forward slashes
	 * and forces upper-case drive letters.
	 * Allows for two leading slashes for Windows network shares, but
	 * ensures that all other duplicate slashes are reduced to a single.
	 *
	 * @since  1.0
	 * @since  WP 3.9.0
	 * @since  WP 4.4.0 Ensures upper-case drive letters on Windows systems.
	 * @since  WP 4.5.0 Allows for Windows network shares.
	 * @source WordPress
	 *
	 * @param  string $path Path to normalize.
	 * @return string Normalized path.
	 */
	function wp_normalize_path( $path ) {
		$path = str_replace( '\\', '/', $path );
		$path = preg_replace( '|(?<=.)/+|', '/', $path );
		if ( ':' === substr( $path, 1, 1 ) ) {
			$path = ucfirst( $path );
		}
		return $path;
	}

endif;


if ( ! function_exists( 'wp_is_ini_value_changeable' ) ) :
	/**
	 * Determines whether a PHP ini value is changeable at runtime.
	 *
	 * @since  1.0
	 * @since  WP 4.6.0
	 * @source WordPress
	 *
	 * @link http://php.net/manual/en/function.ini-get-all.php
	 *
	 * @param (string) $setting The name of the ini setting to check.
	 *
	 * @return (bool) True if the value is changeable at runtime. False otherwise.
	 */
	function wp_is_ini_value_changeable( $setting ) {
		static $ini_all;

		if ( ! isset( $ini_all ) ) {
			$ini_all = false;
			// Sometimes `ini_get_all()` is disabled via the `disable_functions` option for "security purposes".
			if ( function_exists( 'ini_get_all' ) ) {
				$ini_all = ini_get_all();
			}
		}

		// Bit operator to workaround https://bugs.php.net/bug.php?id=44936 which changes access level to 63 in PHP 5.2.6 - 5.2.17.
		if ( isset( $ini_all[ $setting ]['access'] ) && ( INI_ALL === ( $ini_all[ $setting ]['access'] & 7 ) || INI_USER === ( $ini_all[ $setting ]['access'] & 7 ) ) ) {
			return true;
		}

		// If we were unable to retrieve the details, fail gracefully to assume it's changeable.
		if ( ! is_array( $ini_all ) ) {
			return true;
		}

		return false;
	}
endif;

/** --------------------------------------------------------------------------------------------- */
/** IMAGIFY ===================================================================================== */
/** --------------------------------------------------------------------------------------------- */

if ( ! function_exists( 'imagify_is_attachment_mime_type_supported' ) ) :
	/**
	 * Get all mime type which could be optimized by Imagify.
	 *
	 * @since  1.0
	 * @since  Imagify 1.3
	 * @author Grégory Viguier
	 * @source Imagify
	 *
	 * @return array $mime_type  The mime type.
	 */
	function get_imagify_mime_type() {
		return array(
			'image/jpeg',
			'image/png',
			'image/gif',
		);
	}
endif;

if ( ! function_exists( 'imagify_is_attachment_mime_type_supported' ) ) :
	/**
	 * Tell if an attachment has a supported mime type.
	 * Was previously Imagify_AS3CF::is_mime_type_supported() since 1.6.6.
	 * Ironically, this function is used in Imagify::is_mime_type_supported() since 1.6.9.
	 *
	 * @since  1.0
	 * @since  Imagify 1.6.8
	 * @author Grégory Viguier
	 * @source Imagify
	 *
	 * @param  int $attachment_id The attachment ID.
	 * @return bool
	 */
	function imagify_is_attachment_mime_type_supported( $attachment_id ) {
		static $is = array( false );

		$attachment_id = absint( $attachment_id );

		if ( isset( $is[ $attachment_id ] ) ) {
			return $is[ $attachment_id ];
		}

		$mime_types = get_imagify_mime_type();
		$mime_types = array_flip( $mime_types );
		$mime_type  = (string) get_post_mime_type( $attachment_id );

		$is[ $attachment_id ] = isset( $mime_types[ $mime_type ] );

		return $is[ $attachment_id ];
	}
endif;

if ( ! function_exists( 'imagify_get_filesystem' ) ) :
	/**
	 * Get WP Direct filesystem object. Also define chmod constants if not done yet.
	 *
	 * @since  1.0
	 * @since  Imagify 1.6.5
	 * @author Grégory Viguier
	 * @source Imagify
	 *
	 * @return object A `$wp_filesystem` object.
	 */
	function imagify_get_filesystem() {
		static $filesystem;

		if ( $filesystem ) {
			return $filesystem;
		}

		require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php' );
		require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php' );

		$filesystem = new WP_Filesystem_Direct( new StdClass() );

		// Set the permission constants if not already set.
		if ( ! defined( 'FS_CHMOD_DIR' ) ) {
			define( 'FS_CHMOD_DIR', ( fileperms( ABSPATH ) & 0777 | 0755 ) );
		}
		if ( ! defined( 'FS_CHMOD_FILE' ) ) {
			define( 'FS_CHMOD_FILE', ( fileperms( ABSPATH . 'index.php' ) & 0777 | 0644 ) );
		}

		return $filesystem;
	}
endif;
