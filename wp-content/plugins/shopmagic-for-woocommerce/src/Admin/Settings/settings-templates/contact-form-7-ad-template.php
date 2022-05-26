<?php
/** @var string $nonce */
/** @var string $ajax_action */
?>
<div class="shopmagic-cart-notice shopmagic-pro-notice">
	<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
		<path d="M551.991 64H129.28l-8.329-44.423C118.822 8.226 108.911 0 97.362 0H12C5.373 0 0 5.373 0 12v8c0 6.627 5.373 12 12 12h78.72l69.927 372.946C150.305 416.314 144 431.42 144 448c0 35.346 28.654 64 64 64s64-28.654 64-64a63.681 63.681 0 0 0-8.583-32h145.167a63.681 63.681 0 0 0-8.583 32c0 35.346 28.654 64 64 64 35.346 0 64-28.654 64-64 0-17.993-7.435-34.24-19.388-45.868C506.022 391.891 496.76 384 485.328 384H189.28l-12-64h331.381c11.368 0 21.177-7.976 23.496-19.105l43.331-208C578.592 77.991 567.215 64 551.991 64zM240 448c0 17.645-14.355 32-32 32s-32-14.355-32-32 14.355-32 32-32 32 14.355 32 32zm224 32c-17.645 0-32-14.355-32-32s14.355-32 32-32 32 14.355 32 32-14.355 32-32 32zm38.156-192H171.28l-36-192h406.876l-40 192zm-106.641-75.515l-51.029 51.029c-4.686 4.686-12.284 4.686-16.971 0l-51.029-51.029c-7.56-7.56-2.206-20.485 8.485-20.485H320v-52c0-6.627 5.373-12 12-12h8c6.627 0 12 5.373 12 12v52h35.029c10.691 0 16.045 12.926 8.486 20.485z"/>
	</svg>

	<h1><?php esc_html_e( 'ShopMagic For Contact Form 7', 'shopmagic-for-woocommerce' ); ?></h1>

	<h2><?php esc_html_e( '100% FREE Contact Form 7 integration for WooCommerce!', 'shopmagic-for-woocommerce' ); ?></h2>

	<p>
		<strong><?php esc_html_e( 'With ShopMagic now you can automate your forms submissions.', 'shopmagic-for-woocommerce' ); ?></strong>
	</p>

	<p>
		<br>
		<button id="shopmagic-carts-installer"
				class="button button-primary button-hero"><?php esc_html_e( 'Enable Now for Free →', 'shopmagic-for-woocommerce' ); ?></button>
	</p>
</div>

<script type="text/javascript">
	(function ($) {
		$('#shopmagic-carts-installer').click(function (e) {
			e.preventDefault();
			$(this).addClass('install-now updating-message');
			$(this).text('<?php esc_html_e( 'Installing. Please wait...', 'shopmagic-for-woocommerce' ); ?>');

			let data = {
				action: '<?php echo esc_attr( $ajax_action ); ?>',
				_wpnonce: '<?php echo esc_attr( wp_create_nonce( $nonce ) ); ?>'
			};

			$.post(ajaxurl, data, function (response) {
				if (response.success) {
					let button = $('#shopmagic-carts-installer');
					button.attr('disabled', 'disabled');
					button.removeClass('install-now updating-message');
					button.text('<?php esc_html_e( 'Installed', 'shopmagic-for-woocommerce' ); ?>');
					window.location.reload();
				}
			});
		});
	})(jQuery);
</script>

<style>
	.button-hero.updating-message:before {
		margin-top: 13px;
	}
</style>
