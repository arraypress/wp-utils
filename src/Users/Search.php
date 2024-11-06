<?php
/**
 * Users Search Utility Class for WordPress
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Users;

use ArrayPress\Utils\Common\Sanitize;
use WP_User_Query;
use function absint;
use function esc_html;
use function wp_parse_args;

class Search {

	/**
	 * @var array List of roles to include in the search.
	 */
	private array $roles;

	/**
	 * @var array List of capabilities to include in the search.
	 */
	private array $capabilities;

	/**
	 * @var int Number of users to retrieve.
	 */
	private int $number;

	/**
	 * @var string The field to order the results by.
	 */
	private string $orderby;

	/**
	 * @var string The order direction of the results.
	 */
	private string $order;

	/**
	 * @var array Meta query arguments.
	 */
	private array $meta_query;

	/**
	 * @var array Search columns.
	 */
	private array $search_columns;

	/**
	 * Constructor for the Users class.
	 *
	 * @param array  $roles   List of roles to include. Default is empty array (all roles).
	 * @param int    $number  Number of users to retrieve. Default is -1 (all users).
	 * @param string $orderby The field to order the results by. Default is 'display_name'.
	 * @param string $order   The order direction of the results. Default is 'ASC'.
	 */
	public function __construct(
		array $roles = [],
		int $number = - 1,
		string $orderby = 'display_name',
		string $order = 'ASC'
	) {
		$this->roles          = $roles;
		$this->capabilities   = [];
		$this->number         = $number;
		$this->orderby        = $orderby;
		$this->order          = $order;
		$this->meta_query     = [];
		$this->search_columns = [ 'user_login', 'user_nicename', 'user_email', 'display_name' ];
	}

	/**
	 * Set the roles to include in the search.
	 *
	 * @param array $roles List of roles to include.
	 *
	 * @return self
	 */
	public function set_roles( array $roles ): self {
		$this->roles = $roles;

		return $this;
	}

	/**
	 * Set the capabilities to include in the search.
	 *
	 * @param array $capabilities List of capabilities to include.
	 *
	 * @return self
	 */
	public function set_capabilities( array $capabilities ): self {
		$this->capabilities = $capabilities;

		return $this;
	}

	/**
	 * Set the number of users to retrieve.
	 *
	 * @param int $number Number of users to retrieve.
	 *
	 * @return self
	 */
	public function set_number( int $number ): self {
		$this->number = $number;

		return $this;
	}

	/**
	 * Set the field to order the results by.
	 *
	 * @param string $orderby The field to order the results by.
	 *
	 * @return self
	 */
	public function set_orderby( string $orderby ): self {
		$this->orderby = $orderby;

		return $this;
	}

	/**
	 * Set the order direction of the results.
	 *
	 * @param string $order The order direction of the results.
	 *
	 * @return self
	 */
	public function set_order( string $order ): self {
		$this->order = $order;

		return $this;
	}

	/**
	 * Set the search columns.
	 *
	 * @param array $columns The columns to search in.
	 *
	 * @return self
	 */
	public function set_search_columns( array $columns ): self {
		$this->search_columns = $columns;

		return $this;
	}

	/**
	 * Add a meta query to filter users.
	 *
	 * @param string $key     The meta key.
	 * @param mixed  $value   The meta value.
	 * @param string $compare The comparison operator (=, !=, >, >=, <, <=, LIKE, NOT LIKE, IN, NOT IN, BETWEEN, NOT
	 *                        BETWEEN, EXISTS, NOT EXISTS).
	 * @param string $type    The data type (NUMERIC, BINARY, CHAR, DATE, DATETIME, DECIMAL, SIGNED, TIME, UNSIGNED).
	 *
	 * @return self
	 */
	public function add_meta_query( string $key, $value, string $compare = '=', string $type = 'CHAR' ): self {
		$this->meta_query[] = [
			'key'     => $key,
			'value'   => $value,
			'compare' => $compare,
			'type'    => $type,
		];

		return $this;
	}

	/**
	 * Set the relation for meta queries.
	 *
	 * @param string $relation The relation between meta queries ('AND' or 'OR').
	 *
	 * @return self
	 */
	public function set_meta_query_relation( string $relation ): self {
		if ( ! empty( $this->meta_query ) ) {
			$this->meta_query['relation'] = strtoupper( $relation );
		}

		return $this;
	}

	/**
	 * Clear all meta queries.
	 *
	 * @return self
	 */
	public function clear_meta_query(): self {
		$this->meta_query = [];

		return $this;
	}

	/**
	 * Perform a search for users.
	 *
	 * @param string $search         The search string.
	 * @param array  $args           Optional. Additional arguments to pass to the search query. Default is an empty
	 *                               array.
	 * @param bool   $return_objects Optional. Whether to return user objects. Default is false.
	 *
	 * @return array An array of formatted search results or user objects.
	 */
	public function get_results( string $search, array $args = [], bool $return_objects = false ): array {
		$search = Sanitize::search( $search );

		// Default query arguments.
		$args = wp_parse_args( $args, [
			'search'         => '*' . $search . '*',
			'search_columns' => $this->search_columns,
			'number'         => $this->number,
			'orderby'        => $this->orderby,
			'order'          => $this->order,
		] );

		if ( ! empty( $this->roles ) ) {
			$args['role__in'] = $this->roles;
		}

		if ( ! empty( $this->capabilities ) ) {
			$args['capability'] = $this->capabilities;
		}

		// Add meta query if set
		if ( ! empty( $this->meta_query ) ) {
			$args['meta_query'] = $this->meta_query;
		}

		$query = new WP_User_Query( $args );
		$users = $query->get_results();

		return $return_objects ? $users : $this->format_results( $users );
	}

	/**
	 * Format search results into an array of options.
	 *
	 * @param array $users Array of user objects.
	 *
	 * @return array An array of formatted search results, each containing 'value' and 'label'.
	 */
	private function format_results( array $users ): array {
		if ( empty( $users ) ) {
			return [];
		}

		$options = [];

		foreach ( $users as $user ) {
			$options[] = [
				'value' => absint( $user->ID ),
				'label' => esc_html( $user->display_name ),
			];
		}

		return $options;
	}

}