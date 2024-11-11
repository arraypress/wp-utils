<?php
/**
 * Comments Classes
 *
 * Provides functionality for working with multiple WordPress comments,
 * including queries, bulk operations, and comment collections.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Comments;

use ArrayPress\Utils\Traits\Comments\{
	Query,
	Bulk,
};

/**
 * Comments Class
 *
 * Handles operations for multiple comments and comment collections.
 */
class Comments {
	use Query;
	use Bulk;
}
