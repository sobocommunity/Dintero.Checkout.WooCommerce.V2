<?php //phpcs:ignore
/**
 * Class for Dintero Checkout Gateway.
 *
 * @package Dintero_Checkout/Classes
 */

/**
 * Class Dintero_Gateway
 */
class Dintero_Gateway extends WC_Payment_Gateway {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->id                 = 'dintero';
		$this->method_title       = __( 'Dintero', 'dintero-checkout-for-woocommerce' );
		$this->method_description = __( 'Dintero Checkout', 'dintero-checkout-for-woocommerce' );
		$this->supports           = apply_filters(
			'dintero_one_gateway_supports',
			array(
				'products',
				'refunds',
			)
		);
		$this->has_fields         = false;
		$this->init_form_fields();
		$this->init_settings();
		$this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );
		$this->enabled     = $this->get_option( 'enabled' );
		$this->test_mode   = 'yes' === $this->get_option( 'test_mode' );
		$this->logging     = 'yes' === $this->get_option( 'logging' );
		add_action(
			'woocommerce_update_options_payment_gateways_' . $this->id,
			array(
				$this,
				'process_admin_options',
			)
		);
	}

	/**
	 * Initialize settings fields.
	 *
	 * @return void
	 */
	public function init_form_fields() {
		$this->form_fields = Dintero_Settings_Fields::setting_fields();
	}

	/**
	 * Check if payment method should be available.
	 *
	 * @return boolean
	 */
	public function is_available() {
		return ( 'yes' !== $this->enabled );
	}
}
