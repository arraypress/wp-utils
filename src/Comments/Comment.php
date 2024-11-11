<?php
/**
 * Comment Class
 *
 * Provides functionality for working with individual WordPress comments.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Comments;

use ArrayPress\Utils\Traits\Comment\{
	Core,
	Dates,
	Hierarchy,
	Status
};
use ArrayPress\Utils\Traits\Shared\Meta;

/**
 * Class Comment
 *
 * Handles operations for individual comments.
 */
class Comment {
	use Core;
	use Hierarchy;
	use Status;
	use Dates;
	use Meta;

	/**
	 * Get the meta type for this class.
	 *
	 * @return string
	 */
	protected static function get_meta_type(): string {
		return 'comment';
	}
}