<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://makewebbetter.com
 * @since      1.0.0
 *
 * @package    hubwoo-deal-per-order-lite
 * @subpackage hubwoo-deal-per-order-lite/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    hubwoo-deal-per-order-lite
 * @subpackage hubwoo-deal-per-order-lite/includes
 * @author     MakeWebBetter <webmaster@makewebbetter.com>
 */
class Hubspot_Deal_Per_Order_Lite_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		unlink( WC_LOG_DIR . 'hubwoo-deal-lite-logs.log' );
	}
}
