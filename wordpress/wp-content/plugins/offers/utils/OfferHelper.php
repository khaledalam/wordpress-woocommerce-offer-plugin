<?php


class OfferHelper {

    /**
     * Return all "offer" Products.
     *
     * @return array
     */
    public static function getOfferProducts(): array
    {
        return get_posts([
            'post_type' => 'product',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'id',
            'meta_query' => [ //  custom attribute "is_offer"
//                [ // and has 0 price
//                    'key'       => '_price',
//                    'compare'   => '=',
//                    'value'      => 0,
//                ],
                [
                    'key'       => '_product_attributes',
                    'compare'   => 'LIKE',
                    'value'      => 'is_offer'
                ],
            ]
        ]);
    }

    /**
     * Check if the product is an "offer" or not?
     **
     * @param string $productId
     * @return bool
     */
    public static function isProductOffer(string $productId): bool
    {
        // woocommerce option
        $offerProductId = get_option('product_offer_id');

        return $offerProductId === $productId;
    }

    /**
     * Check if product is eligible for an "offer" product or not.
     *
     * if $getOfferProductId param is true  => return {product_id || NULL}
     * if $getOfferProductId param is false => return {true || false}
     *
     * @param string $productId
     * @param bool $getOfferProductId
     * @return bool|null
     */
    public static function productEligibleForOffer(string $productId, bool $getOfferProductId = false)
    {
        $productCategory = wp_get_post_terms($productId, 'product_cat');
        if (count($productCategory) > 0) {
            $productCategory = $productCategory[0];
        }

        if (!$productCategory->term_id) {
            return $getOfferProductId ? null : false;
        }

        $offerProductId = get_option('_offers_key_cat_term_id_product_id_' . $productCategory->term_id);

        $offerProduct = get_post($offerProductId);

        if ($offerProduct) {
            return $getOfferProductId ? $offerProduct->ID : true;
        }
        return $getOfferProductId ? null : false;
    }
}