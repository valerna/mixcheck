<?php

namespace OM4\WooCommerceMixCheck\NewUser;

use OM4\WooCommerceMixCheck\Notice\BaseNotice;
use OM4\WooCommerceMixCheck\Plugin;

defined( 'ABSPATH' ) || exit;


/**
 * Notice that is displayed to new users installing/activating the plugin for the first time.
 * Wording/content for this notice is stored in `templates/notices/new-user.php`
 * Clicking the button in this notice will dismiss the notice and then take the user to our documentation.
 *
 * @since 2.0.0
 */
class NewUserWelcomeNotice extends BaseNotice {

	/**
	 * {@inheritDoc}
	 *
	 * @var string
	 */
	protected $id = 'new_user';

	/**
	 * {@inheritDoc}
	 *
	 * @var boolean
	 */
	protected $has_dismiss_action = true;

	/**
	 * {@inheritDoc}
	 *
	 * @return array
	 */
	protected function template_variables() {
		return array(
			'button_url' => Plugin::DOCUMENTATION_URL . 'new-user-guide/',
		);
	}
}
