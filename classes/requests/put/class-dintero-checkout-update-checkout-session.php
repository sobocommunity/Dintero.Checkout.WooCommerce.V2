<?php //phpcs:ignore
/**
 * Class for updating a checkout session.
 *
 * @package Dintero_Checkout/Classes/Requests/Put
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for a Dintero checkout session.
 */
class Dintero_Checkout_Update_Checkout_Session extends Dintero_Checkout_Request_Put {
	/**
	 * Class constructor.
	 *
	 * @param array $arguments The request arguments.
	 */
	public function __construct( $arguments ) {
		parent::__construct( $arguments );

		$this->log_title = 'Update Dintero Session.';
	}

	/**
	 * Get the request url.
	 *
	 * @return string
	 */
	protected function get_request_url() {
		return "{$this->get_api_url_base()}sessions/{$this->arguments['session_id']}?update_without_lock=true";
	}

	/**
	 * Returns the body for the request.
	 *
	 * @return array
	 */
	public function get_body() {
		$helper = new Dintero_Checkout_Cart();
		$body   = array(
			'order'       => array(
				'amount'          => $helper->get_order_total(),
				'currency'        => $helper->get_currency(),
				'vat_amount'      => $helper->get_tax_total(),
				'items'           => $helper->get_order_lines(),
				'shipping_option' => $helper->get_shipping_object(),
			),
			'remove_lock' => true,
		);

		// Set if express or not.
		if ( 'express' === $this->settings['checkout_type'] && 'embedded' === $this->settings['form_factor'] ) {
			$body['express']['shipping_options'] = ( empty( $body['order']['shipping_option'] ) ) ? array() : array( $body['order']['shipping_option'] );
		}

		if ( empty( $body['order']['shipping_option'] ) ) {
			unset( $body['order']['shipping_option'] );
		}

		return $body;
	}
}
