<?php
/**
 * Posts Core Trait
 *
 * Provides fundamental functionality for retrieving and querying multiple WordPress posts.
 * Includes methods for fetching posts by various criteria like IDs, titles, authors,
 * categories, dates, and other common parameters.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\Posts;

use ArrayPress\Utils\Common\Sanitize;
use ArrayPress\Utils\Posts\Post;
use WP_Post;

/**
 * Core Trait
 *
 * Handles basic post retrieval and query operations for multiple posts.
 */
trait Core {

	/**
	 * Get multiple posts by their IDs.
	 *
	 * @param array        $post_ids    Array of post IDs.
	 * @param string|array $post_type   Optional. Post type or array of post types. Default 'any'.
	 * @param string|array $post_status Optional. Post status or array of post statuses. Default 'publish'.
	 *
	 * @return WP_Post[] Array of WP_Post objects.
	 */
	public static function get( array $post_ids, $post_type = 'any', $post_status = 'publish' ): array {
		if ( empty( $post_ids ) ) {
			return [];
		}

		return get_posts( [
			'post__in'            => array_map( 'absint', $post_ids ),
			'post_type'           => $post_type,
			'post_status'         => $post_status,
			'posts_per_page'      => count( $post_ids ),
			'orderby'             => 'post__in',
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
		] );
	}

	/**
	 * Get an array of unique post IDs based on provided post titles or slugs.
	 *
	 * @param array  $post_titles An array of post titles or slugs to search for.
	 * @param string $post_type   Optional. The post type to search within. Default is 'post'.
	 *
	 * @return array An array of unique post IDs as integers.
	 */
	public static function get_ids_by_titles( array $post_titles, string $post_type = 'post' ): array {
		if ( empty( $post_titles ) ) {
			return [];
		}

		$unique_post_ids = [];
		foreach ( $post_titles as $title_or_slug ) {
			if ( ! empty( $title_or_slug ) ) {
				$post_id = Post::get_id_by_title( $title_or_slug, $post_type );
				if ( $post_id ) {
					$unique_post_ids[] = $post_id;
				}
			}
		}

		return array_unique( $unique_post_ids );
	}

	/**
	 * Get post titles based on provided post IDs.
	 *
	 * @param int[] $post_ids Array of post IDs.
	 *
	 * @return array An array of post titles keyed by post ID.
	 */
	public static function get_titles_by_ids( array $post_ids ): array {
		$titles   = [];
		$post_ids = Sanitize::object_ids( $post_ids );

		if ( empty( $post_ids ) ) {
			return $titles;
		}

		foreach ( $post_ids as $post_id ) {
			$post = get_post( $post_id );
			if ( $post ) {
				$titles[ $post_id ] = $post->post_title;
			}
		}

		return $titles;
	}

	/**
	 * Get post thumbnail URLs based on provided post IDs.
	 *
	 * @param int[]  $post_ids Array of post IDs.
	 * @param string $size     The size of the thumbnail (default: 'thumbnail').
	 *
	 * @return array An array of post thumbnail URLs keyed by post ID.
	 */
	public static function get_thumbnails_by_ids( array $post_ids, string $size = 'thumbnail' ): array {
		$thumbnails = [];
		$post_ids   = Sanitize::object_ids( $post_ids );

		if ( empty( $post_ids ) ) {
			return $thumbnails;
		}

		foreach ( $post_ids as $post_id ) {
			$thumbnail_url = get_the_post_thumbnail_url( $post_id, $size );
			if ( $thumbnail_url ) {
				$thumbnails[ $post_id ] = $thumbnail_url;
			}
		}

		return $thumbnails;
	}

	/**
	 * Get post permalinks based on provided post IDs.
	 *
	 * @param int[] $post_ids Array of post IDs.
	 *
	 * @return array An array of post permalinks keyed by post ID.
	 */
	public static function get_permalinks_by_ids( array $post_ids ): array {
		$permalinks = [];
		$post_ids   = Sanitize::object_ids( $post_ids );

		if ( empty( $post_ids ) ) {
			return $permalinks;
		}

		foreach ( $post_ids as $post_id ) {
			$permalink = get_permalink( $post_id );
			if ( $permalink ) {
				$permalinks[ $post_id ] = $permalink;
			}
		}

		return $permalinks;
	}

}