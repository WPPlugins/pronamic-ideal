<?php

/**
 * Title: Mollie iDEAL config factory
 * Description:
 * Copyright: Copyright (c) 2005 - 2016
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.0.0
 */
class Pronamic_WP_Pay_Gateways_Mollie_IDeal_ConfigFactory extends Pronamic_WP_Pay_GatewayConfigFactory {
	public function get_config( $post_id ) {
		$config = new Pronamic_WP_Pay_Gateways_Mollie_IDeal_Config();

		$config->partner_id  = get_post_meta( $post_id, '_pronamic_gateway_mollie_partner_id', true );
		$config->profile_key = get_post_meta( $post_id, '_pronamic_gateway_mollie_profile_key', true );

		$config->mode        = get_post_meta( $post_id, '_pronamic_gateway_mode', true );

		return $config;
	}
}
