<?php
/**
 * Promo panel for Key Manager plugin.
 *
 * @var string $title       Panel title.
 * @var string $description Panel description.
 * @var string $install_url Plugin install URL.
 * @var string $label       CTA button label.
 *
 * @package PluginEver\MinMaxQuantities
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="b8-card">
	<div class="b8-card__header">
		<?php echo esc_html( $title ); ?>
	</div>
	<div class="b8-card__body">
		<p><?php echo esc_html( $description ); ?></p>
	</div>
	<div class="b8-card__footer">
		<span class="b8-badge b8-badge--primary">
			<?php esc_html_e( 'Recommended', 'wc-min-max-quantities' ); ?>
		</span>
		<a href="<?php echo esc_url( $install_url ); ?>" target="_blank" class="b8-button b8-button--secondary">
			<?php echo esc_html( $label ); ?>
		</a>
	</div>
</div>
