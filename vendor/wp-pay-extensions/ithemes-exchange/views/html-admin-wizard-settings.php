<div class="field <?php echo esc_attr( Pronamic_WP_Pay_Extensions_IThemesExchange_Extension::$slug ); ?>-wizard">
	<h3><?php esc_html_e( 'iDEAL', 'pronamic_ideal' ); ?></h3>

	<?php settings_fields( Pronamic_WP_Pay_Extensions_IThemesExchange_Extension::OPTION_GROUP ); ?>

	<?php do_settings_sections( Pronamic_WP_Pay_Extensions_IThemesExchange_Extension::OPTION_GROUP ); ?>

	<input
		class="enable-<?php echo esc_attr( Pronamic_WP_Pay_Extensions_IThemesExchange_Extension::$slug ); ?>"
		name="it-exchange-transaction-methods[]"
		value="<?php echo esc_attr( Pronamic_WP_Pay_Extensions_IThemesExchange_Extension::$slug ); ?>"
		type="hidden"
	/>
</div>
