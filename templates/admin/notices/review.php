<?php
/**
 * Review request notice.
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
		/* translators: 1: opening anchor tag, 2: closing anchor tag. */
			__( 'Enjoying Min Max Quantities?? A %1$sfive-star review%2$s helps other store owners find it and means a lot to our team.', 'wc-min-max-quantities' ),
			'<a href="' . esc_url( (string) wc_min_max_quantities()->review_url ) . '" target="_blank" rel="noopener noreferrer"><strong>',
			'</strong></a>'
		)
	);
	?>
</p>

