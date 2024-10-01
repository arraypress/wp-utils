<?php
/**
 * URL Utilities for WordPress
 *
 * This class provides a wide range of utility functions for handling and manipulating URLs
 * in WordPress. It includes methods for validating URLs, extracting components like
 * domains or file extensions, and handling URL transformations such as relative and
 * absolute conversions. It also includes support for oEmbed and query parameter operations.
 *
 * @package       ArrayPress/Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       0.3.1
 */

declare( strict_types=1 );

namespace ArrayPress\Utils;

/**
 * Check if the class `URL` is defined, and if not, define it.
 */
if ( ! class_exists( 'URL' ) ) :

	/**
	 * URL utility class for WordPress.
	 *
	 * Provides comprehensive utility methods for working with URLs in WordPress.
	 * It includes support for URL validation, extracting components, handling media types,
	 * and manipulating query parameters, among other common URL-related tasks.
	 */
	class URL {

		/**
		 * Audio file extensions.
		 *
		 * @var array
		 */
		private static array $audio_extensions = [ 'mp3', 'm4a', 'ogg', 'wav', 'wma', 'flac', 'aac', 'webm' ];

		/**
		 * Image file extensions.
		 *
		 * @var array
		 */
		private static array $image_extensions = [ 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'tiff' ];

		/**
		 * Video file extensions.
		 *
		 * @var array
		 */
		private static array $video_extensions = [ 'mp4', 'm4v', 'mov', 'wmv', 'avi', 'flv', 'mkv', 'webm' ];

		/**
		 * Archive file extensions.
		 *
		 * @var array
		 */
		private static array $archive_extensions = [ 'zip', 'tar', 'gz', 'rar', '7z', 'bz2' ];

		/**
		 * Audio streaming domains.
		 *
		 * @var array
		 */
		private static array $audio_domains = [
			'spotify.com',
			'open.spotify.com',
			'music.apple.com',
			'tidal.com',
			'listen.tidal.com',
			'soundcloud.com',
			'deezer.com',
			'www.deezer.com',
			'pandora.com',
			'music.amazon.com',
			'music.youtube.com'
		];

		/**
		 * Video hosting domains.
		 *
		 * @var array
		 */
		private static array $video_domains = [
			'youtube.com',
			'youtu.be',
			'vimeo.com',
			'dailymotion.com',
			'twitch.tv',
			'vevo.com',
			'metacafe.com',
			'ted.com',
			'ustream.tv',
			'liveleak.com',
			'vine.co',
			'periscope.tv'
		];

		/**
		 * Social media domains.
		 *
		 * @var array
		 */
		private static array $social_domains = [
			'facebook.com',
			'fb.com',
			'instagram.com',
			'instagr.am',
			'twitter.com',
			't.co',
			'linkedin.com',
			'lnkd.in',
			'tiktok.com',
			'vm.tiktok.com',
			'pinterest.com',
			'pin.it',
			'reddit.com',
			'redd.it',
			'tumblr.com',
			'tumblr.co',
			'snapchat.com',
			'whatsapp.com',
			'wa.me',
			'telegram.org',
			't.me',
			'medium.com',
			'quora.com',
			'qr.ae',
			'flickr.com',
			'flic.kr',
			'vk.com',
			'vk.cc'
		];

		/**
		 * Check if a URL is valid.
		 *
		 * @param string $url The URL to validate.
		 *
		 * @return bool True if the URL is valid, false otherwise.
		 */
		public static function is_valid( string $url ): bool {
			return Validate::is_url( $url );
		}

		/**
		 * Check if a URL is an audio URL.
		 *
		 * @param string $url The URL to validate.
		 *
		 * @return bool Returns true if the URL is an audio URL, false otherwise.
		 */
		public static function is_audio( string $url ): bool {
			// Check file extension
			$extension = self::get_extension( $url );
			if ( in_array( $extension, self::$audio_extensions, true ) ) {
				return true;
			}

			// Check domain
			$parsed_url = parse_url( $url );
			$host       = strtolower( $parsed_url['host'] ?? '' );
			if ( self::is_domain_match( $host, self::$audio_domains ) ) {
				return true;
			}

			// Fallback to oEmbed check
			$oembed = wp_oembed_get( $url );

			return $oembed && strpos( $oembed, '<audio' ) !== false;
		}

		/**
		 * Check if a URL is a video URL.
		 *
		 * @param string $url The URL to check.
		 *
		 * @return bool True if the URL is a video URL, false otherwise.
		 */
		public static function is_video( string $url ): bool {
			// Check file extension
			$extension = self::get_extension( $url );
			if ( in_array( $extension, self::$video_extensions, true ) ) {
				return true;
			}

			// Check domain
			$parsed_url = parse_url( $url );
			$host       = strtolower( $parsed_url['host'] ?? '' );
			if ( self::is_domain_match( $host, self::$video_domains ) ) {
				return true;
			}

			// Fallback to oEmbed check
			$oembed = wp_oembed_get( $url );

			return $oembed && ( strpos( $oembed, '<iframe' ) !== false || strpos( $oembed, '<video' ) !== false );
		}

		/**
		 * Check if a URL is a social media URL.
		 *
		 * @param string $url The URL to check.
		 *
		 * @return bool Whether the URL is a social media URL.
		 */
		public static function is_social( string $url ): bool {
			$parsed_url = parse_url( $url );
			$host       = strtolower( $parsed_url['host'] ?? '' );

			return self::is_domain_match( $host, self::$social_domains );
		}

		/**
		 * Check if a URL is an image URL.
		 *
		 * @param string $url The URL to check.
		 *
		 * @return bool Whether the URL is an image URL.
		 */
		public static function is_image( string $url ): bool {
			$parsed_url = parse_url( $url );
			$path_info  = pathinfo( $parsed_url['path'] ?? '' );
			$extension  = strtolower( $path_info['extension'] ?? '' );

			return in_array( $extension, self::$image_extensions );
		}

		/**
		 * Check if a URL is an archive URL.
		 *
		 * @param string $url The URL to validate.
		 *
		 * @return bool Returns true if the URL is an archive URL, false otherwise.
		 */
		public static function is_archive( string $url ): bool {
			$extension = self::get_extension( $url );

			return in_array( $extension, self::$archive_extensions, true );
		}

		/**
		 * Check if a URL is supported by oEmbed.
		 *
		 * @param string $url The URL to check.
		 *
		 * @return bool True if the URL is supported by oEmbed, false otherwise.
		 */
		public static function is_oembed( string $url ): bool {
			return (bool) wp_oembed_get( $url );
		}

		/**
		 * Get the file extension from a URL.
		 *
		 * @param string $url The URL to parse.
		 *
		 * @return string The file extension or an empty string if not found.
		 */
		public static function get_extension( string $url ): string {
			return strtolower( pathinfo( parse_url( $url, PHP_URL_PATH ), PATHINFO_EXTENSION ) );
		}

		/**
		 * Get the domain name from a URL.
		 *
		 * @param string $url The URL to parse.
		 *
		 * @return string The domain name or an empty string if not found.
		 */
		public static function get_domain( string $url ): string {
			return (string) parse_url( $url, PHP_URL_HOST );
		}

		/**
		 * Make a URL relative, if possible.
		 *
		 * @param string $url The URL to make relative.
		 *
		 * @return string The relative URL or the original URL if it is external.
		 */
		public static function make_relative( string $url ): string {
			if ( self::is_external( $url ) ) {
				return $url;
			}

			$url_parts    = wp_parse_url( $url );
			$relative_url = '';

			if ( isset( $url_parts['path'] ) ) {
				$relative_url .= $url_parts['path'];
			}
			if ( isset( $url_parts['query'] ) ) {
				$relative_url .= '?' . $url_parts['query'];
			}
			if ( isset( $url_parts['fragment'] ) ) {
				$relative_url .= '#' . $url_parts['fragment'];
			}

			return $relative_url ?: '/';
		}

		/**
		 * Check if a URL is external.
		 *
		 * @param string $url The URL to check.
		 *
		 * @return bool True if the URL is external, false otherwise.
		 */
		public static function is_external( string $url ): bool {
			$home_url  = wp_parse_url( home_url() );
			$url_parts = wp_parse_url( $url );

			// If the URL doesn't have a host, it's not external
			if ( ! isset( $url_parts['host'] ) ) {
				return false;
			}

			// Compare the hosts
			if ( $url_parts['host'] !== $home_url['host'] ) {
				return true;
			}

			// If schemes are different and one is not relative, it's external
			if ( isset( $url_parts['scheme'] ) && isset( $home_url['scheme'] ) &&
			     $url_parts['scheme'] !== $home_url['scheme'] &&
			     $url_parts['scheme'] !== '' ) {
				return true;
			}

			return false;
		}

		/**
		 * Convert a relative URL to an absolute URL.
		 *
		 * @param string $relative_url The relative URL.
		 * @param string $base_url     The base URL to use (defaults to the site URL).
		 *
		 * @return string The absolute URL.
		 */
		public static function to_absolute( string $relative_url, string $base_url = '' ): string {
			if ( empty( $base_url ) ) {
				$base_url = get_site_url();
			}

			return wp_normalize_path( trailingslashit( $base_url ) . ltrim( $relative_url, '/' ) );
		}

		/**
		 * Checks if a URL is from the same domain as the current site.
		 *
		 * @param string $url The URL to check.
		 *
		 * @return bool True if the URL is from the same domain, false otherwise.
		 */
		public static function is_same_domain( string $url ): bool {
			return wp_parse_url( $url, PHP_URL_HOST ) === wp_parse_url( home_url(), PHP_URL_HOST );
		}

		/**
		 * Add query parameters to a URL.
		 *
		 * @param string $url    The original URL.
		 * @param array  $params An associative array of query parameters to add.
		 *
		 * @return string The URL with added query parameters.
		 */
		public static function add_query_params( string $url, array $params ): string {
			return add_query_arg( $params, $url );
		}

		/**
		 * Remove query parameters from a URL.
		 *
		 * @param string       $url              The original URL.
		 * @param array|string $params_to_remove An array or string of parameter names to remove.
		 *
		 * @return string The URL with specified query parameters removed.
		 */
		public static function remove_query_params( string $url, $params_to_remove ): string {
			return remove_query_arg( $params_to_remove, $url );
		}

		/**
		 * Encode a URL if it contains invalid characters.
		 *
		 * @param string $url The URL to encode.
		 *
		 * @return string The encoded URL or the original URL if it is numeric.
		 */
		public static function encode_if_invalid( string $url ): string {
			if ( is_numeric( $url ) ) {
				return $url;
			}

			return preg_match( '/[^A-Za-z0-9\-\/_.:]+/', $url ) ? urlencode( $url ) : $url;
		}

		/**
		 * Extract the path from a URL.
		 *
		 * @param string $url The URL to extract the path from.
		 *
		 * @return string The path of the URL.
		 */
		public static function get_path( string $url ): string {
			return (string) parse_url( $url, PHP_URL_PATH );
		}

		/**
		 * Extract the query string from a URL.
		 *
		 * @param string $url The URL to extract the query string from.
		 *
		 * @return string The query string of the URL.
		 */
		public static function get_query_string( string $url ): string {
			return (string) parse_url( $url, PHP_URL_QUERY );
		}

		/**
		 * Remove scheme from URL.
		 *
		 * @param string $url
		 *
		 * @return string
		 */
		public static function remove_scheme( string $url ): string {
			return preg_replace( '/^(?:http|https):/', '', $url );
		}

		/**
		 * Check if a given host matches any domain in the list.
		 *
		 * @param string $host    The host to check.
		 * @param array  $domains The list of domains to match against.
		 *
		 * @return bool Whether the host matches any domain.
		 */
		private static function is_domain_match( string $host, array $domains ): bool {
			foreach ( $domains as $domain ) {
				if ( $host === $domain || strpos( $host, '.' . $domain ) !== false ) {
					return true;
				}
			}

			return false;
		}

	}

endif;