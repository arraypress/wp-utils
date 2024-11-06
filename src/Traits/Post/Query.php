<?php
/**
 * Post Query Trait
 *
 * This trait provides advanced query methods for WordPress posts, including
 * querying by meta values, custom fields, content searching, revisions,
 * and scheduled posts.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\Post;

use WP_Post;
use WP_Query;

trait Query {

	/**
	 * Get next post by meta value.
	 *
	 * Retrieves the next post in sequence based on a numeric meta value.
	 *
	 * @param int    $post_id  The current post ID.
	 * @param string $meta_key The meta key to order by.
	 * @param string $order    Optional. Sort order ('ASC' or 'DESC'). Default 'ASC'.
	 *
	 * @return WP_Post|null Next post object or null if not found.
	 */
	public static function get_next_by_meta( int $post_id, string $meta_key, string $order = 'ASC' ): ?WP_Post {
		$current_value = get_post_meta( $post_id, $meta_key, true );
		if ( $current_value === '' ) {
			return null;
		}

		$args = [
			'post_type'      => get_post_type( $post_id ),
			'posts_per_page' => 1,
			'meta_key'       => $meta_key,
			'meta_value'     => $current_value,
			'meta_type'      => 'NUMERIC',
			'meta_compare'   => $order === 'ASC' ? '>' : '<',
			'orderby'        => 'meta_value_num',
			'order'          => $order,
		];

		$posts = get_posts( $args );

		return ! empty( $posts ) ? $posts[0] : null;
	}

	/**
	 * Get post by custom field value.
	 *
	 * Retrieves a post based on a custom field value.
	 *
	 * @param string $meta_key   The meta key to search by.
	 * @param mixed  $meta_value The meta value to match.
	 * @param array  $args       Optional. Additional query arguments.
	 *
	 * @return WP_Post|null Post object if found, null otherwise.
	 */
	public static function get_by_custom_field( string $meta_key, $meta_value, array $args = [] ): ?WP_Post {
		$default_args = [
			'meta_key'      => $meta_key,
			'meta_value'    => $meta_value,
			'post_type'     => 'any',
			'post_status'   => 'publish',
			'numberposts'   => 1,
			'no_found_rows' => true,
		];

		$args  = wp_parse_args( $args, $default_args );
		$posts = get_posts( $args );

		return ! empty( $posts ) ? $posts[0] : null;
	}

	/**
	 * Find post by content.
	 *
	 * Searches for a post containing specific content.
	 *
	 * @param string $content     The content to search for.
	 * @param array  $args        Optional. Additional query arguments.
	 * @param bool   $exact_match Whether to look for an exact content match.
	 *
	 * @return WP_Post|null Post object if found, null otherwise.
	 */
	public static function find_by_content( string $content, array $args = [], bool $exact_match = false ): ?WP_Post {
		$default_args = [
			'post_type'     => 'any',
			'post_status'   => 'publish',
			'numberposts'   => 1,
			'no_found_rows' => true,
		];

		if ( $exact_match ) {
			$default_args['exact']    = true;
			$default_args['sentence'] = true;
		}

		$default_args['s'] = $content;
		$args              = wp_parse_args( $args, $default_args );

		$query = new WP_Query( $args );

		return $query->have_posts() ? $query->posts[0] : null;
	}

	/**
	 * Get previous revision.
	 *
	 * Retrieves the previous revision of a post.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return WP_Post|null Previous revision or null if not found.
	 */
	public static function get_previous_revision( int $post_id ): ?WP_Post {
		$revisions = wp_get_post_revisions( $post_id, [
			'posts_per_page' => 1,
		] );

		return ! empty( $revisions ) ? array_shift( $revisions ) : null;
	}

	/**
	 * Get next scheduled post.
	 *
	 * Retrieves the next scheduled post after the current one.
	 *
	 * @param int   $post_id The current post ID.
	 * @param array $args    Optional. Additional query arguments.
	 *
	 * @return WP_Post|null Next scheduled post or null if not found.
	 */
	public static function get_next_scheduled( int $post_id, array $args = [] ): ?WP_Post {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return null;
		}

		$default_args = [
			'post_type'      => get_post_type( $post ),
			'post_status'    => 'future',
			'posts_per_page' => 1,
			'orderby'        => 'date',
			'order'          => 'ASC',
			'date_query'     => [
				[
					'after'     => $post->post_date,
					'inclusive' => false,
				],
			],
		];

		$args  = wp_parse_args( $args, $default_args );
		$query = new WP_Query( $args );

		return $query->have_posts() ? $query->posts[0] : null;
	}

}