<?php

/**
 * Fired during plugin activation
 *
 * @link       https://makewebbetter.com
 * @since      1.0.0
 *
 * @package    hubwoo-deal-per-order-lite
 * @subpackage hubwoo-deal-per-order-lite/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    hubwoo-deal-per-order-lite
 * @subpackage hubwoo-deal-per-order-lite/includes
 * @author     MakeWebBetter <webmaster@makewebbetter.com>
 */
class Hubspot_Deal_Per_Order_Lite_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		// phpcs:disable
		@fopen( WC_LOG_DIR.'hubwoo-deal-lite-logs.log', 'a' );
		// phpcs:enable
	}
}
