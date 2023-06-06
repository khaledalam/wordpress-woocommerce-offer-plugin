<?php

use Carbon_Fields\Carbon_Fields;
use Carbon_Fields\Container;
use Carbon_Fields\Field;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/khaledalam
 * @since      1.0.0
 *
 * @package    Offers
 * @subpackage Offers/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Offers
 * @subpackage Offers/admin
 * @author     Khaled Alam <khaledalam.net@gmail.com>
 */
class Offers_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

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
		 * defined in Offers_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Offers_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/offers-admin.css', array(), $this->version, 'all' );

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
		 * defined in Offers_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Offers_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/offers-admin.js', array( 'jquery' ), $this->version, false );
	}


    /**
     * Function for `admin_init` action-hook.
     *
     * Allow shop_manager to access offer plugin.
     *
     * @return WP_Role|null
     */
    public function shop_manager_cap(): ?WP_Role
    {
        $role = get_role( 'shop_manager' );

        // Add the new capability
        $role->add_cap( 'manage_options' );
        return $role;
    }

    /**
     * Function for `carbon_fields_register_fields` action-hook.
     *
     * Register carbon fields.
     */
    public function crb_attach_offers_options(): void
    {
        // @TODO: Use it for other purpose.

        Container::make( 'theme_options', __( 'Offers', 'crb' ) )
            ->set_icon('dashicons-smiley');
    }

    /**
     * Function for `after_setup_theme` action-hook.
     *
     * Load Carbon Fields.
     */
    public function crb_load(): void
    {
        Carbon_Fields::boot();
    }

    /**
     * Function for `woocommerce_get_sections_products` filter-hook.
     *
     * Add "Offers Products" section in products tab.
     *
     * @param $sections
     * @return mixed
     */
    public function products_offers_add_section( $sections ): mixed
    {
        $sections['products_offers'] = __( 'Offers Products', 'text-domain' );
        return $sections;
    }

    /**
     * Function for `woocommerce_get_settings_products` filter-hook.
     *
     * @param $settings
     * @param $current_section
     * @return array|mixed
     */
    public function products_offers_all_settings( $settings, $current_section ): mixed
    {
        $categories = get_terms([
            'orderby'      => 'name',
            'pad_counts'   => false,
            'hierarchical' => 1,
            'hide_empty'   => true,
        ]);
        $categories = array_filter($categories, static function($category): bool {
            return $category->taxonomy === 'product_cat';
        });
        $categoryOffersOptions = [];
        foreach ($categories as $category) {
            if ($category->taxonomy === 'product_cat') {
                $categoryOffersOptions[$category->term_id] = $category->name;
            }
        }

        /**
         * Check if the current section is what we want
         **/
        if ( $current_section === 'products_offers' ) {
            $settings_slider = [];
            // Add Title to the Settings
            $settings_slider[] = [
                'name' => __( 'Offers Products Settings', 'text-domain' ),
                'type' => 'title',
                'desc' => __( 'The following options are used to configure products offers.', 'text-domain' ),
                'id' => 'products_offers_title'
            ];

            // Add text field option: product offer ID
            $settings_slider[] = [
                'name'     => __( 'Product ID', 'text-domain' ),
                'desc_tip' => __( 'This will set the product id as an offer', 'text-domain' ),
                'id'       => 'product_offer_id',
                'type'     => 'text',
                'desc'     => __( 'Enter Product ID that you want to set it as an offer!', 'text-domain' ),
            ];

            // Add text field option: Offer -> apply to category_term_id
            $settings_slider[] = [
                'name'     => __( 'Apply Offer To Category', 'text-domain' ),
                'desc_tip' => __( 'This will set the product id as an offer', 'text-domain' ),
                'id'       => 'product_offer_cat_term_id',
                'type'     => 'select',
                'desc'     => __( 'Select Category that offer will apply to it!', 'text-domain' ),
                'options' => $categoryOffersOptions
            ];

            $settings_slider[] = [ 'type' => 'sectionend', 'id' => 'product_offer' ];
            return $settings_slider;
        }

        return $settings;
    }

}
