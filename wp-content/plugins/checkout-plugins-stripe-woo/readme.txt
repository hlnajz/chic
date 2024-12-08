=== Stripe Payments For WooCommerce by Checkout Plugins ===
Contributors: brainstormforce
Tags: stripe, credit card, apple pay, google pay, express checkout
Requires at least: 5.4
Tested up to: 6.6
Stable tag: 1.10.0
Requires PHP: 5.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Use Stripe Payments for WooCommerce for secure credit card, Apple Pay, and Google Pay transactions. Boost sales with this free, easy-to-use plugin!

== Description ==

<strong>Accept credit card payments in your store with Stripe for WooCommerce.<strong>

The smoother the checkout process, the higher the chance of a sale, and offering multiple payment options is a proven way to boost sales. This is where Stripe WooCommerce comes in.

Stripe Payments For WooCommerce is a payment plugin that delivers a simple, secure way to accept credit card payments using the Stripe service.

With Stripe you can accept payments from several card brands, from large global networks like Visa and Mastercard to local networks like Cartes Bancaires in France or Interac in Canada. Stripe also supports American Express, Discover, JCB, Diners Club and UnionPay.

[youtube https://www.youtube.com/watch?v=CeI5cWJbhvA]

= Live Demo =

Visit [our demo site](https://stripe-demo.checkoutplugins.com) to see how this plugin works.

[Try it out on a free dummy site](https://bsf.io/checkout-plugins-stripe-woo-demo)

## OFFER ONE CLICK CHECKOUT WITH EXPRESS PAY ##

The future of ecommerce checkout is express pay options that make it fast to place orders because your buyers don’t need to fill out the checkout form. All your buyers have to do is click one button and their order is complete.

Stripe For WooCommerce makes it easy to start offering express payment options such as Apple Pay and Google Pay and fully customize the style, design, and location of these express pay buttons.

You will be able to visually style the express pay buttons to match your brand. Next you can choose where you want to show the express pay buttons, on the product page, on the cart page, and on the checkout page.

Stripe for WooCommerce offers complete flexibility without needing to understand a single line of code.

### ABOUT CHECKOUT PLUGINS ###

## WE ARE AN OFFICIAL STRIPE PARTNER ##

Checkout Plugins is an official Stripe partner!

We also make some of the most popular and loved WordPress & WooCommerce products.

## ABOUT US ##

Checkout Plugins is part of the Brainstorm Force family of products which are used on millions of websites.

Here are some of our products:

* **CartFlows**
Currently used by nearly 300,000 store owners to get more orders and increase the order value through our conversion optimized checkout replacement for WooCommerce, checkout order bumps, one-click post purchase upsells, and A/B split testing engine. [Try CartFlows](https://cartflows.com)

* **Cart Abandonment Recovery**
Currently used by nearly 400,000 store owners to capture lost revenue caused by buyers that don’t complete their checkout. Cart Abandonment Recovery captures these lost orders and automatically contacts the lost buyers to get them to complete their order. [Try Cart Abandonment Recovery](https://wordpress.org/plugins/woo-cart-abandonment-recovery/)

* **Variation Swatches For WooCommerce**
Instantly convert traditional dropdown attributes to visually appealing swatches. This plugin provides options to convert woocommerce variation attributes to swatches. We provide multiple options like image, color, label type swatches for both product page and shop page. [Try Variation Swatches For WooCommerce](https://wordpress.org/plugins/variation-swatches-woo/)

* **Astra Theme**
Currently used by nearly 2 million websites, Astra Theme is the most popular WordPress theme and is also the most popular WooCommerce theme. Stripe for WooCommerce was made to work perfectly with Astra Theme. [Visit Astra Theme](https://wpastra.com)

* **Starter Templates**
Currently used by nearly 2 million websites, Starter Templates offers hundreds of complete website templates, including over 50 website designs for WooCommerce stores. [Try Starter Templates](https://wordpress.org/plugins/astra-sites/)

* **SureCart**
Make ecommerce easy with a simple to use, all-in-one platform, that anyone can set up in just a few minutes! [Try SureCart](https://wordpress.org/plugins/surecart/)

As you can see, we know WooCommerce inside and out and help thousands of store owners build highly profitable stores everyday.

Stripe for WooCommerce supports WooCommerce Subscriptions where users can make recurring payments for products and services. It’s the only payment plugin you need for your store!

== Installation ==

1. Install the `Stripe Payments For WooCommerce by Checkout Plugins` either via the WordPress plugin directory or by uploading `checkout-plugins-stripe-woo.zip` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Make sure to disable caching on your checkout and thank you steps

== Frequently Asked Questions ==

= Does this plugin work with WooCommerce Subscriptions? =
Yes, the plugin supports all the functionality of WooCommerce Subscriptions.

= I have an existing subscription with another Stripe Payment Gateway plugin. How can I switch to your plugin without losing my existing subscriptions? =
You can easily switch to Stripe for WooCommerce from your existing plugin without losing your subscription.

Here are easy steps
1. Install our plugin like any other WordPress plugin
2. Follow the documentation [here](https://checkoutplugins.com/docs/stripe-api-settings/) to setup the stripe account keys
3. Enable Stripe payment method from our plugin. This allows all your future transactions to be processed through the new Stripe gateway you set with our plugin.
4. Disable the existing Stripe method from WooCommerce payment settings.
NOTE: NOT to deactivate your old payment gateway plugin. Your old subscription's renewal will be processed through the old plugin automatically.

= Does this plugin work with my theme? =
Yes, Stripe for WooCommerce will work with all themes. If you run into any trouble, please let us know and we will be happy to resolve any issues.

= Do you offer support for this plugin? =
Yes, this plugin is fully supported. You can open a request here on the plugin page or visit our website to fill out our support request form.

* **Support That Cares!**

We understand the need for a quality product backed by dedicated support that cares. We are here to listen to all your queries and help you make the most out of our plugin.

[Need help? We are just a click away!](https://checkoutplugins.com/support/)

== Screenshots ==

1. API Settings
2. Card Payments Settings
3. Express Checkout - Admin Settings
4. Express Checkout Button on Checkout Page

== Changelog ==

= 1.10.0 - TUESDAY, 27th August 2024 =
* New: Enabled Klarna payment method support for several more countries.
* Improvement: Improved the country based conditions for Klarna for better availability on checkout page.
* Improvement: Improved the overall design and functionality of plugin's onboarding process.
* Improvement: Improved the overall notices structures for future compatibility of plugin.

= 1.9.3 - MONDAY, 19th August 2024 =
* Fix: Fixed an issue where the orders status was getting set as pending when the guest checkout option is disabled.
* Fix: Resolved conflicts of payment's setting menu between WooPayments plugin.

= 1.9.2 - MONDAY, 5th August 2024 =
* Improvement: Added a notice about the upcoming deprecation of Giropay. [Read more](https://support.stripe.com/questions/availability-of-giropay-june-2024-update)
* Improvement: Improved error handling while loading the Payment Element method.
* Security Fix: Hardened the security of the plugin suggested by PatchStack.
* Fix: Fixed an issue where the Payment Elements appeared on the checkout page despite no payment methods being enabled.

= 1.9.1 - WEDNESDAY, 10th July 2024 =
* Improvement: Default values for few express checkout settings fields updated.
* Improvement: Improved the conditions to display the separator on checkout page. Now you can hide the separator text by keeping the field empty.
* Fix: Corrected Express checkout button alignment and width issue on checkout page.
* Fix: Addressed the issue where the admin notice for Payment Elements was not getting dismissed.

= 1.9.0 - WEDNESDAY, 3rd July 2024 =
* New: Introduced the "[Payment Element](https://checkoutplugins.com/docs/smarter-way-to-display-payment-methods/)," a smarter way to display payment methods on the checkout page.
* Improvement: Improved the Express Checkout implementation with link pay support and button customization options.
* Fix: Fixed the JavaScript error for the "Add Payment Method" option on the My Account page.
* Fix: Corrected the positioning of the express checkout button on product pages.
* Fix: Resolved hide/show issues with backend settings for product page options.
* Fix: Addressed the issue where the Button Width backend option was hidden in some cases.
* Fix: Fixed the issue where the express checkout separator text was not displayed on the Checkout page in some cases.
* Fix: Ensured payment options are displayed on the WooCommerce Blocks checkout page.
* Fix: Fixed UI issues with the sticky button position on mobile devices.
* Fix: Resolved error notices for undefined variables in local gateways.

= 1.8.1 - THURSDAY, 2nd May 2024 =
* Improvement: Modified the statement descriptor param for card payments as per the guidelines by stripe.

= 1.8.0 - THURSDAY, 28TH March 2024 =
* New: WooCommerce Gutenberg checkout block support for SEPA payment gateway.
* New: WooCommerce Gutenberg checkout block support for WeChat payment gateway.
* New: WooCommerce Gutenberg checkout block support for P24 payment gateway.
* New: WooCommerce Gutenberg checkout block support for Bancontact payment gateway.
* Fix: The statement_descriptor parameter key is changed to statement_descriptor_suffix as per the latest update from the Stripe.

= 1.7.0 - MONDAY, 23RD JANUARY 2024 =
* New: WooCommerce Gutenberg checkout block support for iDEAL payment gateway.
* New: WooCommerce Gutenberg checkout block support for Klarna payment gateway.
* New: WooCommerce Gutenberg checkout block support for Alipay payment gateway.

= 1.6.1 - THURSDAY, 4TH JANUARY 2024 =
* Improvement: Enhanced Alipay payment gateway now supports additional countries for broader accessibility.
* Fix: Updated strings in plugin for better user experience.

= 1.6.0 - THURSDAY, 28TH DECEMBER 2023 =
* New: Adding WooCommerce Gutenberg checkout block support for Credit Card payment method

= 1.5.0 - MONDAY, 16TH OCTOBER 2023 =
* New: Introducing automated webhook creation for streamlined integration.
* Improvement: Enhanced Klarna payment gateway now supports additional countries for broader accessibility.

= 1.4.14 - TUESDAY, 4TH APRIL 2023 =
* Fix: Apple pay domain verification failing.

= 1.4.13 - WEDNESDAY, 29TH MARCH 2023 =
* Fix: Stripe zero currencies getting changed 100 times.

= 1.4.12 - WEDNESDAY, 15TH MARCH 2023 =
* Improvement: Refactored code for plugin security.

= 1.4.11 - SATURDAY, 21ST JANUARY 2023 =
* Improvement: Hardened the security of the plugin.
* Fix: Console warning of Stripe library.

= 1.4.10 - WEDNESDAY, 28TH DECEMBER 2022 =
* Fix: Order Bump product price was not getting added to express checkout.

= 1.4.9 - MONDAY, 5TH DECEMBER 2022 =
* Fix: Express checkout compatibility with CartFlows.
* Fix: Add payment method was not working.

= 1.4.8 - TUESDAY, 15TH NOVEMBER 2022 =
* Fix: WooCommerce HPOS issues.

= 1.4.7 - TUESDAY, 8TH NOVEMBER 2022 =
* Improvement: Added filter 'cpsw_exclude_frontend_scripts' to prevent frontend script loading.
* Improvement: Added WooCommerce HPOS compatibility.
* Fix: Updated WooCommerce deprecated function.

= 1.4.6 - THURSDAY, 9TH JUNE 2022 =
* Fix: Add payment method console error.
* Fix: Redirecting to onboarding wizard on plugin reactivation even if the Stripe is connected.
* Fix: Inconsistent order notes for the refund process.

= 1.4.5 - MONDAY, 6TH JUNE 2022 =
* Improvement: Added free trial/zero amount subscription support.

= 1.4.4 - FRIDAY, 22ND APRIL 2022 =
* Improvement: Added support for older PHP versions.
* Improvement: Modified display strings.

= 1.4.3 - THURSDAY, 21ST APRIL 2022 =
* Fix: Syntax error of older PHP versions.

= 1.4.2 - TUESDAY, 29TH MARCH 2022 =
* Improvement: Added webhook secret step in the onboarding wizard.
* Improvement: Added translation support for card declined messages.
* Fix: Failed payment automatically retries.
* Fix: Failed payment order notes improvement.

= 1.4.1 - TUESDAY, 15TH MARCH 2022 =
* New: Express checkout class layout support.
* Improvement: Added localization for Stripe error messages.
* Improvement: Added compatibility with popular themes.
* Fix: Express checkout console error.
* Fix: Express checkout's broken admin preview.

= 1.4.0 - TUESDAY, 22ND FEBRUARY 2022 =
* New: Supports SEPA payment method.
* New: Supports WeChat payment method.
* Fix: Onboarding menu icon appears even if stripe is connected.
* Fix: Critical error with webhook description fixed.
* Fix: 3ds cards issue on pay order and change payment methods page.

= 1.3.1 - TUESDAY, 8TH FEBRUARY 2022 =
* Fix: Klarna payment method was showing on the checkout page when disabled.

= 1.3.0 - TUESDAY, 1ST FEBRUARY 2022 =
* New: Supports Klarna payment method.
* New: Supports Przelewy24 (P24) payment method.
* New: Supports Bancontact payment method.
* New: Added onboarding wizard.
* New: Display stripe fees on edit order page.
* Improvement: Added localization support.
* Improvement: Customizable Express Checkout buttons.

= 1.2.1 - THURSDAY, 20TH JANUARY 2022 =
* Fix: Add payment method was not working.

= 1.2.0 – TUESDAY, 4TH JANUARY 2022 =
* New: Supports Alipay payment method.
* New: Supports iDEAL payment method.
* Improvement: More customization options for Express Checkout.
* Improvement: Webhook integration for multiple events - charge.refunded, charge.dispute.created, charge.dispute.closed, payment_intent.succeeded, payment_intent.amount_capturable_updated, payment_intent.payment_failed, review.opened, review.closed.

= 1.1.1 – WEDNESDAY, 22ND DECEMBER 2021 =
* Fix: Express Checkout buttons were not appearing in live mode.

= 1.1.0 – TUESDAY, 21ST DECEMBER 2021 =
* New: Express Checkout.

= 1.0.0 – TUESDAY, 23RD NOVEMBER 2021 =
* Initial release.
