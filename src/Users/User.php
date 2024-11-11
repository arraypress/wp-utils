<?php
/**
 * User Utilities for WordPress
 *
 * Provides utility functions for working with WordPress users, including methods
 * for retrieving user data, validating users, and accessing user properties.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Users;

use ArrayPress\Utils\Traits\User\{
	Authentication,
	Avatar,
	Capabilities,
	Comments,
	Core,
	Dates,
	Fields,
	MetaKeys,
	Posts,
	Roles,
	Security,
	Social,
	Spam
};
use ArrayPress\Utils\Traits\Shared\Meta;

/**
 * Class User
 *
 * Utility functions for working with a specific User.
 */
class User {
	use MetaKeys;
	use Authentication;
	use Avatar;
	use Capabilities;
	use Comments;
	use Core;
	use Dates;
	use Fields;
	use Meta;
	use Posts;
	use Roles;
	use Security;
	use Social;
	use Spam;

	/**
	 * Get the meta type for this class.
	 *
	 * @return string
	 */
	protected static function get_meta_type(): string {
		return 'user';
	}

}