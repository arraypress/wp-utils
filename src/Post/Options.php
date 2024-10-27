<?php
/**
 * Post Utilities for WordPress
 *
 * This class provides utility functions for working with WordPress posts, including
 * methods for checking post existence, retrieving post data, working with post types,
 * checking for shortcodes and blocks, handling post metadata, and managing post content.
 * It also offers functions to work with taxonomy terms, post thumbnails, and scheduling posts.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Post;

use WP_Post;
use WP_User;
use WP_Term;
use WP_Error;
use WP_Query;
use ArrayPress\Utils\Common\Extract;
use ArrayPress\Utils\Common\Str;
use ArrayPress\Utils\Database\Exists;
use ArrayPress\Utils\Shared\Meta;

/**
 * Check if the class `Taxonomy` is defined, and if not, define it.
 */
if ( ! class_exists( 'Options' ) ) :

	/**
	 * Post Utilities
	 *
	 * Provides utility functions for managing WordPress posts, such as checking post existence,
	 * retrieving post data by various identifiers, working with post meta, taxonomy terms, and
	 * handling post content. It also supports managing post thumbnails, scheduling posts, and
	 * extracting content-related information like links and word count.
	 */
	class Options {

		/**
		 * Get post statuses and return them in label/value format.
		 *
		 * @param array $args Optional. Arguments to filter post statuses.
		 *
		 * @return array An array of post statuses in label/value format.
		 */
		public static function get_statuses( array $args = [] ): array {
			$defaults   = [];
			$args       = wp_parse_args( $args, $defaults );
			$post_stati = get_post_stati( $args, 'objects' );

			if ( empty( $post_stati ) || ! is_array( $post_stati ) ) {
				return [];
			}

			$options = [];

			foreach ( $post_stati as $status => $details ) {
				if ( ! isset( $status, $details ) ) {
					continue;
				}

				$options[] = [
					'value' => esc_attr( $status ),
					'label' => esc_html( $details->label ?? $status ),
				];
			}

			return $options;
		}

		/**
		 * Get registered custom post types and return them in label/value format.
		 *
		 * @param array $args Optional. Arguments to filter custom post types.
		 *
		 * @return array An array of custom post types in label/value format.
		 */
		public static function get_custom_post_types( array $args = [] ): array {
			$defaults   = [ '_builtin' => false ];
			$args       = wp_parse_args( $args, $defaults );
			$post_types = get_post_types( $args, 'objects' );

			if ( empty( $post_types ) || ! is_array( $post_types ) ) {
				return [];
			}

			$options = [];

			foreach ( $post_types as $post_type => $details ) {
				$options[] = [
					'value' => esc_attr( $post_type ),
					'label' => esc_html( $details->label ),
				];
			}

			return $options;
		}

	}
endif;