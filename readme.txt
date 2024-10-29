=== Plugin Name ===
Contributors: tosinbot, atarapay, xwebyna, damilare4atara
Plugin Name: AtaraPay WooCommerce
Plugin URI: https://plugins.atarapay.com/docs/woocommerce/
Tags: atarapay, atara, payment, escrow, money, online, transaction, ecommerce
Author URI: https://www.atarapay.com/
Author: AtaraPay
Requires at least: 4.6
Tested up to: 6.4
Stable tag: 2.0.13
Requires PHP: 5.2.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html


== Description ==

[AtaraPay​](https://www.atarapay.com/) is a web and mobile tool used by seller and buyer for protection during any online or offline commercial transaction through funds held in escrow by a trusted third-party. The prevalence of the use of cash on delivery is a payment method that benefits only the buyer leaving eCommerce sellers with little option to collect payment before delivery. AtaraPay helps to resolve this by creating an escrow-based payment method, where buyer pays into escrow and seller is notified to make delivery. On successful delivery, the buyer inspects the goods or service and expects to accept or reject it using our mobile and web app. 

Deploying this extension onto your Wordpress website (single or multi- vendor), provides a seamless and simple way of automating payment with our escrow solution. When a shopper gets to the checkout page, AtaraPay is listed as one of the payment options and selected for payment via credit or debit card, bank transfer and USSD. When payment is made, it is held in escrow on behalf of both seller and buyer until the buyer inspects and accepts that the order is in expected condition. For details on how our escrow solution works, please watch the ​[AtaraPay escrow video](https://youtu.be/ybsb-D1as8s)​. 

AtaraPay automates and extremely simplifies the escrow process. It also exposes escrow status APIs that enable seamless integration between AtaraPay and any delivery app service. It automates the delivery or cancellation processes making the escrow process very user friendly and effective. For details on our APIs, visit ​[AtaraPay API Documentation](http://plugins.atarapay.com/docs/woocommerce/)​. 

AtaraPay will increase prepayment for your Magento store and improve the credibility of your business since shoppers are assured by your willingness to allow them inspect the goods before you are credited. Conversely, you are comforted knowing that the shopper has genuine intent to buy since funds are paid before delivery. 


== ACCOUNT & PRICING ==

To use the extension, you have to sign up as an Individual or Business Seller on AtaraPay by clicking here --> ​[AtaraPay Seller Registration](https://app.atarapay.com/#/register/seller) or from our mobile app on Play Store or App Store​ (search word &quot;atarapay&quot;). Setup or subscription fees are not applicable for using this extension. Visit the eCommerce Website Escrow Service section under the ​[AtaraPay Pricing Page](https://www.atarapay.com/pricing)​ for details on our simple pricing model which is essentially a transaction fee of 1.5% of the cart value or a maximum of NGN2,000. We do not earn unless you do!


== ESCROW FEATURES ==

- Supports single- and multi-vendor payout
- Payment is held in escrow and released to the seller only after the buyer accepts.
- You have the option to set a cancellation fee, if the buyer cancels later than the SLA set.
- Provides ability for sellers to set the refund method by choosing either cash refund or product replacement.
- Supports automatic payout of product amount to the seller and commission to the marketplace operator.
- Provides sellers the ability to transfer payment of the payment gateway fees to the buyer.
- Supports card, bank transfer and USSD payment methods.
- Supports Naira (NGN) and Dollars (USD) currencies paid via all Mastercard, VISA, Eurocard and Verve.
- Seamless addition of payment gateways that eliminates the need to reinstall the extension.
- All funds paid into AtaraPay escrow are insured by our Insurance partners. So, both the buyer and seller are equally protected.
- Supports cancellation and delivery from WooComerce without login into AtaraPay
- Supports commission-based transaction between the merchant, buyer and a service provider
- Reflects the AtaraPay escrow statuses in the WooCommerce dashboards of the buyer and merchant
- Supports cross-border payouts into your Wise and PayPal account

== SECURITY INFORMATION ==

The extension does not store card details on the WooCommerce or AtaraPay database but parses only cart information to the payment gateway. All payment gateways encrypt user data in accordance with PCI guidelines and transmit to the customer&#39;s bank to authorize account debit.

== MODULE DEMO ==

**http://woocommerce.atarapay.com/**

== Screenshots ==

1. Plugin Configuration Page
2. Plugin Configuration Page
3. Plugin Configuration Page
4. Plugin Configuration Page
5. Payment Method on Checkout Page
6. After Placing the Order Page
7. Atarapay Payment options
8. AtaraPay Transaction Details Page
9. Payment Success Page
10. Add Service Providers under the AtaraPay Settings in WooCommerce
11. Associate Service Provider and Commission to the Product

== Installation ==

**Stage 1**

1. Register as an Individual Seller or Business Seller account on the AtaraPay test environment at http://staging.atarapay.com/#/register/seller/.
2. Make sure you are able to successfully login to access your account.
3. Go to the &quot;API&quot; tab on the sidebar, click &quot;API Key&quot; and copy the Public and Private keys as shown in the screenshot

**Stage 2**

4. Download the zip file from WordPress Marketplace
5. Login to your WordPress admin dashboard
6. On the left pane, navigate to Plugins \&gt; Add New. Click on the **Add New** button to upload the zip file and hit the **Install Now** button to install the plugin
7. Once it&#39;s successfully installed, click the **Activate** button to activate the plugin
8. On the left pane, navigate to WooCommerce \&gt; Settings and click the &quot;Payments&quot; tab
9. Click on the **Setup** or **Manage** button in front of the AtaraPay plugin block.
  - Your Callback URL – Copy the callback URL provided. Then login to your AtaraPay seller dashboard, navigate to the human icon at the top right \&gt; Settings \&gt; Callback URL. Click open the accordion and paste the URL into the text field and hit the **Save** button to save.
  - Enable/Disable – Check the box to enable AtaraPay Payment Gateway.
  - Test Mode – Check the box to enable Test Mode if you are using a test key for setup purpose, once you are done testing remember to uncheck the Test Mode to enable LIVE usage.
  - Test Public Key – Input the Public Key generated from your http://staging.atarapay.com seller account under the API tab
  - Test Private Key – Input the Test Private Key generated from your http://staging.atarapay.com seller account under the API tab.
  - MarketPlace – If you are a marketplace seller, check the box Enable MarketPlace Mode.
  - Split Payment – Check &quot;Allow Service Provider Payout&quot;, if you want to include service providers in commission-based transactions. If not, leave unchecked.

**Stage 3**

1. To add service providers, navigate to AtaraPay tab on the left pane of your WordPress admin dashboard. Here you can add service providers and also see service providers that were already created by you on your AtaraPay dashboard. (**Please note that you must first add your own account in your AtaraPay dashboard in order to see the list of service providers on this page).**
2. To add your own bank account, login to your seller account at [http://staging.atarapay.com](http://staging.atarapay.com/), then navigate to Payout \&gt; Payout Details and click the **Add Bank Details** button to add your own bank account.
3. You can add service providers from your AtaraPay or WordPress admin dashboards.
4. To associate service provider and their commission per product, login to your WordPress admin dashboard, then navigate to Product \&gt; Add New \&gt; General to add a new product or Product \&gt; All Products to edit an existing one.

**Go-Live**

- Live Public Key – Once you are done testing and ready to go live, reference step 9 above and do the following;
  - Uncheck Test Mode
  - Replace the test keys with the live Public and Private Keys generated from your live seller account which you are to create on https://app.atarapay.com seller account


== Changelog ==

= 1.0 =
* Initial release

= 1.1.0 =
* Fix undefined phone number issue

= 1.1.1 =
* Fix undefined phone number issue
* Code clean up

= 2.0.0 =
- Include split payment to service provider and merchant
- Ability to choose in the Payment settings page, if merchant&#39;s wants split payment with service providers
- Ability to add service providers
- Ability to set commission per product in the Product settings page
- Ability to cancel the transaction from WooCommerce without login into AtaraPay
- Ability to set the delivery status of the transaction from WooCommerce without login into AtaraPay
- Reflects the AtaraPay escrow statuses in the WooCommerce dashboards of the buyer and merchant using the provided Callback URL

= 2.0.1 =
- Enabled the ability for users registered as a Marketplace Operator on AtaraPay to seamlessly integrate to the plug-in
- Updated the AtaraPay escrow statuses in the WooCommerce dashboards of the buyer and merchant using the provided Callback URL
- Code clean up

= 2.0.2 =
- Fixed issue with USD conversion
- Code clean up

= 2.0.3 =
- Add compatibility with WordPress 6.2
- Added compatibility with WooCommerce 7.8.0
- Fixed issue with Paystack plugin

= 2.0.4 =
- Added compatibility with WordPress 6.3
- Added compatibility with WooCommerce 7.8.0
- Send products dimensions, weight, details to the AtaraPay API
- Added support for foreign currencies

= 2.0.5 =
- Fix minor bugs on checkout page

= 2.0.6 =
- Implement debug mode for qa team

= 2.0.7 =
- Add missing attributes to product api


= 2.0.8 && 2.0.9 && 2.0.10 =
- Add support for virtual products

= 2.0.11 =
- Add support for HIPOS
- Improve compatibility with WooCommerce 8.2+
- Include a way to submit USD service provider


= 2.0.12 =
- Add a way to cancel order on WooCommerce

= 2.0.13 =
- Fix issue with Woocommerce Callback API
