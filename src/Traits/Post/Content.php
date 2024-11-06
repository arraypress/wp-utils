<?php
/**
 * Post Content Trait
 *
 * Provides functionality for working with post content, including content retrieval,
 * manipulation, analysis, and extraction of various elements from post content.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Traits\Post;

use ArrayPress\Utils\Common\Extract;
use ArrayPress\Utils\Common\Str;
use WP_Post;
use WP_User;

trait Content {

	/**
	 * Get a specific field from the post.
	 *
	 * Retrieves either a post field or meta field value for the specified post.
	 * First checks if the field exists as a post object property, then falls
	 * back to checking post meta.
	 *
	 * @param int    $post_id The post ID.
	 * @param string $field   The field name to retrieve.
	 *
	 * @return mixed The field value if found, null otherwise.
	 */
	public static function get_field( int $post_id, string $field ) {
		$post = get_post( $post_id );

		if ( ! $post ) {
			return null;
		}

		// First, check if it's a property of the post object
		if ( isset( $post->$field ) ) {
			return $post->$field;
		}

		// Check if it's a custom meta field
		return get_post_meta( $post->ID, $field, true );
	}

	/**
	 * Count words in post content.
	 *
	 * Calculates the total number of words in the post's content.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return int The number of words in the post content.
	 */
	public static function count_words( int $post_id ): int {
		$post = get_post( $post_id );

		return $post ? Str::word_count( $post->post_content ) : 0;
	}

	/**
	 * Get reading time estimate.
	 *
	 * Calculates the estimated reading time for the post content based
	 * on the average reading speed provided.
	 *
	 * @param int $post_id          The post ID.
	 * @param int $words_per_minute Optional. Average reading speed. Default 200.
	 *
	 * @return int Estimated reading time in minutes.
	 */
	public static function get_reading_time( int $post_id, int $words_per_minute = 200 ): int {
		$word_count = self::count_words( $post_id );

		return (int) ceil( $word_count / $words_per_minute );
	}

	/**
	 * Extract content from post based on type.
	 *
	 * Extracts various types of content (URLs, emails, mentions, etc.)
	 * from the post content using the Extract utility class.
	 *
	 * @param int    $post_id The post ID.
	 * @param string $type    The type of content to extract.
	 * @param array  $args    Additional arguments for specific extraction types.
	 *
	 * @return array An array of extracted content.
	 */
	public static function extract( int $post_id, string $type, array $args = [] ): array {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return [];
		}

		$content = $post->post_content;

		switch ( $type ) {
			case 'urls':
				return Extract::urls( $content );
			case 'image_urls':
				return Extract::image_urls( $content );
			case 'audio_urls':
				return Extract::audio_urls( $content );
			case 'video_urls':
				return Extract::video_urls( $content );
			case 'social_urls':
				return Extract::social_urls( $content );
			case 'archive_urls':
				return Extract::archive_urls( $content );
			case 'emails':
				return Extract::emails( $content );
			case 'mentions':
				return Extract::mentions( $content );
			case 'hashtags':
				return Extract::hashtags( $content );
			case 'amounts':
				return Extract::amounts( $content, $args['include_negative'] ?? false );
			case 'ip_addresses':
				return Extract::ip_addresses( $content );
			case 'phone_numbers':
				return Extract::phone_numbers( $content );
			case 'shortcodes':
				return Extract::shortcodes( $content );
			case 'dates':
				return Extract::dates( $content, $args['format'] ?? 'Y-m-d' );
			case 'user_ids':
				return Extract::user_ids( $content, $args['check_all_nums'] ?? false );
			case 'post_ids':
				return Extract::post_ids( $content, $args['check_all_nums'] ?? false );
			case 'usernames':
				return Extract::usernames( $content, $args['validate'] ?? true );
			case 'post_slugs':
				return Extract::post_slugs( $content, $args['validate'] ?? true );
			default:
				return [];
		}
	}

	/**
	 * Get post content.
	 *
	 * Retrieves the content of a post, optionally unfiltered.
	 *
	 * @param int  $post_id The post ID.
	 * @param bool $raw     Whether to return raw content. Default false.
	 *
	 * @return string Post content.
	 */
	public static function get_content( int $post_id, bool $raw = false ): string {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return '';
		}

		return $raw ? $post->post_content : apply_filters( 'the_content', $post->post_content );
	}

	/**
	 * Get post excerpt.
	 *
	 * @param int  $post_id The post ID.
	 * @param bool $raw     Whether to return raw excerpt. Default false.
	 *
	 * @return string Post excerpt.
	 */
	public static function get_excerpt( int $post_id, bool $raw = false ): string {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return '';
		}

		if ( $raw ) {
			return $post->post_excerpt;
		}

		return $post->post_excerpt ? apply_filters( 'get_the_excerpt', $post->post_excerpt )
			: wp_trim_words( $post->post_content );
	}

	/**
	 * Get post title.
	 *
	 * @param int  $post_id The post ID.
	 * @param bool $raw     Whether to return raw title. Default false.
	 *
	 * @return string Post title.
	 */
	public static function get_title( int $post_id, bool $raw = false ): string {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return '';
		}

		return $raw ? $post->post_title : apply_filters( 'the_title', $post->post_title );
	}

	/**
	 * Get post author details.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return array Author details including ID, name, email, and URL.
	 */
	public static function get_author_details( int $post_id ): array {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return [];
		}

		$author = get_userdata( $post->post_author );
		if ( ! $author ) {
			return [];
		}

		return [
			'id'           => $author->ID,
			'login'        => $author->user_login,
			'email'        => $author->user_email,
			'url'          => $author->user_url,
			'display_name' => $author->display_name,
			'nickname'     => $author->nickname,
			'first_name'   => $author->first_name,
			'last_name'    => $author->last_name,
			'description'  => $author->description,
			'avatar_url'   => get_avatar_url( $author->ID )
		];
	}

	/**
	 * Get post author user object.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return WP_User|null User object if author exists, null otherwise.
	 */
	public static function get_author( int $post_id ): ?WP_User {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return null;
		}

		$author = get_userdata( $post->post_author );

		return ( $author instanceof WP_User ) ? $author : null;
	}

	/**
	 * Get post author ID.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return int|null Author ID if post exists, null otherwise.
	 */
	public static function get_author_id( int $post_id ): ?int {
		$post = get_post( $post_id );

		return $post ? (int) $post->post_author : null;
	}

	/**
	 * Get post content type.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return string Content type ('block', 'classic', or 'unknown').
	 */
	public static function get_content_type( int $post_id ): string {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return 'unknown';
		}

		if ( use_block_editor_for_post( $post ) ) {
			return has_blocks( $post->post_content ) ? 'block' : 'classic';
		}

		return 'classic';
	}

	/**
	 * Get post content statistics.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return array Array of content statistics.
	 */
	public static function get_content_stats( int $post_id ): array {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return [];
		}

		$content = $post->post_content;

		return [
			'word_count'      => Str::word_count( $content ),
			'character_count' => strlen( strip_tags( $content ) ),
			'paragraph_count' => substr_count( $content, '</p>' ),
			'has_shortcodes'  => has_shortcode( $content, '' ),
			'has_blocks'      => function_exists( 'has_blocks' ) && has_blocks( $content ),
			'reading_time'    => self::get_reading_time( $post_id ),
			'has_images'      => ! empty( Extract::image_urls( $content ) ),
			'has_links'       => ! empty( Extract::urls( $content ) ),
			'content_type'    => self::get_content_type( $post_id )
		];
	}

}