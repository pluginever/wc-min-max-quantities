<?php defined( 'ABSPATH' ) || exit(); ?>
<div class="wrap about-wrap">
	<h1><?php _e( 'Welcome', 'wc-min-max-qunatities' ); ?></h1>
	<p class="about-text"><?php _e( 'Thank you for installing WooCommerce Min Max Quantities! The plugin allows you to set minimum and maximum allowable product quantities and price per product and order.', 'wc-min-max-qunatities' ); ?></p>
</div>
<div class="wrap" style="margin: 25px 40px 0 20px;">

	<h2><?php _e( 'Features', 'wc-min-max-qunatities' ); ?></h2>
	<div class="ever-wc-minmax-features-container">
	
		<ul>
			<li class="wc-minmax-feature-list">
				<?php ever_wc_minmax_feature_icon('free'); ?> <?php _e( 'Min Max Quantity for Product Globally', 'wc-min-max-qunatities' ); ?>
			</li>

			<li class="wc-minmax-feature-list">
				<?php ever_wc_minmax_feature_icon('free'); ?> <?php _e( 'Min Max Price for Product Globally', 'wc-min-max-qunatities' ); ?>
			</li>

			<li class="wc-minmax-feature-list">
				<?php ever_wc_minmax_feature_icon('free'); ?> <?php _e( 'Minimum Cart Total Price', 'wc-min-max-qunatities' ); ?>
			</li>

			<li class="wc-minmax-feature-list">
				<?php ever_wc_minmax_feature_icon('free'); ?> <?php _e( 'Maximum Cart Total Price', 'wc-min-max-qunatities' ); ?>
			</li>

			<li class="wc-minmax-feature-list">
				<?php ever_wc_minmax_feature_icon('pro'); ?> <?php echo apply_filters( 'wc_minmax_quantities_features_pro', 'Minimum Cart Quantity' ); ?>
			</li>

			<li class="wc-minmax-feature-list">
				<?php ever_wc_minmax_feature_icon('pro'); ?> <?php echo apply_filters( 'wc_minmax_quantities_features_pro', 'Maximum Cart Quantity' ); ?>
			</li>

			<li class="wc-minmax-feature-list">
				<?php ever_wc_minmax_feature_icon('free'); ?> <?php _e( 'Hide Checkout Button', 'wc-min-max-qunatities' ); ?>
			</li>

			<li class="wc-minmax-feature-list">
				<?php ever_wc_minmax_feature_icon('pro'); ?> <?php echo apply_filters( 'wc_minmax_quantities_features_pro', 'Min Max Quantities Rules by Product Attribute' ); ?>
			</li>

			<li class="wc-minmax-feature-list">
				<?php ever_wc_minmax_feature_icon('pro'); ?> <?php echo apply_filters( 'wc_minmax_quantities_features_pro', 'Min Max Quantities Rules by Product Tag' ); ?>
			</li>

			<li class="wc-minmax-feature-list">
				<?php ever_wc_minmax_feature_icon('pro'); ?> <?php echo apply_filters( 'wc_minmax_quantities_features_pro', 'Min Max Quantities Rules by Product Category' ); ?>
			</li>

			<li class="wc-minmax-feature-list">
				<?php ever_wc_minmax_feature_icon('pro'); ?> <?php echo apply_filters( 'wc_minmax_quantities_features_pro', 'Min Max Quantities Rules by Date Range' ); ?>
			</li>

			<li class="wc-minmax-feature-list">
				<?php ever_wc_minmax_feature_icon('pro'); ?> <?php echo apply_filters( 'wc_minmax_quantities_features_pro', 'Prevent Add to Cart' ); ?>
			</li>

			<li class="wc-minmax-feature-list">
				<?php ever_wc_minmax_feature_icon('pro'); ?> <?php echo apply_filters( 'wc_minmax_quantities_features_pro', 'Remove Item from Checkout' ); ?>
			</li>



		</ul>
	
	</div>

</div>

<div class="wrap about-wrap">

	<hr>
	<h2><?php _e( 'Support', 'wc-min-max-qunatities' ); ?></h2>
	<div class="feature-section col two-col">
		<div class="col">
			<h4><?php _e( "Submit A Ticket", "wc-min-max-qunatities" ); ?></h4>
			<p><?php _e( "We offer our support through our advanced ticket system. Use our contact form to filter through your query so your ticket can be allocated to the right department. ", "wc-min-max-qunatities" ); ?></p>
			<a href="http://pluginever.com/contact/" class="button button-large button-primary" target="_blank"><?php esc_html_e( 'Submit a ticket', 'wc-min-max-qunatities' ); ?></a>
		</div>
		<div class="col">
			<h4><?php _e( "Documentation", "wc-min-max-qunatities" ); ?></h4>
			<p><?php _e( "This is the place to go to reference different aspects of the plugin. Our online documentation is a useful resource for learning the ins and outs of using our plugins.", "wc-min-max-qunatities" ); ?></p>
			<a href="http://pluginever.com/documentation/" class="button button-large button-primary" target="_blank"><?php esc_html_e( 'Documentation', 'wc-min-max-qunatities' ); ?></a>
		</div>
	</div>
</div>
