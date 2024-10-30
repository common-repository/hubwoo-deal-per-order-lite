<?php

/**
 * manages all call for collecting info for deals
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubwoo-deal-per-order-lite
 * @subpackage hubwoo-deal-per-order-lite/includes
 */

class HubWooDealLiteCallbacks {

	/**
	 * The single instance of the class.
	 *
	 * @since   1.0.0
	 * @access  protected
	 * @var HubWooDealLiteCallbacks     The single instance of the HubWooDealLiteCallbacks
	 */
	protected static $_instance = null;

	/**
	 * Main HubWooDealLiteCallbacks Instance.
	 *
	 * Ensures only one instance of HubWooDealLiteCallbacks is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return HubWooDealLiteCallbacks - Main instance.
	 */

	public static function get_instance() {

		if ( is_null( self::$_instance ) ) {

			self::$_instance = new self();
		}

		return self::$_instance;
	}


	/**
	 * fetching order details to convert them into deals
	 *
	 * @since    1.0.0
	 */

	public function hubwoo_deal_lite_fetching_info( $order_id, $action, $email = '' ) {

		$response = '';

		if ( ! empty( $order_id ) ) {

			$hubwoo_deal_order = new WC_Order( $order_id );

			if ( ! empty( $hubwoo_deal_order ) || ! is_wp_error( $hubwoo_deal_order ) ) {

				$status = $hubwoo_deal_order->get_status();

				$order_date = get_post_time( 'U', true, $order_id );

				if ( $status == 'refunded' ) {

					$status = 'cancelled';
				}

				if ( $status !== 'processing' && $status !== 'completed' ) {

					$order_date = get_post_time( 'U', true, $order_id ) + ( 7 * 24 * 60 * 60 );
				}

				$order_date = HubWooDealLiteManager::hubwoo_deal_set_utc_midnight( $order_date );

				$total = $hubwoo_deal_order->get_total();

				$customer_email = get_post_meta( $order_id, '_billing_email', true );
			}

			if ( $action == 'create' && ! empty( $email ) ) {

				$contact_vid = self::hubwoo_deals_get_contact_by_email( $email );
				$response    = self::hubwoo_prepare_deal( $order_id, $total, $order_date, $status, $contact_vid );
			} elseif ( $action == 'update' ) {

				$deal_id = get_post_meta( $order_id, 'hubwoo_deal_lite_id', true );

				if ( ! empty( $deal_id ) ) {

					$response = self::hubwoo_prepare_deal( $order_id, $total, $order_date, $status );
				}
			}

			return $response;
		}
	}

	/**
	 * preparing deals for creation
	 *
	 * @since    1.0.0
	 */

	public static function hubwoo_prepare_deal( $id, $total, $date, $status, $contact_vid = '' ) {

		$properties = self::hubwoo_fetch_deal_properties_structure();

		$pipeline_id = Hubspot_Deal_Per_Order_Lite::hubwoo_deals_get_pipeline_id();

		foreach ( $properties as &$single_property ) {

			if ( $single_property['name'] == 'dealname' ) {

				$single_property['value'] = '#' . $id;
			} elseif ( $single_property['name'] == 'closedate' ) {

				$single_property['value'] = $date;
			} elseif ( $single_property['name'] == 'amount' ) {

				$single_property['value'] = $total;
			} elseif ( $single_property['name'] == 'dealstage' ) {

				$single_property['value'] = 'wc-' . $status . '-order';
			} elseif ( $single_property['name'] == 'pipeline' ) {

				$single_property['value'] = $pipeline_id;
			}
		}

		$deal_id = get_post_meta( $id, 'hubwoo_deal_lite_id', true );

		if ( empty( $deal_id ) ) {

			$vids            = array();
			$vids[]          = $contact_vid;
			$associated_vids = array( 'associatedVids' => $vids );
			$deal            = array(
				'properties'   => $properties,
				'associations' => $associated_vids,
			);
			$response        = self::hubwoo_create_deals_on_orders( $id, $deal );

			if ( isset( $response['status_code'] ) && $response['status_code'] == 200 ) {

				if ( isset( $response['response'] ) ) {

					$store_response = json_decode( $response['response'] );

					if ( isset( $store_response->dealId ) ) {

						update_post_meta( $id, 'hubwoo_deal_lite_id', $store_response->dealId );
					}
				}
			}
		} else {

			$properties = array( 'properties' => $properties );
			$response   = self::hubwoo_update_deals( $properties, $deal_id, $id );

			if ( isset( $response['status_code'] ) && $response['status_code'] == 200 ) {
				if ( isset( $response['response'] ) ) {
					$store_response = json_decode( $response['response'] );

					if ( isset( $store_response->dealId ) ) {
						update_post_meta( $id, 'hubwoo_deal_updated', $store_response->dealId );
					}
				}
			}
		}

		return $response;
	}


	/**
	 * preparing deals properties structure
	 *
	 * @since    1.0.0
	 */

	public static function hubwoo_fetch_deal_properties_structure() {

		$properties   = array();
		$properties[] = array(
			'name'  => 'dealname',
			'value' => '',
		);
		$properties[] = array(
			'name'  => 'dealstage',
			'value' => '',
		);
		$properties[] = array(
			'name'  => 'pipeline',
			'value' => '',
		);
		$properties[] = array(
			'name'  => 'closedate',
			'value' => '',
		);
		$properties[] = array(
			'name'  => 'amount',
			'value' => '',
		);

		return $properties;
	}

	/**
	 * checking contacts on HubSpot by email
	 *
	 * @since    1.0.0
	 */

	public static function hubwoo_deals_get_contact_by_email( $email ) {

		$contact_vid = '';

		if ( Hubspot_Deal_Per_Order_Lite::is_valid_client_ids_stored() ) {

			$flag = true;

			if ( Hubspot_Deal_Per_Order_Lite::is_access_token_expired() ) {

				$hapikey = HUBWOO_DEAL_LITE_CLIENTID;
				$hseckey = HUBWOO_DEAL_LITE_SECRETID;
				$status  = HubWooDealLiteManager::get_instance()->hubwoo_deal_lite_refresh_token( $hapikey, $hseckey );

				if ( ! $status ) {
					$flag = false;
				}
			}

			if ( $flag ) {

				$deals_manager = new HubWooDealLiteManager();
				$contact_vid   = $deals_manager->get_customer_by_email( $email );
			}
		}

		return $contact_vid;
	}

	/**
	 * new deals on HubSpot
	 *
	 * @since    1.0.0
	 */

	public static function hubwoo_create_deals_on_orders( $order_id, $deal_details ) {

		if ( Hubspot_Deal_Per_Order_Lite::is_valid_client_ids_stored() ) {

			$flag = true;

			if ( Hubspot_Deal_Per_Order_Lite::is_access_token_expired() ) {

				$hapikey = HUBWOO_DEAL_LITE_CLIENTID;
				$hseckey = HUBWOO_DEAL_LITE_SECRETID;
				$status  = HubWooDealLiteManager::get_instance()->hubwoo_deal_lite_refresh_token( $hapikey, $hseckey );

				if ( ! $status ) {

					$flag = false;
				}
			}

			if ( $flag ) {

				$deals_manager = new HubWooDealLiteManager();
				$response      = $deals_manager->create_new_deal( $order_id, $deal_details );
				return $response;
			}
		}
	}

	/**
	 * updating deals on HubSpot
	 *
	 * @since    1.0.0
	 */

	public static function hubwoo_update_deals( $deal_details, $deal_id, $order_id ) {

		if ( Hubspot_Deal_Per_Order_Lite::is_valid_client_ids_stored() ) {

			$flag = true;

			if ( Hubspot_Deal_Per_Order_Lite::is_access_token_expired() ) {

				$hapikey = HUBWOO_DEAL_LITE_CLIENTID;
				$hseckey = HUBWOO_DEAL_LITE_SECRETID;
				$status  = HubWooDealLiteManager::get_instance()->hubwoo_deal_lite_refresh_token( $hapikey, $hseckey );

				if ( ! $status ) {

					$flag = false;
				}
			}

			if ( $flag ) {

				$deals_manager = new HubWooDealLiteManager();
				$response      = $deals_manager->update_existing_deal( $deal_id, $deal_details, $order_id );
				return $response;
			}
		}
	}
}

