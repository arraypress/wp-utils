<?php
/**
 * Posts Utilities
 *
 * Provides comprehensive utilities for working with multiple WordPress posts,
 * including bulk operations, meta handling, and taxonomy management.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare(strict_types=1);

namespace ArrayPress\Utils\Posts;

use ArrayPress\Utils\Traits\Posts\{
	Core,
	Query,
	Bulk,
	Terms,
	Sanitize
};
use ArrayPress\Utils\Traits\Shared\Meta;

/**
 * Posts Class
 *
 * Handles operations for multiple WordPress posts including retrievals,
 * bulk operations, meta handling, and taxonomy management.
 */
class Posts {
	use Core;
	use Query;
	use Bulk;
	use Terms;
	use Sanitize;
	use Meta;

	/**
	 * Get the meta type for this class.
	 *
	 * @return string
	 */
	protected static function get_meta_type(): string {
		return 'post';
	}
}