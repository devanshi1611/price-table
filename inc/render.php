<?php
if (! defined('ABSPATH')) exit;

/**
 * Render a single package cell value for a structure item type.
 *
 * @param string $type Item value_type.
 * @param array  $r    Package item row data.
 */
function hdp_pt_render_package_value($type, $r)
{
	$value       = $r['value'] ?? '';
	$tooltip     = $r['tooltip'] ?? '';
	$icon        = absint($r['icon'] ?? 0);
	$link        = $r['link'] ?? '';
	$link_title  = $r['link_title'] ?? '';
	$link_target = $r['link_target'] ?? '_self';

	if ('icon' === $type) {
		if ($icon) {
			$icon_html = wp_get_attachment_image(
				$icon,
				'thumbnail',
				false,
				array('class' => 'hdp-pt__icon')
			);

			if ($tooltip) {
				echo '<span class="hdp-pt__icon-tooltip" data-tooltip="' . esc_attr($tooltip) . '" aria-label="' . esc_attr($tooltip) . '">';
				echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '</span>';
			} else {
				echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		} else {
			echo '<span class="hdp-pt__empty">—</span>';
		}
		return;
	}

	if ('text_tooltip' === $type) {
		if ($value !== '') {
			if ($tooltip) {
				echo '<span class="hdp-pt__tooltip-text" data-tooltip="' . esc_attr($tooltip) . '">';
				echo wp_kses_post($value);
				echo '</span>';
			} else {
				echo '<span>' . wp_kses_post($value) . '</span>';
			}
		} else {
			echo '<span class="hdp-pt__empty">—</span>';
		}
		return;
	}

	if ('text_link' === $type) {
		if ($link_title !== '') {
			echo '<span class="hdp-pt__text-link">';
			echo '<span class="hdp-pt__text-link-label">' . esc_html($link_title) . '</span>';
			if ($link) {
				echo '<a class="hdp-pt__link-icon" href="' . esc_url($link) . '" target="' . esc_attr($link_target) . '"'
					. ('_blank' === $link_target ? ' rel="noopener noreferrer"' : '')
					. ' aria-label="' . esc_attr($link_title) . '">';
				echo '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 5h5v5M19 5l-8 8M19 13v4a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>';
				echo '</a>';
			}
			echo '</span>';
		} else {
			echo '<span class="hdp-pt__empty">—</span>';
		}
		return;
	}

	echo $value !== ''
		? '<span>' . wp_kses_post($value) . '</span>'
		: '<span class="hdp-pt__empty">—</span>';
}

/**
 * Inline SVG or custom image for mobile feature bullets.
 *
 * @param array|string $feature Feature row, or legacy icon_style string.
 */
function hdp_pt_render_mobile_feature_icon($feature)
{
	if (is_string($feature)) {
		$feature = array('icon_style' => $feature);
	}

	$style = sanitize_key($feature['icon_style'] ?? 'check');

	if ('custom' === $style) {
		$icon_id = absint($feature['custom_icon'] ?? 0);
		if ($icon_id) {
			echo wp_get_attachment_image(
				$icon_id,
				'thumbnail',
				false,
				array(
					'class'    => 'hdp-pt-mob__icon hdp-pt-mob__icon--custom',
					'alt'      => '',
					'loading'  => 'lazy',
					'decoding' => 'async',
				)
			);
			return;
		}
		echo '<span class="hdp-pt__empty hdp-pt-mob__icon-empty">—</span>';
		return;
	}

	if ('plus' === $style) {
		echo '<svg class="hdp-pt-mob__icon hdp-pt-mob__icon--plus" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M8 3v10M3 8h10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
		return;
	}

	echo '<svg class="hdp-pt-mob__icon hdp-pt-mob__icon--check" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8.5 6.5 12 13 4.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

/**
 * Resolve mobile badge text for a package.
 *
 * @param array $p Package render data.
 * @return string
 */
function hdp_pt_package_mobile_badge($p)
{
	$badge = trim((string) ($p['mobile_badge'] ?? ''));
	if ($badge !== '') {
		return $badge;
	}
	if (! empty($p['featured'])) {
		return 'Aanbevolen';
	}
	return '';
}

function hdp_pt_render_block($block, $content = '', $is_preview = false)
{
	$structure_id = absint(get_field('table_structure'));
	$package_ids  = array_slice(array_map('absint', (array) get_field('packages')), 0, 5);
	$excluded     = hdp_pt_normalize_excluded_keys(get_field('excluded_items'));
	$excluded_lookup = array_fill_keys($excluded, true);

	if (! $structure_id || ! $package_ids) {
		if ($is_preview) {
			echo '<div class="hdp-pt-editor-notice">Select a table structure and up to 5 packages.</div>';
		}
		return;
	}

	$sections = get_field('sections', $structure_id);
	if (! is_array($sections)) {
		return;
	}

	$packages = array();

	foreach ($package_ids as $id) {
		if ('hdp_pt_package' !== get_post_type($id)) {
			continue;
		}

		/* Only packages belonging to the selected structure are valid. */
		if (absint(get_field('structure', $id)) !== $structure_id) {
			continue;
		}

		$price = (array) get_field('price', $id);
		$rows  = (array) get_field('items', $id);
		$items = array();

		foreach ($rows as $row) {
			$key = sanitize_key($row['item_key'] ?? '');
			if (! $key) {
				continue;
			}

			$link = (array) ($row['link'] ?? array());
			$link_target = $link['target'] ?? '_self';

			if (! in_array($link_target, array('_self', '_blank'), true)) {
				$link_target = '_self';
			}

			$items[$key] = array(
				'value'       => wp_kses_post($row['value'] ?? ''),
				'tooltip'     => sanitize_textarea_field($row['tooltip'] ?? ''),
				'icon'        => absint($row['icon'] ?? 0),
				'link'        => esc_url_raw($link['url'] ?? ''),
				'link_title'  => sanitize_text_field($link['title'] ?? ''),
				'link_target' => $link_target,
				'item_type'   => sanitize_key($row['item_type'] ?? ''),
			);
		}

		$button = (array) get_field('button_link', $id);

		$mobile_badge = sanitize_text_field((string) get_field('mobile_badge', $id));
		$mobile_details_label = sanitize_text_field((string) get_field('mobile_details_label', $id));
		if ($mobile_details_label === '') {
			$mobile_details_label = 'Bekijk wat erin zit';
		}

		$packages[] = array(
			'id'                   => $id,
			'title'                => ! empty($price['title']) ? $price['title'] : get_the_title($id),
			'price'                => $price,
			'subtext'              => get_field('subtext', $id),
			'button'               => $button,
			'featured'             => (bool) get_field('featured', $id),
			'items'                => $items,
			'mobile_subtitle'      => sanitize_text_field((string) get_field('mobile_subtitle', $id)),
			'mobile_badge'         => $mobile_badge,
			'mobile_details_label' => $mobile_details_label,
			'mobile_features'      => hdp_pt_filter_mobile_features_by_exclusions(
				hdp_pt_normalize_mobile_features(get_field('mobile_features', $id)),
				$excluded_lookup
			),
		);
	}

	if (! $packages) {
		return;
	}

	$anchor = ! empty($block['anchor']) ? $block['anchor'] : 'hdp-pt-' . wp_unique_id();
	$cols   = count($packages);

	$wrapper_attributes = get_block_wrapper_attributes(
		array(
			'class' => 'hdp-pt',
			'id'    => $anchor,
			'style' => '--hdp-pt-cols: ' . (string) absint($cols) . ';',
		)
	);
	?>
	<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — escaped by get_block_wrapper_attributes() ?>>
		<div class="hdp-pt__grid" role="table" aria-label="Pricing comparison">

			<div class="hdp-pt__header" role="row">
				<div class="hdp-pt__feature-column" role="columnheader"></div>
				<?php foreach ($packages as $p) :
					$featured = $p['featured'] ? ' is-featured' : '';
					$price    = $p['price'];
					$button   = $p['button'];
					?>
					<div class="hdp-pt__package-column<?php echo esc_attr($featured); ?>" role="columnheader">
						<div class="hdp-pt__package-inner">
							<h3 class="hdp-pt__title"><?php echo esc_html($p['title']); ?></h3>
							<div class="hdp-pt__price">
								<span class="hdp-pt__currency"><?php echo esc_html($price['currency'] ?? ''); ?></span>
								<span class="hdp-pt__amount"><?php echo esc_html($price['amount'] ?? ''); ?></span>
							</div>
							<?php if (! empty($p['subtext'])) : ?>
								<div class="hdp-pt__subtext"><?php echo esc_html($p['subtext']); ?></div>
							<?php endif; ?>
							<?php if (! empty($button['url'])) : ?>
								<a class="hdp-pt__button"
									href="<?php echo esc_url($button['url']); ?>"
									target="<?php echo esc_attr($button['target'] ?? '_self'); ?>"
									<?php echo '_blank' === ($button['target'] ?? '_self') ? ' rel="noopener noreferrer"' : ''; ?>>
									<?php echo esc_html($button['title'] ?: 'Aanvragen'); ?>
								</a>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<?php foreach ((array) $sections as $section) :
				$section_items = array();

				foreach ((array) ($section['items'] ?? array()) as $item) {
					$key = ! empty($item['item_key'])
						? sanitize_key($item['item_key'])
						: hdp_pt_make_item_key($item['label'] ?? '');

					if (! $key || isset($excluded_lookup[$key])) {
						continue;
					}

					$section_items[] = $item;
				}

				if (! $section_items) {
					continue;
				}
				?>
				<div class="hdp-pt__section">
					<div class="hdp-pt__section-title" role="rowheader">
						<?php echo esc_html($section['heading'] ?? ''); ?>
					</div>

					<?php foreach ($section_items as $item) :
						$key = ! empty($item['item_key'])
							? sanitize_key($item['item_key'])
							: hdp_pt_make_item_key($item['label'] ?? '');

						$label         = $item['label'] ?? '';
						$type          = $item['value_type'] ?? 'text';
						$label_tooltip = $item['label_tooltip'] ?? '';
						?>
						<div class="hdp-pt__row" role="row">
							<div class="hdp-pt__label" role="rowheader">
								<span class="hdp-pt__label-text"><?php echo esc_html($label); ?></span>
								<?php if ($label_tooltip) : ?>
									<button class="hdp-pt__tooltip"
										type="button"
										aria-label="<?php echo esc_attr($label_tooltip); ?>"
										data-tooltip="<?php echo esc_attr($label_tooltip); ?>">i</button>
								<?php endif; ?>
							</div>

							<?php foreach ($packages as $p) :
								$r        = $p['items'][$key] ?? array();
								$featured = $p['featured'] ? ' is-featured' : '';
								?>
								<div class="hdp-pt__package-value<?php echo esc_attr($featured); ?>"
									role="cell"
									data-package="<?php echo esc_attr($p['title']); ?>">
									<?php hdp_pt_render_package_value($type, $r); ?>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>

		</div><!-- /.hdp-pt__grid -->

		<div class="hdp-pt__mobile" aria-label="Pricing packages">
			<?php foreach ($packages as $p) :
				$featured      = ! empty($p['featured']);
				$price         = $p['price'];
				$button        = $p['button'];
				$badge         = hdp_pt_package_mobile_badge($p);
				$features      = (array) ($p['mobile_features'] ?? array());
				$details_label = $p['mobile_details_label'] ?: 'Bekijk wat erin zit';
				$row_class     = 'hdp-pt-mob__card' . ($featured ? ' is-featured' : '');
				?>
				<div class="<?php echo esc_attr($row_class); ?>">
					<div class="hdp-pt-mob__top">
						<div class="hdp-pt-mob__info">
							<h3 class="hdp-pt-mob__title">
								<?php echo esc_html($p['title']); ?>
								<?php if ($badge !== '') : ?>
									<span class="hdp-pt-mob__badge"><?php echo esc_html($badge); ?></span>
								<?php endif; ?>
							</h3>
							<?php if (! empty($p['mobile_subtitle'])) : ?>
								<div class="hdp-pt-mob__subtitle"><?php echo esc_html($p['mobile_subtitle']); ?></div>
							<?php endif; ?>
						</div>
						<div class="hdp-pt-mob__price">
							<span class="hdp-pt-mob__currency"><?php echo esc_html($price['currency'] ?? ''); ?></span>
							<span class="hdp-pt-mob__amount"><?php echo esc_html($price['amount'] ?? ''); ?></span>
							<?php if (! empty($p['subtext'])) : ?>
								<small class="hdp-pt-mob__subtext"><?php echo esc_html($p['subtext']); ?></small>
							<?php endif; ?>
						</div>
						<?php if (! empty($button['url'])) :
							$btn_class = 'hdp-pt__button hdp-pt-mob__button';
							if (! $featured) {
								$btn_class .= ' hdp-pt-mob__button--ghost';
							}
							?>
							<a class="<?php echo esc_attr($btn_class); ?>"
								href="<?php echo esc_url($button['url']); ?>"
								target="<?php echo esc_attr($button['target'] ?? '_self'); ?>"
								<?php echo '_blank' === ($button['target'] ?? '_self') ? ' rel="noopener noreferrer"' : ''; ?>>
								<?php echo esc_html($button['title'] ?: 'Aanvragen'); ?>
							</a>
						<?php endif; ?>
					</div>

					<?php if ($features) : ?>
						<details class="hdp-pt-mob__details"<?php echo $featured ? ' open' : ''; ?>>
							<summary><?php echo esc_html($details_label); ?></summary>
							<ul class="hdp-pt-mob__list">
								<?php foreach ($features as $feature) : ?>
									<li>
										<?php hdp_pt_render_mobile_feature_icon($feature); ?>
										<span><?php echo esc_html($feature['label'] ?? ''); ?></span>
									</li>
								<?php endforeach; ?>
							</ul>
						</details>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div><!-- /.hdp-pt__mobile -->
	</div>
	<?php
}
