<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

/**
 * Class that prints attachments meta values.
 *
 * @package Imagify Tools
 * @since   1.0
 * @author  Grégory Viguier
 */
class IMGT_Attachments_Metas {

	/**
	 * Class version.
	 *
	 * @var string
	 */
	const VERSION = '1.0.1';

	/**
	 * Meta box ID.
	 *
	 * @var string
	 */
	const METABOX_ID = 'imgt-attachment-metas';

	/**
	 * The single instance of the class.
	 *
	 * @access  protected
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
		if ( current_user_can( imagify_tools_get_capacity() ) ) {
			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), -10 );
		}
	}

	/**
	 * Add some meta boxes in attachment edition page.
	 *
	 * @since  1.0
	 * @author Grégory Viguier
	 */
	public function add_meta_boxes() {
		global $post;

		if ( ! imagify_is_attachment_mime_type_supported( $post->ID ) ) {
			return;
		}

		// Add some CSS only on pages that will display a meta box.
		add_action( 'admin_print_styles-post.php', array( $this, 'print_styles' ) );

		$metas = get_post_meta( $post->ID );

		if ( ! $metas ) {
			// The attachment has no metas, that's a big problem.
			add_meta_box(
				self::METABOX_ID,
				_x( 'Metas', 'attachment meta data', 'imagify-tools' ),
				array( $this, 'print_meta_box_no_content' ),
				'attachment',
				'normal',
				'high'
			);

			add_filter( 'postbox_classes_attachment_' . self::METABOX_ID, array( $this, 'add_meta_box_class' ) );
			return;
		}

		foreach ( $metas as $meta_name => $values ) {
			$metas[ $meta_name ] = array_map( 'maybe_unserialize', $values );
		}

		// Group metas in up to 4 meta boxes.
		$meta_groups = array(
			'wp' => array(
				'title'      => _x( 'Mandatory WP metas', 'attachment meta data', 'imagify-tools' ),
				'skip_empty' => false,
				'required'   => array(
					'_wp_attached_file'       => 1,
					'_wp_attachment_metadata' => 1,
				),
			),
			'imagify' => array(
				'title'      => _x( 'Imagify metas', 'attachment meta data', 'imagify-tools' ),
				'required'   => array(
					'_imagify_status'             => 1,
					'_imagify_optimization_level' => 1,
					'_imagify_data'               => 1,
				),
			),
			's3' => array(
				'title'      => _x( 'Amazon S3 metas', 'attachment meta data', 'imagify-tools' ),
				'required'   => array(
					'wpos3_filesize_total' => 1,
					'amazonS3_info'        => 1,
				),
			),
			'other' => array(
				'title'      => _x( 'Other metas', 'attachment meta data', 'imagify-tools' ),
			),
		);

		if ( ! isset( $metas['_wp_attachment_metadata'][0]['filesize'] ) ) {
			// The files are not removed from the server, so the meta should not be set.
			unset( $meta_groups['s3']['required']['wpos3_filesize_total'] );
		}

		// Add a meta box for each group.
		foreach ( $meta_groups as $box_id => $box_args ) {
			$box_args = array_merge( array(
				'title'      => _x( 'Metas', 'attachment meta data', 'imagify-tools' ),
				'skip_empty' => true,
				'required'   => array(),
			), $box_args );

			if ( $box_args['required'] ) {
				$tmp_metas = array_intersect_key( $metas, $box_args['required'] );
				$metas     = array_diff_key( $metas, $box_args['required'] );
			} else {
				$tmp_metas = $metas;
			}

			if ( ! $tmp_metas && $box_args['skip_empty'] ) {
				continue;
			}

			add_meta_box(
				self::METABOX_ID . '-' . $box_id,
				$box_args['title'],
				array( $this, 'print_meta_box_content' ),
				'attachment',
				'normal',
				'high',
				array(
					'metas'    => $tmp_metas,
					'required' => $box_args['required'],
				)
			);

			// Add a common HTML class to our meta boxes.
			add_filter( 'postbox_classes_attachment_' . self::METABOX_ID . '-' . $box_id, array( $this, 'add_meta_box_class' ) );
		}
	}

	/**
	 * Print the meta box content saying there are no metas.
	 *
	 * @since  1.0
	 * @author Grégory Viguier
	 */
	public function print_meta_box_no_content() {
		echo '<div class="row-error">' . _x( 'None!', 'attachment meta data', 'imagify-tools' ) . '</div>';
	}

	/**
	 * Print the meta box content.
	 *
	 * @since  1.0
	 * @author Grégory Viguier
	 *
	 * @param object $post WP_Post object of the current Attachment post.
	 * @param array  $data An array of data related to the meta box.
	 */
	public function print_meta_box_content( $post, $data ) {
		$metas     = $data['args']['metas'];
		$required  = $data['args']['required'];
		$all_metas = array_merge( $required, $metas );

		echo '<table><tbody>';

		foreach ( $all_metas as $meta_name => $meta_values ) {
			if ( isset( $required[ $meta_name ] ) && ! isset( $metas[ $meta_name ] ) ) {
				echo '<tr class="row-error"><th>' . $meta_name . '</th><td>' . __( 'The meta is missing!', 'imagify-tools' ) . '</td></tr>';
				continue;
			}

			$multiple_metas = count( $meta_values ) > 1;

			echo '<tr>';
			echo '<th>' . $meta_name . '</th>';
			echo '<td>';

			$separator = '';

			foreach ( $meta_values as $meta_value ) {
				if ( is_numeric( $meta_value ) || is_null( $meta_value ) || is_bool( $meta_value ) ) {
					ob_start();
					call_user_func( 'var_dump', $meta_value );
					$meta_value = trim( strip_tags( ob_get_clean() ) );
					$meta_value = preg_replace( '@^.+\.php:\d+:@', '', $meta_value );
					$meta_value = preg_replace( '@\(length=\d+\)$@', '<em><small>\0</small></em>', $meta_value );
				} else {
					$meta_value = esc_html( call_user_func( 'print_r', $meta_value, 1 ) );
				}

				echo $separator;
				echo '<pre>' . $meta_value . '</pre>';
				$separator = $multiple_metas ? '<hr/>' : '';
			}

			echo '</td>';
			echo '</tr>';
		}

		echo '</tbody></table>';
	}

	/**
	 * Add a common HTML class to our meta boxes.
	 *
	 * @since  1.0
	 * @author Grégory Viguier
	 *
	 * @param  array $classes An array of postbox classes.
	 * @return array
	 */
	public function add_meta_box_class( $classes ) {
		$classes[] = self::METABOX_ID;
		return $classes;
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
		.<?php echo self::METABOX_ID; ?> .inside {
			margin-top: 0;
		}
		.<?php echo self::METABOX_ID; ?> .inside table {
			width: 100%;
			border-spacing: 0;
			border-collapse: collapse;
		}
		.<?php echo self::METABOX_ID; ?> .inside th,
		.<?php echo self::METABOX_ID; ?> .inside td {
			padding-top: .5em;
			padding-bottom: .5em;
			vertical-align: top;
		}
		.<?php echo self::METABOX_ID; ?> .inside th {
			width: 15em;
			padding-right: 1em;
			text-align: right;
		}
		.<?php echo self::METABOX_ID; ?> .inside tr + tr th,
		.<?php echo self::METABOX_ID; ?> .inside tr + tr td {
			border-top: solid 1px rgb(238, 238, 238);
		}
		.<?php echo self::METABOX_ID; ?> .inside pre {
			width: 100%;
			margin: .1em 0 0;
			overflow-x: auto;
		}
		.<?php echo self::METABOX_ID; ?> .row-error th,
		.<?php echo self::METABOX_ID; ?> .row-error td {
			font-weight: normal;
			color: #fff;
			background: red;
		}
		.<?php echo self::METABOX_ID; ?> div.row-error {
			font-weight: normal;
			color: #fff;
			background: red;
			padding-left: 1em;
		}
		@media only screen and (max-width: 1500px) {
			.<?php echo self::METABOX_ID; ?> .inside th,
			.<?php echo self::METABOX_ID; ?> .inside td {
				display: block;
				width: 100%;
			}
			.<?php echo self::METABOX_ID; ?> .inside th {
				padding: .5em 1px;
				text-align: inherit;
			}
			.<?php echo self::METABOX_ID; ?> .inside td {
				border-top: solid 1px rgb(238, 238, 238);
			}
		}
		</style>
		<?php
	}
}
