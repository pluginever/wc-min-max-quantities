<?php
/**
 * Deactivation feedback modal.
 *
 * @since   2.3.2
 * @package PluginEver\MinMaxQuantities
 *
 * @var string                $basename Plugin basename.
 * @var string                $nonce    Feedback nonce.
 * @var array<string, string> $reasons  Reason labels keyed by slug.
 */

defined( 'ABSPATH' ) || exit;
?>
<div id="wc-min-max-quantities-feedback" class="wc-min-max-quantities-feedback" style="display:none;">
	<div class="wc-min-max-quantities-feedback__overlay"></div>
	<div class="wc-min-max-quantities-feedback__modal" role="dialog" aria-modal="true">
		<h2><?php esc_html_e( 'Quick question before you go', 'wc-min-max-quantities' ); ?></h2>
		<p><?php esc_html_e( 'What is the reason for deactivating?', 'wc-min-max-quantities' ); ?></p>
		<ul>
			<?php foreach ( $reasons as $slug => $label ) : ?>
				<li>
					<label>
						<input type="radio" name="wc-min-max-quantities-reason" value="<?php echo esc_attr( $slug ); ?>">
						<?php echo esc_html( $label ); ?>
					</label>
				</li>
			<?php endforeach; ?>
		</ul>
		<textarea rows="2" placeholder="<?php esc_attr_e( 'Tell us more (optional)', 'wc-min-max-quantities' ); ?>"></textarea>
		<div class="wc-min-max-quantities-feedback__actions">
			<a href="#" class="button button-primary" data-submit><?php esc_html_e( 'Submit &amp; Deactivate', 'wc-min-max-quantities' ); ?></a>
			<a href="#" class="button-link" data-skip><?php esc_html_e( 'Skip &amp; Deactivate', 'wc-min-max-quantities' ); ?></a>
		</div>
	</div>
</div>
<style>
	.wc-min-max-quantities-feedback__overlay { position: fixed; inset: 0; background: rgba( 0, 0, 0, .5 ); z-index: 99998; }
	.wc-min-max-quantities-feedback__modal { position: fixed; top: 50%; left: 50%; transform: translate( -50%, -50% ); width: 90%; max-width: 420px; padding: 24px; background: #fff; border-radius: 6px; box-shadow: 0 5px 30px rgba( 0, 0, 0, .3 ); z-index: 99999; }
	.wc-min-max-quantities-feedback__modal ul { margin: 12px 0; }
	.wc-min-max-quantities-feedback__modal textarea { width: 100%; }
	.wc-min-max-quantities-feedback__actions { display: flex; align-items: center; gap: 12px; margin-top: 16px; }
</style>
<script>
	( function ( $ ) {
		var $wrap = $( '#wc-min-max-quantities-feedback' );
		var target = '';

		$( 'tr[data-plugin="<?php echo esc_js( $basename ); ?>"]' ).find( '.deactivate a' ).on( 'click', function ( e ) {
			e.preventDefault();
			target = $( this ).attr( 'href' );
			$wrap.show();
		} );

		$wrap.on( 'click', '[data-skip], .wc-min-max-quantities-feedback__overlay', function ( e ) {
			e.preventDefault();
			window.location.href = target;
		} );

		$wrap.on( 'click', '[data-submit]', function ( e ) {
			e.preventDefault();
			$.post( window.ajaxurl, {
				action: 'wc_min_max_quantities_feedback',
				nonce: '<?php echo esc_js( $nonce ); ?>',
				reason: $wrap.find( 'input[name="wc-min-max-quantities-reason"]:checked' ).val() || '',
				details: $wrap.find( 'textarea' ).val() || ''
			} ).always( function () {
				window.location.href = target;
			} );
		} );
	}( jQuery ) );
</script>
