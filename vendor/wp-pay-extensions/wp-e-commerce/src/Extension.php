<?php

/**
 * Title: WP eCommerce extension
 * Description:
 * Copyright: Copyright (c) 2005 - 2017
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.0.4
 * @since 1.0.0
 */
class Pronamic_WP_Pay_Extensions_WPeCommerce_Extension {
	/**
	 * Slug
	 *
	 * @var string
	 */
	const SLUG = 'wp-e-commerce';

	/**
	 * Option for config ID
	 *
	 * @var string
	 */
	const OPTION_IDEAL_CONFIG_ID = 'pronamic_pay_ideal_wpsc_config_id';

	/**
	 * Option for config ID
	 *
	 * @var string
	 */
	const OPTION_PRONAMIC_CONFIG_ID = 'pronamic_pay_pronamic_wpsc_config_id';

	/**
	 * Option for payment method
	 *
	 * @var string
	 */
	const OPTION_PRONAMIC_PAYMENT_METHOD = 'pronamic_pay_pronamic_wpsc_payment_method';

	//////////////////////////////////////////////////

	/**
	 * Bootstrap
	 */
	public static function bootstrap() {
		// Add gateway to gateways
		add_filter( 'wpsc_merchants_modules',               array( __CLASS__, 'merchants_modules' ) );

		// Update payment status when returned from iDEAL
		add_action( 'pronamic_payment_status_update_' . self::SLUG, array( __CLASS__, 'status_update' ), 10, 2 );

		// Source Column
		add_filter( 'pronamic_payment_source_text_' . self::SLUG, array( __CLASS__, 'source_text' ), 10, 2 );
		add_filter( 'pronamic_payment_source_description_' . self::SLUG,   array( __CLASS__, 'source_description' ), 10, 2 );
		add_filter( 'pronamic_payment_source_url_' . self::SLUG,   array( __CLASS__, 'source_url' ), 10, 2 );
	}

	//////////////////////////////////////////////////

	/**
	 * Merchants modules
	 *
	 * @param array $gateways
	 */
	public static function merchants_modules( $gateways ) {
		global $nzshpcrt_gateways, $num, $wpsc_gateways, $gateway_checkout_form_fields;

		$gateways[] = array(
			'name'                   => __( 'Pronamic', 'pronamic_ideal' ),
			'api_version'            => 2.0,
			'class_name'             => 'Pronamic_WP_Pay_Extensions_WPeCommerce_PronamicMerchant',
			'has_recurring_billing'  => false,
			'wp_admin_cannot_cancel' => false,
			'display_name'           => __( 'Pronamic', 'pronamic_ideal' ),
			'requirements'           => array(
				'php_version'   => 5.0,
				'extra_modules' => array(),
			),
			'form'                   => 'pronamic_ideal_wpsc_pronamic_merchant_form',
			'submit_function'        => 'pronamic_ideal_wpsc_pronamic_merchant_submit_function',
			// this may be legacy, not yet decided
			'internalname'           => 'wpsc_merchant_pronamic',
		);

		$gateways[] = array(
			'name'                   => __( 'Pronamic iDEAL', 'pronamic_ideal' ),
			'api_version'            => 2.0,
			'image'                  => plugins_url( '/images/icon-32x32.png', Pronamic_WP_Pay_Plugin::$file ),
			'class_name'             => 'Pronamic_WP_Pay_Extensions_WPeCommerce_IDealMerchant',
			'has_recurring_billing'  => false,
			'wp_admin_cannot_cancel' => false,
			'display_name'           => __( 'iDEAL', 'pronamic_ideal' ),
			'requirements'           => array(
				'php_version'   => 5.0,
				'extra_modules' => array(),
			),
			'form'                   => 'pronamic_ideal_wpsc_ideal_merchant_form',
			'submit_function'        => 'pronamic_ideal_wpsc_ideal_merchant_submit_function',
			// this may be legacy, not yet decided
			'internalname'           => 'wpsc_merchant_pronamic_ideal',
		);

		$gateway_checkout_form_fields['wpsc_merchant_pronamic']       = Pronamic_WP_Pay_Extensions_WPeCommerce_PronamicMerchant::advanced_inputs();
		$gateway_checkout_form_fields['wpsc_merchant_pronamic_ideal'] = Pronamic_WP_Pay_Extensions_WPeCommerce_IDealMerchant::advanced_inputs();

		return $gateways;
	}

	//////////////////////////////////////////////////

	/**
	 * Update lead status of the specified payment
	 *
	 * @param Pronamic_Pay_Payment $payment
	 */
	public static function status_update( Pronamic_Pay_Payment $payment, $can_redirect = false ) {
		$merchant = new Pronamic_WP_Pay_Extensions_WPeCommerce_IDealMerchant( $payment->get_source_id() );
		$data = new Pronamic_WP_Pay_Extensions_WPeCommerce_PaymentData( $merchant );

		$url = $data->get_normal_return_url();

		switch ( $payment->status ) {
			case Pronamic_WP_Pay_Statuses::CANCELLED:
				$merchant->set_purchase_processed_by_purchid( Pronamic_WP_Pay_Extensions_WPeCommerce_WPeCommerce::PURCHASE_STATUS_INCOMPLETE_SALE );
				// $merchant->set_transaction_details( $payment->transaction->getId(), Pronamic_WP_Pay_Extensions_WPeCommerce_WPeCommerce::PURCHASE_STATUS_INCOMPLETE_SALE );

				$url = $data->get_cancel_url();

				break;
			case Pronamic_WP_Pay_Statuses::EXPIRED:

				break;
			case Pronamic_WP_Pay_Statuses::FAILURE:

				break;
			case Pronamic_WP_Pay_Statuses::SUCCESS:
				/*
				 * Transactions results
				 *
				 * @see https://github.com/wp-e-commerce/WP-e-Commerce/blob/v3.8.9.5/wpsc-merchants/paypal-pro.merchant.php#L303
				 */
				$session_id = get_post_meta( $payment->get_id(), '_pronamic_payment_wpsc_session_id', true );

				transaction_results( $session_id );

				$merchant->set_purchase_processed_by_purchid( Pronamic_WP_Pay_Extensions_WPeCommerce_WPeCommerce::PURCHASE_STATUS_ACCEPTED_PAYMENT );

				$url = $data->get_success_url();

				break;
			case Pronamic_WP_Pay_Statuses::OPEN:

				break;
			default:

				break;
		}

		if ( $can_redirect ) {
			wp_redirect( $url );

			exit;
		}
	}

	//////////////////////////////////////////////////

	/**
	 * Source column
	 */
	public static function source_text( $text, Pronamic_WP_Pay_Payment $payment ) {
		$text  = '';

		$text .= __( 'WP e-Commerce', 'pronamic_ideal' ) . '<br />';

		$text .= sprintf(
			'<a href="%s">%s</a>',
			add_query_arg( array(
				'page'           => 'wpsc-sales-logs',
				'purchaselog_id' => $payment->get_source_id(),
			), admin_url( 'index.php' ) ),
			sprintf( __( 'Purchase #%s', 'pronamic_ideal' ), $payment->get_source_id() )
		);

		return $text;
	}

	/**
	 * Source description.
	 */
	public static function source_description( $description, Pronamic_Pay_Payment $payment ) {
		$description = __( 'WP e-Commerce Purchase', 'pronamic_ideal' );

		return $description;
	}

	/**
	 * Source URL.
	 */
	public static function source_url( $url, Pronamic_Pay_Payment $payment ) {
		$url = add_query_arg( array(
			'page'           => 'wpsc-sales-logs',
			'purchaselog_id' => $payment->get_source_id(),
		), admin_url( 'index.php' ) );

		return $url;
	}
}
