<?php
/**
 * Class for handling session creation request.
 *
 * @package Dintero_Checkout/Classes/Requests/Post
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dintero_Checkout_Create_Session class.
 */
class Dintero_Checkout_Create_Session extends Dintero_Checkout_Request_Post {

	/**
	 * Class constructor.
	 *
	 * @param array $arguments The request arguments.
	 */
	public function __construct( $arguments ) {
		parent::__construct( $arguments );

		$this->log_title = 'Create Dintero session.';
	}

	/**
	 * Get the request url.
	 *
	 * @return string
	 */
	protected function get_request_url() {
		return "{$this->get_api_url_base()}sessions-profile";
	}

	/**
	 * Returns the body for the request.
	 *
	 * @return array
	 */
	public function get_body() {
		if ( ! empty( $this->arguments['order_id'] ) ) {
			$test = false;
			// $helper = ( new Dintero_Checkout_Cart() )->cart( $this->arguments['order_id'] );
		} else {
			$helper = new Dintero_Checkout_Cart();
		}

		$body = array(
			'url'        => array(
				'return_url' => add_query_arg(
					array(
						'gateway' => 'dintero',
						'key'     => '', // ( ! empty( $order ) ) ? $order->get_order_key() : '',
					),
					home_url()
				),
			),
			'order'      => array(
				'amount'             => $helper::get_order_total(),
				'currency'           => $helper::get_currency(),
				'merchant_reference' => $helper::get_merchant_reference(),
				'vat_amount'         => $helper::get_tax_total(),
				'items'              => $helper::get_order_lines(),
				'shipping_option'    => $helper::get_shipping_object(),
			),
			'profile_id' => $this->settings['profile_id'],
		);

		if ( ! Dintero_Checkout_Callback::is_localhost() ) {
			$this->request_args['url']['callback_url'] = Dintero_Checkout_Callback::callback_url( '$order->get_order_key()' );
		}

		if ( empty( $body['order']['shipping_option'] ) ) {
			unset( $body['order']['shipping_option'] );
		}

		// Set if express or not.
		// if ( 'yes' === $this->settings['express'] ) {
			$body['express']['shipping_options'] = array();
		// }

		return $body;
	}
}
