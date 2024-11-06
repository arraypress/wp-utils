<?php
/**
 * Post Class
 *
 * Provides comprehensive utilities for working with WordPress posts, including methods
 * for post existence verification, retrieval, content management, media handling,
 * hierarchical relationships, and status operations.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Posts;

use ArrayPress\Utils\Traits\Post\Comments;
use ArrayPress\Utils\Traits\Post\Conditional;
use ArrayPress\Utils\Traits\Post\Content;
use ArrayPress\Utils\Traits\Post\Core;
use ArrayPress\Utils\Traits\Post\Dates;
use ArrayPress\Utils\Traits\Post\Format;
use ArrayPress\Utils\Traits\Post\Hierarchy;
use ArrayPress\Utils\Traits\Post\Media;
use ArrayPress\Utils\Traits\Post\Password;
use ArrayPress\Utils\Traits\Post\Query;
use ArrayPress\Utils\Traits\Post\Status;
use ArrayPress\Utils\Traits\Post\Sticky;
use ArrayPress\Utils\Traits\Post\Terms;
use ArrayPress\Utils\Traits\Shared\Meta;

/**
 * Class Post
 *
 * Main Post utility class that combines various traits for comprehensive
 * single post management.
 *
 * @package ArrayPress\Utils\Posts
 */
class Post {
	use Comments;
	use Conditional;
	use Content;
	use Core;
	use Dates;
	use Format;
	use Hierarchy;
	use Media;
	use Meta;
	use Password;
	use Query;
	use Status;
	use Sticky;
	use Terms;

	/**
	 * Get the meta type for this class.
	 *
	 * Implements the abstract method from the Meta trait to specify
	 * that this class deals with post meta.
	 *
	 * @return string The meta type 'post'.
	 */
	protected static function get_meta_type(): string {
		return 'post';
	}
}