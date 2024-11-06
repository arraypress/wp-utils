<?php
/**
 * Comments Search Utility Class for WordPress
 *
 * Provides advanced search functionality for WordPress comments with
 * chainable methods for building complex search queries.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Comments;

use ArrayPress\Utils\Common\Sanitize;
use WP_Comment_Query;
use function absint;
use function esc_html;
use function wp_parse_args;

/**
 * Class Search
 *
 * Search utility for comments.
 */
class Search {

	/**
	 * @var string Comment status to include in the search.
	 */
	private string $status;

	/**
	 * @var int Number of comments to retrieve.
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
	 * @var array|null Post types to include comments from.
	 */
	private ?array $post_types;

	/**
	 * @var int|null Specific post ID to get comments from.
	 */
	private ?int $post_id;

	/**
	 * @var array|null User IDs to get comments from.
	 */
	private ?array $user_ids;

	/**
	 * @var bool Include replies in search results.
	 */
	private bool $include_replies;

	/**
	 * Constructor for the Comments Search class.
	 *
	 * @param string $status          Comment status to include. Default is 'approve'.
	 * @param int    $number          Number of comments to retrieve. Default is -1 (all comments).
	 * @param string $orderby         The field to order results by. Default is 'comment_date_gmt'.
	 * @param string $order           The order direction. Default is 'DESC'.
	 * @param bool   $include_replies Whether to include replies. Default is true.
	 */
	public function __construct(
		string $status = 'approve',
		int $number = - 1,
		string $orderby = 'comment_date_gmt',
		string $order = 'DESC',
		bool $include_replies = true
	) {
		$this->status          = $status;
		$this->number          = $number;
		$this->orderby         = $orderby;
		$this->order           = $order;
		$this->meta_query      = [];
		$this->post_types      = null;
		$this->post_id         = null;
		$this->user_ids        = null;
		$this->include_replies = $include_replies;
	}

	/**
	 * Set the comment status.
	 *
	 * @param string $status Comment status ('approve', 'hold', 'spam', 'trash', 'any').
	 *
	 * @return self
	 */
	public function set_status( string $status ): self {
		$this->status = $status;

		return $this;
	}

	/**
	 * Set the number of comments to retrieve.
	 *
	 * @param int $number Number of comments to retrieve.
	 *
	 * @return self
	 */
	public function set_number( int $number ): self {
		$this->number = $number;

		return $this;
	}

	/**
	 * Set the field to order results by.
	 *
	 * @param string $orderby The field to order by.
	 *
	 * @return self
	 */
	public function set_orderby( string $orderby ): self {
		$this->orderby = $orderby;

		return $this;
	}

	/**
	 * Set the order direction.
	 *
	 * @param string $order The order direction ('ASC' or 'DESC').
	 *
	 * @return self
	 */
	public function set_order( string $order ): self {
		$this->order = strtoupper( $order );

		return $this;
	}

	/**
	 * Set whether to include replies.
	 *
	 * @param bool $include Whether to include replies.
	 *
	 * @return self
	 */
	public function set_include_replies( bool $include ): self {
		$this->include_replies = $include;

		return $this;
	}

	/**
	 * Set post types to get comments from.
	 *
	 * @param array $post_types Array of post types.
	 *
	 * @return self
	 */
	public function set_post_types( array $post_types ): self {
		$this->post_types = $post_types;

		return $this;
	}

	/**
	 * Set specific post ID to get comments from.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return self
	 */
	public function set_post_id( int $post_id ): self {
		$this->post_id = $post_id;

		return $this;
	}

	/**
	 * Set user IDs to get comments from.
	 *
	 * @param array $user_ids Array of user IDs.
	 *
	 * @return self
	 */
	public function set_user_ids( array $user_ids ): self {
		$this->user_ids = $user_ids;

		return $this;
	}

	/**
	 * Add a meta query to filter comments.
	 *
	 * @param string $key     The meta key.
	 * @param mixed  $value   The meta value.
	 * @param string $compare The comparison operator.
	 * @param string $type    The data type.
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
	 * Perform a search for comments.
	 *
	 * @param string $search         The search string.
	 * @param array  $args           Optional. Additional query arguments.
	 * @param bool   $return_objects Whether to return comment objects. Default false.
	 *
	 * @return array Array of formatted results or comment objects.
	 */
	public function get_results( string $search, array $args = [], bool $return_objects = false ): array {
		$search = Sanitize::search( $search );

		// Default query arguments
		$args = wp_parse_args( $args, [
			'search'       => $search,
			'status'       => $this->status,
			'number'       => $this->number,
			'orderby'      => $this->orderby,
			'order'        => $this->order,
			'hierarchical' => $this->include_replies ? 'threaded' : false,
		] );

		// Add post type filtering if set
		if ( ! is_null( $this->post_types ) ) {
			$args['post_type'] = $this->post_types;
		}

		// Add post ID filtering if set
		if ( ! is_null( $this->post_id ) ) {
			$args['post_id'] = $this->post_id;
		}

		// Add user ID filtering if set
		if ( ! is_null( $this->user_ids ) ) {
			$args['user_id__in'] = $this->user_ids;
		}

		// Add meta query if set
		if ( ! empty( $this->meta_query ) ) {
			$args['meta_query'] = $this->meta_query;
		}

		$query    = new WP_Comment_Query( $args );
		$comments = $query->get_comments();

		return $return_objects ? $comments : $this->format_results( $comments );
	}

	/**
	 * Format search results into an array of options.
	 *
	 * @param array $comments Array of comment objects.
	 *
	 * @return array Formatted search results.
	 */
	private function format_results( array $comments ): array {
		if ( empty( $comments ) ) {
			return [];
		}

		$options = [];

		foreach ( $comments as $comment ) {
			$options[] = [
				'value'   => absint( $comment->comment_ID ),
				'label'   => esc_html( wp_trim_words( $comment->comment_content, 10 ) ),
			];
		}

		return $options;
	}

}