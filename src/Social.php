<?php
/**
 * Social Media Utilities for WordPress
 *
 * This class provides utility functions for working with social media platforms in WordPress.
 * It includes methods for retrieving social media platform lists, share URLs, and button templates.
 *
 * @package       ArrayPress\Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        ArrayPress Team
 */

declare( strict_types=1 );

namespace ArrayPress\Utils;

/**
 * Check if the class `Social` is defined, and if not, define it.
 */
if ( ! class_exists( 'Social' ) ) :

	/**
	 * Social Utilities
	 *
	 * Provides utility functions for managing WordPress posts, such as checking post existence,
	 * retrieving post data by various identifiers, working with post meta, taxonomy terms, and
	 * handling post content. It also supports managing post thumbnails, scheduling posts, and
	 * extracting content-related information like links and word count.
	 */
	class Social {
		
		/**
		 * Get a list of popular social media platforms.
		 *
		 * @return array An array of social media platforms with their names and Dashicon classes.
		 */
		public static function get_platforms(): array {
			return [
				'facebook'  => [
					'name' => esc_html__( 'Facebook', 'arraypress' ),
					'icon' => 'dashicons dashicons-facebook',
				],
				'x'         => [
					'name' => esc_html__( 'X (formerly Twitter)', 'arraypress' ),
					'icon' => 'dashicons dashicons-twitter',
				],
				'linkedin'  => [
					'name' => esc_html__( 'LinkedIn', 'arraypress' ),
					'icon' => 'dashicons dashicons-linkedin',
				],
				'pinterest' => [
					'name' => esc_html__( 'Pinterest', 'arraypress' ),
					'icon' => 'dashicons dashicons-pinterest',
				],
				'youtube'   => [
					'name' => esc_html__( 'YouTube', 'arraypress' ),
					'icon' => 'dashicons dashicons-video-alt3',
				],
				'snapchat'  => [
					'name' => esc_html__( 'Snapchat', 'arraypress' ),
					'icon' => 'dashicons dashicons-camera',
				],
				'telegram'  => [
					'name' => esc_html__( 'Telegram', 'arraypress' ),
					'icon' => 'dashicons dashicons-email',
				],
				'tiktok'    => [
					'name' => esc_html__( 'TikTok', 'arraypress' ),
					'icon' => 'dashicons dashicons-video-alt2',
				],
				'whatsapp'  => [
					'name' => esc_html__( 'WhatsApp', 'arraypress' ),
					'icon' => 'dashicons dashicons-whatsapp',
				],
				'reddit'    => [
					'name' => esc_html__( 'Reddit', 'arraypress' ),
					'icon' => 'dashicons dashicons-reddit',
				],
				'email'     => [
					'name' => esc_html__( 'Email', 'arraypress' ),
					'icon' => 'dashicons dashicons-email-alt',
				],
			];
		}

		/**
		 * Get a share URL for a specific social media platform.
		 *
		 * @param string $platform The social media platform.
		 * @param string $url      The URL to be shared.
		 * @param string $title    The title of the content being shared.
		 * @param array  $args     Optional. Additional arguments for the share URL.
		 *
		 * @return string|null The share URL or null if the platform is not supported.
		 */
		public static function get_share_url( string $platform, string $url, string $title, array $args = [] ): ?string {
			$encoded_url   = urlencode( $url );
			$encoded_title = urlencode( $title );

			switch ( strtolower( $platform ) ) {
				case 'facebook':
					return "https://www.facebook.com/sharer/sharer.php?u={$encoded_url}";

				case 'x':
				case 'twitter': // For backwards compatibility
					$via = isset( $args['via'] ) ? '&via=' . urlencode( $args['via'] ) : '';

					return "https://x.com/intent/tweet?url={$encoded_url}&text={$encoded_title}{$via}";

				case 'linkedin':
					return "https://www.linkedin.com/shareArticle?mini=true&url={$encoded_url}&title={$encoded_title}";

				case 'pinterest':
					$image = isset( $args['image'] ) ? '&media=' . urlencode( $args['image'] ) : '';

					return "https://pinterest.com/pin/create/button/?url={$encoded_url}&description={$encoded_title}{$image}";

				case 'telegram':
					return "https://t.me/share/url?url={$encoded_url}&text={$encoded_title}";

				case 'whatsapp':
					return "https://api.whatsapp.com/send?text={$encoded_title}%20{$encoded_url}";

				case 'reddit':
					return "https://reddit.com/submit?url={$encoded_url}&title={$encoded_title}";

				case 'email':
					$subject = $args['subject'] ?? $title;
					$body    = $args['body'] ?? $url;

					return "mailto:?subject=" . urlencode( $subject ) . "&body=" . urlencode( $body );

				default:
					return null;
			}
		}

		/**
		 * Get share URLs for various social media platforms.
		 *
		 * @param string $url   The URL to be shared.
		 * @param string $title The title of the content being shared.
		 *
		 * @return array An array of share URLs for different platforms.
		 */
		public static function get_share_urls( string $url, string $title ): array {
			$platforms  = self::get_platforms();
			$share_urls = [];

			foreach ( $platforms as $platform => $data ) {
				$share_url = self::get_share_url( $platform, $url, $title );
				if ( $share_url !== null ) {
					$share_urls[ $platform ] = $share_url;
				}
			}

			return $share_urls;
		}

		/**
		 * Get HTML template for a single social media share button.
		 *
		 * @param string $platform The social media platform.
		 * @param string $url      The URL to be shared.
		 * @param string $title    The title of the content being shared.
		 * @param array  $args     Optional. Additional arguments for the share URL.
		 *
		 * @return string The HTML template for the share button.
		 */
		public static function get_share_button_template( string $platform, string $url, string $title, array $args = [] ): string {
			$platforms = self::get_platforms();
			if ( ! isset( $platforms[ $platform ] ) ) {
				return '';
			}

			$share_url = self::get_share_url( $platform, $url, $title, $args );

			return sprintf(
				'<a href="%s" class="social-share-button %s" target="_blank" rel="noopener noreferrer"%s>
                    <span class="%s" aria-hidden="true"></span>
                    <span class="screen-reader-text">%s</span>
                </a>',
				esc_url( $share_url ?? '#' ),
				esc_attr( $platform ),
				$share_url ? '' : ' onclick="return false;"',
				esc_attr( $platforms[ $platform ]['icon'] ),
				/* translators: %s: Social media platform name */
				sprintf( esc_html__( 'Share on %s', 'arraypress' ), $platforms[ $platform ]['name'] )
			);
		}

		/**
		 * Get HTML templates for social media share buttons.
		 *
		 * @param string $url   The URL to be shared.
		 * @param string $title The title of the content being shared.
		 *
		 * @return array An array of HTML templates for share buttons.
		 */
		public static function get_share_button_templates( string $url, string $title ): array {
			$platforms = self::get_platforms();
			$templates = [];

			foreach ( $platforms as $platform => $data ) {
				$templates[ $platform ] = self::get_share_button_template( $platform, $url, $title );
			}

			return $templates;
		}

		/**
		 * Get social media profile URL patterns.
		 *
		 * @return array An array of URL patterns for social media profiles.
		 */
		public static function get_profile_url_patterns(): array {
			return [
				'facebook'  => 'https://www.facebook.com/%s',
				'x'         => 'https://x.com/%s',
				'linkedin'  => 'https://www.linkedin.com/in/%s',
				'pinterest' => 'https://www.pinterest.com/%s',
				'youtube'   => 'https://www.youtube.com/user/%s',
				'snapchat'  => 'https://www.snapchat.com/add/%s',
				'telegram'  => 'https://t.me/%s',
				'tiktok'    => 'https://www.tiktok.com/@%s',
				'whatsapp'  => 'https://wa.me/%s',
				'reddit'    => 'https://www.reddit.com/user/%s',
			];
		}

		/**
		 * Validate a social media profile URL.
		 *
		 * @param string $url      The URL to validate.
		 * @param string $platform The social media platform to validate against.
		 *
		 * @return bool True if the URL is valid for the given platform, false otherwise.
		 */
		public static function validate_profile_url( string $url, string $platform ): bool {
			$patterns = self::get_profile_url_patterns();
			if ( ! isset( $patterns[ $platform ] ) ) {
				return false;
			}

			$pattern = str_replace( '%s', '([a-zA-Z0-9_\-\.]+)', $patterns[ $platform ] );

			return (bool) preg_match( '#^' . $pattern . '$#i', $url );
		}

	}

endif;