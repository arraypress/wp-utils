<?php
/**
 * Extraction Utilities for WordPress
 *
 * This class provides a set of utility methods for extracting specific data
 * from strings, such as mentions, hashtags, URLs, amounts, emails, and more.
 *
 * @package       ArrayPress/Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils;

/**
 * Check if the class `Extract` is defined, and if not, define it.
 */
if ( ! class_exists( 'Extract' ) ) :

	/**
	 * Extract Utility Class
	 *
	 * Provides methods for extracting various types of data from strings.
	 */
	class Extract {

		/**
		 * Extract mentions (@username) from a string.
		 *
		 * @param string $string The input string.
		 *
		 * @return array An array of extracted mentions.
		 */
		public static function mentions( string $string ): array {
			preg_match_all( '/@(\w+)/', $string, $matches );

			return $matches[1];
		}

		/**
		 * Extract hashtags (#hashtag) from a string.
		 *
		 * @param string $string The input string.
		 *
		 * @return array An array of extracted hashtags.
		 */
		public static function hashtags( string $string ): array {
			preg_match_all( '/#(\w+)/', $string, $matches );

			return $matches[1];
		}

		/**
		 * Extract URLs from a string.
		 *
		 * @param string $string The input string.
		 *
		 * @return array An array of extracted URLs.
		 */
		public static function urls( string $string ): array {
			$urls = wp_extract_urls( $string );

			return array_filter( $urls, function ( $url ) {
				return wp_http_validate_url( $url ) !== false;
			} );
		}

		/**
		 * Extract email addresses from a string.
		 *
		 * @param string $string The input string.
		 *
		 * @return array An array of extracted email addresses.
		 */
		public static function emails( string $string ): array {
			$words  = str_word_count( $string, 1, '.@+-_' );
			$emails = array_filter( $words, 'is_email' );

			return array_values( $emails );
		}

		/**
		 * Extract monetary amounts from a string.
		 *
		 * @param string $string The input string.
		 *
		 * @return array An array of extracted monetary amounts.
		 */
		public static function amounts( string $string ): array {
			preg_match_all( '/\$?\s?([0-9,]+(\.[0-9]{2})?)/', $string, $matches );

			return array_map( 'floatval', str_replace( ',', '', $matches[1] ) );
		}

		/**
		 * Extract IP addresses from a string.
		 *
		 * @param string $string The input string.
		 *
		 * @return array An array of extracted IP addresses.
		 */
		public static function ip_addresses( string $string ): array {
			$words        = preg_split( '/\s+/', $string );
			$ip_addresses = array_filter( $words, 'rest_is_ip_address' );

			// Re-index the array to remove gaps in keys
			return array_values( $ip_addresses );
		}

		/**
		 * Extract phone numbers from a string.
		 *
		 * @param string $string The input string.
		 *
		 * @return array An array of extracted phone numbers.
		 */
		public static function phone_numbers( string $string ): array {
			$pattern = '/\+?([0-9]{1,4})?[-.\s]?\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}/';
			preg_match_all( $pattern, $string, $matches );

			return array_map( 'trim', $matches[0] );
		}

		/**
		 * Extract WordPress shortcodes from a string.
		 *
		 * @param string $string The input string.
		 *
		 * @return array An array of extracted shortcodes.
		 */
		public static function shortcodes( string $string ): array {
			global $shortcode_tags;

			if ( empty( $shortcode_tags ) || ! is_array( $shortcode_tags ) ) {
				return array();
			}

			// Get all registered shortcode tags
			$tagnames  = array_keys( $shortcode_tags );
			$tagregexp = join( '|', array_map( 'preg_quote', $tagnames ) );

			// Use WordPress's own shortcode regex pattern
			$pattern = '/' . get_shortcode_regex( array( $tagregexp ) ) . '/s';

			preg_match_all( $pattern, $string, $matches, PREG_SET_ORDER );

			$shortcodes = array();
			foreach ( $matches as $shortcode ) {
				$shortcodes[] = $shortcode[0];
			}

			return $shortcodes;
		}

		/**
		 * Extract image URLs from a string.
		 *
		 * @param string $string The input string.
		 *
		 * @return array An array of extracted image URLs.
		 */
		public static function image_urls( string $string ): array {
			$urls = self::urls( $string );

			return array_filter( $urls, [ URL::class, 'is_image' ] );
		}

		/**
		 * Extract audio URLs from a string.
		 *
		 * @param string $string The input string.
		 *
		 * @return array An array of extracted audio URLs.
		 */
		public static function audio_urls( string $string ): array {
			$urls = self::urls( $string );

			return array_filter( $urls, [ URL::class, 'is_audio' ] );
		}

		/**
		 * Extract video URLs from a string.
		 *
		 * @param string $string The input string.
		 *
		 * @return array An array of extracted video URLs.
		 */
		public static function video_urls( string $string ): array {
			$urls = self::urls( $string );

			return array_filter( $urls, [ URL::class, 'is_video' ] );
		}

		/**
		 * Extract social media URLs from a string.
		 *
		 * @param string $string The input string.
		 *
		 * @return array An array of extracted social media URLs.
		 */
		public static function social_urls( string $string ): array {
			$urls = self::urls( $string );

			return array_filter( $urls, [ URL::class, 'is_social' ] );
		}

		/**
		 * Extract archive URLs from a string.
		 *
		 * @param string $string The input string.
		 *
		 * @return array An array of extracted archive URLs.
		 */
		public static function archive_urls( string $string ): array {
			$urls = self::urls( $string );

			return array_filter( $urls, [ URL::class, 'is_archive' ] );
		}

		/**
		 * Extract WordPress user IDs from a string.
		 *
		 * @param string $string         The input string.
		 * @param bool   $check_all_nums Whether to check all numeric values in the string. Default false.
		 *
		 * @return array An array of extracted user IDs.
		 */
		public static function user_ids( string $string, bool $check_all_nums = false ): array {
			$user_ids = array();

			if ( $check_all_nums ) {
				preg_match_all( '/\b\d+\b/', $string, $matches );
			} else {
				preg_match_all( '/\buser_id[:=](\d+)\b/', $string, $matches );
				$matches = $matches[1];
			}

			foreach ( $matches as $potential_id ) {
				$user_id = intval( $potential_id );
				if ( get_userdata( $user_id ) !== false ) {
					$user_ids[] = $user_id;
				}
			}

			return array_unique( $user_ids );
		}

		/**
		 * Extract WordPress post IDs from a string.
		 *
		 * @param string $string         The input string.
		 * @param bool   $check_all_nums Whether to check all numeric values in the string. Default false.
		 *
		 * @return array An array of extracted post IDs.
		 */
		public static function post_ids( string $string, bool $check_all_nums = false ): array {
			$post_ids = array();

			if ( $check_all_nums ) {
				preg_match_all( '/\b\d+\b/', $string, $matches );
			} else {
				preg_match_all( '/\bpost_id[:=](\d+)\b/', $string, $matches );
				$matches = $matches[1];
			}

			foreach ( $matches as $potential_id ) {
				$post_id = intval( $potential_id );
				if ( get_post_status( $post_id ) !== false ) {
					$post_ids[] = $post_id;
				}
			}

			return array_unique( $post_ids );
		}

		/**
		 * Extract and validate WordPress usernames from a string.
		 *
		 * @param string $string   The input string.
		 * @param bool   $validate Whether to validate the existence of the users.
		 *
		 * @return array An array of extracted usernames or user objects if validated.
		 */
		public static function usernames( string $string, bool $validate = true ): array {
			$words               = preg_split( '/\s+/', $string );
			$potential_usernames = array_filter( $words, function ( $word ) {
				return preg_match( '/^[a-zA-Z0-9_-]{3,20}$/', $word );
			} );

			if ( ! $validate ) {
				return $potential_usernames;
			}

			$valid_users = array();
			foreach ( $potential_usernames as $username ) {
				$user = get_user_by( 'login', $username );
				if ( $user ) {
					$valid_users[] = $user;
				}
			}

			return $valid_users;
		}

		/**
		 * Extract WordPress post slugs from a string.
		 *
		 * @param string $string   The input string.
		 * @param bool   $validate Whether to validate the existence of the posts.
		 *
		 * @return array An array of extracted post slugs or post objects if validated.
		 */
		public static function post_slugs( string $string, bool $validate = true ): array {
			$words           = preg_split( '/\s+/', $string );
			$potential_slugs = array_filter( $words, function ( $word ) {
				return preg_match( '/^[a-zA-Z0-9-]+$/', $word );
			} );

			if ( ! $validate ) {
				return $potential_slugs;
			}

			$valid_posts = array();
			foreach ( $potential_slugs as $slug ) {
				$post = get_page_by_path( $slug, OBJECT, 'post' );
				if ( $post ) {
					$valid_posts[] = $post;
				}
			}

			return $valid_posts;
		}

		/**
		 * Extract hex color codes from a string.
		 *
		 * @param string $string The input string.
		 *
		 * @return array An array of extracted hex color codes.
		 */
		public static function hex_colors( string $string ): array {
			$words = preg_split( '/\s+/', $string );

			$hex_colors = array_filter( $words, function ( $word ) {
				return Validate::is_hex_color( $word );
			} );

			return array_values( $hex_colors );
		}

		/**
		 * Extract ISBNs from a string.
		 *
		 * @param string $string The input string.
		 *
		 * @return array An array of extracted ISBNs.
		 */
		public static function isbn_numbers( string $string ): array {
			$words = preg_split( '/\s+/', $string );
			$isbns = array_filter( $words, function ( $word ) {
				$isbn = preg_replace( '/[-\s]/', '', $word );

				return Validate::is_isbn10( $isbn ) || Validate::is_isbn13( $isbn );
			} );

			return array_map( function ( $isbn ) {
				return preg_replace( '/[-\s]/', '', $isbn );
			}, array_values( $isbns ) );
		}

		/**
		 * Extract dates from a string.
		 *
		 * @param string $string The input string.
		 * @param string $format The format to return the dates in (default: 'Y-m-d').
		 *
		 * @return array An array of extracted dates in the specified format.
		 */
		public static function dates( string $string, string $format = 'Y-m-d' ): array {
			$words = preg_split( '/\s+/', $string );
			$dates = array();

			foreach ( $words as $word ) {
				$timestamp = strtotime( $word );
				if ( $timestamp !== false ) {
					$date = date( $format, $timestamp );
					if ( $date !== false && $date !== '1970-01-01' ) {  // Avoid default dates
						$dates[] = $date;
					}
				}
			}

			return array_unique( $dates );
		}

		/**
		 * Extract times from a string.
		 *
		 * @param string $string The input string.
		 * @param string $format The format to return the times in (default: 'H:i:s').
		 *
		 * @return array An array of extracted times in the specified format.
		 */
		public static function times( string $string, string $format = 'H:i:s' ): array {
			$words = preg_split( '/\s+/', $string );
			$times = array();

			foreach ( $words as $word ) {
				$timestamp = strtotime( $word );
				if ( $timestamp !== false ) {
					$time = date( $format, $timestamp );
					if ( $time !== '00:00:00' ) {  // Avoid default times
						$times[] = $time;
					}
				}
			}

			return array_unique( $times );
		}

	}

endif;