<?php

namespace OM4\MixCheck\Admin;

use OM4\MixCheck\Admin\FeedUI;
use OM4\MixCheck\Admin\SystemStatus;

defined( 'ABSPATH' ) || exit;

/**
 * Administration (dashboard) functionality
 *
 * @deprecated 2.0.0
 */
class Dashboard {

	/**
	 * Constructor
	 */
	public function __construct() {
		new FeedUI();
		new SystemStatus();
	}
}
