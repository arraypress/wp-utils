<?php
/**
 * Terms Search Utility Class for WordPress
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Terms;

use ArrayPress\Utils\Common\Sanitize;
use WP_Term_Query;
use function absint;
use function esc_html;
use function wp_parse_args;

/**
 * Class Search
 *
 * Search utility for terms.
 */
class Search {

	/**
	 * @var array List of taxonomies to include in the search.
	 */
	private array $taxonomies;

	/**
	 * @var bool Whether to hide empty terms.
	 */
	private bool $hide_empty;

	/**
	 * @var int Number of terms to retrieve.
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
	 * Constructor for the Terms class.
	 *
	 * @param array  $taxonomies List of taxonomies to include. Default is ['category'].
	 * @param bool   $hide_empty Whether to hide empty terms. Default is false.
	 * @param int    $number     Number of terms to retrieve. Default is 0 (all).
	 * @param string $orderby    The field to order the results by. Default is 'name'.
	 * @param string $order      The order direction of the results. Default is 'ASC'.
	 */
	public function __construct(
		array $taxonomies = [ 'category' ],
		bool $hide_empty = false,
		int $number = 0,
		string $orderby = 'name',
		string $order = 'ASC'
	) {
		$this->taxonomies = $taxonomies;
		$this->hide_empty = $hide_empty;
		$this->number     = $number;
		$this->orderby    = $orderby;
		$this->order      = $order;
		$this->meta_query = [];
	}

	/**
	 * Set the taxonomies to include in the search.
	 *
	 * @param array $taxonomies List of taxonomies to include.
	 *
	 * @return self
	 */
	public function set_taxonomies( array $taxonomies ): self {
		$this->taxonomies = $taxonomies;

		return $this;
	}

	/**
	 * Set whether to hide empty terms.
	 *
	 * @param bool $hide_empty Whether to hide empty terms.
	 *
	 * @return self
	 */
	public function set_hide_empty( bool $hide_empty ): self {
		$this->hide_empty = $hide_empty;

		return $this;
	}

	/**
	 * Set the number of terms to retrieve.
	 *
	 * @param int $number Number of terms to retrieve.
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
	 * Add a meta query to filter terms.
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
	 * Perform a search for terms.
	 *
	 * @param string $search         The search string.
	 * @param array  $args           Optional. Additional arguments to pass to the search query. Default is an empty
	 *                               array.
	 * @param bool   $return_objects Optional. Whether to return term objects. Default is false.
	 *
	 * @return array An array of formatted search results or term objects.
	 */
	public function get_results( string $search, array $args = [], bool $return_objects = false ): array {
		$search = Sanitize::search( $search );

		// Default query arguments.
		$args = wp_parse_args( $args, [
			'taxonomy'   => $this->taxonomies,
			'hide_empty' => $this->hide_empty,
			'number'     => $this->number,
			'orderby'    => $this->orderby,
			'order'      => $this->order,
			'search'     => $search,
		] );

		// Add meta query if set
		if ( ! empty( $this->meta_query ) ) {
			$args['meta_query'] = $this->meta_query;
		}

		$query = new WP_Term_Query( $args );
		$terms = $query->get_terms();

		return $return_objects ? $terms : $this->format_results( $terms );
	}

	/**
	 * Format search results into an array of options.
	 *
	 * @param array $terms Array of term objects.
	 *
	 * @return array An array of formatted search results, each containing 'value' and 'label'.
	 */
	private function format_results( array $terms ): array {
		if ( empty( $terms ) ) {
			return [];
		}

		$options = [];

		foreach ( $terms as $term ) {
			$options[] = [
				'value' => absint( $term->term_id ),
				'label' => esc_html( $term->name ),
			];
		}

		return $options;
	}

}