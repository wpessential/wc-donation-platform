<?php
/**
 * This class implements integrations with other WooCommerce Extensions
 */

class WCDP_Integrator
{
    /**
     * Bootstraps the class and hooks required actions & filters
     */
    public static function init() {
        //Integration with WooCommerce Subscriptions
        //https://woocommerce.com/products/woocommerce-subscriptions/
        include_once 'woocommerce-subscriptions/class-wcdp-subscriptions.php';
        WCDP_Subscriptions::init();

        //Integration with WooCommerce PDF Invoices & Packing Slips
        //https://wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/
        include_once 'woocommerce-pdf-invoices-packing-slips/class-wcdp-pdf-Invoices.php';
        WCDP_Pdf_Invoices::init();

		//Add support for Stripe & PayPal Express Checkout
		//https://wordpress.org/plugins/woocommerce-gateway-stripe/
		//https://wordpress.org/plugins/woocommerce-paypal-payments/
		$stripe_active = in_array('woocommerce-gateway-stripe/woocommerce-gateway-stripe.php', apply_filters('active_plugins', get_option('active_plugins')));
		$paypal_active = in_array('woocommerce-paypal-payments/woocommerce-paypal-payments.php', apply_filters('active_plugins', get_option('active_plugins')));
		if ($stripe_active || $paypal_active) {
			include_once 'express-checkout/class-wcdp-express-checkout.php';
			new WCDP_Express_Checkout();
		}
		if ($paypal_active) {
			//PayPal Payment Gateway does not load with an empty checkout
			add_filter('woocommerce_cart_get_cart_contents_total', 'WCDP_Integrator::cart_contents_total', 10, 1);
		}

		//Integration with Subscriptions for WooCommerce
		//https://wordpress.org/plugins/subscriptions-for-woocommerce/
		include_once 'subscriptions-for-woocommerce/class-wcdp-subscriptions-for-woocommerce.php';
		WCDP_Subscriptions_For_WooCommerce::init();
    }

	/**
	 * Return true if cart contains a WooCommerce Subscriptions or Subscriptions for WooCommerce product
	 * @return bool
	 */
	public static function wcdp_contains_subscription(): bool
	{
		if (class_exists('WC_Subscriptions_Cart')) {
			return  WC_Subscriptions_Cart::cart_contains_subscription();
		} else if (function_exists('mwb_sfw_is_cart_has_subscription_product')) {
			return mwb_sfw_is_cart_has_subscription_product();
		} else {
			return false;
		}
	}

	/**
	 * PayPal Payment Plugin only loads when the cart total != 0
	 * @param $total
	 * @return float|mixed
	 */
	public static function cart_contents_total($total) {
		if ($total == 0 && WCDP_FORM::wcdp_has_donation_form()) {
			//Return very small amount (rounded to 0 in checkout)
			return 4.9E-324;
		}
		return $total;
	}
}
