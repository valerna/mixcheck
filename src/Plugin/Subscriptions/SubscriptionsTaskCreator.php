<?php

declare(strict_types=1);

namespace OM4\WooCommerceMixCheck\Plugin\Subscriptions;

use OM4\WooCommerceMixCheck\TaskHistory\Task\CreatorBase;

defined( 'ABSPATH' ) || exit;

/**
 * Subscription Task Creator.
 *
 * @since 2.8.0
 */
class SubscriptionsTaskCreator extends CreatorBase {

	/**
	 * {@inheritDoc}
	 */
	public static function resource_type() {
		return 'subscription';
	}

	/**
	 * {@inheritDoc}
	 */
	public static function resource_name() {
		return __( 'Subscription', 'woocommerce-zapier' );
	}
}
