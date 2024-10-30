<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://makewebbetter.com
 * @since      1.0.0
 *
 * @package    hubwoo-deal-per-order-lite
 * @subpackage hubwoo-deal-per-order-lite/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    hubwoo-deal-per-order-lite
 * @subpackage hubwoo-deal-per-order-lite/public
 * @author     MakeWebBetter <webmaster@makewebbetter.com>
 */
class Hubspot_Deal_Per_Order_Lite_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * preparing to setup a new deal for HubSpot
	 *
	 * @since    1.0.0
	 * @param    int $order_id       Order ID
	 */
	public function hubwoo_deal_lite_new_order( $order_id ) {

		if ( ! empty( $order_id ) ) {

			$user_id = get_post_meta( $order_id, '_customer_user', true );

			if ( $user_id != 0 && $user_id > 0 ) {

				$user           = get_user_by( 'id', $user_id );
				$customer_email = $user->data->user_email;
			} else {

				$order          = new WC_Order( $order_id );
				$customer_email = $order->get_billing_email();
			}

			HubWooDealLiteCallbacks::get_instance()->hubwoo_deal_lite_fetching_info( $order_id, 'create', $customer_email );
		}
	}
}
