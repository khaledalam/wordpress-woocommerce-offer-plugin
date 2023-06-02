## ANCHOVY ∪ NIU = { 9H }

#### Custom wordpress plugin to add woocommerce offers/gifts for specific products' categories.

Demo video: 

https://github.com/khaledalam/wordpress-woocommerce-offer-plugin/assets/8682067/4cc3f06d-7daa-4011-9c3f-f7b7a74f83b1




DB dump: [wordpress.sql](./wordpress.sql)

---

### Problem/requirements
- Ability to auto-add offer products(gifts) to wordpress woocommerce cart when user try to add any products from specific categories.
- Ability to set the allowed categories <-> gifts from admin dashboard.
- Restrict cart CRUD operations for this kind of "offer" products.

---
### Example

- 3 t-shirt products => belongs to [Special Category] => add gift product(Clothes Hanger) for each item.
- 2 drink products => belongs to [Super Special Category] => add gift product(Shalimo) for each item.
- 2 short products => belongs to [Uncategorized] => add no gifts.

<img src="./admin.png"/><br/>
<img src="./cart.png"/>

----------------------------------------------------------------
### Approach/solution/technical
- Create custom plugin (Offer) that will allow the admin & shop manager to manage/set categories <-> gifts association in the dashboard.
- Use filters/actions hooks:
  - <b>Actions</b>:
    - woocommerce_add_cart_item_data
      - <small>Trigger when add product to cart from /shop page or single-product page.</small>
    - woocommerce_update_cart_action_cart_updated
        - <small>Handle change quantity of products in cart.</small>
    - woocommerce_remove_cart_item
      - <small>Handle removing items from cart.</small>
    - admin_init
      - <small>Allow shop_manager to access offer plugin.</small>
  - <b>Filters</b>:
    - woocommerce_cart_item_remove_link
      - <small>Remove the remove item button in cart for "offer" products.</small>
    - woocommerce_cart_item_quantity
      - <small>Remove the quantity input of "offer" products on cart page.</small>
- No hard-coded
- "Offer" product marking depends on custom product attribute "is_offer" key
  - to use 0-pricing also as a mark uncomment `meta_query` in `OfferHelper::getOfferProducts()` and edit `OfferHelper::isProductOffer()` as well.
- Relation between product's category<->"offer" product depends on option with key `offers_key_cat_term_id_product_id_{$category->term_id}` existence.
      
---
### Auth:
admin:<br>
user: 9Huser<br>
pass: 9Hpassword9H<br><br>
shop manager:<br>
user: 9hshopmanager<br>
pass: shopmanager9H

PMA:<br>
user: root<br>
pass: password<br>


> $ docker-compose up -d --build


---
#### References
- [wordpress-docker-compose](https://github.com/kassambara/wordpress-docker-compose) used as template.
- [wppb](https://wppb.me/) used as plugin template.
