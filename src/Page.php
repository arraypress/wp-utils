<?php
/**
 * Page Utilities for WordPress
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\Utils;

/**
 * Check if the class `Page` is defined, and if not, define it.
 */
if ( ! class_exists( 'Page' ) ) :

	/**
	 * Page Utilities
	 *
	 * Provides utility functions for managing WordPress posts, such as checking post existence,
	 * retrieving post data by various identifiers, working with post meta, taxonomy terms, and
	 * handling post content. It also supports managing post thumbnails, scheduling posts, and
	 * extracting content-related information like links and word count.
	 */
	class Page {

		/**
		 * Get page templates and return them in label/value format.
		 *
		 * @return array An array of page templates in label/value format.
		 */
		public static function get_template_options(): array {
			$theme = wp_get_theme();

			if ( ! $theme->exists() ) {
				return [];
			}

			$page_templates = $theme->get_page_templates();

			$options = [
				[
					'value' => 'default',
					'label' => esc_html__( 'Default Template', 'arraypress' ),
				]
			];

			foreach ( $page_templates as $template_filename => $template_name ) {
				$options[] = [
					'value' => esc_attr( $template_filename ),
					'label' => esc_html( $template_name ),
				];
			}

			return $options;
		}

		/**
		 * Determines if we're currently on a specific page.
		 *
		 * @param int|string|WP_Post $page Page ID, title, slug, or WP_Post object.
		 *
		 * @return bool True if on the specified page, false otherwise.
		 */
		public static function is_specific( $page ): bool {
			return is_page( $page );
		}

		/**
		 * Retrieve the URI of a specific page.
		 *
		 * @param int|WP_Post $page         Page ID or WP_Post object.
		 * @param string      $query_string Optional. The query string to append to the URI.
		 *
		 * @return string The URI of the specified page.
		 */
		public static function get_uri( $page, string $query_string = '' ): string {
			$page_uri = get_permalink( $page );

			if ( $page_uri && $query_string ) {
				$page_uri = add_query_arg( $query_string, '', $page_uri );
			}

			return $page_uri ?: '';
		}

		/**
		 * Checks if a specific page exists.
		 *
		 * @param int|string|WP_Post $page Page ID, title, slug, or WP_Post object.
		 *
		 * @return bool True if the page exists, false otherwise.
		 */
		public static function exists( $page ): bool {
			return get_post_status( $page ) !== false;
		}

		/**
		 * Retrieves the title of a specific page.
		 *
		 * @param int|WP_Post $page Page ID or WP_Post object.
		 *
		 * @return string The title of the specified page.
		 */
		public static function get_title( $page ): string {
			return get_the_title( $page );
		}

		/**
		 * Retrieves the content of a specific page.
		 *
		 * @param int|WP_Post $page Page ID or WP_Post object.
		 *
		 * @return string The content of the specified page.
		 */
		public static function get_content( $page ): string {
			$post = get_post( $page );

			return $post ? apply_filters( 'the_content', $post->post_content ) : '';
		}

		/**
		 * Checks if a specific page is published.
		 *
		 * @param int|WP_Post $page Page ID or WP_Post object.
		 *
		 * @return bool True if the page is published, false otherwise.
		 */
		public static function is_published( $page ): bool {
			return get_post_status( $page ) === 'publish';
		}

		/**
		 * Retrieves the template of a specific page.
		 *
		 * @param int|WP_Post $page Page ID or WP_Post object.
		 *
		 * @return string The template of the specified page.
		 */
		public static function get_template( $page ): string {
			return get_page_template_slug( $page ) ?: '';
		}

		/**
		 * Checks if a specific page has a specific template.
		 *
		 * @param int|WP_Post $page     Page ID or WP_Post object.
		 * @param string      $template The template to check.
		 *
		 * @return bool True if the page has the specified template, false otherwise.
		 */
		public static function is_template( $page, string $template ): bool {
			return get_page_template_slug( $page ) === $template;
		}

		/**
		 * Get the parent page ID.
		 *
		 * @param int|WP_Post $page Page ID or WP_Post object.
		 *
		 * @return int Parent page ID, or 0 if there's no parent.
		 */
		public static function get_parent_id( $page ): int {
			$post = get_post( $page );

			return $post ? (int) $post->post_parent : 0;
		}

		/**
		 * Check if the page is a parent page (has no parent).
		 *
		 * @param int|WP_Post $page Page ID or WP_Post object.
		 *
		 * @return bool True if it's a parent page, false otherwise.
		 */
		public static function is_parent_page( $page ): bool {
			return self::get_parent_id( $page ) === 0;
		}

		/**
		 * Get child pages of a specific page.
		 *
		 * @param int|WP_Post $page Page ID or WP_Post object.
		 *
		 * @return array An array of child page IDs.
		 */
		public static function get_child_pages( $page ): array {
			$children = get_pages( [
				'child_of' => $page,
			] );

			return array_map( function ( $child ) {
				return $child->ID;
			}, $children );
		}

		/**
		 * Check if a page has child pages.
		 *
		 * @param int|WP_Post $page Page ID or WP_Post object.
		 *
		 * @return bool True if the page has children, false otherwise.
		 */
		public static function has_children( $page ): bool {
			$children = self::get_child_pages( $page );

			return ! empty( $children );
		}

		/**
		 * Get the page's last modified date.
		 *
		 * @param int|WP_Post $page   Page ID or WP_Post object.
		 * @param string      $format Optional. PHP date format. Default 'Y-m-d H:i:s'.
		 *
		 * @return string The formatted date.
		 */
		public static function get_last_modified( $page, string $format = 'Y-m-d H:i:s' ): string {
			$post = get_post( $page );

			return $post ? mysql2date( $format, $post->post_modified ) : '';
		}
	}

endif;