<?php
/**
 * Attachment Utilities for WordPress
 *
 * This class provides utility methods for working with attachments in WordPress,
 * focusing on operations using attachment IDs. It includes methods for retrieving
 * attachment details such as type, dimensions, file size, alt text, and dominant color.
 * It also handles different content types like images, audio, and video attachments.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Media;

use ArrayPress\Utils\Common\MIME;

/**
 * Check if the class `Attachment` is defined, and if not, define it.
 */
if ( ! class_exists( 'Attachment' ) ) :

	/**
	 * Attachment utility class for WordPress.
	 *
	 * This class provides utility methods for working with attachments in WordPress,
	 * including retrieving attachment types, file details, HTML content, image dimensions,
	 * alt text, and dominant colors. It supports handling different attachment types such
	 * as audio, video, images, and general files.
	 */
	class Attachment {

		/**
		 * Get the type of attachment.
		 *
		 * @param int $attachment_id The attachment ID.
		 *
		 * @return string The type of the attachment ('audio', 'video', 'image', or 'file').
		 */
		public static function get_type( int $attachment_id ): string {
			$file      = wp_get_attachment_url( $attachment_id );
			$mime_type = MIME::get_file_type( $file );

			return MIME::get_general_type( $mime_type );
		}

		/**
		 * Get the file type based on the attachment ID.
		 *
		 * @param int $attachment_id The attachment ID.
		 *
		 * @return string The file type.
		 */
		public static function get_file_type( int $attachment_id ): string {
			$file      = get_attached_file( $attachment_id );
			$mime_type = MIME::get_file_type( $file );

			return MIME::get_general_type( $mime_type );
		}

		/**
		 * Get the HTML content for an attachment.
		 *
		 * @param int    $attachment_id The attachment ID.
		 * @param string $size          The size of the image to retrieve. Default is 'thumbnail'.
		 * @param string $link_type     The type of link to wrap around the image. Default is 'none'.
		 * @param array  $attr          Additional attributes to add to the HTML element.
		 *
		 * @return array The attachment content and type.
		 */
		public static function get_content( int $attachment_id, string $size = 'thumbnail', string $link_type = 'none', array $attr = [] ): array {
			$type = self::get_type( $attachment_id );
			$url  = wp_get_attachment_url( $attachment_id );

			switch ( $type ) {
				case 'video':
					$content = self::get_video_content( $attachment_id );
					break;
				case 'audio':
					$content = wp_audio_shortcode( [ 'src' => $url ] );
					break;
				case 'image':
					$content = self::get_image_html( $attachment_id, $size, $link_type, $attr );
					break;
				default:
					$content = wp_get_attachment_link( $attachment_id, '' );
			}

			return [ 'content' => $content, 'type' => $type ];
		}

		/**
		 * Get the HTML for an image attachment.
		 *
		 * @param int    $attachment_id The attachment ID.
		 * @param string $size          The size of the image to retrieve.
		 * @param string $link_type     The type of link to wrap around the image.
		 * @param array  $attr          Additional attributes to add to the img tag.
		 *
		 * @return string The HTML for the image attachment.
		 */
		public static function get_image_html( int $attachment_id, string $size, string $link_type, array $attr ): string {
			if ( $link_type === 'file' ) {
				return wp_get_attachment_link( $attachment_id, $size, false, false, false, $attr );
			} elseif ( $link_type === 'none' ) {
				return wp_get_attachment_image( $attachment_id, $size, false, $attr );
			} else {
				return wp_get_attachment_link( $attachment_id, $size, true, false, false, $attr );
			}
		}

		/**
		 * Get the video content for an attachment.
		 *
		 * @param int $attachment_id The attachment ID.
		 *
		 * @return string The video content HTML.
		 */
		private static function get_video_content( int $attachment_id ): string {
			$url        = wp_get_attachment_url( $attachment_id );
			$atts       = [ 'src' => $url ];
			$dimensions = self::get_dimensions( $attachment_id );

			if ( $dimensions ) {
				$atts['width']  = $dimensions['width'];
				$atts['height'] = $dimensions['height'];
			}

			return wp_video_shortcode( $atts );
		}

		/**
		 * Get the dimensions of an attachment.
		 *
		 * @param int $attachment_id The attachment ID.
		 *
		 * @return array|null The dimensions array or null if not found.
		 */
		public static function get_dimensions( int $attachment_id ): ?array {
			$metadata = wp_get_attachment_metadata( $attachment_id );

			if ( ! empty( $metadata['width'] ) && ! empty( $metadata['height'] ) ) {
				return [
					'width'  => $metadata['width'],
					'height' => $metadata['height'],
				];
			}

			return null;
		}

		/**
		 * Get the orientation of an image attachment.
		 *
		 * @param int $attachment_id The ID of the attachment.
		 *
		 * @return string|null Returns 'portrait' if the image is taller than it is wide,
		 *                     'landscape' if it is wider than it is tall, or null if the
		 *                     attachment is not an image or if the dimensions are not available.
		 */
		public static function get_orientation( int $attachment_id ): ?string {
			$dimensions = self::get_dimensions( $attachment_id );

			if ( ! $dimensions ) {
				return null;
			}

			return ( $dimensions['height'] > $dimensions['width'] ) ? 'portrait' : 'landscape';
		}

		/**
		 * Check if an attachment is protected within a specified directory.
		 *
		 * @param int    $attachment_id     The attachment ID.
		 * @param array  $allowed_filetypes Optional. Allowed file types. Default empty array.
		 * @param string $directory         Optional. Directory to check. Default 'protected'.
		 *
		 * @return bool True if the attachment is protected, false otherwise.
		 */
		public static function is_protected( int $attachment_id, array $allowed_filetypes = [], string $directory = 'protected' ): bool {
			$attachment_path = get_attached_file( $attachment_id );

			if ( ! $attachment_path || ! file_exists( $attachment_path ) ) {
				return false;
			}

			if ( ! self::is_in_directory( $attachment_id, $directory ) ) {
				return false;
			}

			if ( empty( $allowed_filetypes ) ) {
				$common_types      = MIME::get_common_types();
				$allowed_filetypes = array_merge( ...array_values( $common_types ) );
			}

			$mime_type = MIME::get_file_type( $attachment_path );

			return ! MIME::is_allowed( $mime_type, $allowed_filetypes );
		}

		/**
		 * Check if an attachment is within a specific directory.
		 *
		 * @param int    $attachment_id The attachment ID to check.
		 * @param string $directory     The specific directory to check.
		 *
		 * @return bool True if the attachment is within the specified directory, false otherwise.
		 */
		public static function is_in_directory( int $attachment_id, string $directory ): bool {
			$upload_dir      = wp_upload_dir();
			$uploads_path    = $upload_dir['basedir'];
			$attachment_path = get_attached_file( $attachment_id );

			return str_starts_with( $attachment_path, $uploads_path . '/' . $directory . '/' );
		}

		/**
		 * Get the file size of an attachment.
		 *
		 * @param int $attachment_id The attachment ID.
		 *
		 * @return string The formatted file size or 'N/A'.
		 */
		public static function get_file_size( int $attachment_id ): string {
			$file_path = get_attached_file( $attachment_id );

			if ( $file_path && file_exists( $file_path ) ) {
				$file_size = filesize( $file_path );

				return $file_size ? size_format( $file_size ) : 'N/A';
			}

			return 'N/A';
		}

		/**
		 * Get the file extension of an attachment.
		 *
		 * @param int $attachment_id The attachment ID.
		 *
		 * @return string|null The file extension or null if not found.
		 */
		public static function get_file_extension( int $attachment_id ): ?string {
			$file_path = get_attached_file( $attachment_id );

			return $file_path ? pathinfo( $file_path, PATHINFO_EXTENSION ) : null;
		}

		/**
		 * Retrieves the specified field for the specified attachment.
		 *
		 * @param int    $attachment_id The ID of the attachment.
		 * @param string $type          The type of field to retrieve: 'title', 'caption', 'description', or 'alt'.
		 * @param string $default       The default value to return if the specified field is not found.
		 *
		 * @return string The value of the specified field for the specified attachment.
		 */
		public static function get_field( int $attachment_id, string $type, string $default = '' ): string {
			$attachment = get_post( $attachment_id );

			if ( ! $attachment || $attachment->post_type !== 'attachment' ) {
				return $default;
			}

			switch ( $type ) {
				case 'title':
					return trim( $attachment->post_title ) ?: $default;
				case 'caption':
					return trim( $attachment->post_excerpt ) ?: $default;
				case 'description':
					return trim( $attachment->post_content ) ?: $default;
				case 'alt':
					return trim( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) ?: $default;
				default:
					return $default;
			}
		}

		/**
		 * Get image alt text.
		 *
		 * @param int $attachment_id The attachment ID.
		 *
		 * @return string The alt text or an empty string if not set.
		 */
		public static function get_alt_text( int $attachment_id ): string {
			return get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ?: '';
		}

		/**
		 * Set image alt text.
		 *
		 * @param int    $attachment_id The attachment ID.
		 * @param string $alt_text      The alt text to set.
		 *
		 * @return bool True on success, false on failure.
		 */
		public static function set_alt_text( int $attachment_id, string $alt_text ): bool {
			return update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt_text );
		}

		/**
		 * Get the duration of an audio or video attachment.
		 *
		 * @param int $attachment_id The attachment ID.
		 *
		 * @return int The duration of the attachment in seconds.
		 */
		public static function get_duration( int $attachment_id ): int {
			$file = get_attached_file( $attachment_id );

			if ( ! $file || ! file_exists( $file ) ) {
				return 0;
			}

			$type = self::get_type( $attachment_id );

			if ( $type === 'audio' ) {
				require_once( ABSPATH . 'wp-admin/includes/media.php' );
				$metadata = wp_read_audio_metadata( $file );

				return $metadata['length'] ?? 0;
			} elseif ( $type === 'video' ) {
				$metadata = wp_read_video_metadata( $file );

				return $metadata['length'] ?? 0;
			}

			return 0;
		}

		/**
		 * Get image source set.
		 *
		 * @param int $attachment_id The attachment ID.
		 *
		 * @return string The srcset attribute string.
		 */
		public static function get_srcset( int $attachment_id ): string {
			$srcset = (string) wp_get_attachment_image_srcset( $attachment_id, 'full' );

			return $srcset ?: '';
		}

		/**
		 * Get image as a data URI.
		 *
		 * @param int    $attachment_id The attachment ID.
		 * @param string $size          The image size to use.
		 *
		 * @return string|null The data URI string or null if conversion fails.
		 */
		public static function get_data_uri( int $attachment_id, string $size = 'full' ): ?string {
			$image_url  = wp_get_attachment_image_url( $attachment_id, $size );
			$image_path = get_attached_file( $attachment_id );

			if ( ! $image_url || ! file_exists( $image_path ) ) {
				return null;
			}

			$image_data = file_get_contents( $image_path );
			$mime_type  = mime_content_type( $image_path );

			if ( $image_data && $mime_type ) {
				return 'data:' . $mime_type . ';base64,' . base64_encode( $image_data );
			}

			return null;
		}

		/**
		 * Check if the image has transparency.
		 *
		 * @param int $attachment_id The attachment ID.
		 *
		 * @return bool|null True if transparent, false if not, null if can't be determined.
		 */
		public static function has_transparency( int $attachment_id ): ?bool {
			$file_path = get_attached_file( $attachment_id );
			if ( ! $file_path || ! file_exists( $file_path ) ) {
				return null;
			}

			$mime_type = mime_content_type( $file_path );

			switch ( $mime_type ) {
				case 'image/png':
					$image = imagecreatefrompng( $file_path );

					return imagecolortransparent( $image ) != - 1 || ( imagecolorat( $image, 0, 0 ) & 0x7F000000 ) != 0;
				case 'image/gif':
					$image = imagecreatefromgif( $file_path );

					return imagecolortransparent( $image ) != - 1;
				default:
					return false;
			}
		}

		/**
		 * Get image sizes
		 *
		 * @return array An array of image size properties.
		 */
		public static function get_sizes(): array {
			global $_wp_additional_image_sizes;
			$sizes = [];

			foreach ( get_intermediate_image_sizes() as $size ) {
				if ( in_array( $size, [ 'thumbnail', 'medium', 'medium_large', 'large' ] ) ) {
					$sizes[ $size ] = [
						'width'  => (int) get_option( $size . '_size_w' ),
						'height' => (int) get_option( $size . '_size_h' ),
						'crop'   => (bool) get_option( $size . '_crop' ),
					];
				} elseif ( isset( $_wp_additional_image_sizes[ $size ] ) ) {
					$sizes[ $size ] = [
						'width'  => (int) $_wp_additional_image_sizes[ $size ]['width'],
						'height' => (int) $_wp_additional_image_sizes[ $size ]['height'],
						'crop'   => (bool) $_wp_additional_image_sizes[ $size ]['crop'],
					];
				}
			}

			return $sizes;
		}

		/**
		 * Retrieves an array of available image sizes in WordPress.
		 *
		 * @return array An array of available image sizes.
		 */
		public static function get_size_options(): array {
			$sizes   = self::get_sizes();
			$options = [];

			foreach ( $sizes as $size => $data ) {
				$options[] = [
					'label' => ucwords( str_replace( '_', ' ', $size ) ),
					'value' => $size,
				];
			}

			$options[] = [
				'label' => __( 'Original', 'arraypress' ),
				'value' => 'full',
			];

			return $options;
		}

		/**
		 * Generate an image thumbnail HTML with enhanced flexibility and features.
		 *
		 * @param int          $attachment_id     The attachment ID.
		 * @param mixed        $size              The image size. Default is 'thumbnail'.
		 *                                        Can be a string (registered size) or array [width, height].
		 * @param array        $attr              Optional. Additional attributes for the image tag.
		 * @param array|string $container_classes Classes for the container div. Can be array or space-separated string.
		 * @param array        $container_attr    Optional. Additional attributes for the container div.
		 * @param string       $fallback          HTML/text to display if image doesn't exist. Default is em dash.
		 * @param bool         $lazy_load         Whether to enable lazy loading. Default true.
		 * @param bool         $link_to_full      Whether to link to full-size image. Default false.
		 *
		 * @return string The HTML for the image thumbnail.
		 */
		public static function image_thumbnail(
			int $attachment_id,
			$size = 'thumbnail',
			array $attr = [],
			$container_classes = [ 'thumbnail' ],
			array $container_attr = [],
			string $fallback = '&mdash;',
			bool $lazy_load = true,
			bool $link_to_full = false
		): string {
			// Validate attachment exists
			if ( ! wp_attachment_is_image( $attachment_id ) ) {
				return $fallback;
			}

			// Process container classes
			$container_classes = is_array( $container_classes ) ? $container_classes : explode( ' ', $container_classes );
			$container_classes = array_map( 'sanitize_html_class', $container_classes );
			$container_classes = array_filter( $container_classes );

			// Prepare image attributes
			$default_attr = [
				'class'   => 'image-thumbnail',
				'loading' => $lazy_load ? 'lazy' : 'eager',
			];
			$attr         = wp_parse_args( $attr, $default_attr );

			// Get image HTML
			$image_html = wp_get_attachment_image( $attachment_id, $size, false, $attr );
			if ( ! $image_html ) {
				return $fallback;
			}

			// Wrap image in link if requested
			if ( $link_to_full ) {
				$full_url = wp_get_attachment_image_url( $attachment_id, 'full' );
				if ( $full_url ) {
					$image_html = sprintf(
						'<a href="%s" class="thumbnail-link">%s</a>',
						esc_url( $full_url ),
						$image_html
					);
				}
			}

			// Prepare container attributes
			$container_attr['class'] = implode( ' ', $container_classes );
			$container_attributes    = '';

			foreach ( $container_attr as $key => $value ) {
				if ( $value === true ) {
					$container_attributes .= ' ' . esc_attr( $key );
				} else {
					$container_attributes .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( $value ) );
				}
			}

			// Return final HTML
			return sprintf( '<div%s>%s</div>', $container_attributes, $image_html );
		}

		/**
		 * Get dominant color of an image.
		 *
		 * @param int $attachment_id The attachment ID.
		 *
		 * @return string|null Hex color code or null if it can't be determined.
		 */
		public static function get_dominant_color( int $attachment_id ): ?string {
			$file_path = get_attached_file( $attachment_id );
			if ( ! $file_path || ! file_exists( $file_path ) ) {
				return null;
			}

			$image  = imagecreatefromstring( file_get_contents( $file_path ) );
			$scaled = imagescale( $image, 1, 1, IMG_BICUBIC );
			$index  = imagecolorat( $scaled, 0, 0 );
			$rgb    = imagecolorsforindex( $scaled, $index );

			return sprintf( "#%02x%02x%02x", $rgb['red'], $rgb['green'], $rgb['blue'] );
		}

		/**
		 * Check if the image attachment is animated GIF.
		 *
		 * @param int $attachment_id The attachment ID.
		 *
		 * @return bool True if the image is an animated GIF, false otherwise.
		 */
		public static function is_animated_gif( int $attachment_id ): bool {
			$file_path = get_attached_file( $attachment_id );
			if ( ! $file_path || ! file_exists( $file_path ) ) {
				return false;
			}

			if ( mime_content_type( $file_path ) !== 'image/gif' ) {
				return false;
			}

			$contents = file_get_contents( $file_path );
			$str_loc  = 0;
			$count    = 0;
			while ( $count < 2 ) {
				$where1 = strpos( $contents, "\x00\x21\xF9\x04", $str_loc );
				if ( $where1 === false ) {
					break;
				}
				$str_loc = $where1 + 1;
				$where2  = strpos( $contents, "\x00\x2C", $str_loc );
				if ( $where2 === false ) {
					break;
				}
				if ( $where1 + 8 == $where2 ) {
					$count ++;
				}
				$str_loc = $where2 + 1;
			}

			return $count > 1;
		}

	}
endif;