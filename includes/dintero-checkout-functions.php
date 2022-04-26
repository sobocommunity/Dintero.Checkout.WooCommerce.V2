<?php

/**
 * Utility functions.
 *
 * @package Dintero_Checkout/Includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add a button for changing gateway (intended to be used on the checkout page).
 *
 * @return void
 */
function dintero_checkout_wc_show_another_gateway_button() {
	$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
	if ( count( $available_gateways ) > 1 ) {

		$settings                   = get_option( 'woocommerce_dintero_checkout_settings' );
		$select_another_method_text = $settings['redirect_select_another_method_text'];

		if ( empty( $select_another_method_text ) ) {
			$select_another_method_text = __( 'Select another payment method', 'dintero-checkout-for-woocommerce' );
		}

		?>
		<p class="dintero-checkout-select-other-wrapper">
			<a class="checkout-button button" href="#" id="dintero-checkout-select-other">
				<?php echo esc_html( $select_another_method_text ); ?>
			</a>
		</p>
		<?php
	}
}

/**
 * Unsets all sessions set by Dintero.
 *
 * @return void
 */
function dintero_unset_sessions() {
	WC()->session->__unset( 'dintero_checkout_session_id' );
	WC()->session->__unset( 'dintero_merchant_reference' );
}

/**
 * Prints error message as notices.
 *
 * @param WP_Error $wp_error A WordPress error object.
 * @return void
 */
function dintero_print_error_message( $wp_error ) {
	foreach ( $wp_error->get_error_messages() as $error ) {
		wc_add_notice( ( is_array( $error ) ) ? $error['message'] : $error, 'error' );
	}
}

/**
 * Sanitize phone number.
 * Allow only '+' (if at the start), and numbers.
 *
 * @param string $phone Phone number.
 * @return string
 */
function dintero_sanitize_phone_number( $phone ) {
	return preg_replace( '/(?!^)[+]?[^\d]/', '', $phone );
}

/**
 * Sets the shipping method in WooCommerce from Dintero.
 *
 * @param array|bool $data The shipping data from Dintero. False if not set.
 * @return void
 */
function dintero_update_wc_shipping( $data ) {
	// Set cart definition.
	$merchant_reference = WC()->session->get( 'dintero_merchant_reference' );

	// If we don't have a Dintero merchant reference, return void.
	if ( empty( $merchant_reference ) ) {
		return;
	}

	// If the data is empty, return void.
	if ( empty( $data ) ) {
		return;
	}

	do_action( 'dintero_update_shipping_data', $data );

	set_transient( 'dintero_shipping_data_' . $merchant_reference, $data, HOUR_IN_SECONDS );
	$chosen_shipping_methods   = array();
	$chosen_shipping_methods[] = wc_clean( $data['id'] );
	WC()->session->set( 'chosen_shipping_methods', apply_filters( 'dintero_chosen_shipping_method', $chosen_shipping_methods ) );
}
