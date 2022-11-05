<?php

/**
 * 秀出可以使用的折扣
 */

 add_action( 'wp_enqueue_scripts', 'handle_coupon' );

 function handle_coupon(){
      if ( is_checkout() ) {
          wp_enqueue_script( 'handle-coupon', get_stylesheet_directory_uri() . '/assets/js/handle-coupon.js', array( 'wc-checkout' ), YC_VER, true );
      }
 }

add_action('woocommerce_before_checkout_form', 'yf_coupon_available', 300);

function yf_coupon_available()
{
  if (!is_user_logged_in()) return;
  $user_id = get_current_user_id();
  $user_birthday = get_user_meta($user_id, 'birthday', true);
  $user_points = gamipress_get_user_points($user_id, 'yf_reward');
  $user_points_img = '<img width="21" height="21" src="' . get_the_post_thumbnail_url(701) . '" />';
  $coupons = yf_get_coupons(); //取得需要購物金的優惠券
  $origin_coupons = yf_get_origin_coupons(); //取得一般的優惠券





  $cart_total = (int) WC()->cart->subtotal;
  // echo '<pre>';
  // var_dump($coupons);
  // echo '</pre>';

?>
  <style>
    .list-group~.woocommerce-message {
      display: none !important;
    }
  </style>
  <?php if (!empty($coupons)) : ?>
    <h2 class="">使用購物金</h2>
    <div class="list-group mb-2" style="border-radius: 5px;">
      <?php foreach ($coupons as $coupon) :
        $data = coupons_available($coupon);
        //    echo '<pre>';
        //    var_dump($data);
        //    echo '</pre>';

      ?>
        <label class="list-group-item list-group-item-action <?= $data['disabled_bg'] ?>">
          <input id="coupon-<?= $coupon->ID; ?>" name="yf_coupon" class="form-check-input me-1" type="radio" value="<?= $coupon->post_title; ?>" <?= $data['disabled'] ?>>
          <?= $coupon->post_title . $coupon->post_excerpt . $data['reason']; ?>
        </label>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
<?php
}

function yf_get_coupons()
{

  $coupon_posts_without_minimum_amount = get_posts(array(
    'posts_per_page'   => -1,
    'post_type'        => 'shop_coupon',
    'post_status'      => 'publish',
    'meta_query' => array(
      'relation' => 'AND',
      'minimum_amount_clause' => array(
        'key' => 'minimum_amount',
        'compare' => 'NOT EXISTS',
      ),
      'reuqire_yf_reward_clause' => array(
        'key' => 'reuqire_yf_reward',
        'value' => '1'
      ),
    ),
  ));

  $coupon_posts_with_minimum_amount = get_posts(array(
    'posts_per_page'   => -1,
    'meta_key' => 'minimum_amount',
    'orderby' => 'meta_value_num',
    'order'            => 'ASC',
    'post_type'        => 'shop_coupon',
    'post_status'      => 'publish',
    'meta_query' => array(
      'relation' => 'AND',
      'minimum_amount_clause' => array(
        'key' => 'minimum_amount',
        'compare' => 'EXISTS',
      ),
      'reuqire_yf_reward_clause' => array(
        'key' => 'reuqire_yf_reward',
        'value' => '1'
      ),
    ),
  ));

  $coupon_posts = array_merge($coupon_posts_without_minimum_amount, $coupon_posts_with_minimum_amount);

  $user_id = get_current_user_id();
  $user_birthday = get_user_meta($user_id, 'birthday', true);
  $user_member_lv_id = yf_get_user_member_lv_id($user_id);
  $user_member_lv_title = get_the_title($user_member_lv_id);
  $birthday_coupon_name = $user_member_lv_title . '當月生日禮金';




  if (date('m', strtotime($user_birthday)) != date('m')) {
    foreach ($coupon_posts as $key => $coupon_post) {
      if (strpos($coupon_post->post_title, "生日禮金") !== false) {
        // 用戶沒生日，不應用
        unset($coupon_posts[$key]);
      }
      $date_expires = get_post_meta($coupon_post->ID, 'date_expires', true);

      if (!empty($date_expires) && (int) $date_expires < time()) {
        //過期的coupon
        unset($coupon_posts[$key]);
        continue;
      }
    }
  } else {
    //用戶生日
    foreach ($coupon_posts as $key => $coupon_post) {
      if (strpos($coupon_post->post_title, "生日禮金") !== false && $coupon_post->post_title != $birthday_coupon_name) {
        // 用戶沒生日，不應用
        unset($coupon_posts[$key]);
      }
    }
  }


  return $coupon_posts; // always use return in a shortcode
}

function yf_get_origin_coupons()
{
  $origin_coupon_posts_without_minimum_amount_1 = get_posts(array(
    'posts_per_page'   => -1,
    'post_type'        => 'shop_coupon',
    'post_status'      => 'publish',
    'meta_query' => array(
      'relation' => 'AND',
      'reuqire_yf_reward_exist_clause' => array(
        'key' => 'reuqire_yf_reward',
        'compare' => 'NOT EXISTS',
      ),
      'reuqire_yf_reward_clause' => array(
        'key' => 'minimum_amount',
        'compare' => 'NOT EXISTS',
      ),
    ),
  ));

  $origin_coupon_posts_without_minimum_amount_2 = get_posts(array(
    'posts_per_page'   => -1,
    'post_type'        => 'shop_coupon',
    'post_status'      => 'publish',
    'meta_query' => array(
      'relation' => 'AND',
      'reuqire_yf_reward_exist_clause' => array(
        'key' => 'reuqire_yf_reward',
        'value' => '0',
      ),
      'reuqire_yf_reward_clause' => array(
        'key' => 'minimum_amount',
        'compare' => 'NOT EXISTS',
      ),
    ),
  ));

  $origin_coupon_posts_with_minimum_amount = get_posts(array(
    'posts_per_page'   => -1,
    'meta_key' => 'minimum_amount',
    'orderby' => 'meta_value_num',
    'order'            => 'ASC',
    'post_type'        => 'shop_coupon',
    'post_status'      => 'publish',
    'meta_query' => array(
      'relation' => 'OR',
      'reuqire_yf_reward_exist_clause' => array(
        'key' => 'reuqire_yf_reward',
        'compare' => 'NOT EXISTS',
      ),
      'reuqire_yf_reward_clause' => array(
        'key' => 'reuqire_yf_reward',
        'value' => '0'
      ),
    ),
  ));
  $coupon_posts = array_merge($origin_coupon_posts_without_minimum_amount_1, $origin_coupon_posts_without_minimum_amount_2,  $origin_coupon_posts_with_minimum_amount);
  return $coupon_posts;
}




function coupons_available($coupon)
{
  $cart_total = (int) WC()->cart->subtotal;
  $user_id = get_current_user_id();
  $user_points = (int) gamipress_get_user_points($user_id, 'yf_reward');
  $coupon_amount = (int) get_post_meta($coupon->ID, 'coupon_amount', true);
  $minimum_amount = (int) get_post_meta($coupon->ID, 'minimum_amount', true);

  $data = [];
  if ($user_points < $coupon_amount) {
    $data['is_available'] = false;
    $data['reason'] = "，<span class='text-danger'>您的購物金不足(目前${user_points})，無法使用折扣</span>";
    $data['disabled'] = "disabled";
    $data['disabled_bg'] = "bg-light cursor-not-allowed d-none";
    return $data;
  } elseif ($cart_total < $minimum_amount) {

    $d = $minimum_amount - $cart_total;
    $shop_url = site_url('shop');
    $data['is_available'] = false;
    $data['reason'] = "，<span class='text-danger'>還差 ${d} 元</span>，<a href='${shop_url}'>再去多買幾件 》</a>";
    $data['disabled'] = "disabled";
    $data['disabled_bg'] = "bg-light cursor-not-allowed";
    return $data;
  } else {
    $data['is_available'] = true;
    $data['reason'] = "";
    $data['disabled'] = "";
    $data['disabled_bg'] = "";
    return $data;
  }
}

/**
 * @see https://woocommerce.github.io/code-reference/files/woocommerce-includes-wc-stock-functions.html#source-view.100
 * 訂單成立時才扣購物金
 */
//woocommerce_payment_complete


function point_reduct_with_coupon($order_id)
{
  $order = wc_get_order($order_id);
  $coupon_codes   = $order->get_coupon_codes();
  $coupon_amount = 0;
  foreach ($coupon_codes as $key => $coupon_code) {
    $the_coupon = new WC_Coupon($coupon_code);
    $coupon_amount += $the_coupon->get_amount();
  }

  if ($coupon_amount === 0) return;


  $user_id = $order->get_customer_id();
  $user = get_user_by('id', $user_id);
  $user_member_lv_id = yf_get_user_member_lv_id($user_id);
  $user_member_lv_title = get_the_title($user_member_lv_id);
  $points_type = 'yf_reward';

  $args = array(
    'reason' => "使用購物金 NT$ $coupon_amount - $user->display_name ($user_member_lv_title)",
  );
  gamipress_deduct_points_to_user($user_id, $coupon_amount, $points_type, $args);
}



//add_action( 'woocommerce_payment_complete', 'point_reduct_with_coupon' );
// add_action( 'woocommerce_order_status_completed', 'point_reduct_with_coupon' );
add_action('woocommerce_order_status_processing', 'point_reduct_with_coupon');
// add_action( 'woocommerce_order_status_on-hold', 'point_reduct_with_coupon' );

/**
 * 訂單取消時
 */

function point_restore_with_coupon($order_id)
{
  $order = wc_get_order($order_id);
  $coupon_codes   = $order->get_coupon_codes();
  $coupon_amount = 0;
  foreach ($coupon_codes as $key => $coupon_code) {
    $the_coupon = new WC_Coupon($coupon_code);
    $coupon_amount += $the_coupon->get_amount();
  }

  if ($coupon_amount === 0) return;

  $user_id = $order->get_customer_id();
  $user = get_user_by('id', $user_id);
  $user_member_lv_id = yf_get_user_member_lv_id($user_id);
  $user_member_lv_title = get_the_title($user_member_lv_id);
  $points_type = 'yf_reward';

  $args = array(
    'reason' => "訂單取消，購物金退回 NT$ $coupon_amount - $user->display_name ($user_member_lv_title)",
  );
  gamipress_award_points_to_user($user_id, $coupon_amount, $points_type, $args);
}



add_action('woocommerce_order_status_cancelled', 'point_restore_with_coupon');
// add_action( 'woocommerce_order_status_pending', 'point_restore_with_coupon' );
