<?php

/**
 * Title: Deutsche Bank - iDEAL Expert - v3 config factory
 * Description:
 * Copyright: Copyright (c) 2005 - 2016
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.0.0
 */
class Pronamic_WP_Pay_Gateways_DeutscheBank_IDealExpertV3_ConfigFactory extends Pronamic_WP_Pay_Gateways_IDealAdvancedV3_ConfigFactory {
	public function __construct() {
		parent::__construct( 'Pronamic_WP_Pay_Gateways_DeutscheBank_IDealExpertV3_Config', 'Pronamic_WP_Pay_Gateways_DeutscheBank_IDealExpertV3_TestConfig' );
	}
}
