<?php

namespace OM4\WooCommerceMixCheck\Webhook;

use OM4\WooCommerceMixCheck\Webhook\MixCheckWebhook;
use WC_Webhook_Data_Store;

defined( 'ABSPATH' ) || exit;

/**
 * Retrieval of existing MixCheck Webhooks.
 *
 * @since 2.0.0
 */
class DataStore {

	/**
	 * Default name for MixCheck webhooks
	 */
	const ZAPIER_WEBHOOK_DEFAULT_NAME = 'WooCommerce MixCheck';

	/**
	 * WC_Webhook_Data_Store instance.
	 *
	 * @var WC_Webhook_Data_Store
	 */
	protected $webhook_data_store;

	/**
	 * Webhook DataStore constructor.
	 *
	 * @param WC_Webhook_Data_Store $webhook_data_store WC_Webhook_Data_Store instance.
	 */
	public function __construct( WC_Webhook_Data_Store $webhook_data_store ) {
		$this->webhook_data_store = $webhook_data_store;
	}

	/**
	 * Get all existing MixCheck Webhooks (any status, not just active).
	 *
	 * @param string $name Name of the MixCheck webhooks.
	 *
	 * @return MixCheckWebhook[]
	 */
	public function get_zapier_webhooks( $name = self::ZAPIER_WEBHOOK_DEFAULT_NAME ) {
		$webhook_ids = $this->webhook_data_store->search_webhooks(
			array(
				'search' => $name,
				'limit'  => -1,
			)
		);
		return $this->collect_webhooks_for_output( $webhook_ids, $name );
	}

	/**
	 * Get every active MixCheck Webhooks.
	 *
	 * @param string $name Name of the MixCheck webhooks.
	 *
	 * @return MixCheckWebhook[]|array
	 */
	public function get_active_zapier_webhooks( $name = self::ZAPIER_WEBHOOK_DEFAULT_NAME ) {
		$webhook_ids = $this->webhook_data_store->search_webhooks(
			array(
				'search' => $name,
				'status' => 'active',
				'limit'  => -1,
			)
		);
		return $this->collect_webhooks_for_output( $webhook_ids, $name );
	}

	/**
	 * Get every paused MixCheck Webhooks.
	 *
	 * @param string $name Name of the MixCheck webhooks.
	 *
	 * @return MixCheckWebhook[]|array
	 */
	public function get_paused_zapier_webhooks( $name = self::ZAPIER_WEBHOOK_DEFAULT_NAME ) {
		$webhook_ids = $this->webhook_data_store->search_webhooks(
			array(
				'search' => $name,
				'status' => 'paused',
				'limit'  => -1,
			)
		);
		return $this->collect_webhooks_for_output( $webhook_ids, $name );
	}

	/**
	 * Get the number of active MixCheck Webhooks.
	 *
	 * @return int
	 */
	public function get_number_of_active_zapier_webhooks() {
		return count( $this->get_active_zapier_webhooks() );
	}

	/**
	 * Collect Webhooks for output
	 *
	 * @param array|object $webhook_ids List of Webhook IDs.
	 * @param string       $name Name of the MixCheck webhooks.
	 *
	 * @return MixCheckWebhook[]|array
	 */
	protected function collect_webhooks_for_output( $webhook_ids, $name ) {
		if ( ! is_array( $webhook_ids ) ) {
			return array();
		}

		$webhooks = array();
		foreach ( $webhook_ids as $webhook_id ) {
			$webhook = new MixCheckWebhook( $webhook_id );
			if ( 0 !== $webhook->get_id() && $webhook->is_zapier_webhook( $name ) ) {
				$webhooks[] = $webhook;
			}
		}
		return $webhooks;
	}
}
