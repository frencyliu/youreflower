(function($) {

  $(document).ready(function() {
    let old_coupon = '';
    $('input[name="yf_coupon"]').on('change', function() {
      $('body').trigger('update_checkout', {
              update_shipping_method: false
            });
      console.log('origin', old_coupon);
      console.log('change to', $(this).val());
      yf_remove_coupon(old_coupon)
      setTimeout(() => {
        yf_apply_coupon()
        old_coupon = $(this).val();
      }, 1000);

    });


    function yf_apply_coupon() {
      const data = {
        security: wc_checkout_params.apply_coupon_nonce,
        coupon_code: $('input[name="yf_coupon"]:checked').val()
      };
      $.ajax({
        type: 'POST',
        url: wc_checkout_params.wc_ajax_url.toString().replace('%%endpoint%%', 'apply_coupon'),
        data: data,
        success: function(code) {
          $('.woocommerce-error, .woocommerce-message').remove();

          if (code) {
            $('form.checkout_coupon').before(code);
            $('form.checkout_coupon').slideUp();

            $(document.body).trigger('update_checkout', {
              update_shipping_method: false
            });
            $(document.body).trigger('applied_coupon_in_checkout', [data.coupon_code]);


          }
        },
        dataType: 'html'
      });
    }

    function yf_remove_coupon(coupon) {
      if (coupon === undefined || coupon === '') return;
      const data = {
        security: wc_checkout_params.remove_coupon_nonce,
        coupon: coupon
      };

      $.ajax({
        type: 'POST',
        url: wc_checkout_params.wc_ajax_url.toString().replace('%%endpoint%%', 'remove_coupon'),
        data: data,
        success: function(code) {
          $('.woocommerce-error, .woocommerce-message').remove();

          if (code) {
            $( 'form.woocommerce-checkout' ).before( code );

            $(document.body).trigger('removed_coupon_in_checkout', [data.coupon]);
            $(document.body).trigger('update_checkout', {
              update_shipping_method: false
            });

            // Remove coupon code from coupon field
            $('form.checkout_coupon').find('input[name="coupon_code"]').val('');
          }
        },
        error: function(jqXHR) {
          if (wc_checkout_params.debug_mode) {
            /* jshint devel: true */
            console.log(jqXHR.responseText);
          }
        },
        dataType: 'html'
      });
    }
  })
})(jQuery)