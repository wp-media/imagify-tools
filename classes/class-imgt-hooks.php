<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

/**
 * Class that handles the plugin hooks.
 *
 * @package Imagify Tools
 * @since   1.0
 * @author  Grégory Viguier
 */
class IMGT_Hooks {

	/**
	 * Class version.
	 *
	 * @var string
	 */
	const VERSION = '1.0';

	/**
	 * The single instance of the class.
	 *
	 * @var object
	 */
	protected static $_instance;

	/**
	 * The constructor.
	 *
	 * @since  1.0
	 * @author Grégory Viguier
	 */
	protected function __construct() {}

	/**
	 * Get the main Instance.
	 *
	 * @since  1.0
	 * @author Grégory Viguier
	 *
	 * @return object Main instance.
	 */
	public static function get_instance() {
		if ( ! isset( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Delete the main Instance.
	 *
	 * @since  1.0
	 * @author Grégory Viguier
	 */
	public static function delete_instance() {
		unset( self::$_instance );
	}

	/**
	 * Class init.
	 *
	 * @since  1.0
	 * @author Grégory Viguier
	 */
	public function init() {
		// Non async optimization.
		add_filter( 'imagify_do_async_job_args', array( $this, 'make_async_job_blocking' ) );
	}

	/**
	 * Filter the arguments used to launch an async job: make async optimization non async.
	 *
	 * @since  1.0
	 * @author Grégory Viguier
	 *
	 * @param  array $args An array of arguments passed to wp_remote_post().
	 * @return array
	 */
	public function make_async_job_blocking( $args ) {
		if ( imagify_tools_get_site_transient( 'imgt_blocking_requests' ) ) {
			$args['timeout'] = 30;
			unset( $args['blocking'] );
		}

		return $args;
	}
}
