<?php

/**
 * Title: Abstract Integration
 * Description:
 * Copyright: Copyright (c) 2005 - 2016
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.0.1
 * @since 1.0.0
 * @see https://github.com/thephpleague/omnipay-common/blob/master/src/Omnipay/Common/AbstractGateway.php
 */
abstract class Pronamic_WP_Pay_Gateways_AbstractIntegration implements Pronamic_WP_Pay_Gateways_IntegrationInterface {
	protected $id;

	protected $name;

	public function get_id() {
		return $this->id;
	}

	public function set_id( $id ) {
		$this->id = $id;
	}

	public function get_name() {
		return $this->name;
	}

	public function set_name( $name ) {
		$this->name = $name;
	}

	/**
	 * Get required settings for this integration.
	 *
	 * @see https://github.com/wp-premium/gravityforms/blob/1.9.16/includes/fields/class-gf-field-multiselect.php#L21-L42
	 * @return array
	 */
	public function get_settings() {
		return array();
	}

	public function get_dashboard_url() {
		$url = array();

		if ( isset( $this->dashboard_url ) ) {
			if ( is_string( $this->dashboard_url ) ) {
				$url = array( $this->dashboard_url );
			} elseif ( is_array( $this->dashboard_url ) ) {
				$url = $this->dashboard_url;
			}
		}

		return $url;
	}

	public function get_product_url() {
		$url = false;

		if ( isset( $this->product_url ) ) {
			$url = $this->product_url;
		} elseif ( isset( $this->url ) ) {
			$url = $this->url;
		}

		return $url;
	}
}
