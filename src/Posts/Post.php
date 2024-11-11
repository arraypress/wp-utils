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

use ArrayPress\Utils\Traits\Post\{
	Comments,
	Conditional,
	Content,
	Core,
	Dates,
	Format,
	Hierarchy,
	Media,
	Password,
	Query,
	Relationship,
	Status,
	Sticky,
	Terms
};
use ArrayPress\Utils\Traits\Shared\Meta;

/**
 * Class Post
 *
 * Main Post utility class that combines various traits for comprehensive
 * single post management.
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
	use Relationship;
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