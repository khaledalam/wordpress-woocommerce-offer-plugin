<?php

namespace utils;

class OfferHelper {

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
     * @return bool|mixed|void|null
     */
    public static function productEligibleForOffer(string $productId, bool $getOfferProductId = false)
    {
        $productCategories = wp_get_post_terms($productId, 'product_cat');

        $offerApplyToCategoryTermId = get_option('product_offer_cat_term_id');
        $offerProductId = get_option('product_offer_id');

        foreach ($productCategories as $productCategory) {
            if ((string)$productCategory->term_id === $offerApplyToCategoryTermId) {
                return $getOfferProductId ? (string)$offerProductId : true;
            }
        }

        return $getOfferProductId ? null : false;
    }
}