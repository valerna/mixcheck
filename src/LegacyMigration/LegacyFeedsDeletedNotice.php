<?php

namespace OM4\WooCommerceMixCheck\LegacyMigration;

use OM4\WooCommerceMixCheck\Notice\BaseNotice;
use OM4\MixCheck\Admin\FeedUI;

defined( 'ABSPATH' ) || exit;


/**
 * Notice that is displayed to users after they delete their last Legacy MixCheck
 * Feed. When a user dismisses this notice, then deactivate Legacy Mode.
 * Wording/content for this notice is stored in
 * `templates/notices/legacy-feeds-deleted.php`
 *
 * @since 2.0.0
 */
class LegacyFeedsDeletedNotice extends BaseNotice {

	/**
	 * {@inheritDoc}
	 *
	 * @var string
	 */
	protected $id = 'legacy_feeds_deleted';

	/**
	 * This notice has custom code to execute when the notice is dismissed.
	 *
	 * @var bool
	 */
	protected $has_dismiss_action = true;

	/**
	 * Executed whenever this notice is dismissed by the user.
	 *
	 * - Disable legacy mode.
	 * - Redirect the user back to the main MixCheck screen.
	 *
	 * @return void
	 */
	public function dismissed() {
		$this->settings->set_legacy_mode_disabled();

		// Delete `wc_zapier_feed_messages` option that exists for Legacy Feed users.
		$this->settings->delete_setting( 'feed_messages' );

		// Redirect the user back to the main MixCheck screen.
		if ( ! headers_sent() ) {
			wp_safe_redirect( $this->admin_ui->get_url() );
			exit;
		}
	}
}
