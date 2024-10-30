<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://makewebbetter.com
 * @since      1.0.0
 *
 * @package    hubwoo-deal-per-order-lite
 * @subpackage hubwoo-deal-per-order-lite/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    hubwoo-deal-per-order-lite
 * @subpackage hubwoo-deal-per-order-lite/includes
 * @author     MakeWebBetter <webmaster@makewebbetter.com>
 */
class Hubspot_Deal_Per_Order_Lite {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Hubspot_Deal_Per_Order_Lite_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		if ( defined( 'HUBWOO_DEAL_LITE_VERSION' ) ) {

			$this->version = HUBWOO_DEAL_LITE_VERSION;
		} else {

			$this->version = '1.0.2';
		}

		$this->plugin_name = 'hubwoo-deal-per-order-lite';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Hubspot_Deal_Per_Order_Lite_Loader. Orchestrates the hooks of the plugin.
	 * - Hubspot_Deal_Per_Order_Lite_i18n. Defines internationalization functionality.
	 * - Hubspot_Deal_Per_Order_Lite_Admin. Defines all hooks for the admin area.
	 * - Hubspot_Deal_Per_Order_Lite_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hubspot-deal-per-order-lite-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hubspot-deal-per-order-lite-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-hubspot-deal-per-order-lite-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-hubspot-deal-per-order-lite-public.php';

		/**
		 * The class responsible for defining all actions for connection on HubSpot
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hubwoo-deals-lite-manager.php';

		/**
		 * The class responsible for defining all actions to collect order info
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-hubwoo-deals-lite-callbacks.php';

		$this->loader = new Hubspot_Deal_Per_Order_Lite_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Hubspot_Deal_Per_Order_Lite_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Hubspot_Deal_Per_Order_Lite_i18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Hubspot_Deal_Per_Order_Lite_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'mwb_hdl_redirect_from_hubspot' );
		$this->loader->add_action( 'wp_ajax_hubwoo_deal_lite_get_pipeline', $plugin_admin, 'hubwoo_deal_lite_get_pipeline' );
		$this->loader->add_action( 'wp_ajax_hubwoo_deal_lite_check_oauth_access_token', $plugin_admin, 'hubwoo_deal_lite_check_oauth_access_token' );
		$this->loader->add_action( 'wp_ajax_hubwoo_deal_lite_create_pipeline', $plugin_admin, 'hubwoo_deal_lite_create_pipeline' );
		$this->loader->add_action( 'wp_ajax_hubwoo_deal_lite_accept', $plugin_admin, 'hubwoo_deal_lite_accept' );
		$this->loader->add_action( 'wp_ajax_hubwoo_deal_lite_later', $plugin_admin, 'hubwoo_deal_lite_later' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'hubwoo_deal_lite_privacy_message' );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'hubwoo_deal_lite_notice' );
		$pipeline_id = $this->hubwoo_deals_get_pipeline_id();
		if ( ! empty( $pipeline_id ) && $this->is_pipeline_setup_completed() ) {
			$this->loader->add_action( 'woocommerce_order_status_changed', $plugin_admin, 'hubwoo_deal_lite_update' );
		}
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public      = new Hubspot_Deal_Per_Order_Lite_Public( $this->get_plugin_name(), $this->get_version() );
		$hubwoo_deal_enable = get_option( 'hubwoo_deal_lite_settings_enable', 'yes' );
		$pipeline_id        = $this->hubwoo_deals_get_pipeline_id();
		if ( $this->is_pipeline_setup_completed() && ! empty( $pipeline_id ) ) {
			$this->loader->add_action( 'woocommerce_new_order', $plugin_public, 'hubwoo_deal_lite_new_order', 10, 1 );
		}
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Hubspot_Deal_Per_Order_Lite_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Retrieve the status of pipeline setup
	 *
	 * @since     1.0.0
	 * @return    boolean    pipeline setup status
	 */
	public static function is_pipeline_setup_completed() {

		return get_option( 'hubwoo_deal_lite_pipeline', false );
	}

	/**
	 * Retrieve the status of pipeline setup
	 *
	 * @since     1.0.0
	 */
	public static function hubwoo_deal_lite_notice( $message, $type = 'error' ) {

		$classes = 'notice ';

		switch ( $type ) {

			case 'update':
				$classes .= 'updated';
				break;

			case 'update-nag':
				$classes .= 'update-nag';
				break;
			case 'success':
				$classes .= 'notice-success is-dismissible';
				break;

			default:
				$classes .= 'error';
		}

		$notice  = '<div class="' . esc_attr( $classes ) . '">';
		$notice .= '<p>' . esc_html( $message ) . '</p>';
		$notice .= '</div>';

		echo wp_kses_post( $notice );
	}

	/**
	 * Valid client ids stored for hubspot or not
	 *
	 * @since     1.0.0
	 * @return    boolean    valid client ids status
	 */
	public static function is_valid_client_ids_stored() {

		$hapikey = HUBWOO_DEAL_LITE_CLIENTID;
		$hseckey = HUBWOO_DEAL_LITE_SECRETID;

		if ( $hapikey && $hseckey ) {

			return get_option( 'hubwoo_deal_lite_valid_client_ids_stored', false );
		}

		return false;
	}

	/**
	 * Valid client ids stored for hubspot or not
	 *
	 * @since     1.0.0
	 * @return    boolean    valid client ids status
	 */
	public static function is_oauth_success() {

		return get_option( 'hubwoo_deal_lite_oauth_success', false );
	}

	/**
	 * check access token is expired or not
	 *
	 * @since    1.0.0
	 * @return    boolean    check access toekn is expired
	 */

	public static function is_access_token_expired() {

		$get_expiry = get_option( 'hubwoo_deal_lite_token_expiry', false );

		if ( $get_expiry ) {

			$current_time = time();

			if ( ( $get_expiry > $current_time ) && ( $get_expiry - $current_time ) <= 50 ) {

				return true;
			} elseif ( ( $current_time > $get_expiry ) ) {

				return true;
			}
		}

		return false;
	}

	/**
	 * get hubspot deal pipeline id
	 *
	 * @since    1.0.0
	 * @return  deal pipeline id
	 */

	public static function hubwoo_deals_get_pipeline_id() {

		return get_option( 'hubwoo_deal_lite_pipeline_id', '' );
	}

	/**
	 * get hubspot oauth url for authorization
	 *
	 * @since    1.0.0
	 * @return   hubspot oauth url
	 */
	public static function get_hubspot_oauth_url() {

		$url         = 'https://app.hubspot.com/oauth/authorize';
		$hapikey     = HUBWOO_DEAL_LITE_CLIENTID;
		$hubspot_url = add_query_arg(
			array(
				'client_id'    => $hapikey,
				'scope'        => 'oauth%20contacts',
				'redirect_uri' => admin_url( 'admin.php' ),
			),
			$url
		);

		return $hubspot_url;
	}

	/**
	 * check email sending status
	 *
	 * @since    1.0.0
	 * @return   email sending status
	 */

	public static function is_display_suggestion_popup() {

		$suggest = get_option( 'hubwoo_deal_lite_send_suggestions', false );

		if ( $suggest ) {

			$success = get_option( 'hubwoo_deal_lite_mail_sent', false );

			if ( ! $success ) {

				$later = get_option( 'hubwoo_deal_lite_decide_later', false );

				if ( ! $later ) {

					return true;
				}
			}
		}

		return false;
	}
}
