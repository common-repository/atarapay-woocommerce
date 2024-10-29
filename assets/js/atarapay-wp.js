jQuery( function( $ ) {
	'use strict';

	/**
	 * Object to handle admin functions.
	 */
	var wc_at_atara_admin = {
		/**
		 * Initialize.
		 */
		init: function() {

			// Toggle api key settings.
			$( document.body ).on( 'change', '#woocommerce_at_atara_testmode', function() {
				var test_public_key = $( '#woocommerce_at_atara_test_public_key' ).parents( 'tr' ).eq( 0 ),test_secret_key = $( '#woocommerce_at_atara_test_secret_key' ).parents( 'tr' ).eq( 0 ),
					live_public_key = $( '#woocommerce_at_atara_live_public_key' ).parents( 'tr' ).eq( 0 ),
					live_secret_key = $( '#woocommerce_at_atara_live_secret_key' ).parents( 'tr' ).eq( 0 );

				if ( $( this ).is( ':checked' ) ) {
					test_public_key.show();
					test_secret_key.show();
					live_public_key.hide();
					live_secret_key.hide();
				} else {
					test_public_key.hide();
					test_secret_key.hide();
					live_public_key.show();
					live_secret_key.show();
				}
			} );

			$( '#woocommerce_at_atara_testmode' ).change();
		}
	};

	wc_at_atara_admin.init();

});
