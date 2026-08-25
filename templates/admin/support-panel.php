<?php
/**
 * Support panel.
 *
 * @var string $title         Panel title.
 * @var string $community_url Community URL.
 * @var string $contact_url   Contact URL.
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
		<ul>
			<li>
				<a href="<?php echo esc_url( $community_url ); ?>" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Join our Community', 'wc-min-max-quantities' ); ?>
				</a>
			</li>
			<li>
				<a href="<?php echo esc_url( $contact_url ); ?>" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Request a Feature', 'wc-min-max-quantities' ); ?>
				</a>
			</li>
			<li>
				<a href="<?php echo esc_url( $contact_url ); ?>" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Report a Bug', 'wc-min-max-quantities' ); ?>
				</a>
			</li>
		</ul>
	</div>
</div>
