<?php

/**
 * Title: Mollie gateway
 * Description:
 * Copyright: Copyright (c) 2005 - 2016
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.0.7
 * @since 1.0.0
 */
class Pronamic_WP_Pay_Gateways_Mollie_IDeal_Gateway extends Pronamic_WP_Pay_Gateway {
	/**
	 * Slug of this gateway
	 *
	 * @var string
	 */
	const SLUG = 'mollie_ideal';

	/////////////////////////////////////////////////

	/**
	 * Constructs and initializes an Mollie gateway
	 *
	 * @param Pronamic_WP_Pay_Gateways_Mollie_IDeal_Config $config
	 */
	public function __construct( Pronamic_WP_Pay_Gateways_Mollie_IDeal_Config $config ) {
		parent::__construct( $config );

		$this->set_method( Pronamic_WP_Pay_Gateway::METHOD_HTTP_REDIRECT );
		$this->set_has_feedback( true );
		$this->set_amount_minimum( 1.20 );
		$this->set_slug( self::SLUG );

		$this->client = new Pronamic_WP_Pay_Gateways_Mollie_IDeal_Client( $config->partner_id );
		$this->client->set_test_mode( Pronamic_IDeal_IDeal::MODE_TEST === $config->mode );
	}

	/////////////////////////////////////////////////

	/**
	 * Get issuers
	 *
	 * @see Pronamic_WP_Pay_Gateway::get_issuers()
	 */
	public function get_issuers() {
		$groups = array();

		$result = $this->client->get_banks();

		if ( $result ) {
			$groups[] = array(
				'options' => $result,
			);
		} else {
			$this->error = $this->client->get_error();
		}

		return $groups;
	}

	/////////////////////////////////////////////////

	public function get_issuer_field() {
		if ( Pronamic_WP_Pay_PaymentMethods::IDEAL === $this->get_payment_method() ) {
			return array(
				'id'       => 'pronamic_ideal_issuer_id',
				'name'     => 'pronamic_ideal_issuer_id',
				'label'    => __( 'Choose your bank', 'pronamic_ideal' ),
				'required' => true,
				'type'     => 'select',
				'choices'  => $this->get_transient_issuers(),
			);
		}
	}

	/////////////////////////////////////////////////

	/**
	 * Get payment methods
	 *
	 * @return mixed an array or null
	 */
	public function get_payment_methods() {
		return Pronamic_WP_Pay_PaymentMethods::IDEAL;
	}

	/////////////////////////////////////////////////

	/**
	 * Get supported payment methods
	 *
	 * @see Pronamic_WP_Pay_Gateway::get_supported_payment_methods()
	 */
	public function get_supported_payment_methods() {
		return array(
			Pronamic_WP_Pay_PaymentMethods::IDEAL,
		);
	}

	/////////////////////////////////////////////////

	/**
	 * Start
	 *
	 * @see Pronamic_WP_Pay_Gateway::start()
	 */
	public function start( Pronamic_Pay_Payment $payment ) {
		$result = $this->client->create_payment(
			$payment->get_issuer(),
			Pronamic_WP_Util::amount_to_cents( $payment->get_amount() ),
			$payment->get_description(),
			$payment->get_return_url(),
			$payment->get_return_url()
		);

		if ( false !== $result ) {
			$payment->set_transaction_id( $result->transaction_id );
			$payment->set_action_url( $result->url );

		} else {
			$this->error = $this->client->get_error();
		}
	}

	/////////////////////////////////////////////////

	/**
	 * Update status of the specified payment
	 *
	 * @param Pronamic_Pay_Payment $payment
	 */
	public function update_status( Pronamic_Pay_Payment $payment ) {
		$result = $this->client->check_payment( $payment->get_transaction_id() );

		if ( false !== $result ) {
			$consumer = $result->consumer;

			switch ( $result->status ) {
				case Pronamic_WP_Pay_Gateways_Mollie_IDeal_Statuses::SUCCESS :
					$payment->set_consumer_name( $consumer->name );
					$payment->set_consumer_account_number( $consumer->account );
					$payment->set_consumer_city( $consumer->city );
				case Pronamic_WP_Pay_Gateways_Mollie_IDeal_Statuses::CANCELLED :
				case Pronamic_WP_Pay_Gateways_Mollie_IDeal_Statuses::EXPIRED :
				case Pronamic_WP_Pay_Gateways_Mollie_IDeal_Statuses::FAILURE :
					$payment->set_status( $result->status );

					break;
				case Pronamic_WP_Pay_Gateways_Mollie_IDeal_Statuses::CHECKED_BEFORE :
					// Nothing to do here

					break;
			}
		} else {
			$this->error = $this->client->get_error();
		}
	}
}
