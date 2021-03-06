<?php

/**
 * Title: ClassiPress iDEAL Add-On
 * Description:
 * Copyright: Copyright (c) 2005 - 2017
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.0.4
 * @since 1.0.0
 */
class Pronamic_WP_Pay_Extensions_ClassiPress_Extension {
	/**
	 * Slug
	 *
	 * @var string
	 */
	const SLUG = 'classipress';

	//////////////////////////////////////////////////

	/**
	 * Bootstrap
	 */
	public static function bootstrap() {
		add_action( 'appthemes_init', array( __CLASS__, 'appthemes_init' ) );

		/*
		 * We have to add this action on bootstrap, because we can't
		 * deterime earlier we are dealing with ClassiPress
		 */
		if ( is_admin() ) {
			add_action( 'cp_action_gateway_values', array( __CLASS__, 'gateway_values' ) );
		}
	}

	//////////////////////////////////////////////////

	/**
	 * Initialize
	 */
	public static function appthemes_init() {
		global $app_theme;

		if ( 'ClassiPress' === $app_theme ) {
			add_action( 'cp_action_payment_method', array( __CLASS__, 'payment_method' ) );
			add_action( 'cp_action_gateway', array( __CLASS__, 'gateway_process' ) );

			add_action( 'template_redirect', array( __CLASS__, 'process_gateway' ) );

			add_filter( 'pronamic_payment_redirect_url_' . self::SLUG, array( __CLASS__, 'redirect_url' ), 10, 2 );
			add_action( 'pronamic_payment_status_update_' . self::SLUG, array( __CLASS__, 'update_status' ), 10, 1 );
			add_filter( 'pronamic_payment_source_text_' . self::SLUG, array( __CLASS__, 'source_text' ), 10, 2 );
			add_filter( 'pronamic_payment_source_description_' . self::SLUG,   array( __CLASS__, 'source_description' ), 10, 2 );
			add_filter( 'pronamic_payment_source_url_' . self::SLUG,   array( __CLASS__, 'source_url' ), 10, 2 );
		}
	}

	//////////////////////////////////////////////////

	/**
	 * Gateway value
	 */
	public static function gateway_values() {
		global $app_abbr;

		// Gateway values
		global $action_gateway_values;

		$action_gateway_values = array(
			// Tab Start
			array(
				'type'    => 'tab',
				'tabname' => __( 'iDEAL', 'pronamic_ideal' ),
				'id'      => '',
			),
			// Title
			array(
				'type'    => 'title',
				'name'    => __( 'iDEAL Options', 'pronamic_ideal' ),
				'id'      => '',
			),
			// Logo/Picture
			array(
				'type'    => 'logo',
				'name'    => sprintf( '<img src="%s" alt="" />', plugins_url( 'images/icon-32x32.png', Pronamic_WP_Pay_Plugin::$file ) ),
				'id'      => '',
			),
			// Select Box
			array(
				'type'    => 'select',
				'name'    => __( 'Enable iDEAL', 'pronamic_ideal' ),
				'options' => array(
					'yes' => __( 'Yes', 'pronamic_ideal' ),
					'no'  => __( 'No', 'pronamic_ideal' ),
				),
				'id'      => $app_abbr . '_pronamic_ideal_enable',
			),
			// Select Box
			array(
				'type'    => 'select',
				'name'    => __( 'iDEAL Configuration', 'pronamic_ideal' ),
				'options' => Pronamic_WP_Pay_Plugin::get_config_select_options(),
				'id'      => $app_abbr . '_pronamic_ideal_config_id',
			),
			array(
				'type'    => 'tabend',
				'id'      => '',
			),
		);
	}

	//////////////////////////////////////////////////

	private function get_config_id() {
		global $app_abbr;

		$config_id = get_option( $app_abbr . '_pronamic_ideal_config_id' );

		return $config_id;
	}

	/**
	 * Get the config
	 *
	 * @return Pronamic_WordPress_IDeal_Configuration
	 */
	private function get_gateway() {
		$config_id = $this->get_config_id();

		$gateway = Pronamic_WP_Pay_Plugin::get_gateway( $config_id );

		return $gateway;
	}

	//////////////////////////////////////////////////

	/**
	 * Add the option to the payment drop-down list on checkout
	 */
	public static function payment_method() {
		global $app_abbr;

		if ( 'yes' === get_option( $app_abbr . '_pronamic_ideal_enable' ) ) {
			echo '<option value="pronamic_ideal">' . __( 'iDEAL', 'pronamic_ideal' ) . '</option>';
		}
	}

	//////////////////////////////////////////////////

	/**
	 * Process gateway
	 */
	public static function process_gateway() {
		if ( ! filter_has_var( INPUT_POST, 'classipress_pronamic_ideal' ) ) {
			return;
		}

		$config_id = $this->get_config_id();

		$gateway = $this->get_gateway();

		if ( ! $gateway ) {
			return;
		}

		$id = filter_input( INPUT_POST, 'oid', FILTER_SANITIZE_STRING );

		$order = Pronamic_WP_Pay_Extensions_ClassiPress_ClassiPress::get_order_by_id( $id );

		$data = new Pronamic_WP_Pay_Extensions_ClassiPress_PaymentData( $order );

		$payment = Pronamic_WP_Pay_Plugin::start( $config_id, $gateway, $data );

		wp_redirect( $payment->get_pay_redirect_url() );

		exit;
	}

	//////////////////////////////////////////////////

	/**
	 * Process gateway
	 */
	public static function gateway_process( $order_values ) {
		// If gateway wasn't selected then immediately return
		if ( 'pronamic_ideal' !== $order_values['cp_payment_method'] ) {
			return;
		}

		// Add transaction entry
		$transaction_id = Pronamic_WP_Pay_Extensions_ClassiPress_ClassiPress::add_transaction_entry( $order_values );

		// Handle gateway
		$gateway = $this->get_gateway();

		if ( ! $gateway ) {
			return;
		}

		$data = new Pronamic_WP_Pay_Extensions_ClassiPress_PaymentData( $order_values );

		// Hide the checkout page container HTML element
		echo '<style type="text/css">.thankyou center { display: none; }</style>';

		?>
		<form class="form_step" method="post" action="">
			<?php

			echo Pronamic_IDeal_IDeal::htmlHiddenFields( array(
				'cp_payment_method'  => 'pronamic_ideal',
				'oid'                => $data->get_order_id(),
			) );

			echo $gateway->get_input_html();

			?>

			<p class="btn1">
				<?php

				printf(
					'<input class="ideal-button" type="submit" name="classipress_pronamic_ideal" value="%s" />',
					__( 'Pay with iDEAL', 'pronamic_ideal' )
				);

				?>
			</p>
		</form>
		<?php
	}

	//////////////////////////////////////////////////

	/**
	 * Payment redirect URL filter.
	 *
	 * @since unreleased
	 * @param string                  $url
	 * @param Pronamic_WP_Pay_Payment $payment
	 * @return string
	 */
	public static function redirect_url( $url, $payment ) {
		$id = $payment->get_source_id();

		$order = Pronamic_WP_Pay_Extensions_ClassiPress_ClassiPress::get_order_by_id( $id );

		$data  = new Pronamic_WP_Pay_Extensions_ClassiPress_PaymentData( $order );

		$url = $data->get_normal_return_url();

		switch ( $payment->status ) {
			case Pronamic_WP_Pay_Statuses::CANCELLED:

				break;
			case Pronamic_WP_Pay_Statuses::EXPIRED:

				break;
			case Pronamic_WP_Pay_Statuses::FAILURE:

				break;
			case Pronamic_WP_Pay_Statuses::SUCCESS:
				$url = $data->get_success_url();

				break;
			case Pronamic_WP_Pay_Statuses::OPEN:

				break;
			default:

				break;
		}

		return $url;
	}

	//////////////////////////////////////////////////

	/**
	 * Update lead status of the specified payment
	 *
	 * @param string $payment
	 */
	public static function update_status( Pronamic_Pay_Payment $payment ) {
		$id = $payment->get_source_id();

		$order = Pronamic_WP_Pay_Extensions_ClassiPress_ClassiPress::get_order_by_id( $id );

		switch ( $payment->status ) {
			case Pronamic_WP_Pay_Statuses::CANCELLED:

				break;
			case Pronamic_WP_Pay_Statuses::EXPIRED:

				break;
			case Pronamic_WP_Pay_Statuses::FAILURE:

				break;
			case Pronamic_WP_Pay_Statuses::SUCCESS:
				if ( ! Pronamic_WP_Pay_Extensions_ClassiPress_Order::is_completed( $order ) ) {
					Pronamic_WP_Pay_Extensions_ClassiPress_ClassiPress::process_ad_order( $id );

					Pronamic_WP_Pay_Extensions_ClassiPress_ClassiPress::process_membership_order( $order );

					Pronamic_WP_Pay_Extensions_ClassiPress_ClassiPress::update_payment_status_by_txn_id( $id, Pronamic_WP_Pay_Extensions_ClassiPress_PaymentStatuses::COMPLETED );
				}

				break;
			case Pronamic_WP_Pay_Statuses::OPEN:

				break;
			default:

				break;
		}
	}

	//////////////////////////////////////////////////

	/**
	 * Source column
	 */
	public static function source_text( $text, Pronamic_Pay_Payment $payment ) {
		$text  = '';

		$text .= __( 'ClassiPress', 'pronamic_ideal' ) . '<br />';

		$text .= sprintf(
			'<a href="%s">%s</a>',
			add_query_arg( 'page', 'transactions', admin_url( 'admin.php' ) ),
			sprintf( __( 'Order #%s', 'pronamic_ideal' ), $payment->get_source_id() )
		);

		return $text;
	}

	/**
	 * Source description.
	 */
	public static function source_description( $description, Pronamic_Pay_Payment $payment ) {
		$description = __( 'ClassiPress Order', 'pronamic_ideal' );

		return $description;
	}

	/**
	 * Source URL.
	 */
	public static function source_url( $url, Pronamic_Pay_Payment $payment ) {
		$url = add_query_arg( 'page', 'transactions', admin_url( 'admin.php' ) );

		return $url;
	}
}
