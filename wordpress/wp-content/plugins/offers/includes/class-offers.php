<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/khaledalam
 * @since      1.0.0
 *
 * @package    Offers
 * @subpackage Offers/includes
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
 * @package    Offers
 * @subpackage Offers/includes
 * @author     Khaled Alam <khaledalam.net@gmail.com>
 */
class Offers {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Offers_Loader    $loader    Maintains and registers all hooks for the plugin.
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
		if ( defined( 'OFFERS_VERSION' ) ) {
			$this->version = OFFERS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'offers';

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
	 * - Offers_Loader. Orchestrates the hooks of the plugin.
	 * - Offers_i18n. Defines internationalization functionality.
	 * - Offers_Admin. Defines all hooks for the admin area.
	 * - Offers_Public. Defines all hooks for the public side of the site.
     * - OfferHelper. Defines some helper functions that helps for "offer" products.
     * - autoload. Load all installed composer packages. e.g.(htmlburger/carbon-fields).
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-offers-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-offers-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-offers-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-offers-public.php';

        /**
         * Add a helper class.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'utils/OfferHelper.php';

        /**
         * Load all other external composer packages. (htmlburger/carbon-fields)
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/autoload.php';

		$this->loader = new Offers_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Offers_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Offers_i18n();

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

		$plugin_admin = new Offers_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

        $this->loader->add_action( 'carbon_fields_register_fields', $plugin_admin, 'crb_attach_offers_options');
        $this->loader->add_action( 'after_setup_theme', $plugin_admin, 'crb_load' );

        $this->loader->add_filter( 'woocommerce_get_sections_products', $plugin_admin, 'products_offers_add_section', 10, 3);
        $this->loader->add_filter( 'woocommerce_get_settings_products', $plugin_admin, 'products_offers_all_settings', 10, 2);

    }

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks()
    {
		$plugin_public = new Offers_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

        $this->loader->add_action( 'woocommerce_add_cart_item_data', $plugin_public, 'woocommerce_add_cart_item_data', 10, 2 );
        $this->loader->add_action( 'woocommerce_update_cart_action_cart_updated', $plugin_public, 'woocommerce_update_cart_action_cart_updated');
        $this->loader->add_action( 'woocommerce_remove_cart_item', $plugin_public, 'woocommerce_remove_cart_item', 10, 2);
        $this->loader->add_action( 'wp', $plugin_public, 'action_wp');

        $this->loader->add_filter( 'woocommerce_cart_item_remove_link', $plugin_public, 'woocommerce_cart_item_remove_link', 20, 2);
        $this->loader->add_filter( 'woocommerce_cart_item_quantity', $plugin_public, 'woocommerce_cart_item_quantity', 10, 3);
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
	 * @return    Offers_Loader    Orchestrates the hooks of the plugin.
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
}
