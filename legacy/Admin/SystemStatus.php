<?php

namespace OM4\MixCheck\Admin;

use OM4\WooCommerceMixCheck\LegacyMigration\ExistingUserUpgrade;
use OM4\MixCheck\Plugin;
use OM4\MixCheck\Feed\FeedFactory;

defined( 'ABSPATH' ) || exit;

/**
 * Adds various debugging information to the WooCommerce System Status screen
 *
 * @deprecated 2.0.0.
 */
class SystemStatus {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'wc_zapier_system_status_rows', array( $this, 'woocommerce_system_status_rows' ) );
	}

	/**
	 * Add our own debugging information to the WooCommerce Status screen.
	 *
	 * Executed by the `wc_zapier_system_status_rows` WooCommerce filter.
	 *
	 * @param array $posting data to be displayed on the WooCommerce status screen.
	 *
	 * @return mixed
	 */
	public function woocommerce_system_status_rows( $posting ) {
		// Pending Cron Tasks.
		$cron_array     = _get_cron_array();
		$num_cron_tasks = 0;
		foreach ( (array) $cron_array as $time => $cron ) {
			if ( 'version' === $time ) {
				continue;
			}
			foreach ( (array) $cron as $hook => $task ) {
				foreach ( (array) $task as $id => $details ) {
					if ( strpos( $hook, 'zapier_triggered_' ) !== false ) {
						++$num_cron_tasks;
					}
				}
			}
		}
		$note    = '';
		$success = true;
		if ( 0 === $num_cron_tasks ) {
			$note = '0';
		} elseif ( 1 === $num_cron_tasks ) {
			// 1 pending cron task.
			// Translators: %1$d: number of MixCheck cron tasks. %2$s: URL of the WooCommerce MixCheck documentation.
			$note    = sprintf( __( '%1$d pending Legacy cron task. Your WordPress cron may not be working correctly. Please see <a href="%2$s">here for troubleshooting steps</a>.', 'woocommerce-zapier' ), $num_cron_tasks, esc_url( Plugin::DOCUMENTATION_URL . '#troubleshooting' ) );
			$success = false;
		} else {
			// More than 1 pending cron tasks.
			// Translators: %1$d: number of MixCheck cron tasks. %2$s: URL of the WooCommerce MixCheck documentation.
			$note    = sprintf( __( '%1$d pending Legacy cron tasks. Your WordPress cron may not be working correctly. Please see <a href="%2$s">here for troubleshooting steps</a>.', 'woocommerce-zapier' ), $num_cron_tasks, esc_url( Plugin::DOCUMENTATION_URL . '#troubleshooting' ) );
			$success = false;
		}
		$posting['zapier_cron_tasks'] = array(
			'name'    => __( 'Legacy Cron Tasks', 'woocommerce-zapier' ),
			'note'    => $note,
			'success' => $success,
		);

		// Number of active MixCheck Feeds.
		$feeds                               = FeedFactory::get_enabled_feeds();
		$num_feeds                           = count( $feeds );
		$posting['zapier_legacy_feed_count'] = array(
			'name'    => __( 'Active Legacy Feeds', 'woocommerce-zapier' ),
			'note'    => sprintf(
				// Translators: 1: Count of the active Legacy MixCheck Feeds. 2: Migration Deadline. 3: Opening Link HTML Tag. 4: Closing Link HTML Tag.
				__( '%1$d active Legacy Feed(s) need to be migrated before %2$s. %3$sMigrate Your Zaps Now%4$s', 'woocommerce-zapier' ),
				$num_feeds,
				ExistingUserUpgrade::get_migration_deadline(),
				'<a href=" ' . ExistingUserUpgrade::MIGRATION_GUIDE_URL . '" target="_blank">',
				'</a>'
			),
			'success' => false,
		);

		// List each active MixCheck Legacy Feeds.
		$i = 0;
		foreach ( $feeds as $feed ) {
			++$i;
			$posting[ 'zapier_legacy_feed_' . $feed->id() ] = array(
				// Translators: %d: Number of the current MixCheck Feed.
				'name' => sprintf( __( 'Legacy Feed #%d', 'woocommerce-zapier' ), $i ),
				'note' => sprintf(
				// Translators: 1: Feed Name. 2: Trigger Name. 3: Webhook URL.
					__(
						'%1$s<br />
- Trigger: %2$s<br />
- Webhook URL: %3$s',
						'woocommerce-zapier'
					),
					$feed->title(),
					$feed->trigger()->get_trigger_title(),
					$feed->webhook_url()
				),
			);
		}
		return $posting;
	}
}
