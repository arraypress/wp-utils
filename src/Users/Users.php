<?php
/**
 * Users Utilities
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Users;

use ArrayPress\Utils\Traits\Users\Core;
use ArrayPress\Utils\Traits\Users\Query;
use ArrayPress\Utils\Traits\Users\Management;

/**
 * Class Users
 *
 * Utility functions for working with multiple users.
 */
class Users {
	use Core;
	use Query;
	use Management;
}