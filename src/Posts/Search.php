<?php
/**
 * Posts Search Utility Class for WordPress
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.1
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Posts;

use ArrayPress\Utils\Common\Sanitize;
use WP_Query;
use function absint;
use function esc_html;
use function wp_parse_args;

/**
 * Class Search
 *
 * Search utility for posts.
 */
class Search {

	/**
	 * @var array List of post types to include in the search.
	 */
	private array $post_types;

	/**
	 * @var array List of post statuses to include in the search.
	 */
	private array $post_status;

	/**
	 * @var int Number of posts to retrieve.
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
	 * @var array Taxonomy query arguments.
	 */
	private array $tax_query;

	/**
	 * @var array Meta query arguments.
	 */
	private array $meta_query;

	/**
	 * Constructor for the Posts class.
	 *
	 * @param array  $post_types  List of post types to include. Default is ['post'].
	 * @param array  $post_status List of post statuses to include. Default is ['publish'].
	 * @param int    $number      Number of posts to retrieve. Default is 30.
	 * @param string $orderby     The field to order the results by. Default is 'title'.
	 * @param string $order       The order direction of the results. Default is 'ASC'.
	 */
	public function __construct(
		array $post_types = [ 'post' ],
		array $post_status = [ 'publish' ],
		int $number = 30,
		string $orderby = 'title',
		string $order = 'ASC'
	) {
		$this->post_types  = $post_types;
		$this->post_status = $post_status;
		$this->number      = $number;
		$this->orderby     = $orderby;
		$this->order       = $order;
		$this->tax_query   = [];
		$this->meta_query  = [];
	}

	/**
	 * Set the post types to include in the search.
	 *
	 * @param array $post_types List of post types to include.
	 *
	 * @return self
	 */
	public function set_post_types( array $post_types ): self {
		$this->post_types = $post_types;

		return $this;
	}

	/**
	 * Set the post statuses to include in the search.
	 *
	 * @param array $post_status List of post statuses to include.
	 *
	 * @return self
	 */
	public function set_post_status( array $post_status ): self {
		$this->post_status = $post_status;

		return $this;
	}

	/**
	 * Set the number of posts to retrieve.
	 *
	 * @param int $number Number of posts to retrieve.
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
	 * Add a taxonomy query to filter posts.
	 *
	 * @param string       $taxonomy Taxonomy slug.
	 * @param string|array $terms    Term slug(s) or ID(s).
	 * @param string       $field    Select taxonomy term by. Possible values are 'term_id', 'name', 'slug' or
	 *                               'term_taxonomy_id'.
	 * @param string       $operator Operator to test. Possible values are 'IN', 'NOT IN', 'AND', 'EXISTS' and 'NOT
	 *                               EXISTS'.
	 *
	 * @return self
	 */
	public function add_tax_query( string $taxonomy, $terms, string $field = 'slug', string $operator = 'IN' ): self {
		$this->tax_query[] = [
			'taxonomy' => $taxonomy,
			'field'    => $field,
			'terms'    => $terms,
			'operator' => $operator,
		];

		return $this;
	}

	/**
	 * Set the relation for taxonomy queries.
	 *
	 * @param string $relation The relation between taxonomy queries ('AND' or 'OR').
	 *
	 * @return self
	 */
	public function set_tax_query_relation( string $relation ): self {
		if ( ! empty( $this->tax_query ) ) {
			$this->tax_query['relation'] = strtoupper( $relation );
		}

		return $this;
	}

	/**
	 * Clear all taxonomy queries.
	 *
	 * @return self
	 */
	public function clear_tax_query(): self {
		$this->tax_query = [];

		return $this;
	}

	/**
	 * Add a meta query to filter posts.
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
	 * Perform a search for posts.
	 *
	 * @param string $search         The search string.
	 * @param array  $args           Optional. Additional arguments to pass to the search query. Default is an empty
	 *                               array.
	 * @param bool   $return_objects Optional. Whether to return post objects. Default is false.
	 *
	 * @return array An array of formatted search results or post objects.
	 */
	public function get_results( string $search, array $args = [], bool $return_objects = false ): array {
		$search = Sanitize::search( $search );

		// Default query arguments.
		$args = wp_parse_args( $args, [
			'post_type'        => $this->post_types,
			'post_status'      => $this->post_status,
			'posts_per_page'   => $this->number,
			'orderby'          => $this->orderby,
			'order'            => $this->order,
			's'                => $search,
			'suppress_filters' => false,
		] );

		// Add taxonomy query if set
		if ( ! empty( $this->tax_query ) ) {
			$args['tax_query'] = $this->tax_query;
		}

		// Add meta query if set
		if ( ! empty( $this->meta_query ) ) {
			$args['meta_query'] = $this->meta_query;
		}

		$query = new WP_Query( $args );
		$posts = $query->posts;

		return $return_objects ? $posts : $this->format_results( $posts );
	}

	/**
	 * Format search results into an array of options.
	 *
	 * @param array $posts Array of post objects.
	 *
	 * @return array An array of formatted search results, each containing 'value' and 'label'.
	 */
	private function format_results( array $posts ): array {
		if ( empty( $posts ) ) {
			return [];
		}

		$options = [];

		foreach ( $posts as $post ) {
			$options[] = [
				'value' => absint( $post->ID ),
				'label' => esc_html( $post->post_title ),
			];
		}

		return $options;
	}

}