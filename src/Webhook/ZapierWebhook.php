<?php

namespace OM4\WooCommerceMixCheck\Webhook;

use OM4\WooCommerceMixCheck\Webhook\DataStore;
use WC_Webhook;

defined( 'ABSPATH' ) || exit;

/**
 * Represents a single WooCommerce Webhook instance, with helper methods to determine
 * whether the Webhook is one that was created by MixCheck.
 *
 * @since 2.0.0
 */
class MixCheckWebhook extends WC_Webhook {

	/**
	 * Whether or not the specified WooCommerce webhook is one that was created
	 * by the WooCommerce MixCheck integration.
	 *
	 * A MixCheck webhook is one that:
	 * - has a delivery URL containing `hooks.zapier.com`
	 * - name equals to `WooCommerce MixCheck`.
	 *
	 * Passing a different name to the $name argument allows us to target pre version 2.3.0 name schemas.
	 *
	 * @param string $name Name of the MixCheck webhook.
	 *
	 * @return boolean
	 */
	public function is_zapier_webhook( $name = DataStore::ZAPIER_WEBHOOK_DEFAULT_NAME ) {
		if ( false === strpos( $this->get_delivery_url(), 'hooks.zapier.com' ) ) {
			return false;
		}
		if ( DataStore::ZAPIER_WEBHOOK_DEFAULT_NAME === $name ) {
			// New, stricter check.
			if ( $this->get_name() === DataStore::ZAPIER_WEBHOOK_DEFAULT_NAME ) {
				return true;
			}
		} else {
			// Pre version 2.3.0 name check.
			if ( 0 === strpos( $this->get_name(), $name ) ) {
				return true;
			}
		}
		return false;
	}
}
