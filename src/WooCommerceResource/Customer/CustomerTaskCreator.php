<?php

declare(strict_types=1);

namespace OM4\WooCommerceMixCheck\WooCommerceResource\Customer;

use OM4\WooCommerceMixCheck\TaskHistory\Task\CreatorBase;

defined( 'ABSPATH' ) || exit;

/**
 * Customer Task Creator.
 *
 * @since 2.8.0
 */
class CustomerTaskCreator extends CreatorBase {

	/**
	 * {@inheritDoc}
	 */
	public static function resource_type() {
		return 'customer';
	}

	/**
	 * {@inheritDoc}
	 */
	public static function resource_name() {
		return __( 'Customer', 'woocommerce-zapier' );
	}
}
