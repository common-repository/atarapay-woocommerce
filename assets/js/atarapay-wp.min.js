jQuery(function (e) {
  "use strict";
  (function () {
    e(document.body).on(
      "change",
      "#woocommerce_at_atara_testmode",
      function () {
        var t = e("#woocommerce_at_atara_test_public_key").parents("tr").eq(0),
          o = e("#woocommerce_at_atara_test_secret_key").parents("tr").eq(0),
          _ = e("#woocommerce_at_atara_live_public_key").parents("tr").eq(0),
          c = e("#woocommerce_at_atara_live_secret_key").parents("tr").eq(0);
        e(this).is(":checked")
          ? (t.show(), o.show(), _.hide(), c.hide())
          : (t.hide(), o.hide(), _.show(), c.show());
      }
    ),
      e("#woocommerce_at_atara_testmode").change();
  })();
});
