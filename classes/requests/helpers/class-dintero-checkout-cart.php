<?php
/**
 * Class for handling cart items.
 *
 * @package Dintero_Checkout/Classes/Requests/Helpers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for handling cart items.
 */
class Dintero_Checkout_Cart extends Dintero_Checkout_Helper_Base {
	/**
	 * Get the order total.
	 *
	 * @return int
	 */
	public static function get_order_total() {
		return self::format_number( WC()->cart->total );
	}

	/**
	 * Get the tax total.
	 *
	 * @return int
	 */
	public static function get_tax_total() {
		return self::format_number( WC()->cart->get_total_tax() );
	}

	/**
	 * Get the cart currency.
	 *
	 * @return string
	 */
	public static function get_currency() {
		return get_woocommerce_currency();
	}

	/**
	 * Get the merchant reference.
	 *
	 * @return string
	 */
	public static function get_merchant_reference() {
		return uniqid( 'dwc' );
	}

	/**
	 * Gets formatted cart items.
	 *
	 * @return array Formatted cart items.
	 */
	public static function get_order_lines() {
		$cart = WC()->cart->get_cart();

		// Get cart items.
		foreach ( $cart as $cart_item ) {
			$formatted_cart_items[] = self::get_cart_item( $cart_item );
		}

		/**
		 * Get cart fees.
		 *
		 * @var $cart_fees WC_Cart_Fees
		 */
		$cart_fees = WC()->cart->get_fees();
		foreach ( $cart_fees as $fee ) {
			$formatted_cart_items[] = self::get_fee( $fee );
		}

		// Get cart shipping.
		// TODO implement shipping in iframe.
		if ( WC()->cart->needs_shipping() && count( WC()->shipping->get_packages() ) > 1 ) {
			$shipping_methods = WC()->shipping->get_packages()[0]['rates'];
			foreach ( $shipping_methods as $shipping_method ) {
				$shipping               = self::get_shipping( $shipping_method );
				$formatted_cart_items[] = $shipping;
			}
		}

		return $formatted_cart_items;
	}

	/**
	 * Gets the formated shipping lines.
	 *
	 * @return array|null
	 */
	public static function get_shipping_lines() {
		return null;
	}

	/**
	 * Get the formated order line from a cart item.
	 *
	 * @param array $cart_item The cart item.
	 * @return array
	 */
	public static function get_cart_item( $cart_item ) {
		$id      = ( empty( $cart_item['variation_id'] ) ) ? $cart_item['product_id'] : $cart_item['variation_id'];
		$product = wc_get_product( $id );

		return array(
			'id'          => self::get_product_sku( $product ),
			'line_id'     => self::get_product_sku( $product ),
			'description' => self::get_product_name( $cart_item ),
			'quantity'    => $cart_item['quantity'],
			'amount'      => self::format_number( $cart_item['line_total'] ),
			'vat_amount'  => self::format_number( $cart_item['line_tax'] ),
		);
	}

	/**
	 * Undocumented static function
	 *
	 * @param object $product The WooCommerce Product.
	 * @return string
	 */
	public static function get_product_sku( $product ) {
		if ( $product->get_sku() ) {
			$item_reference = $product->get_sku();
		} else {
			$item_reference = $product->get_id();
		}

		return strval( $item_reference );
	}

	/**
	 * Gets the product name.
	 *
	 * @param object $cart_item The cart item.
	 * @return string
	 */
	public static function get_product_name( $cart_item ) {
		$cart_item_data = $cart_item['data'];
		$cart_item_name = $cart_item_data->get_name();
		$item_name      = apply_filters( 'dintero_cart_item_name', $cart_item_name, $cart_item );
		return strip_tags( $item_name );
	}

	/**
	 * Get the formated order line from a fee.
	 *
	 * @param object $fee The cart fee.
	 * @return array
	 */
	public static function get_fee( $fee ) {
		$name = $fee->name;
		return array(
			/* NOTE: The id and line_id must match the same id and line_id on capture and refund. */
			'id'          => $name,
			'line_id'     => $name,
			'description' => $name,
			'quantity'    => 1,
			'amount'      => self::format_number( $fee->amount + $fee->tax ),
			'vat_amount'  => self::format_number( $fee->tax ),
		);
	}

	/**
	 * Gets the formated order line from shipping.
	 *
	 * @param object $shipping_method The id of the shipping method.
	 * @return array
	 */
	public static function get_shipping( $shipping_method ) {
		return array(
			/* NOTE: The id and line_id must match the same id and line_id on capture and refund. */
			'id'          => $shipping_method->get_id(),
			'line_id'     => $shipping_method->get_id(),
			'amount'      => self::format_number( $shipping_method->get_cost() + $shipping_method->get_shipping->tax() ),
			'operator'    => '',
			'description' => '',
			'title'       => $shipping_method->get_label(),
			'vat_amount'  => self::format_number( $shipping_method->get_shipping_tax() ),
			'vat'         => ( 0 !== $shipping_method->get_cost() ) ? self::format_number( $shipping_method->get_shipping_tax() / $shipping_method->get_cost() ) : 0,
			'quantity'    => 1,
		);
	}

	/**
	 * Get the formated shipping object.
	 *
	 * @return array|null
	 */
	public static function get_shipping_object() {
		if ( WC()->cart->needs_shipping() && count( WC()->shipping->get_packages() ) === 1 ) {
			$packages        = WC()->shipping()->get_packages();
			$chosen_methods  = WC()->session->get( 'chosen_shipping_methods' );
			$chosen_shipping = $chosen_methods[0];
			foreach ( $packages as $i => $package ) {
				foreach ( $package['rates'] as $shipping_method ) {
					if ( $chosen_shipping === $shipping_method->id ) {
						if ( $shipping_method->cost > 0 ) {
							return array(
								'id'          => $shipping_method->get_id(),
								'line_id'     => $shipping_method->get_id(),
								'amount'      => self::format_number( $shipping_method->get_cost() + $shipping_method->get_shipping_tax() ),
								'operator'    => '',
								'description' => '',
								'title'       => $shipping_method->get_label(),
								'vat_amount'  => self::format_number( $shipping_method->get_shipping_tax() ),
								'vat'         => ( 0 !== $shipping_method->get_cost() ) ? self::format_number( $shipping_method->get_shipping_tax() / $shipping_method->get_cost() ) : 0,
							);
						} else {
							return array(
								'id'          => $shipping_method->get_id(),
								'line_id'     => $shipping_method->get_id(),
								'amount'      => 0,
								'operator'    => '',
								'description' => '',
								'title'       => $shipping_method->get_label(),
								'vat_amount'  => 0,
								'vat'         => 0,
							);
						}
					}
				}
			}
		}

		return null;
	}
}
