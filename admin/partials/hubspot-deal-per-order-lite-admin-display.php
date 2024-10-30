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
 * @subpackage hubwoo-deal-per-order-lite/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {

	exit;
}

$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'creation-setting';
?>

<div style="display:none;" class="loading-style-bg" id="hubwoo-deal-lite-loader">
	<img src="<?php echo esc_url( HUBWOO_DEAL_LITE_URL . 'admin/images/loader.gif' ); ?>">
</div>
<div class="hubwoo_deal_lite_main_wrapper">
	<div class="wrap woocommerce hubwoo-deal-lite">
		<form action="" method="post">
			<h1 class="hubdeals_plugin_title"><?php esc_html_e( 'Deal Per Order for HubSpot', 'hubwoo-deal-per-order-lite' ); ?></h1>
			<nav class="nav-tab-wrapper woo-nav-tab-wrapper">
				<a class="nav-tab <?php echo esc_attr( $active_tab ) == 'creation-setting' ? 'nav-tab-active' : ''; ?>" href="?page=hubwoo-deal-lite&tab=creation-setting"><?php esc_html_e( 'Create Deals', 'hubwoo-deal-per-order-lite' ); ?></a>
			</nav>
			<?php

			if ( 'creation-setting' == $active_tab ) {
				if ( file_exists( HUBWOO_DEAL_LITE_ABSPATH . 'admin/partials/templates/hubwoo-deal-lite-setup-template.php' ) ) {
					include_once HUBWOO_DEAL_LITE_ABSPATH . 'admin/partials/templates/hubwoo-deal-lite-setup-template.php';
				}
			}
			?>
		</form>
	</div>
</div>
