<?php

/**
 * Title: Mollie iDEAL config
 * Description:
 * Copyright: Copyright (c) 2005 - 2016
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.0.0
 */
class Pronamic_WP_Pay_Gateways_Mollie_IDeal_Config extends Pronamic_WP_Pay_GatewayConfig {
	public $partner_id;

	public $profile_key;

	public function get_gateway_class() {
		return 'Pronamic_WP_Pay_Gateways_Mollie_IDeal_Gateway';
	}
}
