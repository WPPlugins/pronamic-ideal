<?php

/**
 * Title: WP e-Commerce payment data
 * Description:
 * Copyright: Copyright (c) 2005 - 2017
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.0.3
 * @since 1.0.0
 */
class Pronamic_WP_Pay_Extensions_WPeCommerce_PaymentData extends Pronamic_WP_Pay_PaymentData {
	/**
	 * Merchant
	 *
	 * @var wpsc_merchant
	 * @see http://plugins.trac.wordpress.org/browser/wp-e-commerce/tags/3.8.7.6.2/wpsc-includes/merchant.class.php
	 */
	private $merchant;

	//////////////////////////////////////////////////

	/**
	 * Constructs and initializes an iDEAL WP e-Commerce data proxy
	 *
	 * @param wpsc_merchant $merchant
	 */
	public function __construct( $merchant ) {
		parent::__construct();

		$this->merchant = $merchant;
	}

	//////////////////////////////////////////////////
	// WP e-Commerce specific
	//////////////////////////////////////////////////

	/**
	 * Get purchase ID
	 *
	 * @see https://github.com/wp-e-commerce/WP-e-Commerce/blob/v3.8.9.5/wpsc-includes/merchant.class.php#L41
	 * @return string
	 */
	public function get_purchase_id() {
		$purchase_id = null;

		if ( isset( $this->merchant->purchase_id ) ) {
			$purchase_id = $this->merchant->purchase_id;
		}

		return $purchase_id;
	}


	/**
	 * Get session ID
	 *
	 * @see https://github.com/wp-e-commerce/WP-e-Commerce/blob/v3.8.9.5/wpsc-includes/merchant.class.php#L175
	 * @return string
	 */
	public function get_session_id() {
		return $this->get_cart_data( 'session_id' );
	}

	/**
	 * Get cart data
	 *
	 * @param sring $key1
	 * @param string $key2
	 * @return string
	 */
	private function get_cart_data( $key1, $key2 = null ) {
		$data = null;

		if ( isset( $this->merchant->cart_data[ $key1 ] ) ) {
			$data = $this->merchant->cart_data[ $key1 ];

			if ( isset( $key2 ) && is_array( $data ) && isset( $data[ $key2 ] ) ) {
				$data = $data[ $key2 ];
			}
		}

		return $data;
	}

	//////////////////////////////////////////////////
	// Other
	//////////////////////////////////////////////////

	/**
	 * Get source indicator
	 *
	 * @see Pronamic_Pay_PaymentDataInterface::get_source()
	 * @return string
	 */
	public function get_source() {
		return 'wp-e-commerce';
	}

	//////////////////////////////////////////////////

	/**
	 * Get description
	 *
	 * @see Pronamic_Pay_PaymentDataInterface::get_description()
	 * @return string
	 */
	public function get_description() {
		// @see http://plugins.trac.wordpress.org/browser/wp-e-commerce/tags/3.8.7.6.2/wpsc-includes/merchant.class.php#L41
		return sprintf( __( 'Order %s', 'pronamic_ideal' ), $this->merchant->purchase_id );
	}

	/**
	 * Get order ID
	 *
	 * @see Pronamic_Pay_PaymentDataInterface::get_order_id()
	 * @return string
	 */
	public function get_order_id() {
		// @see http://plugins.trac.wordpress.org/browser/wp-e-commerce/tags/3.8.7.6.2/wpsc-includes/merchant.class.php#L41
		return $this->merchant->purchase_id;
	}

	/**
	 * Get items
	 *
	 * @see Pronamic_Pay_PaymentDataInterface::get_items()
	 * @return Pronamic_IDeal_Items
	 */
	public function get_items() {
		// Items
		$items = new Pronamic_IDeal_Items();

		// Item
		// We only add one total item, because iDEAL cant work with negative price items (discount)
		$item = new Pronamic_IDeal_Item();
		$item->setNumber( $this->merchant->purchase_id );
		$item->setDescription( sprintf( __( 'Order %s', 'pronamic_ideal' ), $this->merchant->purchase_id ) );
		// @see http://plugins.trac.wordpress.org/browser/wp-e-commerce/tags/3.8.7.6.2/wpsc-includes/merchant.class.php#L188
		$item->setPrice( $this->get_cart_data( 'total_price' ) );
		$item->setQuantity( 1 );

		$items->addItem( $item );

		return $items;
	}

	//////////////////////////////////////////////////
	// Currency
	//////////////////////////////////////////////////

	/**
	 * Get currency alphabetic code
	 *
	 * @see Pronamic_Pay_PaymentDataInterface::get_currency_alphabetic_code()
	 * @return string
	 */
	public function get_currency_alphabetic_code() {
		// @see http://plugins.trac.wordpress.org/browser/wp-e-commerce/tags/3.8.7.6.2/wpsc-includes/merchant.class.php#L177
		return $this->get_cart_data( 'store_currency' );
	}

	//////////////////////////////////////////////////
	// Customer
	//////////////////////////////////////////////////

	public function get_email() {
		// @see http://plugins.trac.wordpress.org/browser/wp-e-commerce/tags/3.8.7.6.2/wpsc-includes/merchant.class.php#L191
		return $this->get_cart_data( 'email_address' );
	}

	public function get_customer_name() {
		// @see http://plugins.trac.wordpress.org/browser/wp-e-commerce/tags/3.8.7.6.2/wpsc-includes/merchant.class.php#L60
		return $this->get_cart_data( 'billing_address', 'first_name' ) . ' ' . $this->get_cart_data( 'billing_address', 'last_name' );
	}

	public function get_address() {
		// @see http://plugins.trac.wordpress.org/browser/wp-e-commerce/tags/3.8.7.6.2/wpsc-includes/merchant.class.php#L60
		return $this->get_cart_data( 'billing_address', 'address' );
	}

	public function get_city() {
		// @see http://plugins.trac.wordpress.org/browser/wp-e-commerce/tags/3.8.7.6.2/wpsc-includes/merchant.class.php#L60
		return $this->get_cart_data( 'billing_address', 'city' );
	}

	public function get_zip() {
		// @see http://plugins.trac.wordpress.org/browser/wp-e-commerce/tags/3.8.7.6.2/wpsc-includes/merchant.class.php#L60
		return $this->get_cart_data( 'billing_address', 'post_code' );
	}

	//////////////////////////////////////////////////
	// URL's
	// @todo we could also use $this->merchant->cart_data['transaction_results_url']
	// @see http://plugins.trac.wordpress.org/browser/wp-e-commerce/tags/3.8.8.3/wpsc-includes/merchant.class.php#L184
	//////////////////////////////////////////////////

	public function get_normal_return_url() {
		return add_query_arg(
			array(
				'sessionid' => $this->get_cart_data( 'session_id' ),
				'gateway'   => 'wpsc_merchant_pronamic_ideal',
			),
			get_option( 'transact_url' )
		);
	}

	public function get_cancel_url() {
		/*
		 * If we don't add the 'sessionid' paramater to transaction URL visitors will
		 * see the message 'Sorry your transaction was not accepted.', see:
		 *
		 * http://plugins.trac.wordpress.org/browser/wp-e-commerce/tags/3.8.8.3/wpsc-theme/functions/wpsc-transaction_results_functions.php#L94
		 */
		return add_query_arg(
			array(
				// 'sessionid' => $this->merchant->cart_data['session_id'],
				'gateway'   => 'wpsc_merchant_pronamic_ideal',
				'return'    => 'cancel',
			),
			get_option( 'transact_url' )
		);
	}

	public function get_success_url() {
		return add_query_arg(
			array(
				'sessionid' => $this->get_cart_data( 'session_id' ),
				'gateway'   => 'wpsc_merchant_pronamic_ideal',
			),
			get_option( 'transact_url' )
		);
	}

	public function get_error_url() {
		/*
		 * If we don't add the 'sessionid' paramater to transaction URL visitors will
		 * see the message 'Sorry your transaction was not accepted.', see:
		 *
		 * http://plugins.trac.wordpress.org/browser/wp-e-commerce/tags/3.8.8.3/wpsc-theme/functions/wpsc-transaction_results_functions.php#L94
		 */
		return add_query_arg(
			array(
				// 'sessionid' => $this->merchant->cart_data['session_id'],
				'gateway'   => 'wpsc_merchant_pronamic_ideal',
				'return'    => 'error',
			),
			get_option( 'transact_url' )
		);
	}
}
