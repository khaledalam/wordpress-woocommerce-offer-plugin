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


    public  function crb_attach_offers_options()
    {
        $offersProducts = OfferHelper::getOfferProducts();

        $offersProductsOptions = ['NA' => 'N/A'];
        foreach ($offersProducts as $product) {
            $offersProductsOptions[$product->ID] = $product->post_name;
        }

        $categories = get_terms([
            'orderby'      => 'name',
            'pad_counts'   => false,
            'hierarchical' => 1,
            'hide_empty'   => true,
        ]);
        foreach ($categories as $category) {
            if ($category->taxonomy === 'product_cat') {

                $categoryOffersOptions[] = Field::make(
                    'select',
                    'offers_key_cat_term_id_product_id_' . $category->term_id,
                    '"' . $category->name . '" category will be eligible for:'
                )->add_options($offersProductsOptions);
            }
        }


        Container::make( 'theme_options', __( 'Offers', 'crb' ) )
            ->set_icon('dashicons-carrot')
            ->add_fields($categoryOffersOptions);
    }


    /**
     * According to requirements: The client wants to restrict this offer to one category
     *
     * @TODO Handle case if set same offer for multiple categories.
     */
    public function add_offer_cat_option() {

    }

    public function crb_load() {
        Carbon_Fields::boot();
    }
}
