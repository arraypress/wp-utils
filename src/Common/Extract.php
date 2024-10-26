<?php
/**
 * Extraction Utilities for WordPress
 *
 * This class provides a set of utility methods for extracting specific data
 * from strings, such as mentions, hashtags, URLs, amounts, emails, and more.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Common;

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
		 * Extract unique mentions (@username) from a string while excluding email addresses.
		 * Uses WordPress username validation and simple string parsing.
		 *
		 * @param string $string The input string.
		 *
		 * @return array An array of unique extracted mentions without the @ symbol.
		 */
		public static function mentions( string $string ): array {
			// Split string into words
			$words = preg_split( '/\s+/', $string );

			// Initialize array for valid mentions
			$mentions = [];

			foreach ( $words as $word ) {
				// Skip if not starting with @
				if ( ! str_starts_with( $word, '@' ) ) {
					continue;
				}

				// Skip if it looks like an email
				if ( str_contains( $word, '.' ) ) {
					continue;
				}

				// Extract username part (remove @ and any trailing punctuation)
				$username = preg_replace( '/^@([a-zA-Z0-9_-]+).*$/', '$1', $word );

				// Skip empty results
				if ( empty( $username ) ) {
					continue;
				}

				// Validate using WordPress function
				if ( validate_username( $username ) ) {
					$mentions[] = $username;
				}
			}

			return array_unique( $mentions );
		}

		/**
		 * Extract unique hashtags (#hashtag) from a string.
		 *
		 * @param string $string The input string.
		 *
		 * @return array An array of unique extracted hashtags.
		 */
		public static function hashtags( string $string ): array {
			preg_match_all( '/#(\w+)/', $string, $matches );

			return ! empty( $matches[1] ) ? array_unique( $matches[1] ) : [];
		}

		/**
		 * Extract unique and valid URLs from a string.
		 *
		 * @param string $string The input string.
		 *
		 * @return array An array of unique, validated URLs.
		 */
		public static function urls( string $string ): array {
			// Extract URLs using WordPress function
			$urls = wp_extract_urls( $string );

			// Validate URLs and remove duplicates
			$valid_urls = array_unique( array_filter( $urls, function ( $url ) {
				return filter_var( $url, FILTER_VALIDATE_URL ) !== false;
			} ) );

			// Reindex the array
			return array_values( $valid_urls );
		}

		/**
		 * Extract unique email addresses from a string.
		 *
		 * @param string $string The input string.
		 *
		 * @return array An array of unique extracted email addresses.
		 */
		public static function emails( string $string ): array {
			$words  = str_word_count( $string, 1, '.@+-_' );
			$emails = array_filter( $words, 'is_email' );

			// Trim, convert to lowercase, and remove duplicates
			$emails = array_map( function ( $email ) {
				return strtolower( trim( $email ) );
			}, $emails );

			// Remove duplicates and reindex the array
			return array_values( array_unique( $emails ) );
		}

		/**
		 * Extract monetary amounts from a string.
		 *
		 * @param string $string           The input string.
		 * @param bool   $include_negative Whether to include negative amounts (default false).
		 *
		 * @return array An array of extracted monetary amounts.
		 */
		public static function amounts( string $string, bool $include_negative = false ): array {
			$pattern = $include_negative
				? '/(-?\$?\s?[0-9,]+(\.[0-9]{2})?)/u'
				: '/(\$?\s?[0-9,]+(\.[0-9]{2})?)/u';

			preg_match_all( $pattern, $string, $matches );

			return array_map( function ( $amount ) {
				$amount = str_replace( [ ',', '$', ' ' ], '', $amount );

				return floatval( $amount );
			}, $matches[1] );
		}

		/**
		 * Extract IP addresses from a string.
		 *
		 * @param string $string The input string.
		 *
		 * @return array An array of extracted IP addresses.
		 */
		public static function ip_addresses( string $string ): array {
			$words = preg_split( '/\s+/', $string );

			$ip_addresses = array_filter( array_map( function ( $word ) {
				$trimmed = trim( $word );

				return rest_is_ip_address( $trimmed ) ? $trimmed : null;
			}, $words ) );

			// Remove duplicates and re-index the array
			return array_values( array_unique( $ip_addresses ) );
		}

		/**
		 * Extract unique phone numbers from a string.
		 *
		 * @param string $string The input string.
		 *
		 * @return array An array of unique extracted phone numbers.
		 */
		public static function phone_numbers( string $string ): array {
			$pattern = '/\+?([0-9]{1,4})?[-.\s]?\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}/';
			preg_match_all( $pattern, $string, $matches );

			// Remove duplicates and reindex the array
			return array_values( array_unique( array_map( 'trim', $matches[0] ) ) );
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

			return array_values( array_unique( $hex_colors ) );
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