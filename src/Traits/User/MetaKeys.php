<?php
/**
 * Trait: User MetaKeys
 *
 * Provides centralized meta key configuration for all user-related functionality.
 *
 * @package     ArrayPress\Utils\Traits\User
 * @since       1.0.0
 * @author      David Sherlock
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\User;

trait MetaKeys {
	/**
	 * Get meta key configuration.
	 *
	 * @return array
	 */
	protected static function get_meta_keys(): array {
		return [
			'last_login'      => 'last_login',
			'last_active'     => 'last_active',
			'suspended'       => 'wp_user_suspended',
			'verified'        => 'account_verified',
			'registration_ip' => 'registration_ip',
			'last_login_ip'   => 'last_login_ip',

			// Social Media
			'social'          => [
				'twitter'   => 'twitter',
				'facebook'  => 'facebook',
				'instagram' => 'instagram',
				'linkedin'  => 'linkedin',
				'youtube'   => 'youtube',
				'github'    => 'github'
			],
		];
	}

	/**
	 * Get a specific meta key.
	 *
	 * @param string      $key    The key to get from meta keys configuration.
	 * @param string|null $subkey Optional. The sub key for nested configurations.
	 *
	 * @return string The meta key or the input if not found.
	 */
	protected static function get_meta_key( string $key, ?string $subkey = null ): string {
		$keys = static::get_meta_keys();

		if ( $subkey !== null && isset( $keys[ $key ][ $subkey ] ) ) {
			return $keys[ $key ][ $subkey ];
		}

		return $keys[ $key ] ?? $key;
	}

}