<?php
if (! defined('ABSPATH')) exit;

add_action('admin_head', function () {
	$s = get_current_screen();

	if (! $s || ! in_array($s->post_type, array('hdp_pt_package', 'hdp_pt_structure'), true)) {
		return;
	}
?>
	<style>
		/* Package item repeater */
		.hdp-pt-config-items .acf-row,
		.hdp-pt-config-items .layout {
			margin-bottom: 14px;
		}

		.hdp-pt-config-items .acf-fields>.acf-field {
			padding: 12px 16px;
		}

		/*
		 * Structure metadata fields are stored for mapping but not edited
		 * manually. The visible header is rendered by JS (.hdp-pt-item-meta).
		 */
		.hdp-pt-config-items [data-name="item_key"],
		.hdp-pt-config-items [data-name="item_label"],
		.hdp-pt-config-items [data-name="item_type"] {
			display: none !important;
		}

		/* Structure + Exclude Items sit side by side via ACF widths. */

		/* Full-section loader while Structure → Items sync runs. */
		.hdp-pt-config-items {
			position: relative;
		}

		.hdp-pt-config-items.hdp-pt-items-loading > .acf-input {
			position: relative;
			overflow: hidden !important;
			pointer-events: none;
			/* Height is locked via inline style in JS so row changes do not reflow. */
		}

		/*
		 * Take the repeater out of normal flow while loading so add/remove
		 * does not change the visible Items section height.
		 */
		.hdp-pt-config-items.hdp-pt-items-loading > .acf-input > .acf-repeater {
			position: absolute !important;
			left: 0 !important;
			right: 0 !important;
			top: 0 !important;
			width: 100% !important;
			opacity: 0 !important;
			visibility: hidden !important;
			pointer-events: none !important;
			z-index: 1;
		}

		.hdp-pt-items-loader {
			display: none;
			position: absolute;
			inset: 0;
			z-index: 20;
			align-items: center;
			justify-content: center;
			flex-direction: column;
			gap: 10px;
			background: rgba(255, 255, 255, 0.92);
			border: 1px solid #dcdcde;
			border-radius: 4px;
			box-sizing: border-box;
		}

		.hdp-pt-config-items.hdp-pt-items-loading .hdp-pt-items-loader {
			display: flex;
		}

		.hdp-pt-items-loader .spinner {
			float: none;
			margin: 0;
			visibility: visible;
		}


		/*
		 * Items are structure-controlled. Hide manual add/remove UI.
		 * Programmatic sync temporarily adds .hdp-pt-syncing to reveal Add.
		 */
		.hdp-pt-config-items > .acf-input > .acf-repeater > .acf-actions,
		.hdp-pt-config-items .acf-repeater > .acf-actions {
			display: none !important;
		}

		.hdp-pt-config-items.hdp-pt-syncing > .acf-input > .acf-repeater > .acf-actions,
		.hdp-pt-config-items.hdp-pt-syncing .acf-repeater > .acf-actions {
			display: block !important;
			position: absolute !important;
			left: -9999px !important;
			height: 0 !important;
			overflow: hidden !important;
		}

		.hdp-pt-config-items .acf-row-handle .acf-icon.-minus,
		.hdp-pt-config-items .acf-row-handle a[data-event="remove-row"],
		.hdp-pt-config-items .acf-row-handle .remove {
			display: none !important;
		}

		/*
		 * Package item order is controlled by Structure — disable drag/reorder UI.
		 * Structure CPT repeaters are intentionally left alone.
		 */
		.hdp-pt-config-items .acf-row-handle.order,
		.hdp-pt-config-items .acf-row-handle .acf-icon.-collapse + .acf-row-handle.order {
			pointer-events: none !important;
			cursor: default !important;
		}

		.hdp-pt-config-items .acf-row-handle.order {
			/* Keep the gutter for layout, but remove the drag affordance. */
			color: transparent !important;
			background-image: none !important;
		}

		.hdp-pt-config-items .ui-sortable-handle {
			cursor: default !important;
		}

		.hdp-pt-config-items.ui-sortable .acf-row,
		.hdp-pt-config-items .ui-sortable .acf-row {
			cursor: default !important;
		}


		.hdp-pt-hidden {
			display: none !important;
		}


		/* Structure metadata shown at the top of each package item. */
		.hdp-pt-config-items .hdp-pt-item-meta {
			display: flex;
    		gap: 10px;
			width: 100%;
			box-sizing: border-box;
			padding: 10px 14px;
			margin: 0 0 10px;
			background: #f6f7f7;
			border-bottom: 1px solid #dcdcde;
			color: #1d2327;
		}

		.hdp-pt-config-items .hdp-pt-item-meta__label {
			font-weight: 600;
			font-size: 14px;
			line-height: 1.4;
		}

		.hdp-pt-config-items .hdp-pt-item-meta__type {
			margin-top: 2px;
			font-weight: 400;
			font-size: 12px;
			color: #646970;
			line-height: 1.4;
		}

		/* Price group: Title, Currency and Price share one row. */
		[data-name="price"]>.acf-input>.acf-fields>.acf-field {
			float: left !important;
			clear: none !important;
			box-sizing: border-box;
		}

		[data-name="price"]>.acf-input>.acf-fields>.acf-field[data-width="33.33"] {
			width: 33.33% !important;
		}

		[data-name="price"]>.acf-input>.acf-fields>.acf-field[data-width="33.34"] {
			width: 33.34% !important;
		}

		[data-name="price"]>.acf-input>.acf-fields:after {
			content: '';
			display: table;
			clear: both;
		}

		/*
		 * Package item fields use two columns.
		 * JavaScript controls which fields are relevant to item_type.
		 */
		.hdp-pt-config-items .acf-row>.acf-fields>.acf-field[data-name="value"],
		.hdp-pt-config-items .acf-row>.acf-fields>.acf-field[data-name="tooltip"],
		.hdp-pt-config-items .acf-row>.acf-fields>.acf-field[data-name="icon"],
		.hdp-pt-config-items .acf-row>.acf-fields>.acf-field[data-name="link"],
		.hdp-pt-config-items .layout>.acf-fields>.acf-field[data-name="value"],
		.hdp-pt-config-items .layout>.acf-fields>.acf-field[data-name="tooltip"],
		.hdp-pt-config-items .layout>.acf-fields>.acf-field[data-name="icon"],
		.hdp-pt-config-items .layout>.acf-fields>.acf-field[data-name="link"] {
			float: left !important;
			clear: none !important;
			width: 50% !important;
			box-sizing: border-box !important;
		}

		.hdp-pt-config-items .acf-row>.acf-fields:after,
		.hdp-pt-config-items .layout>.acf-fields:after {
			content: '';
			display: table;
			clear: both;
		}

		/* Structure items: block layout — hide internal key, keep drag/reorder. */
		.hdp-pt-structure-items [data-name="item_key"],
		.hdp-pt-structure-items .hdp-pt-internal-key {
			display: none !important;
		}

		.hdp-pt-structure-items > .acf-input > .acf-repeater > .acf-table,
		.hdp-pt-structure-items .acf-repeater {
			width: 100%;
		}

		.hdp-pt-structure-items .acf-row:not(.acf-clone) {
			margin-bottom: 10px;
			border: 1px solid #dcdcde;
			border-radius: 4px;
			background: #fff;
		}

		.hdp-pt-structure-items .acf-row-handle.order {
			width: 20px !important;
			background: #f6f7f7;
		}

		.hdp-pt-structure-items .acf-fields {
			padding: 8px 12px 4px !important;
		}
	</style>
<?php
});

add_action('admin_footer-post.php', 'hdp_pt_admin_js');
add_action('admin_footer-post-new.php', 'hdp_pt_admin_js');

function hdp_pt_admin_js()
{
	$s = get_current_screen();

	if (! $s || 'hdp_pt_package' !== $s->post_type || ! function_exists('acf')) {
		return;
	}

	$nonce = wp_create_nonce('hdp_pt_sync_items');
	$post_id = absint(isset($_GET['post']) ? $_GET['post'] : 0);
	$initial_cache = array();

	if ($post_id) {
		foreach (hdp_pt_get_saved_package_item_rows($post_id) as $row) {
			$key = sanitize_key((string) (hdp_pt_package_row_get($row, 'item_key') ?? ''));
			if (! $key) {
				continue;
			}
			$initial_cache[$key] = array(
				'value' => (string) (hdp_pt_package_row_get($row, 'value') ?? ''),
				'tooltip' => (string) (hdp_pt_package_row_get($row, 'tooltip') ?? ''),
				'item_label' => (string) (hdp_pt_package_row_get($row, 'item_label') ?? ''),
				'item_type' => (string) (hdp_pt_package_row_get($row, 'item_type') ?? ''),
				'icon' => hdp_pt_package_row_get($row, 'icon'),
				'link' => hdp_pt_package_row_get($row, 'link'),
			);
		}
	}
?>
	<script>
		(function($) {
			'use strict';

			var nonce = <?php echo wp_json_encode($nonce); ?>;
			var STRUCTURE_KEY = 'field_hdp_pt_package_structure';
			var ITEMS_KEY = 'field_hdp_pt_package_items';
			var rowValueCache = <?php echo wp_json_encode($initial_cache); ?> || {};
			var structureItemsCache = [];
			var syncing = false;
			var pendingSync = null;
			var lastStructureId = null;
			var editorReady = false;

			function getPackageStructureField() {
				return $('[data-key="' + STRUCTURE_KEY + '"]').first();
			}

			function getPackageItemsField() {
				return $('[data-key="' + ITEMS_KEY + '"]').first();
			}

			function getItemsInput($items) {
				if (!$items || !$items.length) {
					return $();
				}
				var $input = $items.children('.acf-input').first();
				if (!$input.length) {
					$input = $items.find('> .acf-input').first();
				}
				return $input;
			}

			function ensureItemsLoader($items) {
				var $input = getItemsInput($items);
				if (!$input.length) {
					return $();
				}

				var $loader = $input.children('.hdp-pt-items-loader');
				if (!$loader.length) {
					$loader = $(
						'<div class="hdp-pt-items-loader" aria-hidden="true">' +
							'<span class="spinner is-active"></span>' +
						'</div>'
					);
					$input.append($loader);
				}

				return $loader;
			}

			function lockItemsSectionHeight($items, height) {
				var $input = getItemsInput($items);
				if (!$input.length) {
					return;
				}
				var locked = Math.max(parseInt(height, 10) || 0, 120);
				$input.css({
					height: locked + 'px',
					minHeight: locked + 'px',
					overflow: 'hidden'
				});
			}

			function measureItemsContentHeight($items) {
				var $input = getItemsInput($items);
				var $repeater = $input.children('.acf-repeater').first();
				if (!$repeater.length) {
					$repeater = $input.find('.acf-repeater').first();
				}
				if (!$repeater.length) {
					return 120;
				}

				var height = $repeater.outerHeight(true) || 0;
				if (!height && $repeater[0]) {
					height = $repeater[0].scrollHeight || 0;
				}
				return Math.max(height, 60);
			}

			function showItemsLoader($items) {
				if (!$items.length) {
					return;
				}

				var $input = getItemsInput($items);
				ensureItemsLoader($items);

				var currentHeight = $input.outerHeight() || $items.outerHeight() || 120;
				lockItemsSectionHeight($items, currentHeight);
				$items.addClass('hdp-pt-items-loading');
			}

			function hideItemsLoader($items) {
				if (!$items.length) {
					return;
				}

				var $input = getItemsInput($items);

				function reveal() {
					var finalHeight = measureItemsContentHeight($items);
					lockItemsSectionHeight($items, finalHeight);

					window.requestAnimationFrame(function() {
						$items.removeClass('hdp-pt-items-loading');
						window.requestAnimationFrame(function() {
							$input.css({
								height: '',
								minHeight: '',
								overflow: ''
							});
						});
					});
				}

				window.requestAnimationFrame(function() {
					setTimeout(reveal, 20);
				});
			}

			function getAcfField($el) {
				if (!$el || !$el.length || typeof acf.getField !== 'function') {
					return null;
				}
				try {
					return acf.getField($el);
				} catch (e) {
					return null;
				}
			}

			function getField($scope, name) {
				var $direct = $scope.children('.acf-fields').children('.acf-field[data-name="' + name + '"]');
				if ($direct.length) {
					return $direct.first();
				}
				return $scope.find('.acf-field[data-name="' + name + '"]').first();
			}

			function getInput($scope, name) {
				var $field = getField($scope, name);
				return $field.find('input[type="text"], input[type="hidden"], textarea, select').filter(':not([type="checkbox"]):not([type="radio"])').first();
			}

			function setInput($scope, name, value) {
				var $input = getInput($scope, name);

				if (!$input.length) {
					return;
				}

				$input.prop('disabled', false).val(value == null ? '' : value).trigger('change');
			}

			function getValue($scope, name) {
				var $input = getInput($scope, name);
				return $input.length ? String($input.val() || '') : '';
			}

			function getStructureId($structure) {
				if (!$structure || !$structure.length) {
					return '';
				}

				var field = getAcfField($structure);
				if (field && typeof field.val === 'function') {
					var v = field.val();
					if (Array.isArray(v)) {
						v = v[0];
					}
					if (v) {
						return String(v);
					}
				}

				var $select = $structure.find('select').first();
				if ($select.length && $select.val()) {
					return String($select.val());
				}

				var $input = $structure.find('input[type="hidden"]').first();
				return $input.length ? String($input.val() || '') : '';
			}

			function typeLabel(type) {
				var labels = {
					icon: 'Icon with Tooltip',
					text: 'Plain text',
					text_tooltip: 'Plain text with tooltip',
					text_link: 'Text with link'
				};

				return labels[type] || '';
			}

			function getItemRows($items) {
				var rows = [];
				var $table = $items.find('.acf-repeater').first();
				var $scope = $table.length ? $table : $items;
				var selector = [
					'> .acf-list > .layout:not(.acf-clone):not(.acf-deleted)',
					'> .acf-blocks > .layout:not(.acf-clone):not(.acf-deleted)',
					'> table > tbody > .acf-row:not(.acf-clone):not(.acf-deleted)',
					'> .acf-row:not(.acf-clone):not(.acf-deleted)'
				].join(', ');

				function pushRow($row) {
					if ($row.hasClass('acf-deleted') || !$row.closest('body').length) {
						return;
					}
					rows.push({
						row: $row,
						key: getValue($row, 'item_key')
					});
				}

				$scope.find(selector).each(function() {
					pushRow($(this));
				});

				if (!rows.length) {
					$items.find('.acf-row:not(.acf-clone):not(.acf-deleted), .layout:not(.acf-clone):not(.acf-deleted)').each(function() {
						var $row = $(this);
						if ($row.closest('[data-key="' + ITEMS_KEY + '"]').attr('data-key') !== ITEMS_KEY) {
							return;
						}
						pushRow($row);
					});
				}

				return rows;
			}

			function showField($field) {
				if (!$field.length) {
					return;
				}

				$field.removeClass('acf-hidden hdp-pt-hidden');
				$field[0].style.removeProperty('display');
			}

			function hideField($field) {
				if (!$field.length) {
					return;
				}

				$field.removeClass('acf-hidden');
				$field.addClass('hdp-pt-hidden');
				$field[0].style.setProperty('display', 'none', 'important');
			}

			function decorateItemMeta($row, item) {
				var $fields = $row.children('.acf-fields').first();
				if (!$fields.length) {
					$fields = $row.find('.acf-fields').first();
				}
				if (!$fields.length) {
					return;
				}

				var label = item && item.label ? item.label : getValue($row, 'item_label');
				var type = item && item.type ? item.type : getValue($row, 'item_type');
				var typeText = item && item.type_label ? item.type_label : typeLabel(type);

				if (!label) {
					return;
				}

				var $meta = $fields.children('.hdp-pt-item-meta');

				if (!$meta.length) {
					$meta = $('<div class="hdp-pt-item-meta"></div>');
					$fields.prepend($meta);
				}

				$meta.html(
					'<div class="hdp-pt-item-meta__label"></div>' +
					'<div class="hdp-pt-item-meta__type"></div>'
				);
				$meta.find('.hdp-pt-item-meta__label').text(label);
				$meta.find('.hdp-pt-item-meta__type').text(typeText ? '(' + typeText + ')' : '');
			}

			function refreshVisibility($row, forcedType) {
				var type = forcedType || getValue($row, 'item_type');

				if (!type) {
					return;
				}

				var $value = getField($row, 'value');
				var $tooltip = getField($row, 'tooltip');
				var $icon = getField($row, 'icon');
				var $link = getField($row, 'link');

				hideField($value);
				hideField($tooltip);
				hideField($icon);
				hideField($link);

				switch (type) {
					case 'icon':
						showField($tooltip);
						showField($icon);
						break;

					case 'text':
						showField($value);
						break;

					case 'text_tooltip':
						showField($value);
						showField($tooltip);
						break;

					case 'text_link':
						showField($link);
						break;
				}
			}

			function decorateItems($items) {
				if (!$items.length) {
					return;
				}

				getItemRows($items).forEach(function(entry) {
					var label = getValue(entry.row, 'item_label');
					var type = getValue(entry.row, 'item_type');

					if (label) {
						decorateItemMeta(entry.row, {
							label: label,
							type: type,
							type_label: typeLabel(type)
						});
					}

					refreshVisibility(entry.row, type);
				});
			}

			function cacheRowValues($row, key) {
				if (!key) {
					return;
				}

				var cached = {
					value: getValue($row, 'value'),
					tooltip: getValue($row, 'tooltip'),
					item_label: getValue($row, 'item_label'),
					item_type: getValue($row, 'item_type'),
					icon: null,
					link: null
				};

				var iconField = getAcfField(getField($row, 'icon'));
				if (iconField && typeof iconField.val === 'function') {
					cached.icon = iconField.val();
				}

				var linkField = getAcfField(getField($row, 'link'));
				if (linkField && typeof linkField.val === 'function') {
					cached.link = linkField.val();
				}

				rowValueCache[key] = cached;
			}

			function cacheAllRows($items) {
				getItemRows($items).forEach(function(entry) {
					if (entry.key) {
						cacheRowValues(entry.row, entry.key);
					}
				});
			}

			function restoreRowValues($row, key) {
				var cached = rowValueCache[key];
				if (!cached) {
					return;
				}

				if (cached.value != null) {
					setInput($row, 'value', cached.value);
				}
				if (cached.tooltip != null) {
					setInput($row, 'tooltip', cached.tooltip);
				}

				var iconField = getAcfField(getField($row, 'icon'));
				if (iconField && cached.icon != null && typeof iconField.val === 'function') {
					iconField.val(cached.icon);
				}

				var linkField = getAcfField(getField($row, 'link'));
				if (linkField && cached.link != null && typeof linkField.val === 'function') {
					linkField.val(cached.link);
				}
			}

			function removeItemRowSilent($items, $row, key, done) {
				if (key) {
					cacheRowValues($row, key);
				}

				if (!$row || !$row.length) {
					if (done) done();
					return;
				}

				var field = getAcfField($items);
				if (field && typeof field.remove === 'function') {
					try {
						field.remove($row);
					} catch (e) {
						$row.remove();
					}
				} else {
					$row.remove();
				}

				var attempts = 0;
				(function waitGone() {
					var gone = !$row.length ||
						!$row.closest('body').length ||
						$row.hasClass('acf-deleted') ||
						!$row.parent().length;

					if (gone || ++attempts > 40) {
						if (!gone && $row.length) {
							$row.remove();
						}
						if (done) done();
						return;
					}
					setTimeout(waitGone, 40);
				})();
			}

			function clearAllItemRows($items, done) {
				cacheAllRows($items);

				function removeNext() {
					var rows = getItemRows($items);
					if (!rows.length) {
						if (done) done();
						return;
					}
					removeItemRowSilent($items, rows[0].row, rows[0].key, removeNext);
				}

				removeNext();
			}

			function getStructureItems(structureId, done) {
				$.post(ajaxurl, {
					action: 'hdp_pt_get_structure_items',
					nonce: nonce,
					structure_id: structureId
				}, function(response) {
					if (response && response.success) {
						structureItemsCache = response.data || [];
						done(structureItemsCache);
					} else {
						alert('Could not load the structure items.');
						done([]);
					}
				}, 'json').fail(function() {
					alert('Could not load the structure items.');
					done([]);
				});
			}

			function addItemRow($items, item, done) {
				var field = getAcfField($items);
				var $row = null;

				if (field && typeof field.add === 'function') {
					try {
						$row = field.add();
					} catch (e) {
						$row = null;
					}
				}

				if (!$row || !$row.length) {
					var $add = $items.find('.acf-actions .acf-button, a[data-event="add-row"]').first();
					if (!$add.length) {
						if (done) done();
						return;
					}

					var before = getItemRows($items).length;
					$items.addClass('hdp-pt-syncing');
					$add.trigger('click');

					var attempts = 0;

					(function waitForRow() {
						var rows = getItemRows($items);
						if (rows.length > before) {
							$row = rows[rows.length - 1].row;
							fillAddedRow($row, item);
							$items.removeClass('hdp-pt-syncing');
							if (done) done($row);
							return;
						}
						if (++attempts > 40) {
							$items.removeClass('hdp-pt-syncing');
							if (done) done(null);
							return;
						}
						setTimeout(waitForRow, 75);
					})();
					return;
				}

				fillAddedRow($row, item);
				if (done) done($row);
			}

			function fillAddedRow($row, item) {
				setInput($row, 'item_key', item.key);
				setInput($row, 'item_label', item.label);
				setInput($row, 'item_type', item.type);
				restoreRowValues($row, item.key);

				/*
				 * New/restored icon items: use Structure default when the
				 * package has no cached/custom icon yet.
				 */
				if (item.type === 'icon' && item.icon) {
					var iconField = getAcfField(getField($row, 'icon'));
					if (iconField && typeof iconField.val === 'function') {
						var current = iconField.val();
						if (!current) {
							iconField.val(item.icon);
						}
					}
				}

				decorateItemMeta($row, item);
				refreshVisibility($row, item.type);
			}

			function findStructureItem(key) {
				for (var i = 0; i < structureItemsCache.length; i++) {
					if (structureItemsCache[i].key === key) {
						return structureItemsCache[i];
					}
				}
				return null;
			}

			/**
			 * Rebuild Items in Structure order after a Structure change.
			 * Not used on initial page load.
			 */
			function syncItems($items, structureId, callback) {
				if (!structureId || !$items.length) {
					hideItemsLoader($items);
					if (callback) callback();
					return;
				}

				if (syncing) {
					pendingSync = {
						items: $items,
						structureId: structureId,
						callback: callback
					};
					showItemsLoader($items);
					return;
				}

				syncing = true;
				pendingSync = null;
				showItemsLoader($items);

				getStructureItems(structureId, function(structureItems) {
					cacheAllRows($items);

					clearAllItemRows($items, function() {
						function addNext(index) {
							if (index >= structureItems.length) {
								decorateItems($items);
								disableItemsSortable($items);
								finishSync(callback);
								return;
							}

							addItemRow($items, structureItems[index], function() {
								addNext(index + 1);
							});
						}

						addNext(0);
					});
				});
			}

			function finishSync(callback) {
				var $items = getPackageItemsField();
				syncing = false;

				if (pendingSync) {
					var next = pendingSync;
					pendingSync = null;
					syncItems(next.items, next.structureId, next.callback);
					return;
				}

				hideItemsLoader($items);
				if (callback) {
					callback();
				}
			}

			function replaceItemsForStructure($items, structureId, callback) {
				showItemsLoader($items);

				if (!structureId) {
					clearAllItemRows($items, function() {
						hideItemsLoader($items);
						if (callback) callback();
					});
					return;
				}

				syncItems($items, structureId, callback);
			}

			var structureChangeTimer = null;

			function onStructureChanged() {
				if (!editorReady) {
					return;
				}

				if (structureChangeTimer) {
					clearTimeout(structureChangeTimer);
				}

				structureChangeTimer = setTimeout(function() {
					structureChangeTimer = null;

					var $structure = getPackageStructureField();
					var $items = getPackageItemsField();
					var structureId = getStructureId($structure) || '';

					if (String(structureId) === String(lastStructureId || '')) {
						return;
					}

					lastStructureId = structureId;
					showItemsLoader($items);
					replaceItemsForStructure($items, structureId);
				}, 50);
			}

			function disableItemsSortable($items) {
				if (!$items || !$items.length) {
					return;
				}

				var $repeater = $items.find('.acf-repeater').first();
				if (!$repeater.length) {
					return;
				}

				try {
					if ($repeater.hasClass('ui-sortable') && typeof $repeater.sortable === 'function') {
						$repeater.sortable('destroy');
					}
				} catch (e) {}

				$repeater.find('.acf-row-handle.order').attr({
					title: '',
					'aria-disabled': 'true'
				});
			}

			function prepareItemsForSave() {
				var $items = getPackageItemsField();
				if (!$items.length) {
					return true;
				}

				if (syncing) {
					window.alert('Please wait for package items to finish updating before saving.');
					return false;
				}

				cacheAllRows($items);

				getItemRows($items).forEach(function(entry) {
					if (!entry.key) {
						return;
					}

					var item = findStructureItem(entry.key);
					if (item) {
						setInput(entry.row, 'item_key', item.key);
						setInput(entry.row, 'item_label', item.label);
						setInput(entry.row, 'item_type', item.type);
					}
				});

				return true;
			}

			function setupPackageEditor() {
				var $structure = getPackageStructureField();
				var $items = getPackageItemsField();

				if (!$structure.length || !$items.length) {
					editorReady = true;
					return;
				}

				/*
				 * Page load: do NOT sync/rebuild. ACF already rendered the
				 * saved Structure + Items.
				 */
				decorateItems($items);
				disableItemsSortable($items);

				lastStructureId = getStructureId($structure) || '';
				editorReady = true;
			}

			function boot() {
				setupPackageEditor();
			}

			if (window.acf && typeof acf.addAction === 'function') {
				acf.addAction('ready', boot);
				acf.addAction('ready_field/key=' + ITEMS_KEY, function(field) {
					if (!field) {
						return;
					}
					if (typeof field.addSortable === 'function') {
						field.addSortable = function() {};
					}
					if (typeof field.off === 'function') {
						field.off('mouseover');
					}
					disableItemsSortable(field.$el || getPackageItemsField());
				});
				acf.addAction('append', function($el) {
					setTimeout(function() {
						var $items = getPackageItemsField();
						if ($items.length && $el.closest('[data-key="' + ITEMS_KEY + '"]').length) {
							decorateItems($items);
							disableItemsSortable($items);
						}
					}, 50);
				});
				acf.addAction('change_field/key=' + STRUCTURE_KEY, function() {
					onStructureChanged();
				});
			} else {
				$(document).ready(boot);
			}

			$(document).on('change', '[data-key="' + ITEMS_KEY + '"] [data-name="item_type"] input', function() {
				refreshVisibility($(this).closest('.acf-row, .layout'));
			});

			$(document).on('change', '[data-key="' + STRUCTURE_KEY + '"] select', function() {
				onStructureChanged();
			});

			$(document).on('click', '#publish, #save-post', function(e) {
				if (!prepareItemsForSave()) {
					e.preventDefault();
					return false;
				}
			});

			$('#post').on('submit', function(e) {
				if (!prepareItemsForSave()) {
					e.preventDefault();
					return false;
				}
			});

		})(jQuery);
	</script>
<?php
}

/**
 * Structure CPT: when Value Type becomes "icon", seed Default Icon with circle.png
 * if the image field is still empty (editable afterward).
 */
add_action('admin_footer-post.php', 'hdp_pt_structure_admin_js');
add_action('admin_footer-post-new.php', 'hdp_pt_structure_admin_js');

function hdp_pt_structure_admin_js()
{
	$s = get_current_screen();

	if (! $s || 'hdp_pt_structure' !== $s->post_type || ! function_exists('acf')) {
		return;
	}

	$circle_id = hdp_pt_get_circle_icon_attachment_id();
	if (! $circle_id) {
		return;
	}
?>
	<script>
		(function($) {
			'use strict';

			var CIRCLE_ICON_ID = <?php echo (int) $circle_id; ?>;
			var TYPE_KEY = 'field_hdp_pt_value_type';
			var ICON_KEY = 'field_hdp_pt_structure_default_icon';

			function ensureDefaultIcon($row) {
				if (!$row || !$row.length) {
					return;
				}

				var $type = $row.find('[data-key="' + TYPE_KEY + '"]').first();
				var typeVal = '';
				var typeField = (typeof acf.getField === 'function') ? acf.getField($type) : null;

				if (typeField && typeof typeField.val === 'function') {
					typeVal = String(typeField.val() || '');
				} else {
					typeVal = String($type.find('select').val() || '');
				}

				if (typeVal !== 'icon') {
					return;
				}

				var $icon = $row.find('[data-key="' + ICON_KEY + '"]').first();
				var iconField = (typeof acf.getField === 'function') ? acf.getField($icon) : null;
				if (!iconField || typeof iconField.val !== 'function') {
					return;
				}

				var current = iconField.val();
				if (!current) {
					iconField.val(CIRCLE_ICON_ID);
				}
			}

			if (window.acf && typeof acf.addAction === 'function') {
				acf.addAction('ready', function() {
					$('.hdp-pt-structure-items .acf-row:not(.acf-clone)').each(function() {
						ensureDefaultIcon($(this));
					});
				});

				acf.addAction('append', function($el) {
					var $row = $el && $el.hasClass('acf-row') ? $el : $el.find('.acf-row').first();
					if ($row.length && $row.closest('.hdp-pt-structure-items').length) {
						setTimeout(function() {
							ensureDefaultIcon($row);
						}, 50);
					}
				});

				acf.addAction('change_field/key=' + TYPE_KEY, function(field) {
					if (!field || !field.$el) {
						return;
					}
					var $row = field.$el.closest('.acf-row');
					setTimeout(function() {
						ensureDefaultIcon($row);
					}, 50);
				});
			}
		})(jQuery);
	</script>
<?php
}

add_action('wp_ajax_hdp_pt_get_structure_items', function () {

	check_ajax_referer('hdp_pt_sync_items', 'nonce');

	if (! current_user_can('edit_posts')) {
		wp_send_json_error('Permission denied', 403);
	}

	$id = absint($_POST['structure_id'] ?? 0);

	if ('hdp_pt_structure' !== get_post_type($id)) {
		wp_send_json_error('Invalid structure');
	}

	wp_send_json_success(hdp_pt_get_structure_items($id));
});
