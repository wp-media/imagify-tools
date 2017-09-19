<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

/** --------------------------------------------------------------------------------------------- */
/** PHP ========================================================================================= */
/** --------------------------------------------------------------------------------------------- */

// SPL can be disabled on PHP 5.2.
if ( ! function_exists( 'spl_autoload_register' ) ) :
	$_wp_spl_autoloaders = array();

	/**
	 * Autoloader compatibility callback.
	 *
	 * @since  1.0
	 * @since  WP 4.6.0
	 * @source WordPress
	 *
	 * @param string $classname Class to attempt autoloading.
	 */
	function __autoload( $classname ) {
		global $_wp_spl_autoloaders;
		foreach ( $_wp_spl_autoloaders as $autoloader ) {
			if ( ! is_callable( $autoloader ) ) {
				// Avoid the extra warning if the autoloader isn't callable.
				continue;
			}

			call_user_func( $autoloader, $classname );

			// If it has been autoloaded, stop processing.
			if ( class_exists( $classname, false ) ) {
				return;
			}
		}
	}

	/**
	 * Registers a function to be autoloaded.
	 *
	 * @since  1.0
	 * @since  WP 4.6.0
	 * @source WordPress
	 *
	 * @throws Exception If the function to register is not callable.
	 *
	 * @param callable $autoload_function The function to register.
	 * @param bool     $throw             Optional. Whether the function should throw an exception
	 *                                    if the function isn't callable. Default true.
	 * @param bool     $prepend           Whether the function should be prepended to the stack.
	 *                                    Default false.
	 */
	function spl_autoload_register( $autoload_function, $throw = true, $prepend = false ) {
		if ( $throw && ! is_callable( $autoload_function ) ) {
			// String not translated to match PHP core.
			throw new Exception( 'Function not callable' );
		}

		global $_wp_spl_autoloaders;

		// Don't allow multiple registration.
		if ( in_array( $autoload_function, $_wp_spl_autoloaders, true ) ) {
			return;
		}

		if ( $prepend ) {
			array_unshift( $_wp_spl_autoloaders, $autoload_function );
		} else {
			$_wp_spl_autoloaders[] = $autoload_function;
		}
	}

	/**
	 * Unregisters an autoloader function.
	 *
	 * @since  1.0
	 * @since  WP 4.6.0
	 * @source WordPress
	 *
	 * @param callable $function The function to unregister.
	 * @return bool True if the function was unregistered, false if it could not be.
	 */
	function spl_autoload_unregister( $function ) {
		global $_wp_spl_autoloaders;
		foreach ( $_wp_spl_autoloaders as &$autoloader ) {
			if ( $autoloader === $function ) {
				unset( $autoloader );
				return true;
			}
		}

		return false;
	}

	/**
	 * Retrieves the registered autoloader functions.
	 *
	 * @since  1.0
	 * @since  WP 4.6.0
	 * @source WordPress
	 *
	 * @return array List of autoloader functions.
	 */
	function spl_autoload_functions() {
		return $GLOBALS['_wp_spl_autoloaders'];
	}
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
