<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://makewebbetter.com
 * @since             1.0.0
 * @package           hubwoo-deal-per-order-lite
 *
 * @wordpress-plugin
 * Plugin Name:       Deal per order for HubSpot
 * Description:       Auto creates new Deal on HubSpot for every new order on your woocommerce store.
 * Version:           1.0.2
 * Requires at least:   4.4
 * Tested up to:        4.9
 * WC requires at least:    3.0.0
 * WC tested up to:         3.4.3
 * Author:            MakeWebBetter
 * Author URI:        https://makewebbetter.com
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       hubwoo-deal-per-order-lite
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$hubwoo_deal_lite_activated = true;

if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	$hubwoo_deal_lite_activated = false;
}

if ( $hubwoo_deal_lite_activated ) {

	/**
	 * The code that runs during plugin activation.
	 * This action is documented in includes/class-hubspot-deal-per-order-lite-activator.php
	 */
	function activate_hubspot_deal_per_order_lite() {

		require_once plugin_dir_path( __FILE__ ) . 'includes/class-hubspot-deal-per-order-lite-activator.php';
		Hubspot_Deal_Per_Order_Lite_Activator::activate();
	}

	/**
	 * The code that runs during plugin deactivation.
	 * This action is documented in includes/class-hubspot-deal-per-order-lite-deactivator.php
	 */
	function deactivate_hubspot_deal_per_order_lite() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-hubspot-deal-per-order-lite-deactivator.php';
		Hubspot_Deal_Per_Order_Lite_Deactivator::deactivate();
	}

	register_activation_hook( __FILE__, 'activate_hubspot_deal_per_order_lite' );
	register_deactivation_hook( __FILE__, 'deactivate_hubspot_deal_per_order_lite' );

	/**
	 * define constants.
	 *
	 * @since 1.0.0
	 */
	function hubwoo_deal_lite_constants() {

		hubwoo_deal_lite_define( 'HUBWOO_DEAL_LITE_URL', plugin_dir_url( __FILE__ ) . '/' );
		hubwoo_deal_lite_define( 'HUBWOO_DEAL_LITE_PLUGINS_PATH', plugin_dir_path( __DIR__ ) );
		hubwoo_deal_lite_define( 'HUBWOO_DEAL_LITE_ABSPATH', dirname( __FILE__ ) . '/' );
		hubwoo_deal_lite_define( 'HUBWOO_DEAL_LITE_VERSION', '1.0.1' );
		hubwoo_deal_lite_define( 'HUBWOO_DEAL_LITE_CLIENTID', '769fa3e6-79b1-412d-b69c-6b8242b2c62a' );
		hubwoo_deal_lite_define( 'HUBWOO_DEAL_LITE_SECRETID', '2893dd41-017e-4208-962b-12f7495d16b0' );
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param  string      $name
	 * @param  string|bool $value
	 * @since 1.0.0
	 */
	function hubwoo_deal_lite_define( $name, $value ) {

		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Setting Page Link
	 *
	 * @since    1.0.0
	 * @link  https://makewebbetter.com/
	 */

	function hubwoo_deal_lite_admin_settings( $actions, $plugin_file ) {

		static $plugin;
		if ( ! isset( $plugin ) ) {
			$plugin = plugin_basename( __FILE__ );
		}
		if ( $plugin == $plugin_file ) {
			$settings = array(
				'settings' => '<a href="' . esc_url( admin_url( 'admin.php' ) . '?page=hubwoo-deal-lite' ) . '">' . esc_html__( 'Settings', 'hubwoo-deal-per-order-lite' ) . '</a>',
			);
			$actions  = array_merge( $settings, $actions );
		}
		return $actions;
	}

	// add link for settings
	add_filter( 'plugin_action_links', 'hubwoo_deal_lite_admin_settings', 10, 2 );

	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	require plugin_dir_path( __FILE__ ) . 'includes/class-hubspot-deal-per-order-lite.php';

	/**
	 * Begins execution of the plugin.
	 *
	 * Since everything within the plugin is registered via hooks,
	 * then kicking off the plugin from this point in the file does
	 * not affect the page life cycle.
	 *
	 * @since    1.0.0
	 */
	function run_hubspot_deal_per_order_lite() {

		hubwoo_deal_lite_constants();
		$plugin = new Hubspot_Deal_Per_Order_Lite();
		$plugin->run();
	}

	run_hubspot_deal_per_order_lite();
} else {

	add_action( 'admin_init', 'hubwoo_deal_lite_plugin_deactivate' );

	/**
	 * Call Admin notices
	 *
	 * @author MakeWebBetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */

	function hubwoo_deal_lite_plugin_deactivate() {

		deactivate_plugins( plugin_basename( __FILE__ ) );
		add_action( 'admin_notices', 'hubwoo_deal_lite_plugin_error_notice' );
	}

	/**
	 * Show warning message if woocommerce is not install
	 *
	 * @since 1.0.0
	 * @author MakeWebBetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */

	function hubwoo_deal_lite_plugin_error_notice() {

		?>
		  <div class="error notice is-dismissible">
			 <p><?php esc_html_e( 'WooCommerce is not activated, Please activate WooCommerce first to install HubSpot Deal Per Order Lite.', 'hubwoo-deal-per-order-lite' ); ?></p>
		   </div>
		   <style>
		   #message{display:none;}
		   </style>
		<?php
	}
}
