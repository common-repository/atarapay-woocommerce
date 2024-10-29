var payWithAtaraPay = function () {
  (merchant = new Merchant({ api_key: at_wc_atara_params.public_key })),
    (handler = TrustPay.setup(merchant)),
    handler.initialize(
      at_wc_atara_params.customer_email,
      at_wc_atara_params.customer_phone,
      {
        type: 1,
        currency: at_wc_atara_params.currency,
        amount: 100 * at_wc_atara_params.amount,
        amount_fx: at_wc_atara_params.amount_fx,
        customer_firstname: at_wc_atara_params.customer_first_name,
        customer_lastname: at_wc_atara_params.customer_last_name,
        recipient: at_wc_atara_params.customer_phone,
        order_product_id: at_wc_atara_params.orderId,
        order_product_name: at_wc_atara_params.items,
        order_product_desc: at_wc_atara_params.items_desc,
        is_marketplace: at_wc_atara_params.is_marketplace,
        delivery_date: at_wc_atara_params.date,
        delivery_location: at_wc_atara_params.address,
        sp_id: at_wc_atara_params.sp_id,
        sp_commission: at_wc_atara_params.sp_commission,
        callback_url: at_wc_atara_params.url + "/wc-api/at_wc_atara_gateway",
        platform:"Woocommerce",
      }
    );
};
