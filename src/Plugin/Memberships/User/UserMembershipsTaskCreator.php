<?php

declare(strict_types=1);

namespace OM4\WooCommerceMixCheck\Plugin\Memberships\User;

use OM4\WooCommerceMixCheck\TaskHistory\Task\CreatorBase;

defined( 'ABSPATH' ) || exit;

/**
 * User Membership Task Creator.
 *
 * @since 2.10.0
 */
class UserMembershipsTaskCreator extends CreatorBase {

	/**
	 * {@inheritDoc}
	 */
	public static function resource_type() {
		return 'user_membership';
	}

	/**
	 * {@inheritDoc}
	 */
	public static function resource_name() {
		return __( 'User Membership', 'woocommerce-zapier' );
	}
}
