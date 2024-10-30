<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubwoo-deal-per-order-lite
 * @subpackage hubwoo-deal-per-order-lite/admin/partials/templates
 */
?>
<?php

if ( isset( $_POST['hubwoo-deal-lite-save'] ) && check_admin_referer( 'hubwoo-deal-lite' ) ) {

	unset( $_POST['hubwoo-deal-lite-save'] );
	woocommerce_update_options( Hubspot_Deal_Per_Order_Lite_Admin::hubwoo_deal_lite_settings() );
	$message = esc_html__( 'Settings saved', 'hubwoo-deal-per-order-lite' );
	Hubspot_Deal_Per_Order_Lite::hubwoo_deal_lite_notice( $message, 'success' );
}
?>

<?php
	add_thickbox();
?>

<div id="hubwoo-deal-pipeline-setup-process" style="display:none;">
	<div class="popupwrap">
		<p>
			<?php esc_html_e( 'We are setting up for new WooCommerce Order Pipeline. Please do not navigate or reload the page before our confirmation message.', 'hubwoo-deal-per-order-lite' ); ?>
		</p>
		   <div class="progress">
			<div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width:0%">
			</div>
		  </div>
		<div class="hubwoo-deal-lite-message-area">
		</div>
	</div>
</div>

<?php

	$display = 'none';

if ( Hubspot_Deal_Per_Order_Lite::is_display_suggestion_popup() ) {

	$display = 'block';
}
?>
	<div class="hub_deal_lite_pop_up_wrap" style="display: <?php echo esc_attr( $display ); ?>">
		<div class="pop_up_sub_wrap">
			<p>
				<?php esc_html_e( 'Support the plugin development by sending us tracking data( we just want the HubSpot id and Email id and that too only once )', 'hubwoo-deal-per-order-lite' ); ?>
			</p>
			<div class="button_wrap">
				<a href="javascript:void(0);" class="hubwoo_deal_lite_accept"><?php esc_html_e( 'Yes support it', 'hubwoo-deal-per-order-lite' ); ?></a>
				<a href="javascript:void(0);" class="hubwoo_deal_lite_later"><?php esc_html_e( "I'll decide later", 'hubwoo-deal-per-order-lite' ); ?></a>
			</div>
		</div>
	</div>

<?php

	$message = esc_html__( 'Congratulations! Your orders are ready to be converted as HubSpot Deals. ', 'hubwoo-deal-per-order-lite' );

if ( 'yes' == get_option( 'hubwoo_deal_lite_settings_enable', 'yes' ) ) {

	?>
			<div class="updated">
				<p><?php esc_html_e( 'Congratulations! Your orders are ready to be converted as HubSpot Deals. ', 'hubwoo-deal-per-order-lite' ); ?>
		<?php

		if ( ! Hubspot_Deal_Per_Order_Lite::is_oauth_success() ) {

			$hubspot_url = Hubspot_Deal_Per_Order_Lite::get_hubspot_oauth_url();

			?>
				<span class="hubwoo_deal_lite_oauth_span"><label><?php esc_html_e( 'Please click here to authorize with our HubSpot App ', 'hubwoo-deal-per-order-lite' ); ?></label><a href="<?php echo esc_attr( $hubspot_url ); ?>" class="button-primary"><?php esc_html_e( 'Authorize', 'hubwoo-deal-per-order-lite' ); ?></a></span>
			<?php
		}

		if ( Hubspot_Deal_Per_Order_Lite::is_oauth_success() && ! Hubspot_Deal_Per_Order_Lite::is_pipeline_setup_completed() ) {

			?>
				<a id="hubwoo-deal-lite-pipeline-setup" href="javascript:void(0)" class="button button-primary"><?php esc_html_e( 'Setup Pipeline', 'hubwoo-deal-per-order-lite' ); ?></a>
			<?php
		}
		?>
			</p></div>
		<?php
}
?>

<?php
	woocommerce_admin_fields( Hubspot_Deal_Per_Order_Lite_Admin::hubwoo_deal_lite_settings() );
?>
<p class="submit">
	<input name="hubwoo-deal-lite-save" class="button-primary woocommerce-save-button hubwoo-save-button" type="submit" value="<?php esc_html_e( 'Save changes', 'hubwoo-deal-per-order-lite' ); ?>" />
	<?php wp_nonce_field( 'hubwoo-deal-lite' ); ?>
</p>
