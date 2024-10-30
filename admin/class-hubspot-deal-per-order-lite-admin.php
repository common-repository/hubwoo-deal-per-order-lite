<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://makewebbetter.com
 * @since      1.0.0
 *
 * @package    hubwoo-deal-per-order-lite
 * @subpackage hubwoo-deal-per-order-lite/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    hubwoo-deal-per-order-lite
 * @subpackage hubwoo-deal-per-order-lite/admin
 * @author     MakeWebBetter <webmaster@makewebbetter.com>
 */
class Hubspot_Deal_Per_Order_Lite_Admin {

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
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->hubwoo_deal_lite_admin_actions();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Hubspot_Deal_Per_Order_Lite_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Hubspot_Deal_Per_Order_Lite_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		$screen = get_current_screen();

		if ( isset( $screen->id ) && 'woocommerce_page_hubwoo-deal-lite' == $screen->id ) {

			wp_enqueue_style( $this->plugin_name . '-style', plugin_dir_url( __FILE__ ) . 'css/hubspot-deal-per-order-lite-admin.css', array(), $this->version, 'all' );
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Hubspot_Deal_Per_Order_Lite_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Hubspot_Deal_Per_Order_Lite_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		$screen = get_current_screen();

		if ( isset( $screen->id ) && 'woocommerce_page_hubwoo-deal-lite' == $screen->id ) {

			wp_enqueue_script( $this->plugin_name . '-script', plugin_dir_url( __FILE__ ) . 'js/hubspot-deal-per-order-lite-admin.js', array( 'jquery' ), $this->version, false );
			wp_localize_script(
				$this->plugin_name . '-script',
				'hubwooi18n',
				array(
					'ajaxUrl'                           => admin_url( 'admin-ajax.php' ),
					'hubwooSecurity'                    => wp_create_nonce( 'hubwoo_security' ),
					'hubwooWentWrong'                   => esc_html__( 'Something went wrong, please try again later! or check logs', 'hubwoo-deal-per-order-lite' ),
					'hubwooDealsPipelineSetupCompleted' => esc_html__( 'Pipeline Setup Completed.', 'hubwoo-deal-per-order-lite' ),
					'hubwooCreatingPipeline'            => esc_html__( 'New Pipeline created for HubSpot Deals', 'hubwoo-deal-per-order-lite' ),
					'hubwooMailFailure'                 => esc_html__( 'Mail not sent', 'hubwoo-deal-per-order-lite' ),
				)
			);
		}
	}

	/**
	 * all admin actions listed here
	 *
	 * @since 1.0.0
	 */
	public function hubwoo_deal_lite_admin_actions() {

		add_action( 'admin_menu', array( &$this, 'hubwoo_deal_lite_submenu' ) );
	}

	/**
	 * add hubspot deals submenu in woocommerce menu.
	 *
	 * @since 1.0.0
	 */
	public function hubwoo_deal_lite_submenu() {

		add_submenu_page( 'woocommerce', esc_html__( 'HubSpot Deals', 'hubwoo-deal-per-order-lite' ), esc_html__( 'HubSpot Deals', 'hubwoo-deal-per-order-lite' ), 'manage_woocommerce', 'hubwoo-deal-lite', array( &$this, 'hubwoo_deal_lite_config' ) );
	}

	/**
	 * adding hubspot deal menu display for admin
	 *
	 * @since 1.0.0
	 */
	public function hubwoo_deal_lite_config() {

		if ( file_exists( HUBWOO_DEAL_LITE_ABSPATH . 'admin/partials/hubspot-deal-per-order-lite-admin-display.php' ) ) {
			include_once HUBWOO_DEAL_LITE_ABSPATH . 'admin/partials/hubspot-deal-per-order-lite-admin-display.php';
		}
	}


	/**
	 * general settings for deals admin page
	 *
	 * @since 1.0.0
	 * @return  array   basic settings for admin
	 */
	public static function hubwoo_deal_lite_settings() {

		$settings = array();

		$log_url = '<a target="_blank" href="' . esc_url( admin_url( 'admin.php' ) . '?page=wc-status&tab=logs' ) . '">' . esc_html__( 'Here', 'hubwoo-deal-per-order-lite' ) . '</a>';

		$settings[] = array(
			'title' => esc_html__( 'Create HubSpot Deal on every New Order', 'hubwoo-deal-per-order-lite' ),
			'id'    => 'hubwoo_deal_lite_settings_title',
			'type'  => 'title',
		);

		$settings[] = array(
			'title'   => esc_html__( 'Enable/Disable', 'hubwoo-deal-per-order-lite' ),
			'id'      => 'hubwoo_deal_lite_settings_enable',
			'desc'    => esc_html__( 'Turn on/off the feature', 'hubwoo-deal-per-order-lite' ),
			'type'    => 'checkbox',
			'default' => 'yes',
		);

		$settings[] = array(
			'title'   => esc_html__( 'Enable/Disable', 'hubwoo-deal-per-order-lite' ),
			'id'      => 'hubwoo_deal_lite_log_enable',
			/* translators: %s: log url */
			'desc'    => sprintf( esc_html__( 'Enable logging of the requests. You can view HubSpot Deals log file from %s', 'hubwoo-deal-per-order-lite' ), $log_url ),
			'type'    => 'checkbox',
			'default' => 'yes',
		);

		$settings[] = array(
			'type' => 'sectionend',
			'id'   => 'hubwoo_deal_lite_settings_end',
		);

		return $settings;
	}

	/**
	 * woocommerce order pipeline for deals
	 *
	 * @since 1.0.0
	 */
	public function hubwoo_deal_lite_get_pipeline() {

		check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );

		$pipeline = array();

		$pipeline[] = array(
			'label'        => esc_html__( 'WooCommerce Order Pipeline', 'hubwoo-deal-per-order-lite' ),
			'displayOrder' => 1,
			'stages'       => array(
				array(
					'label'        => esc_html__( 'Failed', 'hubwoo-deal-per-order-lite' ),
					'displayOrder' => 0,
					'probability'  => 0.1,
					'stageId'      => 'wc-failed-order',
				),
				array(
					'label'        => esc_html__( 'Cancelled', 'hubwoo-deal-per-order-lite' ),
					'displayOrder' => 1,
					'probability'  => 0,
					'stageId'      => 'wc-cancelled-order',
				),
				array(
					'label'        => esc_html__( 'Pending', 'hubwoo-deal-per-order-lite' ),
					'displayOrder' => 2,
					'probability'  => 0.5,
					'stageId'      => 'wc-pending-order',
				),
				array(
					'label'        => esc_html__( 'On-hold', 'hubwoo-deal-per-order-lite' ),
					'displayOrder' => 3,
					'probability'  => 0.2,
					'stageId'      => 'wc-on-hold-order',
				),
				array(
					'label'        => esc_html__( 'Processed', 'hubwoo-deal-per-order-lite' ),
					'displayOrder' => 4,
					'probability'  => 1,
					'stageId'      => 'wc-processing-order',
				),
				array(
					'label'        => esc_html__( 'Shipped', 'hubwoo-deal-per-order-lite' ),
					'displayOrder' => 5,
					'probability'  => 1,
					'stageId'      => 'wc-completed-order',
				),
			),
		);

		$pipeline = apply_filters( 'hubwoo_deal_lite_pipeline', $pipeline );
		echo wp_json_encode( $pipeline );
		wp_die();
	}

	/**
	 * creating woocommerce pipelines for deals
	 *
	 * @since 1.0.0
	 */

	public function hubwoo_deal_lite_create_pipeline() {

		check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );

		if ( isset( $_POST['pipelineDetails'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$pipeline_details = map_deep( wp_unslash( $_POST['pipelineDetails'] ), 'sanitize_text_field' );
			$deals_manager    = new HubWooDealLiteManager();
			$response         = $deals_manager->create_deal_pipeline( $pipeline_details );
			if ( isset( $response['status_code'] ) && $response['status_code'] == 200 ) {
				update_option( 'hubwoo_deal_lite_pipeline', true );
				update_option( 'hubwoo_deal_lite_version', HUBWOO_DEAL_LITE_VERSION );
			}
			echo wp_json_encode( $response );
		}

		wp_die();
	}

	/**
	 * updating woocommerce deals on order status transition
	 *
	 * @since 1.0.0
	 */

	public function hubwoo_deals_update( $order_id ) {

		if ( ! empty( $order_id ) ) {

			HubWooDealLiteCallbacks::get_instance()->hubwoo_deal_lite_fetching_info( $order_id, 'update' );
		}
	}

	/**
	 * Generating access token from code return from HubSpot
	 *
	 * @since    1.0.0
	 */

	public function mwb_hdl_redirect_from_hubspot() {

		if ( isset( $_GET['code'] ) ) {
			$hapikey = HUBWOO_DEAL_LITE_CLIENTID;
			$hseckey = HUBWOO_DEAL_LITE_SECRETID;
			if ( $hapikey && $hseckey ) {

				if ( ! Hubspot_Deal_Per_Order_Lite::is_valid_client_ids_stored() ) {

					$response = HubWooDealLiteManager::get_instance()->mwb_hdl_fetch_access_token_from_code( $hapikey, $hseckey );
				}

				$oauth_success = get_option( 'hubwoo_deal_lite_oauth_success', false );

				if ( ! isset( $oauth_success ) || ! $oauth_success ) {

					$response = HubWooDealLiteManager::get_instance()->mwb_hdl_fetch_access_token_from_code( $hapikey, $hseckey );
				}

				wp_safe_redirect( admin_url() . 'admin.php?page=hubwoo-deal-lite' );
				exit();
			}
		}
	}

	/**
	 * checking oauth access token validity
	 *
	 * @since    1.0.0
	 */

	public function hubwoo_deal_lite_check_oauth_access_token() {

		check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );

		$response = array(
			'status'  => true,
			'message' => esc_html__( 'Success', 'hubwoo-deal-per-order-lite' ),
		);

		if ( Hubspot_Deal_Per_Order_Lite::is_access_token_expired() ) {

			$hapikey = HUBWOO_DEAL_LITE_CLIENTID;
			$hseckey = HUBWOO_DEAL_LITE_SECRETID;

			$status = HubWooDealLiteManager::get_instance()->hubwoo_deal_lite_refresh_token( $hapikey, $hseckey );

			if ( ! $status ) {

				$response['status']  = false;
				$response['message'] = esc_html__( 'Something went wrong, please check your API Keys', 'hubwoo-deal-per-order-lite' );
			}
		}

		echo wp_json_encode( $response );

		wp_die();
	}


	/**
	 * updating deal on HubSpot as order status is changed
	 *
	 * @since    1.0.0
	 * @param    int $order_id       Order ID
	 */

	public function hubwoo_deal_lite_update( $order_id ) {

		if ( ! empty( $order_id ) ) {

			HubWooDealLiteCallbacks::get_instance()->hubwoo_deal_lite_fetching_info( $order_id, 'update' );
		}
	}

	/**
	 * privacy policy for GDPR
	 *
	 * @since    1.0.0
	 */

	public function hubwoo_deal_lite_privacy_message() {

		if ( function_exists( 'wp_add_privacy_policy_content' ) ) {

			$content  = '<p>' . esc_html__( 'We use your email to send and track your orders on HubSpot as deals.', 'hubwoo-deal-per-order-lite' ) . '</p>';
			$content .= '<p>' . esc_html__( 'HubSpot is inbound marketing and sales software that helps companies attract visitors, convert leads, and close customers.', 'hubwoo-deal-per-order-lite' ) . '</p>';
			$content .= '<p>' . esc_html__( 'Please see the ', 'hubwoo-deal-per-order-lite' ) . '<a href="https://www.hubspot.com/data-privacy/gdpr" target="_blank" >' . esc_html__( 'HubSpot Data Privacy', 'hubwoo-deal-per-order-lite' ) . '</a>' . esc_html__( ' for more details.', 'hubwoo-deal-per-order-lite' ) . '</p>';

			if ( $content ) {

				wp_add_privacy_policy_content( esc_html__( 'Deal Per Order for HubSpot', 'hubwoo-deal-per-order-lite' ), $content );
			}
		}
	}

	/**
	 * accept the plugin development mail
	 *
	 * @since    1.0.0
	 */

	public function hubwoo_deal_lite_accept() {

		check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );

		$status = HubWooDealLiteManager::get_instance()->send_clients_details();

		if ( $status ) {

			update_option( 'hubwoo_deal_lite_mail_sent', true );
			$status = true;
		} else {

			update_option( 'hubwoo_deal_lite_decide_later', true );
			$status = false;
		}

		echo wp_json_encode( $status );
		wp_die();
	}

	/**
	 * send mail later
	 *
	 * @since    1.0.0
	 */

	public function hubwoo_deal_lite_later() {

		check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );
		update_option( 'hubwoo_deal_lite_decide_later', true );
		wp_die();
	}

	/**
	 * admin notice on dashboard
	 *
	 * @since    1.0.0
	 */

	public function hubwoo_deal_lite_notice() {

		$screen = get_current_screen();

		if ( isset( $screen->id ) && $screen->id == 'woocommerce_page_hubwoo-deal-lite' ) {

			$suggest = get_option( 'hubwoo_deal_lite_send_suggestions', false );

			if ( $suggest ) {

				$success = get_option( 'hubwoo_deal_lite_mail_sent', false );

				if ( ! $success ) {

					$later = get_option( 'hubwoo_deal_lite_decide_later', false );

					if ( $later ) {
						?>
							<div class="update-nag notice-success is-dismissible">
								<p>
									<?php echo esc_html__( 'Please support the plugin development by sending us tracking data. It will help us to track the overall performance. Click ', 'hubwoo-deal-per-order-lite' ); ?>
									<a href="javascript:void(0)" class="hubwoo_deal_lite_tracking"><?php esc_html_e( 'Here', 'hubwoo-deal-per-order-lite' ); ?></a>
								</p>
							</div>
						<?php
					}
				}
			}
		}
	}
}
