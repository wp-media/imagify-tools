<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

global $wpdb, $wp_filter, $wp_rewrite;

if ( function_exists( 'imagify_get_filesystem' ) ) {
	$filesystem = imagify_get_filesystem();
}
