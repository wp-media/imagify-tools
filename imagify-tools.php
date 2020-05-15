<?php
/**
 * Plugin Name: Imagify Tools
 * Plugin URI: https://wordpress.org/plugins/imagify/
 * Description: A WordPress plugin helping debug in Imagify.
 * Version: 1.1.1
 * Requires PHP: 5.3
 * Author: WP Media
 * Author URI: https://wp-media.me/
 * Licence: GPLv2
 *
 * Text Domain: imagify-tools
 * Domain Path: languages
 *
 * Copyright 2020 WP Media
 *
 * @package WP-Media\Imagify\WordPress-Tools-Plugin
 */

defined( 'ABSPATH' ) || die( 'Cheatin’ uh?' );

/**
 * Plugin init.
 */
function imagify_tools_init() {
	if ( ! function_exists( 'filter_var' ) || ! function_exists( 'filter_input' ) || version_compare( PHP_VERSION, '5.3' ) < 0 ) {
		return;
	}

	$plugin_file    = realpath( __FILE__ );
	$plugin_dir     = dirname( $plugin_file );
	$plugin_dirname = wp_basename( $plugin_file, '.php' );

	if ( file_exists( $plugin_dir . '/' . $plugin_dirname . '/classes/class-imagify-tools.php' ) ) {
		$plugin_dir = $plugin_dir . DIRECTORY_SEPARATOR . $plugin_dirname;
	}

	$plugin_dir .= DIRECTORY_SEPARATOR;

	// Define plugin constants.
	define( 'IMAGIFY_TOOLS_VERSION',        '1.1.1' );
	define( 'IMAGIFY_TOOLS_FILE',           $plugin_file );
	define( 'IMAGIFY_TOOLS_PATH',           $plugin_dir );
	define( 'IMAGIFY_TOOLS_CLASSES_PATH',   IMAGIFY_TOOLS_PATH . 'classes' . DIRECTORY_SEPARATOR );
	define( 'IMAGIFY_TOOLS_FUNCTIONS_PATH', IMAGIFY_TOOLS_PATH . 'functions' . DIRECTORY_SEPARATOR );
	define( 'IMAGIFY_TOOLS_VIEWS_PATH',     IMAGIFY_TOOLS_PATH . 'views' . DIRECTORY_SEPARATOR );

	// Include the main class file.
	require_once IMAGIFY_TOOLS_CLASSES_PATH . 'class-imagify-tools.php';

	// Initiate the main class.
	Imagify_Tools::get_instance()->init();
}

imagify_tools_init();
