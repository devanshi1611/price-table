<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_register_block_type' ) ) return;
	acf_register_block_type( array(
		'name' => 'pricing-table',
		'title' => 'Pricing Table',
		'description' => 'Reusable pricing table using a pricing structure and packages.',
		'category' => 'formatting',
		'icon' => 'table-col-after',
		'mode' => 'edit',
		'render_callback' => 'hdp_pt_render_block',
		'supports' => array(
			'anchor' => true,
			'spacing' => array(
				'margin'  => true,
				'padding' => true,
			),
		),
		'acf_block_version' => 3,
	) );
} );

/**
 * Resolve the structure ID for filtering Available Packages.
 * Prefer the live AJAX value from the block editor (unsaved state).
 */
function hdp_pt_block_request_structure_id() {
	if ( isset( $_REQUEST['hdp_pt_structure_id'] ) ) {
		return absint( wp_unslash( $_REQUEST['hdp_pt_structure_id'] ) );
	}
	return 0;
}

/**
 * Package IDs that belong to a given structure (new package.structure field).
 *
 * @param int $structure_id
 * @return int[]
 */
function hdp_pt_get_package_ids_for_structure( $structure_id ) {
	$structure_id = absint( $structure_id );
	if ( ! $structure_id || 'hdp_pt_structure' !== get_post_type( $structure_id ) ) {
		return array();
	}

	$query = new WP_Query( array(
		'post_type'              => 'hdp_pt_package',
		'post_status'            => array( 'publish', 'draft', 'pending', 'private' ),
		'posts_per_page'         => 200,
		'fields'                 => 'ids',
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
		'meta_query'             => array(
			array(
				'key'     => 'structure',
				'value'   => $structure_id,
				'compare' => '=',
			),
		),
	) );

	return array_map( 'absint', $query->posts );
}

/**
 * Filter the block Packages relationship choices by selected structure.
 * Uses the new top-level package `structure` meta — never structure_configs.
 */
add_filter( 'acf/fields/relationship/query/key=field_hdp_pt_block_packages', function ( $args, $field, $post_id ) {
	$structure_id = hdp_pt_block_request_structure_id();

	if ( ! $structure_id ) {
		/* No structure selected yet — show an empty choices list. */
		$args['post__in'] = array( 0 );
		return $args;
	}

	$ids = hdp_pt_get_package_ids_for_structure( $structure_id );

	if ( ! $ids ) {
		$args['post__in'] = array( 0 );
		return $args;
	}

	$args['post__in'] = $ids;
	$args['orderby']  = 'post__in';

	return $args;
}, 10, 3 );

/**
 * AJAX: package IDs belonging to a structure (for pruning invalid selections).
 */
add_action( 'wp_ajax_hdp_pt_packages_for_structure', function () {
	check_ajax_referer( 'hdp_pt_block_packages', 'nonce' );

	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( 'Permission denied', 403 );
	}

	$structure_id = absint( $_POST['structure_id'] ?? 0 );
	wp_send_json_success( hdp_pt_get_package_ids_for_structure( $structure_id ) );
} );

/**
 * AJAX: structure items for the block Exclude Items field choices.
 */
add_action( 'wp_ajax_hdp_pt_block_structure_items', function () {
	check_ajax_referer( 'hdp_pt_block_packages', 'nonce' );

	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( 'Permission denied', 403 );
	}

	$structure_id = absint( $_POST['structure_id'] ?? 0 );
	$items = array();

	foreach ( hdp_pt_get_structure_items( $structure_id ) as $item ) {
		$items[] = array(
			'key'   => $item['key'],
			'label' => $item['label'],
		);
	}

	wp_send_json_success( $items );
} );
