<?php

namespace OM4\MixCheck\Payload\Collection;

use OM4\MixCheck\Payload\Base\Collection;

defined( 'ABSPATH' ) || exit;

/**
 * Holds an array of \OM4\MixCheck\Payload\Item\Note classes.
 *
 * @deprecated 2.0.0
 */
final class Notes extends Collection {

	/**
	 * Holds the name of the Item Class which is allowed in this Collection
	 *
	 * @var  string
	 */
	protected static $item_type = '\\OM4\\MixCheck\\Payload\\Item\\Note';
}
