<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/khaledalam
 * @since      1.0.0
 *
 * @package    Offers
 * @subpackage Offers/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Offers
 * @subpackage Offers/public
 * @author     Khaled Alam <khaledalam.net@gmail.com>
 */
class Offers_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/offers-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/offers-public.js', array( 'jquery' ), $this->version, false );

	}

    /**
     * Function for `woocommerce_add_cart_item_data` action-hook.
     *
     * Trigger when add product to cart from /shop page or single-product page.
     *
     * Logic:
     * 1) Check if product's category is eligible to the "offer" or not,
     * if product's category is eligible then add the "offer"(product)
     * with the same added original product quantity to the cart.
     *
     *
     * @param array $cart_item_data Array of cart item data being added to the cart.
     * @param $product_id
     * @return array
     * @throws Exception
     */
    public function woocommerce_add_cart_item_data(array $cart_item_data, $product_id): array
    {
        $productQuantity = empty($_POST['quantity']) ? 1 : wc_stock_amount($_POST['quantity']);

        // If eligible for "offer" add the offer product to the cart X amount of times.
        if ($offerProductId = OfferHelper::productEligibleForOffer($product_id, true)) {
            WC()->cart->add_to_cart($offerProductId, $productQuantity);
        }

        return $cart_item_data;
    }

    /**
     * Function for `woocommerce_update_cart_action_cart_updated` action-hook.
     *
     * Handle change quantity of products in cart.
     *
     * Logic:
     * 1) Iterate over product which are not an "offer" products,
     * and check if the product is eligible for "offer",
     * if so mark the quantity of this "offer" product.
     *
     * 2) Update "offer" products quantities using the mark map array.
     *
     * 3) if there are "offer" products marked and are not existed in the cart content, add them.
     *
     *
     * @return array
     * @throws Exception
     */
    public function woocommerce_update_cart_action_cart_updated(): array
    {
        $cart = WC()->cart->cart_contents;

        $offerProductIdToQuantityMap = [];

        foreach($cart as $cartItemKey => $values) {

            $cartItem = $cart[$cartItemKey];

            // if product is not an "offer" product
            if (!OfferHelper::isProductOffer($cartItem['product_id'])) {

                // If eligible for "offer" add the offer product to the cart X amount of times.
                if ($offerProductId = OfferHelper::productEligibleForOffer($cartItem['product_id'], true)) {

                    if (!isset($offerProductIdToQuantityMap[$offerProductId])) {
                        $offerProductIdToQuantityMap[$offerProductId] = 0;
                    }

                    $offerProductIdToQuantityMap[$offerProductId] += $cartItem['quantity'];
                }
            }
        }

        foreach($cart as $cartItemKey => $values) {

            $cartItem = $cart[$cartItemKey];

            // if product is an "offer" product
            if (OfferHelper::isProductOffer($cartItem['product_id'])) {

                WC()->cart->set_quantity($cartItemKey, $offerProductIdToQuantityMap[$cartItem['product_id']]);

                // for case update quantity of "offer" product to 0
                // supports woocommerce_cart_item_quantity from backend side.
                unset($offerProductIdToQuantityMap[$cartItem['product_id']]);
            }
        }

        // for case update quantity of "offer" product to 0
        // supports woocommerce_cart_item_quantity from backend side.
        foreach ($offerProductIdToQuantityMap as $productId => $quantity) {
            WC()->cart->add_to_cart($productId, $quantity);
        }

        return $cart;
    }

    /**
     * Function for `woocommerce_remove_cart_item` action-hook.
     *
     * Handle removing items from cart.
     *
     * @param $cart_item_key
     * @param $cart
     * @return array
     * @throws Exception
     */
    public function woocommerce_remove_cart_item($cart_item_key, $cart)
    {
        $productId = $cart->cart_contents[$cart_item_key]['product_id'];
        $productQuantity = $cart->cart_contents[$cart_item_key]['quantity'];

        if ($offerProductId = OfferHelper::productEligibleForOffer($productId, true)) {

            foreach (WC()->cart->get_cart() as $cart_item_key2 => $cart_item) {
                if ($cart_item['product_id'] === $offerProductId) {
                    $newQuantity = $cart_item['quantity'] - $productQuantity;
                    WC()->cart->set_quantity($cart_item_key2, $newQuantity);
                    break;
                }
            }
        }

        return $cart;
    }

    /**
     * Function for `woocommerce_cart_item_remove_link` filter-hook.
     *
     * Remove the remove item button in cart for "offer" products.
     *
     *
     * @param $button_link
     * @param $cart_item_key
     * @return mixed|string
     */
    public function woocommerce_cart_item_remove_link($button_link, $cart_item_key)
    {
        $offersProductsIds = array_map(static function ($product) {
            return $product->ID;
        }, OfferHelper::getOfferProducts());

        // Get the current cart item
        $cart_item = WC()->cart->get_cart()[$cart_item_key];

        // If the targeted product is in cart we remove the button link
        if(in_array($cart_item['data']->get_id(), $offersProductsIds)) {
            $button_link = '';
        }

        return $button_link;
    }

    /**
     * Function for `woocommerce_cart_item_quantity` filter-hook.
     *
     * Remove the quantity input of "offer" products on cart page.
     *
     * @param $product_quantity
     * @param $cart_item_key
     * @param $cart_item
     * @return mixed|string
     */
    public function woocommerce_cart_item_quantity($product_quantity, $cart_item_key, $cart_item)
    {
        if(is_cart() && OfferHelper::isProductOffer($cart_item['product_id'])) {
            $product_quantity = sprintf( '%2$s <input type="hidden" name="cart[%1$s][qty]" value="%2$s" />', $cart_item_key, $cart_item['quantity'] );
        }
        return $product_quantity;
    }

    /**
     * Function for `wp` action-hook.
     *
     * 1. Remove add to cart btn from single page of offers' products. [frontend side solution].
     *
     * @return void
     */
    public function action_wp(): void
    {
        if (!is_product()) {
            return;
        }

        // Remove add to cart btn from single page of offers' products. [frontend side solution]
        $product = get_product( get_queried_object_id() );

        if( OfferHelper::isProductOffer($product->id) ) {
            remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
        }
    }
}
