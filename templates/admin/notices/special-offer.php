<?php
/**
 * Limited-time offer notice.
 *
 * @since   1.0.0
 * @package PluginEver\MinMaxQuantities
 */

defined( 'ABSPATH' ) || exit;
?>
<p>
	<?php
	echo wp_kses_post(
		sprintf(
		/* translators: 1: discount code, 2: opening anchor tag, 3: closing anchor tag. */
			__( 'Limited-time offer: save 30%% on Min Max Quantities Pro with code %1$s. %2$sGrab the deal%3$s.', 'wc-min-max-quantities' ),
			'<strong>STARTER30</strong>',
			'<a href="' . esc_url( wc_min_max_quantities_upgrade_url( 'special_offer', 'notice' ) ) . '" target="_blank" rel="noopener noreferrer"><strong>',
			'</strong></a>'
		)
	);
	?>
</p>
