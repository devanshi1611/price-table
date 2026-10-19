<?php
if (! defined('ABSPATH')) exit;

/**
 * Attachment ID for the bundled circle.png default structure icon.
 *
 * @return int
 */
function hdp_pt_get_circle_icon_attachment_id()
{
	static $id = null;
	if ($id !== null) {
		return $id;
	}

	$cached = absint(get_option('hdp_pt_circle_icon_id'));
	if ($cached && wp_attachment_is_image($cached)) {
		$id = $cached;
		return $id;
	}

	global $wpdb;
	$found = absint($wpdb->get_var(
		"SELECT post_id FROM {$wpdb->postmeta}
		 WHERE meta_key = '_wp_attached_file'
		 AND meta_value LIKE '%/circle.png'
		 ORDER BY post_id DESC
		 LIMIT 1"
	));

	if ($found && wp_attachment_is_image($found)) {
		update_option('hdp_pt_circle_icon_id', $found, false);
		$id = $found;
		return $id;
	}

	$file = HDP_PT_DIR . 'inc/circle.png';
	if (! file_exists($file) || ! function_exists('media_handle_sideload')) {
		$id = 0;
		return $id;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$tmp = wp_tempnam('circle.png');
	if (! $tmp || ! copy($file, $tmp)) {
		$id = 0;
		return $id;
	}

	$file_array = array(
		'name'     => 'circle.png',
		'tmp_name' => $tmp,
	);

	$uploaded = media_handle_sideload($file_array, 0, 'Pricing table default icon');
	if (is_wp_error($uploaded)) {
		@unlink($tmp);
		$id = 0;
		return $id;
	}

	update_option('hdp_pt_circle_icon_id', absint($uploaded), false);
	$id = absint($uploaded);
	return $id;
}

add_action('acf/init', function () {
	if (! function_exists('acf_add_local_field_group')) return;

	$value_types = array(
		'icon'         => 'Icon with Tooltip',
		'text'         => 'Plain text',
		'text_tooltip' => 'Plain text with tooltip',
		'text_link'    => 'Text with link',
	);

	$circle_icon_id = hdp_pt_get_circle_icon_attachment_id();

	acf_add_local_field_group(array(
		'key' => 'group_hdp_pt_structure',
		'title' => 'Pricing Table Structure',
		'fields' => array(
			array(
				'key' => 'field_hdp_pt_sections',
				'label' => 'Sections',
				'name' => 'sections',
				'type' => 'repeater',
				'layout' => 'block',
				'button_label' => 'Add Section',
				'sub_fields' => array(
					array(
						'key' => 'field_hdp_pt_section_heading',
						'label' => 'Section Heading',
						'name' => 'heading',
						'type' => 'text',
						'required' => 1,
					),
					array(
						'key' => 'field_hdp_pt_items',
						'label' => 'Items',
						'name' => 'items',
						'type' => 'repeater',
						'layout' => 'block',
						'button_label' => 'Add Item',
						'wrapper' => array(
							'class' => 'hdp-pt-structure-items',
						),
						'sub_fields' => array(
							array(
								'key' => 'field_hdp_pt_item_label',
								'label' => 'Item Label',
								'name' => 'label',
								'type' => 'text',
								'required' => 1,
								'wrapper' => array(
									'width' => '50',
								),
							),
							array(
								'key' => 'field_hdp_pt_item_key',
								'label' => 'Internal Key',
								'name' => 'item_key',
								'type' => 'text',
								'readonly' => 1,
								'wrapper' => array(
									'class' => 'hdp-pt-internal-key',
									'width' => '0',
								),
							),
							array(
								'key' => 'field_hdp_pt_value_type',
								'label' => 'Value Type',
								'name' => 'value_type',
								'type' => 'select',
								'choices' => $value_types,
								'default_value' => 'text',
								'ui' => 1,
								'wrapper' => array(
									'width' => '50',
								),
							),
							array(
								'key' => 'field_hdp_pt_structure_label_tooltip',
								'label' => 'Label Tooltip',
								'name' => 'label_tooltip',
								'type' => 'textarea',
								'rows' => 2,
								'instructions' => '',
								'wrapper' => array(
									'width' => '50',
								),
							),
							array(
								'key' => 'field_hdp_pt_structure_default_icon',
								'label' => 'Default Icon',
								'name' => 'default_icon',
								'type' => 'image',
								'return_format' => 'id',
								'preview_size' => 'thumbnail',
								'library' => 'all',
								'default_value' => $circle_icon_id ? $circle_icon_id : '',
								'instructions' => '',
								'wrapper' => array(
									'width' => '50',
								),
								'conditional_logic' => array(
									array(
										array(
											'field' => 'field_hdp_pt_value_type',
											'operator' => '==',
											'value' => 'icon',
										),
									),
								),
							),
						),
					),
				),
			),
		),
		'location' => array(array(array('param' => 'post_type', 'operator' => '==', 'value' => 'hdp_pt_structure'))),
	));

	acf_add_local_field_group(array(
		'key' => 'group_hdp_pt_package',
		'title' => 'Pricing Package',
		'fields' => array(
			array(
				'key' => 'field_hdp_pt_price',
				'label' => 'Price',
				'name' => 'price',
				'type' => 'group',
				'layout' => 'block',
				'sub_fields' => array(
					array('key' => 'field_hdp_pt_package_title', 'label' => 'Title', 'name' => 'title', 'type' => 'text', 'required' => 1, 'wrapper' => array('width' => '33.33')),
					array('key' => 'field_hdp_pt_currency', 'label' => 'Currency', 'name' => 'currency', 'type' => 'text', 'default_value' => '€', 'wrapper' => array('width' => '33.33')),
					array('key' => 'field_hdp_pt_amount', 'label' => 'Price', 'name' => 'amount', 'type' => 'text', 'wrapper' => array('width' => '33.34')),
				),
			),
			array('key' => 'field_hdp_pt_subtext', 'label' => 'Price Subtext', 'name' => 'subtext', 'type' => 'text', 'default_value' => 'per maand, excl. btw', 'wrapper' => array('width' => '33.33')),
			array('key' => 'field_hdp_pt_button_link', 'label' => 'Button', 'name' => 'button_link', 'type' => 'link', 'return_format' => 'array', 'default_value' => array('title' => 'Aanvragen', 'url' => '', 'target' => '_self'), 'wrapper' => array('width' => '33.33')),
			array('key' => 'field_hdp_pt_featured', 'label' => 'Featured', 'name' => 'featured', 'type' => 'true_false', 'ui' => 1, 'wrapper' => array('width' => '33.34')),
			array(
				'key' => 'field_hdp_pt_package_structure',
				'label' => 'Structure',
				'name' => 'structure',
				'type' => 'post_object',
				'post_type' => array(
					'hdp_pt_structure',
				),
				'return_format' => 'id',
				'ui' => 1,
				'allow_null' => 0,
				'required' => 1,
				'wrapper' => array(
					'class' => 'hdp-pt-structure-selector',
				),
			),

			/*
			 * Desktop vs Mobile tabs — same package, two presentations.
			 * Desktop Items field name/keys stay unchanged for data compatibility.
			 */
			array(
				'key' => 'field_hdp_pt_tab_desktop',
				'label' => 'Desktop Values',
				'name' => '',
				'type' => 'tab',
				'placement' => 'top',
				'endpoint' => 0,
			),
			array(
				'key' => 'field_hdp_pt_desktop_values_message',
				'label' => 'Desktop comparison values',
				'name' => '',
				'type' => 'message',
				'message' => 'These values fill the desktop comparison table. Rows and order come from the selected Structure. Edit the value for this package only — other packages are not changed.',
			),
			array(
				'key' => 'field_hdp_pt_package_items',
				'label' => 'Desktop Items',
				'name' => 'items',
				'type' => 'repeater',
				'layout' => 'block',
				'button_label' => 'Add Item Value',
				'wrapper' => array(
					'class' => 'hdp-pt-config-items',
				),
				/*
				 * Subfield keys MUST match the migrated package meta references:
				 * field_hdp_pt_package_item_*
				 *
				 * Do not reuse field_hdp_pt_config_item_* (those belong to the
				 * retired structure_configs repeater and caused ACF key collisions).
				 */
				'sub_fields' => array(

					array(
						'key' => 'field_hdp_pt_package_item_key',
						'label' => 'Internal Key',
						'name' => 'item_key',
						'type' => 'text',
						'wrapper' => array(
							'class' => 'hdp-pt-internal-key',
						),
					),

					array(
						'key' => 'field_hdp_pt_package_item_label',
						'label' => 'Item Label',
						'name' => 'item_label',
						'type' => 'text',
						'wrapper' => array(
							'class' => 'hdp-pt-internal-key hdp-pt-readonly-meta',
						),
					),

					array(
						'key' => 'field_hdp_pt_package_item_type',
						'label' => 'Value Type',
						'name' => 'item_type',
						'type' => 'text',
						'wrapper' => array(
							'class' => 'hdp-pt-internal-key hdp-pt-readonly-meta',
						),
					),

					array(
						'key' => 'field_hdp_pt_package_item_value',
						'label' => 'Value',
						'name' => 'value',
						'type' => 'text',
						'wrapper' => array(
							'width' => '50',
						),
					),

					array(
						'key' => 'field_hdp_pt_package_item_tooltip',
						'label' => 'Tooltip Text',
						'name' => 'tooltip',
						'type' => 'textarea',
						'rows' => 2,
						'instructions' => '',
						'wrapper' => array(
							'width' => '50',
						),
					),

					array(
						'key' => 'field_hdp_pt_package_item_icon',
						'label' => 'Icon Override',
						'name' => 'icon',
						'type' => 'image',
						'return_format' => 'id',
						'library' => 'all',
						'instructions' => '',
						'wrapper' => array(
							'width' => '50',
						),
					),

					array(
						'key' => 'field_hdp_pt_package_item_link',
						'label' => 'Link',
						'name' => 'link',
						'type' => 'link',
						'return_format' => 'array',
						'wrapper' => array(
							'width' => '50',
						),
					),

				),
			),

			array(
				'key' => 'field_hdp_pt_tab_mobile',
				'label' => 'Mobile Values',
				'name' => '',
				'type' => 'tab',
				'placement' => 'top',
				'endpoint' => 0,
			),
			array(
				'key' => 'field_hdp_pt_mobile_values_message',
				'label' => 'Mobile card content',
				'name' => '',
				'type' => 'message',
				'message' => 'These values fill the mobile card data.',
			),
			array(
				'key' => 'field_hdp_pt_mobile_subtitle',
				'label' => 'Subtitle',
				'name' => 'mobile_subtitle',
				'type' => 'text',
				'instructions' => '',
			),
			array(
				'key' => 'field_hdp_pt_mobile_badge',
				'label' => 'Badge',
				'name' => 'mobile_badge',
				'type' => 'text',
				'instructions' => '',
				'wrapper' => array(
					'width' => '50',
				),
			),
			array(
				'key' => 'field_hdp_pt_mobile_details_label',
				'label' => 'Details Label',
				'name' => 'mobile_details_label',
				'type' => 'text',
				'default_value' => 'Bekijk wat erin zit',
				'instructions' => '',
				'wrapper' => array(
					'width' => '50',
				),
			),
			array(
				'key' => 'field_hdp_pt_mobile_features',
				'label' => 'Feature List',
				'name' => 'mobile_features',
				'type' => 'repeater',
				'layout' => 'block',
				'button_label' => 'Add Feature',
				'instructions' => '',
				'wrapper' => array(
					'class' => 'hdp-pt-mobile-features',
				),
				'sub_fields' => array(
					array(
						'key' => 'field_hdp_pt_mobile_feature_label',
						'label' => 'Feature Text',
						'name' => 'label',
						'type' => 'text',
						'required' => 1,
						'wrapper' => array(
							'width' => '50',
						),
					),
					array(
						'key' => 'field_hdp_pt_mobile_feature_icon',
						'label' => 'Icon Style',
						'name' => 'icon_style',
						'type' => 'select',
						'choices' => array(
							'check'  => 'Check',
							'plus'   => 'Plus',
							'custom' => 'Custom Icon',
						),
						'default_value' => 'check',
						'ui' => 1,
						'wrapper' => array(
							'width' => '25',
						),
					),
					array(
						'key' => 'field_hdp_pt_mobile_feature_custom_icon',
						'label' => 'Custom Icon',
						'name' => 'custom_icon',
						'type' => 'image',
						'return_format' => 'id',
						'preview_size' => 'thumbnail',
						'library' => 'all',
						'instructions' => 'Shown instead of Check/Plus when Custom Icon is selected.',
						'conditional_logic' => array(
							array(
								array(
									'field' => 'field_hdp_pt_mobile_feature_icon',
									'operator' => '==',
									'value' => 'custom',
								),
							),
						),
						'wrapper' => array(
							'width' => '50',
						),
					),
					array(
						'key' => 'field_hdp_pt_mobile_feature_related',
						'label' => 'Related Desktop Item',
						'name' => 'related_item_key',
						'type' => 'select',
						'choices' => array(),
						'allow_null' => 1,
						'ui' => 1,
						'instructions' => 'Mapping for Items Exclusion',
						'wrapper' => array(
							'width' => '25',
						),
					),
				),
			),

		),
		'location' => array(array(array('param' => 'post_type', 'operator' => '==', 'value' => 'hdp_pt_package'))),
	));

	acf_add_local_field_group(array(
		'key' => 'group_hdp_pt_block',
		'title' => 'Pricing Table',
		'fields' => array(
			array(
				'key' => 'field_hdp_pt_block_structure',
				'label' => 'Table Structure',
				'name' => 'table_structure',
				'type' => 'post_object',
				'post_type' => array('hdp_pt_structure'),
				'return_format' => 'id',
				'ui' => 1,
				'required' => 1,
				'instructions' => 'Select the pricing table structure. Available packages will update to match.',
			),
			array(
				'key' => 'field_hdp_pt_block_packages',
				'label' => 'Available Packages',
				'name' => 'packages',
				'type' => 'relationship',
				'post_type' => array('hdp_pt_package'),
				'filters' => array('search'),
				'return_format' => 'id',
				'max' => 5,
				'min' => 1,
				'ui' => 1,
				'required' => 1,
				'instructions' => 'Only packages belonging to the selected structure are listed. Maximum 5 packages.',
			),
			array(
				'key' => 'field_hdp_pt_block_excluded_items',
				'label' => 'Exclude Items',
				'name' => 'excluded_items',
				'type' => 'select',
				'choices' => array(),
				'default_value' => array(),
				'allow_null' => 1,
				'multiple' => 1,
				'ui' => 1,
				'ajax' => 0,
				'return_format' => 'value',
				'instructions' => 'Hide selected structure items from this pricing table only. Package data is not changed.',
				'wrapper' => array(
					'class' => 'hdp-pt-block-excluded-items',
				),
			),
		),
		'location' => array(array(array('param' => 'block', 'operator' => '==', 'value' => 'acf/pricing-table'))),
	));
});

/**
 * Generate the internal mapping key from the item label.
 * Once an item has a key, it is intentionally preserved if the label changes.
 */
function hdp_pt_make_item_key($label)
{
	$label = remove_accents(wp_strip_all_tags((string) $label));
	$key = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $label), '-'));
	return sanitize_key($key);
}

/**
 * Human-readable labels for the four supported package value types.
 */
function hdp_pt_value_type_labels()
{
	return array(
		'icon'         => 'Icon with Tooltip',
		'text'         => 'Plain text',
		'text_tooltip' => 'Plain text with tooltip',
		'text_link'    => 'Text with link',
	);
}

/**
 * Flatten a structure's sections into an ordered list of items.
 * Used by package metadata hydration and the Sync Items AJAX endpoint.
 *
 * @param int $structure_id
 * @return array<int, array{key:string,label:string,type:string,type_label:string,label_tooltip:string,icon:int}>
 */
function hdp_pt_get_structure_items($structure_id)
{
	$structure_id = absint($structure_id);
	if (! $structure_id || 'hdp_pt_structure' !== get_post_type($structure_id)) {
		return array();
	}

	$sections = get_field('sections', $structure_id);
	$types    = hdp_pt_value_type_labels();
	$fallback_icon = hdp_pt_get_circle_icon_attachment_id();
	$out      = array();

	foreach ((array) $sections as $section) {
		foreach ((array) ($section['items'] ?? array()) as $item) {
			$key = ! empty($item['item_key'])
				? sanitize_key($item['item_key'])
				: hdp_pt_make_item_key($item['label'] ?? '');

			if (! $key) {
				continue;
			}

			$type = sanitize_key($item['value_type'] ?? 'text');
			if (! isset($types[$type])) {
				$type = 'text';
			}

			$icon = 0;
			if ('icon' === $type) {
				$icon = absint($item['default_icon'] ?? 0);
				if (! $icon) {
					$icon = $fallback_icon;
				}
			}

			$out[] = array(
				'key'           => $key,
				'label'         => sanitize_text_field($item['label'] ?? ''),
				'type'          => $type,
				'type_label'    => $types[$type],
				'label_tooltip' => sanitize_textarea_field($item['label_tooltip'] ?? ''),
				'icon'          => $icon,
			);
		}
	}

	return $out;
}

/**
 * Package item subfield name => ACF field key.
 * ACF load_value/update_value often key rows by field key, not name.
 */
function hdp_pt_package_item_field_map()
{
	return array(
		'item_key'   => 'field_hdp_pt_package_item_key',
		'item_label' => 'field_hdp_pt_package_item_label',
		'item_type'  => 'field_hdp_pt_package_item_type',
		'value'      => 'field_hdp_pt_package_item_value',
		'tooltip'    => 'field_hdp_pt_package_item_tooltip',
		'icon'       => 'field_hdp_pt_package_item_icon',
		'link'       => 'field_hdp_pt_package_item_link',
	);
}

/**
 * Read a package item subfield whether the row is name-keyed or field-key-keyed.
 */
function hdp_pt_package_row_get($row, $name)
{
	if (! is_array($row)) {
		return null;
	}
	if (array_key_exists($name, $row)) {
		return $row[$name];
	}
	$map = hdp_pt_package_item_field_map();
	$field_key = $map[$name] ?? '';
	if ($field_key && array_key_exists($field_key, $row)) {
		return $row[$field_key];
	}
	return null;
}

/**
 * Write a package item subfield to both name and field-key slots when present.
 */
function hdp_pt_package_row_set(&$row, $name, $value)
{
	if (! is_array($row)) {
		return;
	}
	$map = hdp_pt_package_item_field_map();
	$field_key = $map[$name] ?? '';

	$row[$name] = $value;
	if ($field_key) {
		$row[$field_key] = $value;
	}
}

/**
 * Normalize excluded item keys from ACF select (multiple) values.
 * Used by the Price Table block (not packages).
 *
 * @param mixed $value
 * @return string[]
 */
function hdp_pt_normalize_excluded_keys($value)
{
	if (! is_array($value)) {
		if ($value === '' || $value === null || $value === false) {
			return array();
		}
		$value = array($value);
	}

	$out = array();
	foreach ($value as $key) {
		$key = sanitize_key((string) $key);
		if ($key) {
			$out[] = $key;
		}
	}

	return array_values(array_unique($out));
}

/**
 * Rebuild package item rows from the selected structure.
 * Preserves value / tooltip / icon / link by item_key.
 *
 * @param array|mixed $rows
 * @param int         $structure_id
 * @param string[]    $excluded_keys Unused — kept for call-site compatibility.
 * @return array
 */
function hdp_pt_sync_package_items_value($rows, $structure_id, $excluded_keys = array())
{
	unset($excluded_keys);

	$structure_items = hdp_pt_get_structure_items($structure_id);

	$existing_by_key = array();
	if (is_array($rows)) {
		foreach ($rows as $row) {
			if (! is_array($row)) {
				continue;
			}
			$key = sanitize_key((string) (hdp_pt_package_row_get($row, 'item_key') ?? ''));
			if ($key && ! isset($existing_by_key[$key])) {
				$existing_by_key[$key] = $row;
			}
		}
	}

	$out = array();

	foreach ($structure_items as $item) {
		$row = isset($existing_by_key[$item['key']]) ? $existing_by_key[$item['key']] : array();
		if (! is_array($row)) {
			$row = array();
		}

		$is_new_row = ! isset($existing_by_key[$item['key']]);

		hdp_pt_package_row_set($row, 'item_key', $item['key']);
		hdp_pt_package_row_set($row, 'item_label', $item['label']);
		hdp_pt_package_row_set($row, 'item_type', $item['type']);

		/*
		 * Icon: apply Structure default only for new/empty package icons.
		 * Never overwrite a customized package icon.
		 */
		if ('icon' === $item['type'] && ! empty($item['icon'])) {
			$existing_icon = hdp_pt_package_row_get($row, 'icon');
			$has_icon = $existing_icon !== null && $existing_icon !== '' && (int) $existing_icon !== 0;
			if ($is_new_row || ! $has_icon) {
				hdp_pt_package_row_set($row, 'icon', absint($item['icon']));
			}
		}
		/* value, tooltip, link intentionally untouched when present. */

		$out[] = $row;
	}

	return $out;
}

/**
 * Hydrate package item metadata from the selected structure.
 * Never overwrites value / tooltip / icon / link.
 *
 * @param array|mixed $rows
 * @param int         $structure_id
 * @return array|mixed
 */
function hdp_pt_hydrate_package_item_metadata($rows, $structure_id)
{
	return hdp_pt_sync_package_items_value($rows, $structure_id, array());
}

add_filter('acf/update_value/name=sections', function ($value) {
	if (! is_array($value)) return $value;

	$circle = hdp_pt_get_circle_icon_attachment_id();

	foreach ($value as &$section) {
		if (empty($section['items']) || ! is_array($section['items'])) continue;
		foreach ($section['items'] as &$item) {
			if (empty($item['item_key']) && ! empty($item['label'])) {
				$item['item_key'] = hdp_pt_make_item_key($item['label']);
			}

			$type = sanitize_key($item['value_type'] ?? 'text');
			if ('icon' === $type) {
				$icon = absint($item['default_icon'] ?? 0);
				if (! $icon && $circle) {
					$item['default_icon'] = $circle;
				}
			}
		}
		unset($item);
	}
	unset($section);
	return $value;
});

/*
 * Ensure missing structure item_keys appear in the editor without requiring a re-save.
 * Does not change existing keys.
 */
add_filter('acf/load_value/key=field_hdp_pt_sections', function ($value) {
	if (! is_array($value)) {
		return $value;
	}
	foreach ($value as &$section) {
		if (empty($section['items']) || ! is_array($section['items'])) {
			continue;
		}
		foreach ($section['items'] as &$item) {
			if (empty($item['item_key']) && ! empty($item['label'])) {
				$item['item_key'] = hdp_pt_make_item_key($item['label']);
			}
		}
		unset($item);
	}
	unset($section);
	return $value;
});

/**
 * Resolve the package structure ID from POST and/or saved meta.
 *
 * @param int $post_id
 * @return int
 */
function hdp_pt_resolve_package_structure_id($post_id)
{
	$post_id = absint($post_id);

	if (isset($_POST['acf']['field_hdp_pt_package_structure'])) {
		$raw = wp_unslash($_POST['acf']['field_hdp_pt_package_structure']);
		if (is_array($raw)) {
			$raw = reset($raw);
		}
		$id = absint($raw);
		if ($id) {
			return $id;
		}
	}

	if ($post_id) {
		$id = absint(get_post_meta($post_id, 'structure', true));
		if ($id) {
			return $id;
		}
	}

	return 0;
}

/**
 * Load previously saved package item rows (name-keyed) for merge-on-save.
 * Prefers canonical ACF repeater meta (`items_N_*`) over a serialized blob.
 *
 * @param int $post_id
 * @return array
 */
function hdp_pt_get_saved_package_item_rows($post_id)
{
	$post_id = absint($post_id);
	if (! $post_id) {
		return array();
	}

	$items_meta = get_post_meta($post_id, 'items', true);
	$count = is_numeric($items_meta) ? absint($items_meta) : 0;

	if ($count < 1) {
		global $wpdb;
		$keys = $wpdb->get_col($wpdb->prepare(
			"SELECT meta_key FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key REGEXP %s",
			$post_id,
			'^items_[0-9]+_item_key$'
		));
		foreach ((array) $keys as $meta_key) {
			if (preg_match('/^items_(\d+)_item_key$/', (string) $meta_key, $m)) {
				$count = max($count, absint($m[1]) + 1);
			}
		}
	}

	$map = hdp_pt_package_item_field_map();
	$rows = array();

	if ($count > 0) {
		for ($i = 0; $i < $count; $i++) {
			$row = array();
			foreach ($map as $name => $field_key) {
				$meta_key = "items_{$i}_{$name}";
				if (! metadata_exists('post', $post_id, $meta_key)) {
					continue;
				}
				hdp_pt_package_row_set($row, $name, get_post_meta($post_id, $meta_key, true));
			}
			$key = sanitize_key((string) (hdp_pt_package_row_get($row, 'item_key') ?? ''));
			if ($key) {
				$rows[] = $row;
			}
		}
	}

	if ($rows) {
		return $rows;
	}

	/* Fallback: unexpected serialized row blob stored in `items`. */
	if (is_array($items_meta)) {
		foreach ($items_meta as $row) {
			if (! is_array($row)) {
				continue;
			}
			$key = sanitize_key((string) (hdp_pt_package_row_get($row, 'item_key') ?? ''));
			if ($key) {
				$rows[] = $row;
			}
		}
	}

	return $rows;
}

/**
 * Merge posted repeater rows with previously saved rows by item_key.
 * Posted rows win; saved rows fill in keys missing from POST (e.g. mid-sync).
 *
 * @param array $posted
 * @param array $saved
 * @return array
 */
function hdp_pt_merge_package_item_rows($posted, $saved)
{
	$out = array();
	$seen = array();

	foreach (array($posted, $saved) as $source) {
		foreach ((array) $source as $row) {
			if (! is_array($row)) {
				continue;
			}
			if (isset($row['acf_deleted']) || isset($row['acfcloneindex'])) {
				continue;
			}
			$key = sanitize_key((string) (hdp_pt_package_row_get($row, 'item_key') ?? ''));
			if (! $key || isset($seen[$key])) {
				continue;
			}
			$out[] = $row;
			$seen[$key] = true;
		}
	}

	return $out;
}

/*
 * Sync package items to the selected structure on load.
 * Priority 20: runs AFTER acf_field_repeater->load_value (prio 10).
 *
 * Also repairs corrupted `items` meta where an array was stored instead of
 * the repeater row count (caused by a prior update_value priority clash).
 */
add_filter('acf/load_value/key=field_hdp_pt_package_items', function ($value, $post_id) {
	$post_id = absint($post_id);
	$raw = $post_id ? get_post_meta($post_id, 'items', true) : null;

	/*
	 * Repeater load_value requires a numeric count. If meta was overwritten
	 * with a row array, recover values from items_N_* and repair the count.
	 */
	if ($post_id && (is_array($raw) || ! is_numeric($raw))) {
		$recovered = hdp_pt_get_saved_package_item_rows($post_id);
		if ($recovered) {
			update_post_meta($post_id, 'items', count($recovered));
			$value = $recovered;
		} elseif (! is_array($value)) {
			$value = array();
		}
	} elseif (! is_array($value)) {
		$value = array();
	}

	$structure_id = absint(get_post_meta($post_id, 'structure', true));
	if (! $structure_id) {
		return $value;
	}

	return hdp_pt_sync_package_items_value($value, $structure_id, array());
}, 20, 2);

/*
 * Align posted rows to the selected structure BEFORE the repeater field
 * writes them. Priority 5 must run before acf_field_repeater->update_value
 * (prio 10), which expects an array of rows and returns the integer count.
 */
add_filter('acf/update_value/key=field_hdp_pt_package_items', function ($value, $post_id) {
	$structure_id = hdp_pt_resolve_package_structure_id($post_id);

	if (! $structure_id) {
		return $value;
	}

	if (! is_array($value)) {
		$value = array();
	}

	if (isset($value['acfcloneindex'])) {
		unset($value['acfcloneindex']);
	}

	$saved = hdp_pt_get_saved_package_item_rows($post_id);
	$merged = hdp_pt_merge_package_item_rows($value, $saved);

	return hdp_pt_sync_package_items_value($merged, $structure_id, array());
}, 5, 2);

/**
 * Choices map for a structure's items (item_key => label).
 *
 * @param int $structure_id
 * @return array<string,string>
 */
function hdp_pt_structure_item_choices($structure_id)
{
	$choices = array();
	foreach (hdp_pt_get_structure_items($structure_id) as $item) {
		$choices[$item['key']] = $item['label'];
	}
	return $choices;
}

/**
 * Keep only exclusion keys that exist on the given structure.
 *
 * @param mixed $value
 * @param int   $structure_id
 * @return string[]
 */
function hdp_pt_filter_excluded_keys_for_structure($value, $structure_id)
{
	$keys = hdp_pt_normalize_excluded_keys($value);
	$valid = hdp_pt_structure_item_choices($structure_id);

	return array_values(array_filter($keys, function ($key) use ($valid) {
		return isset($valid[$key]);
	}));
}

/*
 * Block Exclude Items: choices from the selected Table Structure.
 */
add_filter('acf/load_field/key=field_hdp_pt_block_excluded_items', function ($field) {
	$structure_id = 0;

	if (isset($_REQUEST['hdp_pt_structure_id'])) {
		$structure_id = absint(wp_unslash($_REQUEST['hdp_pt_structure_id']));
	}

	if (! $structure_id && function_exists('get_field')) {
		$structure_id = absint(get_field('table_structure'));
	}

	$field['choices'] = hdp_pt_structure_item_choices($structure_id);

	return $field;
});

add_filter('acf/load_value/key=field_hdp_pt_block_excluded_items', function ($value, $post_id) {
	$structure_id = 0;
	if (function_exists('get_field')) {
		$structure_id = absint(get_field('table_structure', $post_id));
	}
	return hdp_pt_filter_excluded_keys_for_structure($value, $structure_id);
}, 10, 2);

add_filter('acf/update_value/key=field_hdp_pt_block_excluded_items', function ($value, $post_id) {
	$structure_id = 0;

	if (isset($_POST['acf']['field_hdp_pt_block_structure'])) {
		$raw = wp_unslash($_POST['acf']['field_hdp_pt_block_structure']);
		if (is_array($raw)) {
			$raw = reset($raw);
		}
		$structure_id = absint($raw);
	}

	if (! $structure_id && function_exists('get_field')) {
		$structure_id = absint(get_field('table_structure', $post_id));
	}

	return hdp_pt_filter_excluded_keys_for_structure($value, $structure_id);
}, 10, 2);

/**
 * Resolve the current package post ID while editing in admin / ACF AJAX.
 *
 * @return int
 */
function hdp_pt_current_package_post_id()
{
	if (isset($_GET['post'])) {
		$id = absint($_GET['post']);
		if ($id && 'hdp_pt_package' === get_post_type($id)) {
			return $id;
		}
	}

	if (function_exists('acf_get_form_data')) {
		$id = absint(acf_get_form_data('post_id'));
		if ($id && 'hdp_pt_package' === get_post_type($id)) {
			return $id;
		}
	}

	if (isset($_POST['post_id'])) {
		$id = absint($_POST['post_id']);
		if ($id && 'hdp_pt_package' === get_post_type($id)) {
			return $id;
		}
	}

	return 0;
}

/*
 * Mobile feature "Related Desktop Item" choices from the package Structure.
 */
add_filter('acf/load_field/key=field_hdp_pt_mobile_feature_related', function ($field) {
	$structure_id = 0;
	$package_id   = hdp_pt_current_package_post_id();

	if ($package_id) {
		$structure_id = hdp_pt_resolve_package_structure_id($package_id);
	}

	if (! $structure_id && isset($_REQUEST['hdp_pt_structure_id'])) {
		$structure_id = absint(wp_unslash($_REQUEST['hdp_pt_structure_id']));
	}

	$choices = array('' => '— Not linked (always show) —');
	foreach (hdp_pt_structure_item_choices($structure_id) as $key => $label) {
		$choices[$key] = $label;
	}

	$field['choices'] = $choices;

	return $field;
});

/**
 * Normalize mobile feature rows for a package.
 *
 * @param mixed $rows
 * @return array<int, array{label:string,icon_style:string,custom_icon:int,related_item_key:string}>
 */
function hdp_pt_normalize_mobile_features($rows)
{
	$out = array();

	if (! is_array($rows)) {
		return $out;
	}

	foreach ($rows as $row) {
		if (! is_array($row)) {
			continue;
		}

		$label = sanitize_text_field($row['label'] ?? '');
		if ($label === '') {
			continue;
		}

		$icon = sanitize_key($row['icon_style'] ?? 'check');
		if (! in_array($icon, array('check', 'plus', 'custom'), true)) {
			$icon = 'check';
		}

		$out[] = array(
			'label'            => $label,
			'icon_style'       => $icon,
			'custom_icon'      => absint($row['custom_icon'] ?? 0),
			'related_item_key' => sanitize_key($row['related_item_key'] ?? ''),
		);
	}

	return $out;
}

/**
 * Filter mobile features by block-level excluded structure keys.
 * Features without a related_item_key always remain visible.
 *
 * @param array  $features
 * @param array  $excluded_lookup item_key => true
 * @return array
 */
function hdp_pt_filter_mobile_features_by_exclusions($features, $excluded_lookup)
{
	if (! is_array($features) || ! $excluded_lookup) {
		return is_array($features) ? $features : array();
	}

	$out = array();
	foreach ($features as $feature) {
		$related = sanitize_key($feature['related_item_key'] ?? '');
		if ($related && isset($excluded_lookup[$related])) {
			continue;
		}
		$out[] = $feature;
	}

	return $out;
}
