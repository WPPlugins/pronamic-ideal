<?php

/**
 * Title: Ogone order standard config factory
 * Description:
 * Copyright: Copyright (c) 2005 - 2016
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.3.2
 * @since 1.0.0
 */
class Pronamic_WP_Pay_Gateways_Ogone_OrderStandard_ConfigFactory extends Pronamic_WP_Pay_GatewayConfigFactory {
	private $config_class;

	public function __construct( $config_class = 'Pronamic_WP_Pay_Gateways_Ogone_OrderStandard_Config', $config_test_class = 'Pronamic_WP_Pay_Gateways_Ogone_OrderStandard_TestConfig' ) {
		$this->config_class      = $config_class;
		$this->config_test_class = $config_test_class;
	}

	public function get_config( $post_id ) {
		$mode = get_post_meta( $post_id, '_pronamic_gateway_mode', true );

		$config_class = ( 'test' === $mode ) ? $this->config_test_class : $this->config_class;

		$config = new $config_class();

		$form_action_url = get_post_meta( $post_id, '_pronamic_gateway_ogone_form_action_url', true );

		if ( '' !== $form_action_url ) {
			$config->set_form_action_url( $form_action_url );
		}

		$config->mode                = $mode;
		$config->psp_id              = get_post_meta( $post_id, '_pronamic_gateway_ogone_psp_id', true );
		$config->hash_algorithm      = get_post_meta( $post_id, '_pronamic_gateway_ogone_hash_algorithm', true );
		$config->sha_in_pass_phrase  = get_post_meta( $post_id, '_pronamic_gateway_ogone_sha_in_pass_phrase', true );
		$config->sha_out_pass_phrase = get_post_meta( $post_id, '_pronamic_gateway_ogone_sha_out_pass_phrase', true );
		$config->user_id             = get_post_meta( $post_id, '_pronamic_gateway_ogone_user_id', true );
		$config->password            = get_post_meta( $post_id, '_pronamic_gateway_ogone_password', true );
		$config->order_id            = get_post_meta( $post_id, '_pronamic_gateway_ogone_order_id', true );
		$config->param_var           = get_post_meta( $post_id, '_pronamic_gateway_ogone_param_var', true );
		$config->template_page       = get_post_meta( $post_id, '_pronamic_gateway_ogone_template_page', true );

		return $config;
	}
}
