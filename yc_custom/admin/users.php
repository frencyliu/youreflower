<?php
function custom_users_page_js($hook)
{

  //send user data to js

  if ('users.php' == $hook) {
    wp_enqueue_script('users', get_stylesheet_directory_uri() . '/assets/js/admin-users.js', array(), YC_VER, true);

    $users = get_users();
    $php_user_data = [];
    foreach ($users as $user) {
      $user_id = $user->ID;
      $user_member_lv = get_user_meta($user_id, '_gamipress_member_lv_rank', true);
      $user_member_lv_img_url = (empty($user_member_lv)) ? 'http://1.gravatar.com/avatar/1c39955b5fe5ae1bf51a77642f052848?s=96&d=mm&r=g' : (get_the_post_thumbnail_url($user_member_lv));
      $php_user_data['user-' . $user_id] = $user_member_lv_img_url;
    }

    wp_localize_script('users', 'php_user_data', $php_user_data);
  }
  if ('user-edit.php' == $hook || 'profile.php' == $hook) {
    wp_enqueue_script('user-edit', get_stylesheet_directory_uri() . '/assets/js/admin-user-edit.js', array(), YC_VER, true);

    //send user data to js
    switch ($hook) {
      case 'user-edit.php':
        $user_id = $_GET['user_id'];
        break;
      case 'profile.php':
        $user_id = get_current_user_id();
        break;
      default:
        $user_id = $_GET['user_id'];
        break;
    }
    $user_member_lv = get_user_meta($user_id, '_gamipress_member_lv_rank', true);
    $user_member_lv_img_url = (empty($user_member_lv)) ? 'http://1.gravatar.com/avatar/1c39955b5fe5ae1bf51a77642f052848?s=96&d=mm&r=g' : (get_the_post_thumbnail_url($user_member_lv));


    wp_localize_script('user-edit', 'php_user_data', array(
      /* 'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('my-special-string'), */
      'user_member_lv_img_url' => $user_member_lv_img_url,
    ));
  }
}
add_action('admin_enqueue_scripts', 'custom_users_page_js');

function custom_users_page_css()
{
  $screen_id = get_current_screen()->id;


  $css = '';
  ob_start();
?>
  <style>
    /*User-edit*/
    #your-profile,
    tr.user-nickname-wrap {
      display: none !important;
    }
  </style>
<?php
  $css .= ob_get_clean();

  echo $css;
}

add_action('admin_head', 'custom_users_page_css');





//??????????????????
add_filter('manage_users_columns', 'yf_set_custom_edit_users_columns', 300, 1);
//???????????????
add_filter('manage_users_custom_column', 'yf_custom_users_column', 300, 3);


function yf_set_custom_edit_users_columns($columns)
{

  unset($columns['ts0']);
  unset($columns['ts1']);
  unset($columns['ts2']);
  unset($columns['ts3']);
  unset($columns['goal_gg']);
  $columns['member_lv'] = '????????????';
  $columns['sales_last_year'] = '???????????????';
  return $columns;
}

//@todo
function yf_custom_users_column($output, $column_name, $user_id)
{
  if ($column_name == 'member_lv') {
    $value = '';
    $user_member_lv = get_user_meta($user_id, '_gamipress_member_lv_rank', true);

    $expiration_text = get_expiration_date_text_by_user($user_id);

    $value = get_the_title($user_member_lv) . '??????';
    $value .= '<br>';
    $value .= $expiration_text;

    return $value;
  }


  if ($column_name == 'sales_last_year') {
    $value = '';
    $orderdata = get_orderdata_lastyear_by_user($user_id);
    //$orderdata['user_is_registered'];
    $value .= 'NT$ ' . $orderdata['total'];
    $value .= '<br>';
    $value .= '?????? ' . $orderdata['order_num'] . ' ???';
    return $value;
  }





  return $output;
}

add_action('show_user_profile', 'yf_add_field', 100);
add_action('edit_user_profile', 'yf_add_field', 100);
add_action('edit_user_profile_update', 'yf_add_field_update', 100);
add_action('personal_options_update', 'yf_add_field_update', 100);

function yf_add_field($user_obj)
{
  $user_id = $user_obj->ID;
  $user_member_lv = get_user_meta($user_id, '_gamipress_member_lv_rank', true);
  $time_MemberLVchanged_last_time = get_user_meta($user_id, 'time_MemberLVchanged_last_time', true) ?: $user_obj->user_registered;
  $time_MemberLVexpire_date = get_user_meta($user_id, 'time_MemberLVexpire_date', true) ?: '';
  $yf_reward_point = (get_user_meta($user_id, '_gamipress_yf_reward_points', true)) ?: '0';


  $orderdata = get_orderdata_total_by_user($user_id);
  $sales_total = 'NT$ ' . $orderdata['total'];
  $sales_total .= ' | ?????? ' . $orderdata['order_num'] . ' ???';


  $orderdata = get_orderdata_lastyear_by_user($user_id);
  $sales_last_year = 'NT$ ' . $orderdata['total'];
  $sales_last_year .= ' | ?????? ' . $orderdata['order_num'] . ' ???';

  $user_registered = date('Y-m-d H:i:s', strtotime($user_obj->user_registered) + 8 * 3600);


  // ????????? user_id = test ~ test 99 ?????????
  $pattern = "/^test[0-9]{0,2}$/i";
  $match_test_user = preg_match($pattern, $user_obj->user_login, $matches);

  $user_member_lv_id = yf_get_user_member_lv_id($user_id);
  $birthday_points = get_birthday_reward_by_member_lv_id($user_member_lv_id);
  $already_awarded = get_user_meta($user_id, 'yf_user_last_birthday_awarded_on', true);
  $birthday = get_user_meta($user_id, 'birthday', true);
  $birthday_month = date('m', strtotime($birthday));

  if (date('m') != $birthday_month) {
    $already_awarded = '?????????????????????????????????';
  } elseif (empty($already_awarded)) {
    //????????????????????? $user_registered
    $already_awarded = '????????????<span style="color:red;cursor:pointer;" class="description"> ?????? ???????????????<input type="checkbox" id="birthday_points_now" name="birthday_points_now" value="true">????????????</span>';
  } else {
    //???????????????
    if ((strtotime("now") - strtotime($already_awarded) >= 31536000) && date('m') == $birthday_month) {
      $already_awarded = '?????????' . $already_awarded . '??????????????????????????????';
    } else {
      //?????????
      $already_awarded = '?????????' . $already_awarded . '?????????????????????????????????????????????';
    }
  }


  $yf_monthly_reward = get_monthly_reward_by_member_lv_id($user_member_lv_id);

  //??????1???????????????
  $reward_monthly_date = date('Y-m') . '-1';
  $already_monthly_awarded = get_user_meta($user_id, 'yf_user_last_reward_monthly_on', true);

  if (empty($already_monthly_awarded)) {
    $already_monthly_awarded = '????????????<span style="color:red;cursor:pointer;" class="description"> ?????? ???????????????<input type="checkbox" id="monthly_points_now" name="monthly_points_now" value="true">????????????</span>';
  } else {
    //???????????????
    if ((strtotime("now") - strtotime($already_monthly_awarded) >= 1900800) && (strtotime("now") >= strtotime($reward_monthly_date))) {
      //??????????????????22????????????????????? > ?????????
      $already_monthly_awarded = '?????????' . $already_monthly_awarded . '????????????????????????22???';
    } else {
      //?????????
      $already_monthly_awarded = '?????????' . $already_monthly_awarded . '????????????????????????22??????????????????';
    }
  }

?>
  <h2>????????????</h2>
  <table class="form-table" id="fieldset-yc_wallet">
    <tbody>
      <tr>
        <th>
          <label for="member_lv">????????????</label>
        </th>
        <td>
          <select name="member_lv" id="member_lv" class="regular-text" value="<?= $user_member_lv ?>">
            <option value="">?????????</option>
            <?php
            $member_lvs = get_posts([
              'post_type' => 'member_lv',
              'posts_per_page' => -1,
              'post_status' => 'publish',
              'orderby' => 'menu_order',
              'order' => 'ASC'
            ]);

            foreach ($member_lvs as $member_lv) {
              $selected = ($user_member_lv == $member_lv->ID) ? 'selected' : '';
              echo '<option value="' . $member_lv->ID . '" ' . $selected . '>' . $member_lv->post_title . '</option>';
            }
            ?>
          </select>
          <span class="description">?????????????????????<?= $time_MemberLVchanged_last_time ?></span>
        </td>
      </tr>
      <tr>
        <th>
          <label for="time_MemberLVexpire_date">???????????????</label>
        </th>
        <td>
          <?php
          if ('no_expire' === $time_MemberLVexpire_date || time() < strtotime($time_MemberLVexpire_date)) {
            $expire_type = 'text';
            $expire_value = ('no_expire' === $time_MemberLVexpire_date) ? '?????????' : $time_MemberLVexpire_date;
            $expire_status = '';
          } else {
            $expire_type = 'date';
            $expire_value = $time_MemberLVexpire_date;
            $expire_status = '?????????';
          }
          ?>
          <input type="<?= $expire_type ?>" value="<?= $expire_value ?>" id="time_MemberLVexpire_date" name="time_MemberLVexpire_date" disabled="disabled" class="regular-text">
          <span class="description"><?= $expire_status ?></span>
          <?php if ($match_test_user == 1) : ?>
            <span id="force_modify_time_MemberLVexpire_date" class="description" style="color:red;cursor:pointer;">?????? ?????????????????????????????????</span>
          <?php endif; ?>
        </td>
      </tr>
      <tr>
        <th>
          <label for="sales_total">???????????????</label>
        </th>
        <td>
          <input type="text" value="<?= $sales_total ?>" id="sales_total" name="sales_total" disabled="disabled" class="regular-text">
        </td>
      </tr>
      <tr>
        <th>
          <label for="sales_last_year">?????????????????????</label>
        </th>
        <td>
          <input type="text" value="<?= $sales_last_year ?>" id="sales_last_year" name="sales_last_year" disabled="disabled" class="regular-text">
        </td>
      </tr>


      <?php if ($match_test_user == 1) : ?>
        <tr>
          <th>
            <label for="add_order" style="color:red">????????????1???????????????</label>
          </th>
          <td>
            <input type="number" value="" id="add_order" name="add_order" class="regular-text">
            <span class="description" style="color:red">?????? ?????????????????????test???????????????????????????</span>
          </td>
        </tr>

      <?php endif; ?>



      <tr>
        <th>
          <label for="yf_award">?????????????????????</label>
        </th>
        <td>
          <input type="text" value="NT$ <?= $yf_reward_point ?>" id="yf_award" name="yf_award" disabled="disabled" class="regular-text">
          <span class="description">????????????????????????</span>
        </td>
      </tr>
      <tr>
        <th>
          <label>???????????????</label>
        </th>
        <td>
          <input type="text" value="NT$ <?= $yf_monthly_reward ?>" disabled="disabled" name="yf_reward_monthly" class="regular-text">
          <span class="description"><?= $already_monthly_awarded; ?></span>
        </td>
      </tr>
      <tr>
        <th>
          <label>????????????</label>
        </th>
        <td>
          <input type="text" value="NT$ <?= $birthday_points ?>" disabled="disabled" name="birthday_points" class="regular-text">
          <span class="description"><?= $already_awarded ?></span>

        </td>
      </tr>
      <tr>
        <th>
          <label>??????????????????????????????</label>
        </th>
        <td>
          <input type="checkbox" id="clear_already_awarded" name="clear_already_awarded" value="true">
          <span class="description" style="color:red;">?????? ??????????????????????????????????????????????????????</span>
        </td>
      </tr>
      <tr>
        <th>
          <label for="yf_award_add">???????????????</label>
        </th>
        <td>
          <input type="number" value="" id="yf_award_add" name="yf_award_add" class="regular-text">
          <span class="description">???????????????????????????</span>

        </td>
      </tr>
      <tr>
        <th>
          <label for="yf_award_reason">????????????</label>
        </th>
        <td>
          <textarea id="yf_award_reason" name="yf_award_reason" class="regular-text" row="3" placeholder="??????log?????????????????????????????????"></textarea>
        </td>
      </tr>
      <tr class="user_register_time">
        <th>
          <label for="user_register_time">????????????</label>
        </th>
        <td>
          <input type="text" value="<?= $user_registered; ?>" id="user_register_time" name="user_register_time" disabled="disabled" class="regular-text">
        </td>
      </tr>

    </tbody>
  </table>

  <script>
    (function($) {
      // disable mousewheel on a input number field when in focus
      // (to prevent Chromium browsers change the value when scrolling)
      $('form').on('focus', 'input[type=number]', function(e) {
        $(this).on('wheel.disableScroll', function(e) {
          e.preventDefault()
        })
      })
      $('form').on('blur', 'input[type=number]', function(e) {
        $(this).off('wheel.disableScroll')
      })
    })(jQuery)
  </script>
<?php
}


function yf_add_field_update($user_id)
{


  if (!current_user_can('edit_user', $user_id)) {
    return false;
  }
  $user_member_lv = get_user_meta($user_id, '_gamipress_member_lv_rank', true);
  $points = $_POST['yf_award_add'];
  $points_type = 'yf_reward';
  $user_display_name = get_user_meta($user_id, 'display_name', true);
  $admin_name = wp_get_current_user()->display_name;
  $yf_award_reason = empty($_POST['yf_award_reason']) ? ('') : (' | ??????: ' . $_POST['yf_award_reason']);
  $time_MemberLVchanged_last_time = date('Y-m-d H:i:s', strtotime("+8 hours"));
  $time_MemberLVexpire_date = get_user_meta($user_id, 'time_MemberLVexpire_date', true);


  // ????????????
  if (!empty($_POST['add_order'])) {
    $order = wc_create_order();
    $order->set_status('wc-completed');
    $order->set_customer_id($user_id);
    $order->set_total($_POST['add_order']);
    $order->save();
  }

  //??????????????????????????????????????????????????????+1???
  //???????????????????????????
  if ($_POST['member_lv'] != $user_member_lv) {
    update_user_meta($user_id, '_gamipress_member_lv_rank', $_POST['member_lv']);
    update_user_meta($user_id, 'time_MemberLVchanged_last_time', $time_MemberLVchanged_last_time);
    update_user_meta($user_id, 'time_MemberLVexpire_date', date('Y-m-d', strtotime("+1 year")));
  }



  // ???????????????????????????
  if (!empty($_POST['time_MemberLVexpire_date'])) {
    update_user_meta($user_id, 'time_MemberLVexpire_date', $_POST['time_MemberLVexpire_date']);
  }

  // ??????????????????
  if ($_POST['clear_already_awarded'] == 'true') {
    update_user_meta($user_id, 'yf_user_last_reward_monthly_on', '');
    update_user_meta($user_id, 'yf_user_last_birthday_awarded_on', '');
  }

  if ($_POST['birthday_points_now'] == 'true') {
    yf_birthday_by_user_id($user_id);
  }
  if ($_POST['monthly_points_now'] == 'true') {
    yf_reward_monthly_by_user_id($user_id);
  }


  if (!empty($points)) {
    $args = array(
      'reason' => "??????????????????????????? NT$ $points - $user_display_name by $admin_name $yf_award_reason",
    );

    if ($points >= 0) {
      gamipress_award_points_to_user($user_id, $points, $points_type, $args);
    } else {
      gamipress_deduct_points_to_user($user_id, $points, $points_type, $args);
    }
  }
}


add_action(
  'user_row_actions',
  function ($actions) {
    if (isset($actions['view'])) {
      unset($actions['view']);
    }

    return $actions;
  }
);

$member_lvs = get_posts([
  'post_type' => 'member_lv',
  'posts_per_page' => -1,
  'post_status' => 'publish',
  'orderby' => 'menu_order',
  'order' => 'ASC',
]);
global $countUser;
foreach ($member_lvs as $member_lv) {
  $users = get_users([
    'meta_key' => '_gamipress_member_lv_rank',
    'meta_value' => $member_lv->ID,
    'meta_compare' => '=',
  ]);

  $countUser[$member_lv->ID] = count($users);
}


define('CountUser', $countUser);

add_action('manage_users_extra_tablenav', 'render_custom_filter_options');
function render_custom_filter_options()
{
  $member_lvs = get_posts([
    'post_type' => 'member_lv',
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'orderby' => 'menu_order',
    'order' => 'ASC',
  ]);
  $get_member_lv = $_GET['member_lv'];


?>

  <form method="GET">

    <select name="member_lv">
      <option value="0">??????????????????</option>
      <?php foreach ($member_lvs as $member_lv) : ?>
        <option value="<?php echo esc_attr($member_lv->ID); ?>" <?php selected($get_member_lv, $member_lv->ID); ?>><?php echo esc_html($member_lv->post_title) . ' (' . CountUser[$member_lv->ID] . ')'; ?></option>
      <?php endforeach; ?>
    </select>

    <input type="submit" class="button" value="Filter">

  </form>

<?php
}


add_action('pre_get_users', 'filter_users_by_favorite_cms', 99, 1);
function filter_users_by_favorite_cms($query)
{
  // This condition allows us to make sure that we won't modify any query that came from the frontend
  if (!is_admin()) {
    return;
  }

  global $pagenow;

  // This condition allows us to make sure that we're modifying a query that fires on the wp-admin/users.php page
  if ('users.php' === $pagenow) {

    // Let's check if our filter has been used
    if (isset($_GET['member_lv']) && $_GET['member_lv'] !== '0') {
      $meta_query = array(
        array(
          'key' => '_gamipress_member_lv_rank',
          'value' => $_GET['member_lv'],
          'compare' => '='
        )
      );

      $query->set('meta_query', $meta_query);
    }
  }

  return;
}
