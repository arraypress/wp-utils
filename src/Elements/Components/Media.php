<?php
/**
 * Media Component Class
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Elements\Components;

use ArrayPress\Utils\Elements\Element;
use ArrayPress\Utils\Elements\Field;

class Media extends Base {
	/**
	 * Create an avatar element.
	 *
	 * @param string $image_url The URL of the avatar image.
	 * @param string $size      The size of the avatar (xs, sm, md, lg, xl, or pixel value)
	 * @param array  $attrs     Additional attributes for the img element.
	 *
	 * @return string The HTML for the avatar.
	 */
	public static function avatar( string $image_url, string $size = 'md', array $attrs = [] ): string {
		// Base class
		$attrs['class'] = isset( $attrs['class'] ) ? $attrs['class'] . ' wp-avatar' : 'wp-avatar';

		// Handle size
		if ( in_array( $size, [ 'xs', 'sm', 'md', 'lg', 'xl' ] ) ) {
			$attrs['class'] .= " wp-avatar-{$size}";
		} else {
			// Custom size - use inline style
			$size           = is_numeric( $size ) ? $size . 'px' : $size;
			$attrs['style'] = isset( $attrs['style'] ) ?
				$attrs['style'] . ";width:{$size};height:{$size}" :
				"width:{$size};height:{$size}";
		}

		$attrs['src'] = esc_url( $image_url );
		$attrs['alt'] = $attrs['alt'] ?? 'Avatar';

		self::ensure_assets();

		return Element::img( $attrs['src'], $attrs['alt'], $attrs );
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
	public static function attachment_thumbnail(
		int $attachment_id,
		$size = 'thumbnail',
		array $attr = [],
		$container_classes = [ 'thumbnail' ],
		array $container_attr = [],
		string $fallback = Element::MDASH,
		bool $lazy_load = true,
		bool $link_to_full = false
	): string {
		// Validate attachment exists
		if ( ! wp_attachment_is_image( $attachment_id ) ) {
			return $fallback;
		}

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
				$image_html = Element::link( $full_url, $image_html, [ 'class' => 'thumbnail-link' ] );
			}
		}

		// Process container classes if they're in string format
		if ( is_string( $container_classes ) ) {
			$container_classes = explode( ' ', $container_classes );
		}

		// Ensure container classes are in the attributes
		$container_attr['class'] = isset( $container_attr['class'] )
			? $container_attr['class'] . ' ' . implode( ' ', $container_classes )
			: implode( ' ', $container_classes );

		// Create the container div
		return Element::div( $image_html, $container_attr );
	}

	/**
	 * Create a responsive image element with srcset support.
	 *
	 * @param string $src   The source URL of the image.
	 * @param string $alt   The alternative text for the image.
	 * @param array  $sizes Array of image sizes (width => url).
	 * @param array  $attrs Additional attributes for the image.
	 *
	 * @return string The HTML for the responsive image.
	 */
	public static function responsive_image( string $src, string $alt, array $sizes = [], array $attrs = [] ): string {
		if ( ! empty( $sizes ) ) {
			$srcset = [];
			foreach ( $sizes as $width => $url ) {
				$srcset[] = esc_url( $url ) . ' ' . $width . 'w';
			}
			$attrs['srcset'] = implode( ', ', $srcset );
		}

		return Element::img( $src, $alt, $attrs );
	}

	/**
	 * Create an image gallery.
	 *
	 * @param array $images Array of image data (src, alt, caption).
	 * @param array $attrs  Additional attributes for the gallery container.
	 *
	 * @return string The HTML for the image gallery.
	 */
	public static function gallery( array $images, array $attrs = [] ): string {
		$default_styles = [
			'display'               => 'grid',
			'grid-template-columns' => 'repeat(auto-fill, minmax(200px, 1fr))',
			'gap'                   => '1rem',
			'padding'               => '1rem'
		];

		$attrs['style'] = Element::merge_styles( $default_styles, $attrs['style'] ?? '' );

		$gallery_content = '';
		foreach ( $images as $image ) {
			$figure_content = Element::img( $image['src'], $image['alt'] ?? '', [
				'style' => 'width: 100%; height: 100%; object-fit: cover;'
			] );

			if ( ! empty( $image['caption'] ) ) {
				$figure_content .= Element::create( 'figcaption', [], esc_html( $image['caption'] ) );
			}

			$gallery_content .= Element::create( 'figure', [
				'style' => 'margin: 0; padding: 0;'
			], $figure_content );
		}

		self::ensure_assets();

		return Element::div( $gallery_content, $attrs );
	}

	/**
	 * Create a responsive WordPress gallery with lightbox support.
	 *
	 * @param array $attachments     Array of attachment IDs
	 * @param array $args            {
	 *                               Optional. Array of gallery arguments.
	 *
	 * @type string $size            Image size to use. Default 'medium'.
	 * @type int    $columns         Number of columns. Default 3.
	 * @type bool   $show_captions   Whether to show captions. Default true.
	 * @type bool   $lightbox        Whether to enable lightbox. Default true.
	 * @type string $gap             Gap between items. Default '1rem'.
	 * @type array  $container_class Additional classes for container.
	 * @type array  $container_attrs Additional attributes for container.
	 *                               }
	 * @return string HTML gallery output
	 */
	public static function wp_gallery( array $attachments, array $args = [] ): string {
		if ( empty( $attachments ) ) {
			return '';
		}

		Field::ensure_assets();

		$defaults = [
			'size'            => 'medium',
			'columns'         => 3,
			'show_captions'   => true,
			'lightbox'        => true,
			'gap'             => '1rem',
			'container_class' => [],
			'container_attrs' => []
		];

		$args = wp_parse_args( $args, $defaults );

		// Ensure container classes is an array
		if ( is_string( $args['container_class'] ) ) {
			$args['container_class'] = explode( ' ', $args['container_class'] );
		}

		// Base container classes
		$container_classes = array_merge(
			[ 'wp-gallery' ],
			$args['container_class']
		);

		// Container styles
		$styles = [
			'display'               => 'grid',
			'grid-template-columns' => "repeat({$args['columns']}, 1fr)",
			'gap'                   => $args['gap'],
		];

		// Merge container attributes
		$container_attrs = array_merge(
			[
				'class' => implode( ' ', $container_classes ),
				'style' => Element::build_style_string( $styles )
			],
			$args['container_attrs']
		);

		$gallery_content = '';

		foreach ( $attachments as $attachment_id ) {
			$figure_content = '';

			// Create image content
			if ( $args['lightbox'] ) {
				$figure_content .= self::attachment_lightbox(
					$attachment_id,
					$args['size'],
					[ 'class' => 'wp-gallery-item-image' ]
				);
			} else {
				$figure_content .= wp_get_attachment_image(
					$attachment_id,
					$args['size'],
					false,
					[ 'class' => 'wp-gallery-item-image' ]
				);
			}

			// Add caption if enabled
			if ( $args['show_captions'] ) {
				$caption = wp_get_attachment_caption( $attachment_id );
				if ( $caption ) {
					$figure_content .= Element::create(
						'figcaption',
						[ 'class' => 'wp-gallery-item-caption' ],
						esc_html( $caption )
					);
				}
			}

			// Wrap in figure element
			$gallery_content .= Element::create(
				'figure',
				[ 'class' => 'wp-gallery-item' ],
				$figure_content
			);
		}

		self::ensure_assets();

		// Build the final gallery
		return Element::div( $gallery_content, $container_attrs );
	}

	/**
	 * Create a lightbox-ready image.
	 *
	 * @param string $thumb_src The thumbnail image source URL.
	 * @param string $full_src  The full-size image source URL.
	 * @param string $alt       The alternative text for the image.
	 * @param array  $attrs     Additional attributes for the container.
	 *
	 * @return string The HTML for the lightbox image.
	 */
	public static function lightbox_image(
		string $thumb_src,
		string $full_src,
		string $alt = '',
		array $attrs = []
	): string {
		$default_attrs = [
			'class'         => 'lightbox-image',
			'data-full-src' => esc_url( $full_src )
		];

		$attrs = array_merge( $default_attrs, $attrs );

		$img = Element::img( $thumb_src, $alt, [
			'style' => 'cursor: pointer; max-width: 100%; height: auto;'
		] );

		Field::ensure_assets();

		return Element::div( $img, $attrs );
	}

	/**
	 * Create a lightbox-ready image from a WordPress attachment.
	 *
	 * @param int    $attachment_id The WordPress attachment ID.
	 * @param string $size          Optional. Image size to use for thumbnail. Default 'thumbnail'.
	 * @param array  $attrs         Optional. Additional attributes for the container.
	 *
	 * @return string The HTML for the lightbox image container.
	 */
	public static function attachment_lightbox(
		int $attachment_id,
		string $size = 'thumbnail',
		array $attrs = []
	): string {
		$thumb_src = wp_get_attachment_image_url( $attachment_id, $size );
		$full_src  = wp_get_attachment_image_url( $attachment_id, 'full' );
		$alt       = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
		$caption   = wp_get_attachment_caption( $attachment_id );
		$title     = get_the_title( $attachment_id );

		if ( ! $thumb_src || ! $full_src ) {
			return '';
		}

		$default_attrs = [
			'class'         => 'wp-lightbox',
			'data-full-src' => $full_src,
			'data-caption'  => $caption ?: $title,
			'title'         => $title
		];
		$attrs         = array_merge( $default_attrs, $attrs );

		$img = wp_get_attachment_image( $attachment_id, $size, false, [
			'class' => 'wp-lightbox-thumb',
			'style' => 'cursor: pointer; max-width: 100%; height: auto;'
		] );

		Field::ensure_assets();

		return Element::div( $img, $attrs );
	}

}