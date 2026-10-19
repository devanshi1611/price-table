<?php
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'HDP_PT_DIR', __DIR__ . '/' );
define( 'HDP_PT_URL', get_stylesheet_directory_uri() . '/parts/price-table/' );

require_once HDP_PT_DIR . 'inc/cpt.php';
require_once HDP_PT_DIR . 'inc/fields.php';
require_once HDP_PT_DIR . 'inc/admin.php';
require_once HDP_PT_DIR . 'inc/block.php';
require_once HDP_PT_DIR . 'inc/render.php';
require_once HDP_PT_DIR . 'inc/assets.php';
