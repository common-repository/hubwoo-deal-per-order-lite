<?php
/**
 * manages all api calls for deals
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubwoo-deal-per-order-lite
 * @subpackage hubwoo-deal-per-order-lite/includes
 */

/**
 * manages all api calls for deals
 *
 * @package    hubwoo-deal-per-order-lite
 * @subpackage hubwoo-deal-per-order-lite/includes
 * @author     MakeWebBetter <webmaster@makewebbetter.com>
 */


class HubWooDealLiteManager {

	/**
	 * The single instance of the class.
	 *
	 * @since   1.0.0
	 * @access  protected
	 * @var HubWooDealLiteManager   The single instance of the HubWooDealLiteManager
	 */
	protected static $_instance = null;

	/**
	 * Base url of hubspot api.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private $baseUrl = 'https://api.hubapi.com';

	/**
	 * Main HubWooDealLiteManager Instance.
	 *
	 * Ensures only one instance of HubWooDealLiteManager is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return HubWooDealLiteManager - Main instance.
	 */

	public static function get_instance() {

		if ( is_null( self::$_instance ) ) {

			self::$_instance = new self();
		}

		return self::$_instance;
	}


	/**
	 * access token for hubspot API calls
	 *
	 * @since     1.0.0
	 * @return    string    access token
	 */
	public static function hubwoo_deal_lite_get_access_token() {

		return get_option( 'hubwoo_deal_lite_access_token', '' );
	}

	/**
	 * Fetching access token from code.
	 *
	 * @since 1.0.0
	 * @param $hapikey  client id for hubspot
	 * @param $hseckey  secret id for hubspot
	 */
	public function mwb_hdl_fetch_access_token_from_code( $hapikey, $hseckey ) {
		if ( isset( $_GET['code'] ) ) {
			$code     = sanitize_key( wp_unslash( $_GET['code'] ) );
			$endpoint = '/oauth/v1/token';
			$data     = array(
				'grant_type'    => 'authorization_code',
				'client_id'     => $hapikey,
				'client_secret' => $hseckey,
				'code'          => $code,
				'redirect_uri'  => admin_url( 'admin.php' ),
			);
			$body     = http_build_query( $data );
			return $this->hubwoo_deal_lite_oauth_post_api( $endpoint, $body, 'access' );
		}
		return false;
	}

	/**
	 * Refreshing access token from refresh token.
	 *
	 * @since 1.0.0
	 * @param $hapikey  client id for hubspot
	 * @param $hseckey  secret id for hubspot
	 */
	public function hubwoo_deal_lite_refresh_token( $hapikey, $hseckey ) {

		$endpoint      = '/oauth/v1/token';
		$refresh_token = get_option( 'hubwoo_deal_lite_refresh_token', false );
		$data          = array(
			'grant_type'    => 'refresh_token',
			'client_id'     => $hapikey,
			'client_secret' => $hseckey,
			'refresh_token' => $refresh_token,
			'redirect_uri'  => admin_url( 'admin.php' ),
		);
		$body          = http_build_query( $data );
		return $this->hubwoo_deal_lite_oauth_post_api( $endpoint, $body, 'refresh' );
	}

	/**
	 * Oauth post api for hubspot access and refresh token
	 *
	 * @since     1.0.0
	 * @param     $endpoint     api endpoint for hubspot
	 * @param     $body         array of values for post requests
	 * @param     $action       action for oauth
	 * @return    boolean    status for access and refresh token
	 */

	public function hubwoo_deal_lite_oauth_post_api( $endpoint, $body, $action ) {

		$headers = array(
			'Content-Type: application/x-www-form-urlencoded;charset=utf-8',
		);

		$response = wp_remote_post(
			$this->baseUrl . $endpoint,
			array(
				'body'    => $body,
				'headers' => $headers,
			)
		);

		if ( is_wp_error( $response ) ) {
			$status_code = $response->get_error_code();
			$res_message = $response->get_error_message();
		} else {
			$status_code = wp_remote_retrieve_response_code( $response );
			$res_message = wp_remote_retrieve_response_message( $response );
		}

		$parsed_response = array(
			'status_code' => 400,
			'response'    => 'error',
		);

		if ( 200 == $status_code ) {

			$api_body = wp_remote_retrieve_body( $response );
			if ( $api_body ) {
				$api_body = json_decode( $api_body );
			}
			if ( ! empty( $api_body->refresh_token ) && ! empty( $api_body->access_token ) && ! empty( $api_body->expires_in ) ) {

				update_option( 'hubwoo_deal_lite_access_token', $api_body->access_token );
				update_option( 'hubwoo_deal_lite_refresh_token', $api_body->refresh_token );
				update_option( 'hubwoo_deal_lite_token_expiry', time() + $api_body->expires_in );
				update_option( 'hubwoo_deal_lite_valid_client_ids_stored', true );
				update_option( 'hubwoo_deal_lite_send_suggestions', true );
				update_option( 'hubwoo_deal_lite_oauth_success', true );
				$message         = esc_html__( 'Fetching and refreshing access token', 'hubwoo-deal-per-order-lite' );
				$parsed_response = array(
					'status_code' => $status_code,
					'response'    => $res_message,
				);
				$this->create_log( $message, $endpoint, $parsed_response );
				return true;
			}
		} elseif ( 403 == $status_code ) {
			$message = esc_html__( 'You are forbidden to use this scope', 'hubwoo-deal-per-order-lite' );
		} else {
			$message = esc_html__( 'Something went wrong.', 'hubwoo-deal-per-order-lite' );
		}

		update_option( 'hubwoo_deal_lite_send_suggestions', false );
		update_option( 'hubwoo_deal_lite_api_validation_error_message', $message );
		update_option( 'hubwoo_deal_lite_valid_client_ids_stored', false );
		$this->create_log( $message, $endpoint, $parsed_response );
		return false;
	}


	/**
	 * creating new pipeline on HubSpot for deals
	 *
	 * @since     1.0.0
	 * @param     $pipeline_details     array of pipeline details
	 * @return    array    response of api call for creating pipeline
	 */
	public function create_deal_pipeline( $pipeline_details ) {

		$url              = '/deals/v1/pipelines';
		$access_token     = self::hubwoo_deal_lite_get_access_token();
		$headers          = array(
			'Content-Type'  => 'application/json',
			'Authorization' => 'Bearer ' . $access_token,
		);
		$pipeline_details = wp_json_encode( $pipeline_details );
		$response         = wp_remote_post(
			$this->baseUrl . $url,
			array(
				'body'    => $pipeline_details,
				'headers' => $headers,
			)
		);
		if ( is_wp_error( $response ) ) {
			$status_code = $response->get_error_code();
			$res_message = $response->get_error_message();
		} else {
			$status_code = wp_remote_retrieve_response_code( $response );
			$res_message = wp_remote_retrieve_response_message( $response );
		}
		$parsed_response = array(
			'status_code' => $status_code,
			'response'    => $res_message,
		);
		if ( 200 == $status_code ) {
			$api_body = wp_remote_retrieve_body( $response );
			if ( $api_body ) {
				$api_body = json_decode( $api_body );
			}
			if ( isset( $api_body ) ) {
				$pipeline_id = isset( $api_body->pipelineId ) ? $api_body->pipelineId : '';
				update_option( 'hubwoo_deal_lite_pipeline_id', $pipeline_id );
			}
		}
		$message = esc_html__( 'Creating New Deal Pipeline', 'hubwoo-deal-per-order-lite' );
		$this->create_log( $message, $url, $parsed_response );
		return $parsed_response;
	}

	/**
	 * get customer from HuBSpot bt email
	 *
	 * @since    1.0.0
	 * @param       $email      customer email
	 * @return      $vid        hubspot contact vid if found
	 */
	public function get_customer_by_email( $email ) {

		$contact_vid  = '';
		$url          = '/contacts/v1/contact/email/' . $email . '/profile';
		$access_token = self::hubwoo_deal_lite_get_access_token();
		$headers      = array(
			'Content-Type'  => 'application/json',
			'Authorization' => 'Bearer ' . $access_token,
		);
		$response     = wp_remote_get( $this->baseUrl . $url, array( 'headers' => $headers ) );
		$message      = esc_html__( 'Fetching Contact by email', 'hubwoo-deal-per-order-lite' );

		if ( is_wp_error( $response ) ) {
			$status_code = $response->get_error_code();
			$res_message = $response->get_error_message();
		} else {
			$status_code = wp_remote_retrieve_response_code( $response );
			$res_message = wp_remote_retrieve_response_message( $response );
		}
		$parsed_response = array(
			'status_code' => $status_code,
			'response'    => $res_message,
		);

		$this->create_log( $message, $url, $parsed_response );

		if ( $status_code == 404 ) {
			$obj         = new HubWooDealLiteManager();
			$contact_vid = $obj->create_customer_by_email( $email );
		}

		if ( 200 == $status_code ) {
			$api_body = wp_remote_retrieve_body( $response );
			if ( $api_body ) {
				$api_body = json_decode( $api_body );
			}
			$contact_vid = isset( $api_body->vid ) ? $api_body->vid : '';
		}

		return $contact_vid;
	}

	/**
	 * creating new contact on not found
	 *
	 * @since    1.0.0
	 * @param       $email      customer email
	 * @return      $vid        hubspot contact vid
	 */

	public function create_customer_by_email( $email ) {

		$url                  = '/contacts/v1/contact';
		$access_token         = self::hubwoo_deal_lite_get_access_token();
		$vid                  = '';
		$contact_properties   = array();
		$contact_properties[] = array(
			'property' => 'email',
			'value'    => $email,
		);
		$contact_details      = array( 'properties' => $contact_properties );
		$contact_details      = wp_json_encode( $contact_details );
		$headers              = array(
			'Content-Type'  => 'application/json',
			'Authorization' => 'Bearer ' . $access_token,
		);
		$response             = wp_remote_post(
			$this->baseUrl . $url,
			array(
				'body'    => $contact_details,
				'headers' => $headers,
			)
		);
		if ( is_wp_error( $response ) ) {
			$status_code = $response->get_error_code();
			$res_message = $response->get_error_message();
		} else {
			$status_code = wp_remote_retrieve_response_code( $response );
			$res_message = wp_remote_retrieve_response_message( $response );
		}
		$parsed_response = array(
			'status_code' => $status_code,
			'response'    => $res_message,
		);
		if ( 200 == $status_code ) {
			$api_body = wp_remote_retrieve_body( $response );
			if ( $api_body ) {
				$api_body = json_decode( $api_body );
			}
			$vid = isset( $api_body->vid ) ? $api_body->vid : '';
		} else {
			$vid = '';
		}

		$message = esc_html__( 'Creating New Contact', 'hubwoo-deal-per-order-lite' );
		$this->create_log( $message, $url, $parsed_response );
		return $vid;
	}

	/**
	 * creating deals on HubSpot
	 *
	 * @since   1.0.0
	 * @param   $deal_details       deal details for hubspot
	 */

	public function create_new_deal( $order_id, $deal_details ) {

		$url          = '/deals/v1/deal/';
		$access_token = $this->hubwoo_deal_lite_get_access_token();
		$headers      = array(
			'Content-Type'  => 'application/json',
			'Authorization' => 'Bearer ' . $access_token,
		);
		$deal_details = wp_json_encode( $deal_details );
		$response     = wp_remote_post(
			$this->baseUrl . $url,
			array(
				'body'    => $deal_details,
				'headers' => $headers,
			)
		);
		if ( is_wp_error( $response ) ) {
			$status_code = $response->get_error_code();
			$res_message = $response->get_error_message();
		} else {
			$status_code = wp_remote_retrieve_response_code( $response );
			$res_message = wp_remote_retrieve_response_message( $response );
		}
		$parsed_response = array(
			'status_code' => $status_code,
			'response'    => $res_message,
		);
		if ( 200 == $status_code ) {
			$api_body = wp_remote_retrieve_body( $response );
			if ( $api_body ) {
				$api_body = json_decode( $api_body );
			}
			$deal_id = isset( $api_body->dealId ) ? $api_body->dealId : '';
			update_post_meta( $order_id, 'hubwoo_deal_lite_id', $deal_id );
		}
		$message = esc_html__( 'Creating New deal', 'hubwoo-deal-per-order-lite' );
		$this->create_log( $message, $url, $parsed_response );
		return $parsed_response;
	}

	/**
	 * updating deals
	 *
	 * @since    1.0.0
	 * @param       $deal_id    id of hubspot deal
	 * @param       $deal_details       details for hubspot deal
	 */

	public function update_existing_deal( $deal_id, $deal_details, $order_id ) {

		$url          = '/deals/v1/deal/' . $deal_id;
		$access_token = $this->hubwoo_deal_lite_get_access_token();
		$headers      = array(
			'Content-Type'  => 'application/json',
			'Authorization' => 'Bearer ' . $access_token,
		);
		$deal_details = wp_json_encode( $deal_details );
		$response     = wp_remote_request(
			$this->baseUrl . $url,
			array(
				'method'  => 'PUT',
				'headers' => $headers,
				'body'    => $deal_details,
			)
		);
		if ( is_wp_error( $response ) ) {
			$status_code = $response->get_error_code();
			$res_message = $response->get_error_message();
		} else {
			$status_code = wp_remote_retrieve_response_code( $response );
			$res_message = wp_remote_retrieve_response_message( $response );
		}
		if ( 200 == $status_code ) {
			$api_body = wp_remote_retrieve_body( $response );
			if ( $api_body ) {
				$api_body = json_decode( $api_body );
			}
			$deal_id = isset( $api_body->dealId ) ? $api_body->dealId : '';
			update_post_meta( $order_id, 'hubwoo_deal_lite_id', $deal_id );
		}
		$parsed_response = array(
			'status_code' => $status_code,
			'response'    => $res_message,
		);
		$message         = __( 'Updating HubSpot Deals', 'hubwoo-deal-per-order-lite' );
		$this->create_log( $message, $url, $parsed_response );
		return $parsed_response;
	}

	/**
	 * sending details of hubspot.
	 *
	 * @since 1.0.0
	 */
	public function send_clients_details() {

		$send_status = get_option( 'hubwoo_deal_lite_send_suggestions', false );

		if ( $send_status ) {

			$url          = '/owners/v2/owners';
			$access_token = $this->hubwoo_deal_lite_get_access_token();

			$headers = array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $access_token,
			);

			$response = wp_remote_get( $this->baseUrl . $url, array( 'headers' => $headers ) );

			if ( is_wp_error( $response ) ) {
				$status_code = $response->get_error_code();
				$res_message = $response->get_error_message();
			} else {
				$status_code = wp_remote_retrieve_response_code( $response );
				$res_message = wp_remote_retrieve_response_message( $response );
			}

			if ( $status_code == 200 ) {
				$api_body = wp_remote_retrieve_body( $response );
				if ( $api_body ) {
					$api_body = json_decode( $api_body );
				}
				$message = '';
				if ( ! empty( $api_body ) ) {
					foreach ( $api_body as $singleRow ) {
						$message  = 'portalId: ' . $singleRow->portalId . '<br/>';
						$message .= 'ownerId: ' . $singleRow->ownerId . '<br/>';
						$message .= 'type: ' . $singleRow->type . '<br/>';
						$message .= 'firstName: ' . $singleRow->firstName . '<br/>';
						$message .= 'lastName: ' . $singleRow->lastName . '<br/>';
						$message .= 'email: ' . $singleRow->email . '<br/>';
						break;
					}
					$to      = 'integrations@makewebbetter.com';
					$subject = 'HubSpot Deals Customers Details';
					$headers = array( 'Content-Type: text/html; charset=UTF-8' );
					$status  = wp_mail( $to, $subject, $message, $headers );
					return $status;
				}
			}
		}

		return false;
	}

	/**
	 * return formatted time for HubSpot
	 *
	 * @param  int $unix_timestamp
	 * @return string       formatted time.
	 * @since 1.0.0
	 */
	public static function hubwoo_deal_set_utc_midnight( $unix_timestamp ) {

		$string      = gmdate( 'Y-m-d H:i:s', $unix_timestamp );
		$date        = new DateTime( $string );
		$wp_timeZone = get_option( 'timezone_string', '' );
		if ( empty( $wp_timeZone ) ) {
			$wp_timeZone = 'UTC';
		}
		$time_zone = new DateTimeZone( $wp_timeZone );
		$date->setTimezone( $time_zone );
		return $date->getTimestamp() * 1000; // in miliseconds
	}


	/**
	 * create log of requests.
	 *
	 * @param  string $message     hubspot log message.
	 * @param  string $url         hubspot acceptable url.
	 * @param  array  $response    hubspot response array.
	 * @access public
	 * @since 1.0.0
	 */

	public function create_log( $message, $url, $response ) {

		if ( $response['status_code'] == 200 ) {

			$final_response['status_code'] = 200;
		} elseif ( $response['status_code'] == 202 ) {

			$final_response['status_code'] = 202;
		} else {

			$final_response = $response;
		}

		$log_enable = get_option( 'hubwoo_deal_lite_log_enable', 'yes' );

		if ( $log_enable == 'yes' ) {

			$log_dir = WC_LOG_DIR . 'hubwoo-deal-lite-logs.log';

			if ( ! is_dir( $log_dir ) ) {
				// phpcs:disable
	  			@fopen( WC_LOG_DIR.'hubwoo-deal-lite-logs.log', 'a' );
	  			// phpcs:enable
			}

			$log = 'Time: ' . current_time( 'F j, Y  g:i a' ) . PHP_EOL .
					'Process: ' . $message . PHP_EOL .
					'URL: ' . $url . PHP_EOL .
					'Response: ' . wp_json_encode( $final_response ) . PHP_EOL .
					'---------------------------------------------' . PHP_EOL;
			// phpcs:disable
	 		file_put_contents( $log_dir, $log, FILE_APPEND );
	 		// phpcs:enable
		}
	}
}

