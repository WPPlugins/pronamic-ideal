<?php

/**
 * Title: Jigoshop iDEAL gateway
 * Description:
 * Copyright: Copyright (c) 2005 - 2017
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.0.4
 * @since 1.0.0
 */
class Pronamic_WP_Pay_Extensions_Jigoshop_IDealGateway extends jigoshop_payment_gateway {
	/**
	 * The unique ID of this payment gateway
	 *
	 * @var string
	 */
	const ID = 'pronamic_ideal';

	//////////////////////////////////////////////////

	/**
	 * Constructs and initialize an iDEAL gateway
	 */
	public function __construct() {
		/*
		 * Parent constructor only exists in Jigoshop 1.3+:
		 * @see https://github.com/jigoshop/jigoshop/blob/1.2/gateways/gateway.class.php
		 * @see https://github.com/jigoshop/jigoshop/blob/1.3/gateways/gateway.class.php
		 *
		 * The 'jigoshop::jigoshop_version()' function doesn't exists in version < 1.3.
		 * @see https://github.com/jigoshop/jigoshop/blob/dev/classes/jigoshop.class.php#L54
		 *
		 * Use of Jigoshop version constant:
		 * 1.0 = 1202010 - @see https://github.com/jigoshop/jigoshop/blob/1.0/jigoshop.php#L28
		 * 1.1 = 1202130 - @see https://github.com/jigoshop/jigoshop/blob/1.1/jigoshop.php#L28
		 * 1.2 = 1203310 - @see https://github.com/jigoshop/jigoshop/blob/1.2/jigoshop.php#L45
		 * 1.3 = 1207160 - @see https://github.com/jigoshop/jigoshop/blob/1.3/jigoshop.php#L45
		 * 1.9.5 = 1307110 - @see https://github.com/jigoshop/jigoshop/blob/1.9.5/jigoshop.php#L45
		 * 1.9.6 = 1.9.6 - @see https://github.com/jigoshop/jigoshop/blob/1.9.6/jigoshop.php#L45-L47
		 */
		if ( version_compare( JIGOSHOP_VERSION, 1207160, '>=' ) || version_compare( JIGOSHOP_VERSION, '1.3', '>=' ) ) {
			parent::__construct();
		}

		// Give this gateway an unique ID so Jigoshop can identiy this gateway
		$this->id             = self::ID;

		// The method title that Jigoshop will display in the admin
		$this->method_title   = __( 'Pronamic iDEAL', 'pronamic_ideal' );

		// The icon that Jigoshop will display on the payment methods radio list
		$this->icon           = plugins_url( 'images/icon-24x24.png', Pronamic_WP_Pay_Plugin::$file );

		// Let Jigoshop know that this gateway has field
		// Technically only iDEAL advanced variants has fields
		$this->has_fields     = true;

		// Set default Jigoshop variables, load them form the WordPress options
		$this->enabled        = Pronamic_WP_Pay_Extensions_Jigoshop_Jigoshop::get_option( 'pronamic_pay_ideal_jigoshop_enabled' );
		$this->title          = Pronamic_WP_Pay_Extensions_Jigoshop_Jigoshop::get_option( 'pronamic_pay_ideal_jigoshop_title' );
		$this->description	  = Pronamic_WP_Pay_Extensions_Jigoshop_Jigoshop::get_option( 'pronamic_pay_ideal_jigoshop_description' );

		// Set own variables, load them form the WordPress options
		$this->config_id      = Pronamic_WP_Pay_Extensions_Jigoshop_Jigoshop::get_option( 'pronamic_pay_ideal_jigoshop_config_id' );
	}

	//////////////////////////////////////////////////

	/**
	 * Get default options
	 *
	 * @return array
	 */
	protected function get_default_options() {
		$defaults = array();

		// Section
		$defaults[] = array(
			'name' => __( 'Pronamic iDEAL', 'pronamic_ideal' ),
			'type' => 'title',
			'desc' => __( 'Allow iDEAL payments.', 'pronamic_ideal' ),
		);

		// Options
		$defaults[] = array(
			'name'    => __( 'Enable iDEAL', 'pronamic_ideal' ),
			'desc'    => '',
			'tip'     => '',
			'id'      => 'pronamic_pay_ideal_jigoshop_enabled',
			'std'     => 'yes',
			'type'    => 'checkbox',
			'choices' => array(
				'no'  => __( 'No', 'pronamic_ideal' ),
				'yes' => __( 'Yes', 'pronamic_ideal' ),
			),
		);

		$defaults[] = array(
			'name'    => __( 'Title', 'pronamic_ideal' ),
			'desc'    => '',
			'tip'     => __( 'This controls the title which the user sees during checkout.', 'pronamic_ideal' ),
			'id'      => 'pronamic_pay_ideal_jigoshop_title',
			'std'     => __( 'iDEAL', 'pronamic_ideal' ),
			'type'    => 'text',
		);

		$defaults[] = array(
			'name'    => __( 'Description', 'pronamic_ideal' ),
			'desc'    => '',
			'tip'     => __( 'This controls the description which the user sees during checkout.', 'pronamic_ideal' ),
			'id'      => 'pronamic_pay_ideal_jigoshop_description',
			'std'     => '',
			'type'    => 'longtext',
		);

		$defaults[] = array(
			'name'    => __( 'Configuration', 'pronamic_ideal' ),
			'desc'    => '',
			'tip'     => '',
			'id'      => 'pronamic_pay_ideal_jigoshop_config_id',
			'std'     => '',
			'type'    => 'select',
			'choices' => Pronamic_WP_Pay_Plugin::get_config_select_options(),
		);

		return $defaults;
	}

	//////////////////////////////////////////////////

	/**
	 * Payment fields
	 */
	function payment_fields() {
		if ( ! empty( $this->description ) ) {
			echo wpautop( wptexturize( $this->description ) );
		}

		$gateway = Pronamic_WP_Pay_Plugin::get_gateway( $this->config_id );

		if ( $gateway ) {
			if ( $gateway->payment_method_is_required() && null === $gateway->get_payment_method() ) {
				$gateway->set_payment_method( Pronamic_WP_Pay_PaymentMethods::IDEAL );
			}

			echo $gateway->get_input_html();
		}
	}

	//////////////////////////////////////////////////

	/**
	 * Process the payment and return the result
	 */
	function process_payment( $order_id ) {
		$order = new jigoshop_order( $order_id );

		// Mark as on-hold (we're awaiting the payment)
		$order->update_status( 'pending', __( 'Pending iDEAL payment.', 'pronamic_ideal' ) );

		// Do specifiek iDEAL variant processing
		$gateway = Pronamic_WP_Pay_Plugin::get_gateway( $this->config_id );

		if ( $gateway ) {
			$data = new Pronamic_WP_Pay_Extensions_Jigoshop_PaymentData( $order );

			$payment = Pronamic_WP_Pay_Plugin::start( $this->config_id, $gateway, $data );

			$error = $gateway->get_error();

			if ( is_wp_error( $error ) ) {
				jigoshop::add_error( Pronamic_WP_Pay_Plugin::get_default_error_message() );

				if ( current_user_can( 'administrator' ) ) {
					foreach ( $error->get_error_codes() as $code ) {
						jigoshop::add_error( $error->get_error_message( $code ) );
					}
				}

				// see https://github.com/jigoshop/jigoshop/blob/1.4.9/shortcodes/pay.php#L55
				return array(
					'result' => 'failed',
				);
			}

			return array(
				'result'   => 'success',
				'redirect' => $payment->get_pay_redirect_url(),
			);
		}
	}
}
