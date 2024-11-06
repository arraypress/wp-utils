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

use ArrayPress\Utils\Traits\User\MetaKeys;
use ArrayPress\Utils\Traits\User\Authentication;
use ArrayPress\Utils\Traits\User\Avatar;
use ArrayPress\Utils\Traits\User\Capabilities;
use ArrayPress\Utils\Traits\User\Comments;
use ArrayPress\Utils\Traits\User\Core;
use ArrayPress\Utils\Traits\User\Dates;
use ArrayPress\Utils\Traits\User\Info;
use ArrayPress\Utils\Traits\Shared\Meta;
use ArrayPress\Utils\Traits\User\Posts;
use ArrayPress\Utils\Traits\User\Roles;
use ArrayPress\Utils\Traits\User\Security;
use ArrayPress\Utils\Traits\User\Social;
use ArrayPress\Utils\Traits\User\Spam;

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
	use Info;
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