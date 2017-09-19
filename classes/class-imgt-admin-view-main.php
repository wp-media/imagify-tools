<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

/**
 * Class that handle the view for the main page.
 *
 * @package Imagify Tools
 * @since   1.0
 * @author  Grégory Viguier
 */
class IMGT_Admin_View_Main extends IMGT_Admin_View {

	/**
	 * Class version.
	 *
	 * @var string
	 */
	const VERSION = '1.0';

	/**
	 * Template file.
	 *
	 * @var string
	 */
	protected $template = 'main-page.php';

	/**
	 * Init.
	 *
	 * @since  1.0
	 * @author Grégory Viguier
	 */
	public function init() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'admin_print_styles',    array( $this, 'print_styles' ) );
	}

	/**
	 * Enqueue some CSS.
	 *
	 * @since  1.0
	 * @author Grégory Viguier
	 */
	public function enqueue_styles() {
		$url = Imagify_Tools::get_assets_url();
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$ver = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : IMAGIFY_TOOLS_VERSION;

		wp_enqueue_style( 'imgt-admin', $url . 'css/admin' . $min . '.css', array(), $ver );
	}

	/**
	 * Print some CSS.
	 *
	 * @since  1.0
	 * @author Grégory Viguier
	 */
	public function print_styles() {
		?>
		<style>
		.imgt-wrap .row-group-title th {
			color: #fff;
			background-color: #23282d;
		}
		.imgt-wrap tr + tr th,
		.imgt-wrap tr + tr td {
			border-top: 1px solid #e5e5e5;
		}
		.imgt-wrap pre {
			width: 100%;
			margin: 0;
			overflow-x: auto;
		}
		.imgt-wrap .row-error th,
		.imgt-wrap .row-error td {
			color: #fff;
			background: red;
		}
		</style>
		<?php
	}
}
