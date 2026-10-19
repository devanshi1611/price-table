<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'hdp-price-table',
        HDP_PT_URL . 'price-table.css',
        array(),
        filemtime( HDP_PT_DIR . 'price-table.css' )
    );
} );

add_action( 'enqueue_block_assets', function () {
    wp_enqueue_style(
        'hdp-price-table-editor',
        HDP_PT_URL . 'price-table.css',
        array(),
        filemtime( HDP_PT_DIR . 'price-table.css' )
    );
} );

/**
 * Block editor only: structure → packages relationship filtering.
 * Intentionally separate from the package CPT admin.js.
 */
add_action( 'enqueue_block_editor_assets', function () {
	wp_enqueue_script(
		'hdp-price-table-block-editor',
		HDP_PT_URL . 'inc/block-editor.js',
		array( 'jquery', 'acf-input' ),
		'2.3.0',
		true
	);

	wp_localize_script(
		'hdp-price-table-block-editor',
		'hdpPtBlock',
		array(
			'nonce' => wp_create_nonce( 'hdp_pt_block_packages' ),
		)
	);
} );
