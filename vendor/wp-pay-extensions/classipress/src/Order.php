<?php

/**
 * Title: Order
 * Description:
 * Copyright: Copyright (c) 2005 - 2017
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.0.3
 * @since 1.0.0
 */
class Pronamic_WP_Pay_Extensions_ClassiPress_Order {
	/**
	 * Check if the order info is about an advertisement
	 *
	 * @param array $order
	 * @return boolean
	 */
	public static function is_advertisement( array $order ) {
		return isset( $order['ad_id'] ) && ! empty( $order['ad_id'] );
	}

	//////////////////////////////////////////////////

	/**
	 * Check if the order info is about an package
	 *
	 * @param array $order
	 * @return boolean
	 */
	public static function is_package( array $order ) {
		return ! self::is_advertisement( $order );
	}

	//////////////////////////////////////////////////

	/**
	 * Check if the order is completed
	 *
	 * @param array $order
	 * @return boolean
	 */
	public static function is_completed( array $order ) {
		return isset( $order['payment_status'] ) && Pronamic_WP_Pay_Extensions_ClassiPress_PaymentStatuses::COMPLETED === $order['payment_status'];
	}
}
