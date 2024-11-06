<?php
/**
 * Post Media Trait
 *
 * Provides functionality for working with post media attachments, including
 * featured images (thumbnails), and other media-related operations.
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

trait Media {

	/**
	 * Retrieve the URL of the post thumbnail.
	 *
	 * Gets the URL of the post's featured image (thumbnail) if one is set.
	 *
	 * @param int $post_id The ID of the post.
	 *
	 * @return string|false The URL of the post thumbnail or false if not set.
	 */
	public static function get_thumbnail_url( int $post_id ) {
		return get_the_post_thumbnail_url( $post_id );
	}

	/**
	 * Get the featured image (thumbnail) HTML for a post.
	 *
	 * Retrieves the post thumbnail with specified dimensions and attributes.
	 * If no thumbnail exists, returns an empty string.
	 *
	 * @param int   $post_id The post ID. Default 0 (current post).
	 * @param array $args    {
	 *                       Optional. Arguments to customize the thumbnail display.
	 *
	 * @type int    $width   Thumbnail width in pixels. Default 64.
	 * @type int    $height  Thumbnail height in pixels. Default 64.
	 * @type string $class   CSS class names. Default empty.
	 * @type string $alt     Image alt text. Default empty.
	 * @type array  $size    Image size. Default calculated from width/height.
	 *                       }
	 *
	 * @return string HTML img element or empty string if no thumbnail exists.
	 */
	public static function get_thumbnail( int $post_id = 0, array $args = [] ): string {
		$default_args = [
			'width'  => 64,
			'height' => 64,
			'class'  => '',
			'alt'    => '',
			'size'   => [ $args['width'] ?? 64, $args['height'] ?? 64 ]
		];

		$args = wp_parse_args( $args, $default_args );

		return get_the_post_thumbnail( $post_id, $args['size'], $args );
	}

	/**
	 * Get attached media.
	 *
	 * Retrieves media attachments associated with the post.
	 *
	 * @param int    $post_id    The post ID.
	 * @param string $media_type Optional. The media type (e.g., 'image', 'video'). Default 'any'.
	 *
	 * @return WP_Post[] An array of attachment objects.
	 */
	public static function get_attached_media( int $post_id, string $media_type = 'any' ): array {
		return get_attached_media( $media_type, $post_id );
	}

	/**
	 * Check if a post has a thumbnail.
	 *
	 * Determines whether the post has a featured image (thumbnail) set
	 * and if the current theme supports post thumbnails.
	 *
	 * @param int $post_id The ID of the post.
	 *
	 * @return bool True if the post has a thumbnail and theme supports it, false otherwise.
	 */
	public static function has_thumbnail( int $post_id ): bool {
		return current_theme_supports( 'post-thumbnails' ) && has_post_thumbnail( $post_id );
	}

	/**
	 * Get all image attachments.
	 *
	 * Retrieves all image attachments associated with the post.
	 *
	 * @param int   $post_id The post ID.
	 * @param array $args    Optional. Arguments to pass to get_posts() for retrieving images.
	 *
	 * @return WP_Post[] Array of attachment post objects.
	 */
	public static function get_image_attachments( int $post_id, array $args = [] ): array {
		$default_args = [
			'post_parent'    => $post_id,
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'post_status'    => 'inherit',
			'posts_per_page' => - 1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC'
		];

		$args = wp_parse_args( $args, $default_args );

		return get_posts( $args );
	}

	/**
	 * Get featured image ID.
	 *
	 * Retrieves the ID of the post's featured image.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return int|false The featured image ID if set, false otherwise.
	 */
	public static function get_thumbnail_id( int $post_id ) {
		return get_post_thumbnail_id( $post_id );
	}

	/**
	 * Get all video attachments.
	 *
	 * Retrieves all video attachments associated with the post.
	 *
	 * @param int   $post_id The post ID.
	 * @param array $args    Optional. Arguments to pass to get_posts() for retrieving videos.
	 *
	 * @return WP_Post[] Array of attachment post objects.
	 */
	public static function get_video_attachments( int $post_id, array $args = [] ): array {
		$default_args = [
			'post_parent'    => $post_id,
			'post_type'      => 'attachment',
			'post_mime_type' => 'video',
			'post_status'    => 'inherit',
			'posts_per_page' => - 1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC'
		];

		$args = wp_parse_args( $args, $default_args );

		return get_posts( $args );
	}

	/**
	 * Get first image attachment.
	 *
	 * Retrieves the first image attachment associated with the post.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return WP_Post|null First image attachment or null if none exists.
	 */
	public static function get_first_image_attachment( int $post_id ): ?WP_Post {
		$images = self::get_image_attachments( $post_id, [ 'posts_per_page' => 1 ] );

		return ! empty( $images ) ? $images[0] : null;
	}

	/**
	 * Get attachment count.
	 *
	 * Gets the total number of attachments for the post.
	 *
	 * @param int         $post_id   The post ID.
	 * @param string|null $mime_type Optional. Filter by mime type (e.g., 'image', 'video').
	 *
	 * @return int Number of attachments.
	 */
	public static function get_attachment_count( int $post_id, ?string $mime_type = null ): int {
		$args = [
			'post_parent'    => $post_id,
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => - 1,
			'fields'         => 'ids'
		];

		if ( $mime_type !== null ) {
			$args['post_mime_type'] = $mime_type;
		}

		$attachments = get_posts( $args );

		return count( $attachments );
	}

}