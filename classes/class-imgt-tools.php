<?php
defined( 'ABSPATH' ) || die( 'Cheatin’ uh?' );

/**
 * Class that groups various generic helpers.
 *
 * @package Imagify Tools
 * @since   1.0.5
 * @author  Grégory Viguier
 */
class IMGT_Tools {

	/**
	 * Class version.
	 *
	 * @var string
	 */
	const VERSION = '1.0';

	/**
	 * Get the value of a site transient timeout expiration.
	 *
	 * @since  1.0.5
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  string $transient Transient name. Expected to not be SQL-escaped.
	 * @return int               Expiration time in seconds.
	 */
	public static function get_transient_timeout( $transient ) {
		return (int) get_site_option( '_site_transient_timeout_' . $transient );
	}

	/**
	 * Transform an "octal" integer to a "readable" string like "0644".
	 *
	 * Reminder:
	 * `$perm = fileperms( $file );`
	 *
	 *  WHAT                                         | TYPE   | FILE   | FOLDER |
	 * ----------------------------------------------+--------+--------+--------|
	 * `$perm`                                       | int    | 33188  | 16877  |
	 * `substr( decoct( $perm ), -4 )`               | string | '0644' | '0755' |
	 * `substr( sprintf( '%o', $perm ), -4 )`        | string | '0644' | '0755' |
	 * `$perm & 0777`                                | int    | 420    | 493    |
	 * `decoct( $perm & 0777 )`                      | string | '644'  | '755'  |
	 * `substr( sprintf( '%o', $perm & 0777 ), -4 )` | string | '644'  | '755'  |
	 *
	 * @since  1.0.5
	 * @access public
	 * @author Grégory Viguier
	 * @source SecuPress
	 *
	 * @param  int $int An "octal" integer.
	 * @return string
	 */
	public static function to_octal( $int ) {
		return substr( '0' . decoct( $int ), -4 );
	}

	/**
	 * Get all mime types which could be optimized by Imagify.
	 *
	 * @since  1.0.5
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  string $type One of 'image', 'not-image'. Any other value will return all mime types.
	 * @return array        The mime types.
	 */
	public static function get_mime_types( $type = null ) {
		$mimes = array();

		if ( 'not-image' !== $type ) {
			$mimes = array(
				'jpg|jpeg|jpe' => 'image/jpeg',
				'png'          => 'image/png',
				'gif'          => 'image/gif',
			);
		}

		if ( 'image' !== $type ) {
			$mimes['pdf'] = 'application/pdf';
		}

		return $mimes;
	}

	/**
	 * Get post statuses related to attachments.
	 *
	 * @since  1.0.5
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return array The post statuses.
	 */
	public static function get_post_statuses() {
		static $statuses;

		if ( function_exists( 'imagify_get_post_statuses' ) ) {
			return imagify_get_post_statuses();
		}

		if ( isset( $statuses ) ) {
			return $statuses;
		}

		$statuses = array(
			'inherit' => 'inherit',
			'private' => 'private',
		);

		$custom_statuses = get_post_stati( array( 'public' => true ) );
		unset( $custom_statuses['publish'] );

		if ( $custom_statuses ) {
			$statuses = array_merge( $statuses, $custom_statuses );
		}

		return $statuses;
	}
}
