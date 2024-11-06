<?php
/**
 * Users Query Class
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\Users;

use ArrayPress\Utils\Common\Validate;
use WP_User;

/**
 * Users Query Trait
 */
trait Query {

	/**
	 * Get users by meta key and value.
	 *
	 * @param string $meta_key   The meta key to search for.
	 * @param mixed  $meta_value The meta value to match.
	 * @param array  $args       Additional query arguments.
	 *
	 * @return WP_User[] An array of user objects.
	 */
	public static function get_by_meta_key_value( string $meta_key, $meta_value, array $args = [] ): array {
		$default_args = [
			'meta_key'   => $meta_key,
			'meta_value' => $meta_value,
			'number'     => - 1,
		];

		$args = wp_parse_args( $args, $default_args );

		return get_users( $args );
	}

	/**
	 * Get recent users.
	 *
	 * @param int   $number The number of users to retrieve.
	 * @param array $args   Additional query arguments.
	 *
	 * @return WP_User[] An array of user objects.
	 */
	public static function get_recent( int $number = 5, array $args = [] ): array {
		$default_args = [
			'number'  => $number,
			'orderby' => 'registered',
			'order'   => 'DESC',
		];

		$args = wp_parse_args( $args, $default_args );

		return get_users( $args );
	}

	/**
	 * Get users by role.
	 *
	 * @param string $role The role to search for.
	 * @param array  $args Additional query arguments.
	 *
	 * @return WP_User[] An array of user objects.
	 */
	public static function get_by_role( string $role, array $args = [] ): array {
		$default_args = [
			'role'   => $role,
			'number' => - 1,
		];

		$args = wp_parse_args( $args, $default_args );

		return get_users( $args );
	}

	/**
	 * Count users by role.
	 *
	 * @param string $role The role to count users by.
	 *
	 * @return int The number of users with the specified role.
	 */
	public static function count_by_role( string $role ): int {
		return count( self::get_by_role( $role ) );
	}

	/**
	 * Get users by a specific meta key exists.
	 *
	 * @param string $meta_key The meta key to check.
	 * @param array  $args     Additional query arguments.
	 *
	 * @return WP_User[] An array of user objects.
	 */
	public static function get_where_meta_key_exists( string $meta_key, array $args = [] ): array {
		$default_args = [
			'meta_query' => [
				[
					'key'     => $meta_key,
					'compare' => 'EXISTS',
				],
			],
			'number'     => - 1,
		];

		$args = wp_parse_args( $args, $default_args );

		return get_users( $args );
	}

	/**
	 * Get users where a meta value is compared with a specific amount.
	 *
	 * @param string $meta_key The meta key to check.
	 * @param mixed  $amount   The amount to compare against.
	 * @param string $operator The comparison operator.
	 * @param array  $args     Additional query arguments.
	 *
	 * @return WP_User[] An array of user objects.
	 */
	public static function get_where_meta_compared( string $meta_key, $amount, string $operator, array $args = [] ): array {
		if ( ! Validate::is_operator( $operator ) ) {
			return [];
		}

		$default_args = [
			'meta_query' => [
				[
					'key'     => $meta_key,
					'value'   => $amount,
					'compare' => $operator,
					'type'    => 'NUMERIC',
				],
			],
			'number'     => - 1,
		];

		$args = wp_parse_args( $args, $default_args );

		return get_users( $args );
	}

	/**
	 * Get users by a specific capability.
	 *
	 * @param string $capability The capability to search for.
	 * @param array  $args       Additional query arguments.
	 *
	 * @return WP_User[] An array of user objects.
	 */
	public static function get_by_capability( string $capability, array $args = [] ): array {
		global $wpdb;

		$default_args = [
			'meta_query' => [
				[
					'key'     => $wpdb->prefix . 'capabilities',
					'value'   => '"' . $capability . '"',
					'compare' => 'LIKE',
				],
			],
			'number'     => - 1,
		];

		$args = wp_parse_args( $args, $default_args );

		return get_users( $args );
	}

	/**
	 * Get users with no posts.
	 *
	 * @param array $args Additional query arguments.
	 *
	 * @return WP_User[] An array of user objects.
	 */
	public static function get_with_no_posts( array $args = [] ): array {
		$args['has_published_posts'] = false;

		return get_users( $args );
	}

	/**
	 * Get users by registration date range.
	 *
	 * @param string $start_date The start date (YYYY-MM-DD).
	 * @param string $end_date   The end date (YYYY-MM-DD).
	 * @param array  $args       Additional query arguments.
	 *
	 * @return WP_User[] An array of user objects.
	 */
	public static function get_by_date_range( string $start_date, string $end_date, array $args = [] ): array {
		$default_args = [
			'number'     => - 1,
			'date_query' => [
				[
					'after'     => $start_date,
					'before'    => $end_date,
					'inclusive' => true,
				],
			],
		];

		$args = wp_parse_args( $args, $default_args );

		return get_users( $args );
	}

	/**
	 * Get users registered within a specific time period from now.
	 *
	 * @param string $period Time period to check (e.g., '24 hours', '7 days', '1 month').
	 * @param array  $args   Additional query arguments.
	 *
	 * @return WP_User[] An array of user objects.
	 */
	public static function get_by_period( string $period, array $args = [] ): array {
		$period_time = strtotime( '-' . $period );
		if ( ! $period_time ) {
			return [];
		}

		$default_args = [
			'number'     => - 1,
			'date_query' => [
				[
					'after'     => date( 'Y-m-d H:i:s', $period_time ),
					'inclusive' => true,
				]
			],
		];

		$args = wp_parse_args( $args, $default_args );

		return get_users( $args );
	}

	/**
	 * Get users by email domain.
	 *
	 * @param string $domain The email domain to search for.
	 * @param array  $args   Additional query arguments.
	 *
	 * @return WP_User[] An array of user objects.
	 */
	public static function get_by_email_domain( string $domain, array $args = [] ): array {
		$default_args = [
			'search'         => '*@' . $domain,
			'search_columns' => [ 'user_email' ],
			'number'         => - 1,
		];

		$args = wp_parse_args( $args, $default_args );

		return get_users( $args );
	}

}