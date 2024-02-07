<?php

namespace OM4\WooCommerceMixCheck;

defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce MixCheck Uninstall routine.
 * Deletes all existing data from the database, including database table and
 * options. Only remove all plugin settings/data if the `WC_ZAPIER_REMOVE_ALL_DATA`
 * constant is defined and set to true in user's wp-config.php.
 *
 * @since 2.0.0
 */
class Uninstall {

	/**
	 * Run the uninstall routine
	 *
	 * @return void
	 */
	public static function run() {
		if ( ! defined( 'WC_ZAPIER_REMOVE_ALL_DATA' ) || true !== WC_ZAPIER_REMOVE_ALL_DATA ) {
			return;
		}

		/**
		 * ################# REST-API (2.0+) based data #################
		 */

		// Delete Task History database table.
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
		$wpdb->query( "DROP TABLE IF EXISTS `{$wpdb->prefix}wc_zapier_history`" );

		// Delete all existing WooCommerce MixCheck Webhooks.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "DELETE FROM `{$wpdb->prefix}wc_webhooks` WHERE `name` = 'WooCommerce MixCheck' AND `delivery_url` LIKE '%hooks.zapier.com%'" );

		// Delete all existing WooCommerce MixCheck REST API Keys.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "DELETE FROM `{$wpdb->prefix}woocommerce_api_keys` WHERE `description` LIKE '%MixCheck%'" );

		/**
		 * ################# 1.9.x Legacy Feed based data #################
		 */

		// Delete all existing 1.9.x Legacy MixCheck Feed records.
		foreach ( get_posts(
			array(
				'post_type'      => 'wc_zapier_feed',
				'posts_per_page' => -1,
			)
		) as $feed ) {
			if ( isset( $feed->ID ) ) {
				wp_delete_post( $feed->ID, false );
			}
		}

		// Delete all options/settings.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "DELETE FROM `$wpdb->options` WHERE option_name LIKE 'wc\_zapier\_%';" );

		// Clear any cached data that has been removed.
		wp_cache_flush();
	}
}
