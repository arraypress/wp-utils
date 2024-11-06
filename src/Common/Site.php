<?php
/**
 * WordPress Site Utilities
 *
 * This file contains utility functions for managing WordPress site-related operations.
 * It provides a set of static methods encapsulated in the Site class to handle various
 * site-specific tasks and retrieve site information.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Common;

class Site {

	/**
	 * Retrieves the site name.
	 *
	 * @return string The decoded site name, with special HTML characters converted to their corresponding entities.
	 */
	public static function get_name(): string {
		return wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	}

	/**
	 * Retrieves the site URL.
	 *
	 * @param string      $path   Optional. Path relative to the site URL.
	 * @param string|null $scheme Optional. Scheme to give the site URL context. See set_url_scheme().
	 *
	 * @return string Site URL link with optional path appended.
	 */
	public static function get_url( string $path = '', ?string $scheme = null ): string {
		return get_site_url( null, $path, $scheme );
	}

	/**
	 * Retrieves the site's tagline.
	 *
	 * @return string The site's tagline.
	 */
	public static function get_tagline(): string {
		return get_bloginfo( 'description' );
	}

	/**
	 * Checks if the current site is a multisite installation.
	 *
	 * @return bool True if multisite, false otherwise.
	 */
	public static function is_multisite(): bool {
		return is_multisite();
	}

	/**
	 * Retrieves the current site's language.
	 *
	 * @return string The site's language code.
	 */
	public static function get_language(): string {
		return get_bloginfo( 'language' );
	}

	/**
	 * Retrieves the WordPress version for the current site.
	 *
	 * @return string The WordPress version.
	 */
	public static function get_wp_version(): string {
		global $wp_version;

		return $wp_version;
	}

	/**
	 * Retrieves the site's timezone.
	 *
	 * @return string The site's timezone string.
	 */
	public static function get_timezone(): string {
		return wp_timezone_string();
	}

	/**
	 * Checks if the site is currently in maintenance mode.
	 *
	 * @return bool True if in maintenance mode, false otherwise.
	 */
	public static function is_maintenance_mode(): bool {
		return wp_is_maintenance_mode();
	}

	/**
	 * Retrieves the site's admin email address.
	 *
	 * @return string The site admin email address.
	 */
	public static function get_admin_email(): string {
		return get_option( 'admin_email' );
	}

	/**
	 * Checks if the site is running on HTTPS.
	 *
	 * @return bool True if the site is using HTTPS, false otherwise.
	 */
	public static function is_ssl(): bool {
		return is_ssl();
	}

	/**
	 * Retrieves the home URL for the current site.
	 *
	 * @param string      $path   Optional. Path relative to the home URL.
	 * @param string|null $scheme Optional. Scheme to give the home URL context. See set_url_scheme().
	 *
	 * @return string Home URL link with optional path appended.
	 */
	public static function get_home_url( string $path = '', ?string $scheme = null ): string {
		return get_home_url( null, $path, $scheme );
	}

	/**
	 * Retrieves the login URL for the current site.
	 *
	 * @param string $redirect     Optional. Path to redirect to on login.
	 * @param bool   $force_reauth Optional. Whether to force reauthorization, even if a cookie is present.
	 *
	 * @return string The login URL.
	 */
	public static function get_login_url( string $redirect = '', bool $force_reauth = false ): string {
		return wp_login_url( $redirect, $force_reauth );
	}

	/**
	 * Get an array of time zones.
	 *
	 * @param string|null $continent Optional. Filter results by continent. Default null returns all continents.
	 *                               Valid values: 'Africa', 'America', 'Antarctica', 'Arctic', 'Asia',
	 *                               'Atlantic', 'Australia', 'Europe', 'Indian', 'Pacific', 'UTC', 'Manual'
	 *
	 * @return array An array of time zones in label/value format.
	 */
	public static function get_time_zone_options( ?string $continent = null ): array {
		$options = [];

		// Valid continents (stored in lowercase for comparison)
		$valid_continents = array_map( 'strtolower', [
			'Africa',
			'America',
			'Antarctica',
			'Arctic',
			'Asia',
			'Atlantic',
			'Australia',
			'Europe',
			'Indian',
			'Pacific'
		] );

		// If a continent is specified, validate it
		$search_continent = null;
		if ( $continent !== null ) {
			$continent_lower = strtolower( $continent );

			// Check if it's a valid continent, UTC, or Manual
			if ( ! in_array( $continent_lower, [ ...$valid_continents, 'utc', 'manual' ] ) ) {
				return [];
			}

			// Store the original casing for valid geographic continents
			if ( in_array( $continent_lower, $valid_continents ) ) {
				$search_continent = $continent_lower;
			}
		}

		// If continent is 'utc', return only UTC
		if ( $continent_lower === 'utc' ) {
			return [
				[
					'label'   => 'UTC',
					'options' => [
						[
							'value' => 'UTC',
							'label' => 'UTC'
						]
					]
				]
			];
		}

		// If continent is 'manual', return only manual offsets
		if ( $continent_lower === 'manual' ) {
			return [ self::get_manual_offsets_group() ];
		}

		// Get list of timezone identifiers
		$timezone_identifiers = timezone_identifiers_list();

		// Group timezones by continent
		$zones_by_continent = [];
		foreach ( $timezone_identifiers as $timezone ) {
			$parts                   = explode( '/', $timezone );
			$current_continent       = $parts[0] ?? '';
			$current_continent_lower = strtolower( $current_continent );

			// Skip non-geographic zones or non-matching continent
			if ( ! in_array( $current_continent_lower, $valid_continents ) ||
			     ( $search_continent !== null && $current_continent_lower !== $search_continent ) ) {
				continue;
			}

			if ( ! isset( $zones_by_continent[ $current_continent ] ) ) {
				$zones_by_continent[ $current_continent ] = [];
			}

			$city    = $parts[1] ?? '';
			$subcity = $parts[2] ?? '';

			$display = str_replace( '_', ' ', $city );
			if ( $subcity ) {
				$display .= ' - ' . str_replace( '_', ' ', $subcity );
			}

			$zones_by_continent[ $current_continent ][] = [
				'value' => $timezone,
				'label' => $display,
			];
		}

		// Add grouped options
		foreach ( $zones_by_continent as $current_continent => $zones ) {
			$options[] = [
				'label'   => str_replace( '_', ' ', $current_continent ),
				'options' => $zones
			];
		}

		// If no specific continent was requested, add UTC and manual offsets
		if ( $search_continent === null ) {
			// Add UTC option
			$options[] = [
				'label'   => 'UTC',
				'options' => [
					[
						'value' => 'UTC',
						'label' => 'UTC'
					]
				]
			];

			// Add manual offsets
			$options[] = self::get_manual_offsets_group();
		}

		return $options;
	}

	/**
	 * Get the manual offsets group.
	 *
	 * @return array The manual offsets group configuration.
	 */
	private static function get_manual_offsets_group(): array {
		$manual_offsets = [];
		$offset_range   = [
			- 12,
			- 11.5,
			- 11,
			- 10.5,
			- 10,
			- 9.5,
			- 9,
			- 8.5,
			- 8,
			- 7.5,
			- 7,
			- 6.5,
			- 6,
			- 5.5,
			- 5,
			- 4.5,
			- 4,
			- 3.5,
			- 3,
			- 2.5,
			- 2,
			- 1.5,
			- 1,
			- 0.5,
			0,
			0.5,
			1,
			1.5,
			2,
			2.5,
			3,
			3.5,
			4,
			4.5,
			5,
			5.5,
			5.75,
			6,
			6.5,
			7,
			7.5,
			8,
			8.5,
			8.75,
			9,
			9.5,
			10,
			10.5,
			11,
			11.5,
			12,
			12.75,
			13,
			13.75,
			14
		];

		foreach ( $offset_range as $offset ) {
			$offset_name  = $offset >= 0 ? '+' . $offset : (string) $offset;
			$display_name = str_replace( [ '.25', '.5', '.75' ], [ ':15', ':30', ':45' ], $offset_name );

			$manual_offsets[] = [
				'value' => 'UTC' . $offset_name,
				'label' => 'UTC' . $display_name
			];
		}

		return [
			'label'   => 'Manual Offsets',
			'options' => $manual_offsets
		];
	}

}