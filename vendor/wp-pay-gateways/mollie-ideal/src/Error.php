<?php

/**
 * Title: iDEAL Mollie error
 * Description:
 * Copyright: Copyright (c) 2005 - 2016
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.0.0
 * @see https://www.mollie.nl/support/documentatie/betaaldiensten/ideal/en/
 */
class Pronamic_WP_Pay_Gateways_Mollie_IDeal_Error {
	/**
	 * Mollie error code
	 *
	 * @var string
	 */
	private $code;

	/**
	 * Mollie error message
	 *
	 * @var string
	 */
	private $message;

	/////////////////////////////////////////////////

	/**
	 * Constructs and initializes an Mollie error object
	 *
	 * @param string $code
	 * @param string $message
	 */
	public function __construct( $code, $message ) {
		$this->code    = $code;
		$this->message = $message;
	}

	//////////////////////////////////////////////////

	// @todo getters and setters

	//////////////////////////////////////////////////

	/**
	 * Create an string representation of this object
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->code . ' ' . $this->message;
	}
}
