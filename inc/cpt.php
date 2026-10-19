<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', function () {
	register_post_type( 'hdp_pt_structure', array(
		'labels' => array(
			'name' => 'Pricing Structures',
			'singular_name' => 'Pricing Table Structure',
			'add_new_item' => 'Add Pricing Table Structure',
			'edit_item' => 'Edit Pricing Table Structure',
		),
		'public' => false,
		'show_ui' => true,
		'show_in_menu' => true,
		'menu_icon' => 'dashicons-editor-table',
		'supports' => array( 'title' ),
		'has_archive' => false,
		'rewrite' => false,
	) );

	register_post_type( 'hdp_pt_package', array(
		'labels' => array(
			'name' => 'Pricing Packages',
			'singular_name' => 'Pricing Package',
			'add_new_item' => 'Add Pricing Package',
			'edit_item' => 'Edit Pricing Package',
		),
		'public' => false,
		'show_ui' => true,
		'show_in_menu' => true,
		'menu_icon' => 'dashicons-money-alt',
		'supports' => array( 'title' ),
		'has_archive' => false,
		'rewrite' => false,
	) );
} );
