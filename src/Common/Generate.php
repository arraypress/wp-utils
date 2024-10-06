<?php
/**
 * Generation Utilities
 *
 * This class provides utility functions for generating and manipulating various data.
 * It includes helpers for generating unique slugs, usernames, transaction IDs, nonces,
 * filenames, UUIDs, and hashes, as well as creating random color codes. These utilities
 * ensure flexibility and ease of use across a variety of WordPress applications.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Common;

use Random\RandomException;

/**
 * Check if the class `Generate` is defined, and if not, define it.
 */
if ( ! class_exists( 'Generate' ) ) :

	/**
	 * Generation Utilities
	 *
	 * This class provides utility functions for generating and manipulating various data structures,
	 * enabling flexible adjustments and streamlined handling of common data patterns.
	 */
	class Generate {

		/**
		 * Generates a cache key using a prefix and any number of arguments.
		 *
		 * @param string $prefix  The prefix for the cache key.
		 * @param mixed  ...$args Any number of arguments to be included in the cache key.
		 *
		 * @return string The generated cache key.
		 */
		public static function cache_key( string $prefix, ...$args ): string {
			return $prefix . '_' . md5( serialize( $args ) );
		}

		/**
		 * Generate a unique post slug.
		 *
		 * @param string $title     The post title to base the slug on.
		 * @param string $post_type The post type (default: 'post').
		 *
		 * @return string The generated unique slug.
		 */
		public static function unique_post_slug( string $title, string $post_type = 'post' ): string {
			$slug          = sanitize_title( $title );
			$original_slug = $slug;
			$i             = 1;

			while ( get_page_by_path( $slug, OBJECT, $post_type ) ) {
				$slug = $original_slug . '-' . $i ++;
			}

			return $slug;
		}

		/**
		 * Generate a unique post title.
		 *
		 * @param string $base_title The base title to start with.
		 * @param string $post_type  The post type to check against.
		 *
		 * @return string A unique post title.
		 */
		public static function unique_post_title( string $base_title, string $post_type = 'post' ): string {
			$title   = $base_title;
			$counter = 1;
			while ( post_exists( $title, '', '', $post_type ) ) {
				$title = $base_title . ' ' . $counter;
				$counter ++;
			}

			return $title;
		}

		/**
		 * Generate a unique term slug.
		 *
		 * @param string $term_name The term name to base the slug on.
		 * @param string $taxonomy  The taxonomy to check against.
		 *
		 * @return string A unique term slug.
		 */
		public static function unique_term_slug( string $term_name, string $taxonomy ): string {
			$slug          = sanitize_title( $term_name );
			$original_slug = $slug;
			$counter       = 1;
			while ( term_exists( $slug, $taxonomy ) ) {
				$slug = $original_slug . '-' . $counter;
				$counter ++;
			}

			return $slug;
		}

		/**
		 * Generate a unique comment ID.
		 *
		 * @return int A unique comment ID.
		 */
		public static function unique_comment_id(): int {
			global $wpdb;
			$comment_id = $wpdb->get_var( "SELECT MAX(comment_ID) FROM $wpdb->comments" ) + 1;

			return $comment_id;
		}

		/**
		 * Generate a unique menu item ID.
		 *
		 * @return int A unique menu item ID.
		 */
		public static function unique_menu_item_id(): int {
			return wp_unique_id( 'menu-item-' );
		}

		/**
		 * Generate a unique attachment filename.
		 *
		 * @param string $filename The original filename.
		 * @param int    $post_id  The ID of the post to attach the file to.
		 * @param string $dir      The directory to check for existing files.
		 *
		 * @return string A unique attachment filename.
		 */
		public static function unique_attachment_filename( string $filename, int $post_id, string $dir = '' ): string {
			return wp_unique_filename( $dir, $filename, $post_id );
		}

		/**
		 * Generate a unique plugin slug.
		 *
		 * @param string $plugin_name The plugin name to base the slug on.
		 *
		 * @return string A unique plugin slug.
		 */
		public static function unique_plugin_slug( string $plugin_name ): string {
			$slug          = sanitize_title( $plugin_name );
			$all_plugins   = get_plugins();
			$counter       = 1;
			$original_slug = $slug;
			while ( isset( $all_plugins[ $slug ] ) ) {
				$slug = $original_slug . '-' . $counter;
				$counter ++;
			}

			return $slug;
		}

		/**
		 * Generate a unique option name.
		 *
		 * @param string $base_name The base name for the option.
		 *
		 * @return string A unique option name.
		 */
		public static function unique_option_name( string $base_name ): string {
			$option_name = sanitize_key( $base_name );
			$counter     = 1;
			while ( get_option( $option_name ) !== false ) {
				$option_name = sanitize_key( $base_name . '_' . $counter );
				$counter ++;
			}

			return $option_name;
		}

		/**
		 * Generate a unique transient name.
		 *
		 * @param string $base_name The base name for the transient.
		 *
		 * @return string A unique transient name.
		 */
		public static function unique_transient_name( string $base_name ): string {
			$transient_name = substr( sanitize_key( $base_name ), 0, 172 ); // Max length of 172 characters
			$counter        = 1;
			while ( get_transient( $transient_name ) !== false ) {
				$suffix         = '_' . $counter;
				$transient_name = substr( sanitize_key( $base_name ), 0, 172 - strlen( $suffix ) ) . $suffix;
				$counter ++;
			}

			return $transient_name;
		}

		/**
		 * Generate a unique capability name.
		 *
		 * @param string $base_name The base name for the capability.
		 *
		 * @return string A unique capability name.
		 */
		public static function unique_capability_name( string $base_name ): string {
			global $wp_roles;
			$capability = sanitize_key( $base_name );
			$counter    = 1;
			while ( $wp_roles->is_role( $capability ) || isset( $wp_roles->roles[ $capability ] ) ) {
				$capability = sanitize_key( $base_name . '_' . $counter );
				$counter ++;
			}

			return $capability;
		}

		/**
		 * Generate a unique widget ID.
		 *
		 * @param string $widget_base The base ID of the widget.
		 *
		 * @return string A unique widget ID.
		 */
		public static function unique_widget_id( string $widget_base ): string {
			global $wp_registered_widgets;
			$number = 1;
			while ( isset( $wp_registered_widgets[ $widget_base . '-' . $number ] ) ) {
				$number ++;
			}

			return $widget_base . '-' . $number;
		}

		/**
		 * Generate a unique transaction ID.
		 *
		 * @param string $prefix An optional prefix for the transaction ID.
		 *
		 * @return string The generated unique transaction ID.
		 */
		public static function unique_transaction_id( string $prefix = '' ): string {
			$unique_id = uniqid( $prefix, true );

			return str_replace( '.', '', $unique_id );
		}

		/**
		 * Generate a unique nonce with an optional action and user ID.
		 *
		 * @param string   $action  The action to associate with the nonce.
		 * @param int|null $user_id The user ID to associate with the nonce.
		 *
		 * @return string The generated nonce.
		 */
		public static function unique_nonce( string $action = '', ?int $user_id = null ): string {
			if ( is_null( $user_id ) ) {
				$user_id = get_current_user_id();
			}

			return wp_create_nonce( $action . $user_id . time() );
		}

		/**
		 * Generate a unique file name with a given extension.
		 *
		 * @param string $extension The file extension (without the dot).
		 * @param string $prefix    An optional prefix for the filename.
		 *
		 * @return string The generated unique filename.
		 */
		public static function unique_filename( string $extension, string $prefix = '' ): string {
			$unique_id = uniqid( $prefix, true );

			return $unique_id . '.' . ltrim( $extension, '.' );
		}

		/**
		 * Generate a UUID v4.
		 *
		 * @return string The generated UUID.
		 */
		public static function uuid_v4(): string {
			return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
				mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
				mt_rand( 0, 0xffff ),
				mt_rand( 0, 0x0fff ) | 0x4000,
				mt_rand( 0, 0x3fff ) | 0x8000,
				mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
			);
		}

		/**
		 * Generate a hash of the given data.
		 *
		 * @param string $data The data to hash.
		 * @param string $algo The algorithm to use (default: 'sha256').
		 *
		 * @return string The generated hash.
		 */
		public static function hash( string $data, string $algo = 'sha256' ): string {
			return hash( $algo, $data );
		}

		/**
		 * Generate a random hexadecimal color code.
		 *
		 * @return string The generated color code.
		 */
		public static function random_color(): string {
			return sprintf( '#%06X', mt_rand( 0, 0xFFFFFF ) );
		}


		/**
		 * Generates random words.
		 *
		 * @param int $words  Number of words to generate. Default 1.
		 * @param int $length Length of each word. Default 6.
		 *
		 * @return string Generated words separated by hyphens.
		 */
		public static function random_words( int $words = 1, int $length = 6 ): string {
			$vowels     = [ 'a', 'e', 'i', 'o', 'u' ];
			$consonants = [
				'b',
				'c',
				'd',
				'f',
				'g',
				'h',
				'j',
				'k',
				'l',
				'm',
				'n',
				'p',
				'r',
				's',
				't',
				'v',
				'w',
				'x',
				'y',
				'z'
			];

			$result = array();
			for ( $w = 0; $w < $words; $w ++ ) {
				$word = '';
				for ( $i = 0; $i < $length; $i ++ ) {
					$word .= $consonants[ array_rand( $consonants ) ];
					$word .= $vowels[ array_rand( $vowels ) ];
				}
				$result[] = substr( $word, 0, $length );
			}

			return implode( '-', $result );
		}

		/**
		 * Generate a unique username from provided information.
		 *
		 * @param string $first_name First name.
		 * @param string $last_name  Last name.
		 * @param string $email      Email address.
		 * @param string $base_name  Optional base name to use instead of first/last name.
		 *
		 * @return string Generated unique username.
		 */
		public static function unique_username( string $first_name = '', string $last_name = '', string $email = '', string $base_name = '' ): string {
			$username = '';

			// Use base_name if provided
			if ( $base_name ) {
				$username = sanitize_user( $base_name, true );
			} // Try to create username using first and last name
			elseif ( $first_name && $last_name ) {
				$username = sanitize_user( $first_name[0] . $last_name, true );
			} elseif ( $first_name ) {
				$username = sanitize_user( $first_name, true );
			} elseif ( $last_name ) {
				$username = sanitize_user( $last_name, true );
			}

			// If no username yet or it already exists, try email
			if ( ! $username || username_exists( $username ) ) {
				if ( $email && is_email( $email ) ) {
					$email_parts = explode( '@', $email );
					$username    = sanitize_user( $email_parts[0], true );
				}
			}

			// If still no username, generate a random one
			if ( ! $username ) {
				$username = 'user_' . wp_generate_password( 6, false );
			}

			// Ensure username is unique
			$original_username = $username;
			$counter           = 1;
			while ( username_exists( $username ) ) {
				$username = $original_username . $counter;
				$counter ++;
			}

			return $username;
		}

		/**
		 * Generates a random coupon code.
		 *
		 * @param int  $length    Optional. The length of the coupon code to generate. Default 8.
		 * @param bool $lowercase Optional. Whether to return the code in lowercase. Default false.
		 *
		 * @return string|false The random coupon code or false on failure.
		 */
		public static function coupon_code( int $length = 8, bool $lowercase = false ): string {
			if ( $length < 1 ) {
				return '';
			}

			$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
			$code  = '';

			for ( $i = 0; $i < $length; $i ++ ) {
				$code .= $chars[ wp_rand( 0, strlen( $chars ) - 1 ) ];
			}

			return $lowercase ? strtolower( $code ) : $code;
		}

		/**
		 * Generate a more truly "random" alpha-numeric string.
		 *
		 * @param int $length The length of the random string.
		 *
		 * @return string The random string.
		 * @throws RandomException
		 * @throws RandomException
		 */
		public static function random_string( int $length = 16 ): string {
			$string = '';
			while ( ( $len = strlen( $string ) ) < $length ) {
				$size   = $length - $len;
				$bytes  = random_bytes( $size );
				$string .= substr( str_replace( [ '/', '+', '=' ], '', base64_encode( $bytes ) ), 0, $size );
			}

			return $string;
		}

	}
endif;