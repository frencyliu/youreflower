<?php

/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

if (!defined('ABSPATH')) {
  exit;
}


do_action('woocommerce_before_checkout_form', $checkout);


// If checkout registration is disabled and not logged in, the user cannot checkout.
if (!$checkout->is_registration_enabled() && $checkout->is_registration_required() && !is_user_logged_in()) {
  echo esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('You must be logged in to checkout.', 'woocommerce')));
  return;
}

?>

<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data">

  <?php if ($checkout->get_checkout_fields()) : ?>
    <?php do_action('woocommerce_checkout_before_customer_details'); ?>
    <div class="col2-set row" id="customer_details">
      <div class="col-lg-6">
        <?php do_action('woocommerce_checkout_billing'); ?>
      </div>


      <div class="col-lg-6">
        <?php do_action('woocommerce_checkout_shipping'); ?>
      </div>
    </div>
    <?php do_action('woocommerce_checkout_after_customer_details'); ?>



  <?php endif; ?>

  <?php do_action('woocommerce_checkout_before_order_review_heading'); ?>

  <h3 id="order_review_heading"><?php esc_html_e('Your order', 'woocommerce'); ?></h3>

  <?php do_action('woocommerce_checkout_before_order_review'); ?>


  <h3 class="yc_billing_detail_heading">帳單資訊</h3>
  <?php do_action('woocommerce_checkout_after_order_review'); ?>

  <!-- <div id="order_review" class="woocommerce-checkout-review-order"> -->
  <?php do_action('woocommerce_checkout_order_review'); ?>
  <!-- </div> -->



</form>

<?php do_action('woocommerce_after_checkout_form', $checkout); ?>


<style>
  .yc_billing_detail_heading {
    display: block;
    width: 100%;
    order: 1;
  }

  /** common style */
  form.checkout.woocommerce-checkout #customer_details {
    padding: 15px;
    border-radius: 5px;
    border: 1px solid #eee;
  }

  div.woocommerce {
    max-width: 800px;
    margin: 0 auto;
    display: block !important;
  }

  .woocommerce-page.woocommerce-checkout .woocommerce-cart-form table.shop_table th,
  .woocommerce-page.woocommerce-checkout .woocommerce-cart-form table.shop_table td,
  .woocommerce .woocommerce .woocommerce-cart-form table.shop_table,
  .woocommerce-checkout .woocommerce .cart_totals.calculated_shipping table.shop_table,
  .woocommerce-page .cart_totals.calculated_shipping table.shop_table,
  .cart-subtotal {
    border: 0;
  }

  .woocommerce-checkout .woocommerce-cart-form .shop_table {
    background-color: #fdfdfd !important;
    margin-bottom: 0 !important;
    width: 100%;
  }

  .woocommerce-checkout .woocommerce-cart-form .shop_table thead {
    background-color: #f8f8f8 !important;
  }

  .subtotal-in-cart {
    border-top: 1px solid #ebebeb !important;
    border-bottom: 1px solid #ebebeb !important;
  }

  .woocommerce-checkout .woocommerce table.cart td.actions .coupon,
  .woocommerce-page #content table.cart td.actions .coupon,
  .woocommerce-checkout.woocommerce-page table.cart td.actions .coupon,
  button[name='update_cart'],
  .wc-proceed-to-checkout,
  /*#order_review_heading,
	#order_review .shop_table.woocommerce-checkout-review-order-table thead,
	#order_review .shop_table.woocommerce-checkout-review-order-table tbody,
	#order_review .shop_table.woocommerce-checkout-review-order-table tfoot .cart-subtotal,*/
  .woocommerce+.cart_totals tr.woocommerce-shipping-totals.shipping,
  .woocommerce+.cart_totals tr.order-total,
  .hidden,
  .woocommerce-billing-fields>h3 {
    display: none !important;
  }

  .woocommerce-checkout .cart_totals .shop_table {
    border-top: 1px solid #ebebeb !important;
    border-bottom: 1px solid #ebebeb !important;
  }

  .woocommerce table.shop_table tbody:first-child tr:first-child td,
  .woocommerce table.shop_table tbody:first-child tr:first-child th {
    border: 0;
  }

  .woocommerce-page.woocommerce-checkout table.shop_table .cart-subtotal td {
    padding-left: 1rem;
  }

  .woocommerce-cart-form {
    border-bottom: 1px solid #ebebeb;
  }

  select {
    display: block;
    width: 100%;
    background: url('data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/PjwhRE9DVFlQRSBzdmcgIFBVQkxJQyAnLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4nICAnaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkJz48c3ZnIGhlaWdodD0iNTEycHgiIGlkPSJMYXllcl8xIiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCA1MTIgNTEyOyIgdmVyc2lvbj0iMS4xIiB2aWV3Qm94PSIwIDAgNTEyIDUxMiIgd2lkdGg9IjUxMnB4IiB4bWw6c3BhY2U9InByZXNlcnZlIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIj48cGF0aCBkPSJNOTguOSwxODQuN2wxLjgsMi4xbDEzNiwxNTYuNWM0LjYsNS4zLDExLjUsOC42LDE5LjIsOC42YzcuNywwLDE0LjYtMy40LDE5LjItOC42TDQxMSwxODcuMWwyLjMtMi42ICBjMS43LTIuNSwyLjctNS41LDIuNy04LjdjMC04LjctNy40LTE1LjgtMTYuNi0xNS44djBIMTEyLjZ2MGMtOS4yLDAtMTYuNiw3LjEtMTYuNiwxNS44Qzk2LDE3OS4xLDk3LjEsMTgyLjIsOTguOSwxODQuN3oiLz48L3N2Zz4=') 99% 50% no-repeat !important;
    background-size: 16px 12px !important;
    background-color: #fff !important;
    -moz-appearance: none;
    -webkit-appearance: none;
    appearance: none;
    padding-left: 13px;
  }

  form.checkout.woocommerce-checkout {
    display: flex;
    flex-direction: column;
  }

  h3.order_review_heading {
    order: 0;
  }

  .yc_billing_detail_heading {
    order: 2;
  }

  form.checkout.woocommerce-checkout #customer_details {
    order: 3;
    width: 100% !important;
    float: none !important;
    margin-right: 0 !important;
  }

  #payment {
    order: 4;
  }

  form.checkout.woocommerce-checkout #order_review {
    order: 1;
    width: 100% !important;
    float: none !important;
    margin-left: 0 !important;
    margin-bottom: 30px !important;
    padding: 0px 0px 30px 0px !important;
    border: 0 !important;
  }

  .choose_cvs td button {
    font-size: 16px;
    padding: 10px 15px;
    background: none;
    border: 1px solid #ccc;
    ;
    cursor: pointer;
  }

  #placeOrderWrap button#place_order {
    width: 100%;
    display: block;
    float: none;
    margin: 1rem auto;
    font-size: 16px;
    text-align: center;
  }

  .checkout .shop_table tfoot th {
    text-align: left;
    width: 159px;
  }

  .woocommerce-checkout .col-1,
  .woocommerce-checkout .col-2 {
    width: 100%;
    float: none;
    margin-right: 0;
  }

  .select2-search__field {
    border: 1px solid #ccc !important;
  }

  .woocommerce-NoticeGroup.woocommerce-NoticeGroup-checkout {
    width: 100%;
  }

  .woocommerce input[name=zipcode],
  .woocommerce-input-wrapper {
    width: 100%;
  }

  .woocommerce form .form-row label {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: .1em;
    color: #D18300;
  }

  #order_comments::placeholder {
    color: #cccccc;
  }

  #order_review tr>td:last-child,
  #order_review tr>th:last-child {
    text-align: right;
  }

  .woocommerce-info {
    background-color: #fcfcfc;
    border-top-color: var(--primary);
    color: #333;
  }

  .woocommerce-info::before {
    content: "\e600";
    color: var(--primary);
  }

  .woocommerce form.checkout_coupon {
    border-color: var(--primary);
  }

  .woocommerce-checkout-payment#payment {
    background-color: #fcfcfc;
  }

  .woocommerce-checkout #payment div.payment_box {
    background-color: var(--primary);
    color: #333;
  }

  .woocommerce-checkout #payment div.payment_box::before {
    border-bottom-color: var(--primary);
  }

  .woocommerce a.button.alt,
  .woocommerce button.button.alt,
  .woocommerce input.button.alt,
  .woocommerce #respond input#submit.alt {
    background-color: var(--primary);
  }

  .woocommerce a.button.alt:hover,
  .woocommerce button.button.alt:hover,
  .woocommerce input.button.alt:hover,
  .woocommerce #respond input#submit.alt:hover {
    background-color: #000;
  }

  @media screen and (max-width: 920px) {

    #paymentWrap td {
      padding-left: 0 !important;
    }

    .woocommerce #order_review table.shop_table ul#shipping_method.woocommerce-shipping-methods li label {
      font-size: 14px;
    }
  }

  @media screen and (max-width: 920px) {
    #paymentWrap th {
      width: 80px !important;
    }

    .woocommerce-shipping-totals.shipping th,
    .woocommerce #order_review table.shop_table tfoot th {
      width: 80px;
    }
  }
</style>