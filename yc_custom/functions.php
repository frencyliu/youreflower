<?php

define('YC_VER', '1.3.4');



add_filter( 'woocommerce_min_password_strength', 'wpglorify_woocommerce_password_filter', 300 );
function wpglorify_woocommerce_password_filter() {
return 0; } //2 represent medium strength password

// Disable default bootstrap 4 CSS and js
function yc_scripts()
{
    wp_dequeue_style('understrap-styles');
    wp_dequeue_script('understrap-scripts');
    wp_enqueue_script( 'yc_custom', get_stylesheet_directory_uri() . '/assets/js/main.js', array(), YC_VER, true );
}

add_action('wp_enqueue_scripts', 'yc_scripts', 100);

// Remove each style one by one
add_filter('woocommerce_enqueue_styles', 'yc_dequeue_styles');
function yc_dequeue_styles($enqueue_styles)
{
    if (is_checkout()) return $enqueue_styles;
    unset($enqueue_styles['woocommerce-general']);    // Remove the gloss
    unset($enqueue_styles['woocommerce-layout']);        // Remove the layout
    unset($enqueue_styles['woocommerce-smallscreen']);    // Remove the smallscreen optimisation
    return $enqueue_styles;
}

//check user role
function user_has_role($user_id, $role_name)
{
    $user_meta = get_userdata($user_id);
    $user_roles = $user_meta->roles;
    return in_array($role_name, $user_roles);
}



function yc_get_product_id_img()
{
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => -1,
    );

    $arr = array();

    $posts = get_posts($args);

    foreach ($posts as $product) {
        $product_id = $product->ID;
        $product_title = get_the_title($product_id);
        $img = get_the_post_thumbnail_url($product_id);
        $arr[$product_id] = $img;
    }

    return $arr;
}



include_once('woocommerce/index.php');
include_once('member/index.php');
include_once('member/events.php');
include_once('email/include.php');
include_once('admin/users.php');
include_once('admin/yf_menu.php');