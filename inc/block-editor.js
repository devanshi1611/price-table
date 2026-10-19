/**
 * Pricing Table block — editor helpers.
 *
 * - Filters Available Packages by the selected Table Structure.
 * - Populates / prunes Exclude Items choices from that structure.
 * Does not touch the package CPT editor (admin.php).
 */
(function ($) {
	'use strict';

	var STRUCTURE_KEY = 'field_hdp_pt_block_structure';
	var PACKAGES_KEY = 'field_hdp_pt_block_packages';
	var EXCLUDED_KEY = 'field_hdp_pt_block_excluded_items';

	function getFieldScope($el) {
		return $el.closest('.acf-block-fields, .acf-fields, .acf-block-body, .acf-block-component');
	}

	function getSiblingField(field, key) {
		if (!field || !field.$el) {
			return null;
		}

		var $scope = getFieldScope(field.$el);
		var $target = $scope.find('[data-key="' + key + '"]').first();

		if (!$target.length) {
			$target = $('[data-key="' + key + '"]').filter(function () {
				return getFieldScope($(this)).is($scope);
			}).first();
		}

		if (!$target.length || typeof acf.getField !== 'function') {
			return null;
		}

		return acf.getField($target);
	}

	function getStructureId(fromField) {
		var structureField = fromField && fromField.get && fromField.get('key') === STRUCTURE_KEY
			? fromField
			: getSiblingField(fromField, STRUCTURE_KEY);

		if (!structureField || typeof structureField.val !== 'function') {
			return 0;
		}

		var value = structureField.val();

		if (Array.isArray(value)) {
			value = value[0];
		}

		return parseInt(value, 10) || 0;
	}

	function getPackagesField(structureField) {
		return getSiblingField(structureField, PACKAGES_KEY);
	}

	function getExcludedField(structureField) {
		return getSiblingField(structureField, EXCLUDED_KEY);
	}

	function refreshPackagesChoices(packagesField) {
		if (!packagesField || typeof packagesField.fetch !== 'function') {
			return;
		}

		packagesField.set('paged', 1);
		packagesField.fetch();
	}

	/**
	 * When the structure is deliberately changed, drop selected packages
	 * that do not belong to the new structure. Does not clear on initial load.
	 */
	function pruneInvalidSelections(packagesField, structureId, done) {
		if (!packagesField || !structureId) {
			if (done) done();
			return;
		}

		var selected = packagesField.val() || [];
		if (!selected.length) {
			if (done) done();
			return;
		}

		$.post(ajaxurl, {
			action: 'hdp_pt_packages_for_structure',
			nonce: (window.hdpPtBlock && hdpPtBlock.nonce) || '',
			structure_id: structureId
		}).done(function (response) {
			if (!response || !response.success) {
				if (done) done();
				return;
			}

			var allowed = {};
			(response.data || []).forEach(function (id) {
				allowed[parseInt(id, 10)] = true;
			});

			var $values = packagesField.$list ? packagesField.$list('values') : packagesField.$('.values-list');
			var removed = false;

			$values.find('.acf-rel-item').each(function () {
				var $item = $(this);
				var id = parseInt($item.data('id'), 10);

				if (!allowed[id]) {
					removed = true;
					var $remove = $item.find('.acf-icon.-minus.cancel-link, a[data-name="remove_item"]').first();
					if ($remove.length) {
						$remove.trigger('click');
					} else if (typeof packagesField.onClickRemove === 'function') {
						packagesField.onClickRemove({}, $item);
					} else {
						$item.remove();
					}
				}
			});

			if (removed && typeof packagesField.showNotice === 'function') {
				packagesField.showNotice({
					text: 'Removed packages that do not belong to the selected structure.',
					type: 'warning'
				});
			}

			if (done) done();
		}).fail(function () {
			if (done) done();
		});
	}

	function setExcludeChoices(excludeField, items, keepSelected) {
		if (!excludeField) {
			return;
		}

		var selected = keepSelected ? (excludeField.val() || []) : [];
		if (!Array.isArray(selected)) {
			selected = selected ? [selected] : [];
		}

		var valid = {};
		(items || []).forEach(function (item) {
			valid[item.key] = item.label;
		});

		selected = selected.filter(function (key) {
			return !!valid[key];
		});

		if (typeof excludeField.set === 'function') {
			excludeField.set('choices', valid);
		}

		var $select = (excludeField.$input && excludeField.$input()) || excludeField.$('select').first();
		if ($select && $select.length) {
			$select.empty();
			(items || []).forEach(function (item) {
				var $opt = $('<option></option>').attr('value', item.key).text(item.label);
				if (selected.indexOf(item.key) !== -1) {
					$opt.prop('selected', true);
				}
				$select.append($opt);
			});
			$select.val(selected).trigger('change.select2');
		}

		if (typeof excludeField.val === 'function') {
			excludeField.val(selected);
		}
	}

	function refreshExcludeChoices(structureField, keepSelected, done) {
		var excludeField = getExcludedField(structureField);
		var structureId = getStructureId(structureField);

		if (!excludeField) {
			if (done) done();
			return;
		}

		if (!structureId) {
			setExcludeChoices(excludeField, [], false);
			if (done) done();
			return;
		}

		$.post(ajaxurl, {
			action: 'hdp_pt_block_structure_items',
			nonce: (window.hdpPtBlock && hdpPtBlock.nonce) || '',
			structure_id: structureId
		}).done(function (response) {
			var items = (response && response.success) ? (response.data || []) : [];
			setExcludeChoices(excludeField, items, keepSelected !== false);
			if (done) done();
		}).fail(function () {
			if (done) done();
		});
	}

	function onStructureChanged(structureField) {
		var packagesField = getPackagesField(structureField);
		var structureId = getStructureId(structureField);

		refreshExcludeChoices(structureField, false);

		if (!packagesField) {
			return;
		}

		pruneInvalidSelections(packagesField, structureId, function () {
			refreshPackagesChoices(packagesField);
		});
	}

	if (!window.acf) {
		return;
	}

	/* Pass the live (possibly unsaved) structure ID with relationship queries. */
	acf.addFilter('relationship_ajax_data', function (data, field) {
		if (!field || field.get('key') !== PACKAGES_KEY) {
			return data;
		}

		data.hdp_pt_structure_id = getStructureId(field);
		return data;
	});

	acf.addAction('ready_field/key=' + STRUCTURE_KEY, function (field) {
		field.on('change', function () {
			onStructureChanged(field);
		});
	});

	acf.addAction('ready_field/key=' + PACKAGES_KEY, function (field) {
		/* Initial choices for the currently selected (possibly unsaved) structure. */
		setTimeout(function () {
			refreshPackagesChoices(field);
		}, 50);
	});

	acf.addAction('ready_field/key=' + EXCLUDED_KEY, function (field) {
		var structureField = getSiblingField(field, STRUCTURE_KEY);
		if (!structureField) {
			return;
		}
		setTimeout(function () {
			refreshExcludeChoices(structureField, true);
		}, 50);
	});

})(jQuery);
