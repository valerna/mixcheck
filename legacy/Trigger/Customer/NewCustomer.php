<?php

namespace OM4\MixCheck\Trigger\Customer;

use OM4\MixCheck\Exception\MissingDataException;
use OM4\MixCheck\Logger;
use OM4\MixCheck\Payload\Customer as Payload;
use OM4\MixCheck\Trigger\Base;

defined( 'ABSPATH' ) || exit;

/**
 * Configure the "New Customer" trigger
 *
 * @deprecated 2.0.0
 */
class NewCustomer extends Base {

	/**
	 * Constructor
	 */
	public function __construct() {

		// Prefix the trigger key with wc. to denote that this is a trigger that relates to a WooCommerce order.
		$this->trigger_key = 'wc.new_customer';

		$this->trigger_title = __( 'New Customer', 'woocommerce-zapier' );

		$checkout_signup_enabled   = get_option( 'woocommerce_enable_signup_and_login_from_checkout' ) === 'yes' ? true : false;
		$my_account_signup_enabled = get_option( 'woocommerce_enable_myaccount_registration' ) === 'yes' ? true : false;

		$this->trigger_description = __( 'Triggers if a customer chooses to register for an account.', 'woocommerce-zapier' );
		if ( $checkout_signup_enabled ) {
			$this->trigger_description .= __( '<br />Occurs if a customer registers during the checkout process when placing an order.', 'woocommerce-zapier' );
		}

		if ( $my_account_signup_enabled ) {
			$this->trigger_description .= __( '<br />Occurs if a customer registers via the my account page.', 'woocommerce-zapier' );
		}

		// Registration is completely disabled, so show a warning message.
		if ( ! $checkout_signup_enabled && ! $my_account_signup_enabled ) {
			// Translators: %s: URL of the WooCommerce Settings page.
			$this->trigger_description .= sprintf( __( '<br />Warning: this trigger can only occur if your <a href="%s">WooCommerce settings</a> have the <em>Enable registration on the "Checkout" page</em> and/or <em>Enable registration on the "My Account" page</em> setting(s) enabled.', 'woocommerce-zapier' ), admin_url( 'admin.php?page=wc-settings&tab=account' ) ) . '</span>';
		}

		$this->sort_order = 2;

		// WooCommerce action(s)
		// This hook accepts 3 parameters, but we only need the first one.
		// The first parameter is the customer ID (an integer).
		// The second parameter is an array of the new customer's data, which we don't need.
		// The third parameter is a boolean for whether or not a new password was generated, which we don't need.
		$this->actions['woocommerce_created_customer'] = 1;

		parent::__construct();
	}

	/**
	 * Collect assign and convert Customer data prior to sending to MixCheck.
	 *
	 * @param array  $args        Customer ID.
	 * @param string $action_name Name of the WooCommerce hook/action that caused the Customer trigger to trigger.
	 *
	 * @return array|bool|false
	 * @throws MissingDataException If customer data isn't set correctly.
	 */
	public function assemble_data( $args, $action_name ) {

		if ( $this->is_sample() ) {
			// The webhook/trigger is being tested.
			// Send the store's most recent customer, or if that doesn't exist then send the currently logged in user's details.
			$customers = get_users( 'role=customer&orderby=ID&order=DESC&number=1' );
			if ( empty( $customers ) ) {
				// Use the currently logged in user's details.
				$customer_id = wp_get_current_user()->ID;
			} else {
				// Use previous customer.
				$customer_id = $customers[0]->ID;
			}
			$customer = Payload::from_sample();
		} else {
			$customer_id = $args[0];
			$customer    = new Payload();
		}

		$customer_data = get_user_by( 'id', $customer_id );

		if ( ! $customer_data ) {
			// No user/customer information found.
			return false;
		}

		// Gather customer's data so it can be sent to MixCheck.
		$customer->id              = $customer_data->ID;
		$customer->first_name      = $customer_data->first_name;
		$customer->last_name       = $customer_data->last_name;
		$customer->email_address   = $customer_data->user_email;
		$customer->username        = $customer_data->user_login;
		$customer->paying_customer = (bool) $customer_data->paying_customer;

		// Full addresses only available for orders.
		$customer->billing_address  = '';
		$customer->shipping_address = '';

		// Important: the following fields WILL be empty if this customer hasn't
		// placed an order yet, or hasn't added address details to their
		// account.
		$user_meta_fields = array(
			'billing_first_name',
			'billing_last_name',
			'billing_company',
			'billing_email',
			'billing_phone',
			'billing_address_1',
			'billing_address_2',
			'billing_city',
			'billing_postcode',
			'billing_state',
			'billing_country',
			'shipping_first_name',
			'shipping_last_name',
			'shipping_company',
			'shipping_address_1',
			'shipping_address_2',
			'shipping_city',
			'shipping_postcode',
			'shipping_state',
			'shipping_country',
		);

		$customer_meta = get_user_meta( $customer_id );

		foreach ( $user_meta_fields as $field ) {
			$customer->$field = isset( $customer_meta[ $field ][0] ) ? $customer_meta[ $field ][0] : '';
		}

		/* Country & state names */

		// Filling country names.
		if ( ! empty( $customer->billing_country ) && isset( WC()->countries->countries[ $customer->billing_country ] ) ) {
			$customer->billing_country_name = WC()->countries->countries[ $customer->billing_country ];
		} else {
			$customer->billing_country_name = '';
		}
		if ( ! empty( $customer->shipping_country ) && isset( WC()->countries->countries[ $customer->shipping_country ] ) ) {
			$customer->shipping_country_name = WC()->countries->countries[ $customer->shipping_country ];
		} else {
			$customer->shipping_country_name = '';
		}

		// Filling state names.
		if ( ! empty( $customer->billing_state ) && isset( WC()->countries->states[ $customer->billing_country ][ $customer->billing_state ] ) ) {
			$customer->billing_state_name = WC()->countries->states[ $customer->billing_country ][ $customer->billing_state ];
		} else {
			$customer->billing_state_name = '';
		}
		if ( ! empty( $customer->shipping_state ) && isset( WC()->countries->states[ $customer->shipping_country ][ $customer->shipping_state ] ) ) {
			$customer->shipping_state_name = WC()->countries->states[ $customer->shipping_country ][ $customer->shipping_state ];
		} else {
			$customer->shipping_state_name = '';
		}

		( new Logger() )->debug( 'Customer #%s: Assembled customer data.', $customer->id );
		return $customer->to_array();
	}
}
