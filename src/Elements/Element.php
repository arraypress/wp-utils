<?php
/**
 * HTML Element Generator
 *
 * A comprehensive class for generating HTML elements with proper WordPress
 * sanitization and escaping. Provides a clean, object-oriented interface
 * for creating common HTML elements while maintaining security best practices
 * and WordPress coding standards. Features include attribute management,
 * style handling, and wp_kses integration for content filtering.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Elements;

/**
 * Class Element
 *
 * Utility functions for creating DOM elements.
 */
class Element {

	// Existing constant for mdash
	public const MDASH = '&mdash;';

	private const DEFAULT_ALLOWED_HTML = [
		'input'      => [
			'class'            => true,
			'type'             => true,
			'name'             => true,
			'id'               => true,
			'value'            => true,
			'placeholder'      => true,
			'required'         => true,
			'checked'          => true,
			'disabled'         => true,
			'min'              => true,
			'max'              => true,
			'step'             => true,
			'pattern'          => true,
			'readonly'         => true,
			'maxlength'        => true,
			'style'            => true,
			'aria-label'       => true,
			'aria-describedby' => true,
			'aria-invalid'     => true,
			'aria-required'    => true,
			'aria-expanded'    => true,
			'aria-controls'    => true,
			'aria-labelledby'  => true,
			'autocomplete'     => true,
			'autofocus'        => true,
			'inputmode'        => true,
			'list'             => true,
			'form'             => true,
			'formaction'       => true,
			'formmethod'       => true,
			'formnovalidate'   => true,
			'formtarget'       => true,
			'size'             => true,
			'spellcheck'       => true,
		],
		'select'     => [
			'class'            => true,
			'name'             => true,
			'id'               => true,
			'required'         => true,
			'disabled'         => true,
			'multiple'         => true,
			'style'            => true,
			'aria-label'       => true,
			'aria-describedby' => true,
			'aria-invalid'     => true,
			'aria-required'    => true,
			'aria-expanded'    => true,
			'aria-controls'    => true,
			'aria-labelledby'  => true,
			'autofocus'        => true,
			'form'             => true,
			'size'             => true,
		],
		'option'     => [
			'value'    => true,
			'selected' => true,
			'disabled' => true,
			'label'    => true,
		],
		'textarea'   => [
			'class'            => true,
			'name'             => true,
			'id'               => true,
			'rows'             => true,
			'cols'             => true,
			'required'         => true,
			'disabled'         => true,
			'readonly'         => true,
			'maxlength'        => true,
			'placeholder'      => true,
			'style'            => true,
			'aria-label'       => true,
			'aria-describedby' => true,
			'aria-invalid'     => true,
			'aria-required'    => true,
			'aria-expanded'    => true,
			'aria-controls'    => true,
			'aria-labelledby'  => true,
			'autofocus'        => true,
			'form'             => true,
			'minlength'        => true,
			'spellcheck'       => true,
			'wrap'             => true,
		],
		'form'       => [
			'action'         => true,
			'method'         => true,
			'class'          => true,
			'id'             => true,
			'enctype'        => true,
			'aria-label'     => true,
			'novalidate'     => true,
			'target'         => true,
			'autocomplete'   => true,
			'name'           => true,
			'accept-charset' => true,
		],
		'label'      => [
			'for'         => true,
			'class'       => true,
			'aria-label'  => true,
			'aria-hidden' => true,
		],
		'fieldset'   => [
			'class'            => true,
			'id'               => true,
			'disabled'         => true,
			'form'             => true,
			'name'             => true,
			'aria-describedby' => true,
			'aria-label'       => true,
		],
		'legend'     => [
			'class'      => true,
			'aria-label' => true,
		],
		'article'    => [
			'class'            => true,
			'id'               => true,
			'aria-label'       => true,
			'role'             => true,
			'aria-describedby' => true,
			'aria-labelledby'  => true,
		],
		'section'    => [
			'class'            => true,
			'id'               => true,
			'aria-label'       => true,
			'role'             => true,
			'aria-describedby' => true,
			'aria-labelledby'  => true,
		],
		'header'     => [
			'class'           => true,
			'id'              => true,
			'role'            => true,
			'aria-label'      => true,
			'aria-labelledby' => true,
		],
		'main'       => [
			'class'           => true,
			'id'              => true,
			'role'            => true,
			'aria-label'      => true,
			'aria-labelledby' => true,
		],
		'footer'     => [
			'class'           => true,
			'id'              => true,
			'role'            => true,
			'aria-label'      => true,
			'aria-labelledby' => true,
		],
		'figure'     => [
			'class'           => true,
			'id'              => true,
			'aria-label'      => true,
			'aria-labelledby' => true,
		],
		'figcaption' => [
			'class'      => true,
			'aria-label' => true,
		],
		'blockquote' => [
			'cite'       => true,
			'class'      => true,
			'aria-label' => true,
		],
		'datalist'   => [
			'id'         => true,
			'aria-label' => true,
		],
		'progress'   => [
			'value'          => true,
			'max'            => true,
			'class'          => true,
			'aria-label'     => true,
			'aria-valuemin'  => true,
			'aria-valuemax'  => true,
			'aria-valuenow'  => true,
			'aria-valuetext' => true,
		],
		'meter'      => [
			'value'          => true,
			'min'            => true,
			'max'            => true,
			'low'            => true,
			'high'           => true,
			'optimum'        => true,
			'class'          => true,
			'aria-label'     => true,
			'aria-valuemin'  => true,
			'aria-valuemax'  => true,
			'aria-valuenow'  => true,
			'aria-valuetext' => true,
		],
		'output'     => [
			'for'        => true,
			'form'       => true,
			'name'       => true,
			'class'      => true,
			'aria-label' => true,
			'aria-live'  => true,
		],
		'details'    => [
			'open'          => true,
			'class'         => true,
			'aria-expanded' => true,
			'aria-controls' => true,
		],
		'summary'    => [
			'class'         => true,
			// Added ARIA
			'aria-label'    => true,
			'aria-expanded' => true,
		],
		'dialog'     => [
			'open'             => true,
			'class'            => true,
			'aria-label'       => true,
			'aria-labelledby'  => true,
			'aria-describedby' => true,
			'aria-modal'       => true,
		],
		'picture'    => [
			'class'      => true,
			'aria-label' => true,
		],
		'source'     => [
			'src'    => true,
			'type'   => true,
			'srcset' => true,
			'sizes'  => true,
			'media'  => true,
		],
		'track'      => [
			'kind'    => true,
			'src'     => true,
			'srclang' => true,
			'label'   => true,
			'default' => true,
		],
		'iframe'     => [
			'src'             => true,
			'width'           => true,
			'height'          => true,
			'frameborder'     => true,
			'allowfullscreen' => true,
			'class'           => true,
			'title'           => true,
			'allow'           => true,
			'loading'         => true,
			'name'            => true,
			'referrerpolicy'  => true,
			'sandbox'         => true,
			'aria-label'      => true,
		],
		'time'       => [
			'datetime'   => true,
			'class'      => true,
			'aria-label' => true,
		],
		'mark'       => [
			'class'      => true,
			'aria-label' => true,
		],
		// Added missing common elements
		'button'     => [
			'type'             => true,
			'class'            => true,
			'id'               => true,
			'disabled'         => true,
			'name'             => true,
			'value'            => true,
			'aria-label'       => true,
			'aria-expanded'    => true,
			'aria-pressed'     => true,
			'aria-controls'    => true,
			'aria-describedby' => true,
			'aria-haspopup'    => true,
			'form'             => true,
			'formaction'       => true,
			'formmethod'       => true,
			'formnovalidate'   => true,
			'formtarget'       => true,
		],
		'nav'        => [
			'class'           => true,
			'id'              => true,
			'aria-label'      => true,
			'aria-labelledby' => true,
		],
		'aside'      => [
			'class'           => true,
			'id'              => true,
			'aria-label'      => true,
			'aria-labelledby' => true,
		],
		'img'        => [
			'src'        => true,
			'alt'        => true,
			'class'      => true,
			'id'         => true,
			'width'      => true,
			'height'     => true,
			'loading'    => true,
			'decoding'   => true,
			'aria-label' => true,
		],
		'*'          => [
			'aria-*'    => true,
			'data-*'    => true,
			'role'      => true,
			'tabindex'  => true,
			'style'     => true,
			'class'     => true,
			'id'        => true,
			'lang'      => true,
			'dir'       => true,
			'title'     => true,
			'translate' => true,
			'hidden'    => true,
		],
	];

	/**
	 * Stores the allowed HTML tags and attributes for wp_kses
	 *
	 * @var array
	 */
	private static array $allowed_html = [];


	/**
	 * Initialize the default allowed HTML tags and attributes.
	 *
	 * This method sets up the allowed HTML elements and their attributes for use with wp_kses().
	 * It merges WordPress's default 'post' allowed HTML with additional form-specific elements
	 * defined in DEFAULT_ALLOWED_HTML.
	 *
	 * @return void
	 */
	private static function init_allowed_html(): void {
		if ( empty( self::$allowed_html ) ) {
			self::$allowed_html = array_merge(
				wp_kses_allowed_html( 'post' ),
				self::DEFAULT_ALLOWED_HTML
			);
		}
	}

	/**
	 * Set or update allowed HTML tags and attributes
	 *
	 * @param array $tags  Array of tags and their allowed attributes
	 * @param bool  $merge Whether to merge with existing tags or replace completely
	 *
	 * @return void
	 */
	public static function set_allowed_html( array $tags, bool $merge = true ): void {
		self::init_allowed_html();

		if ( $merge ) {
			self::$allowed_html = array_merge( self::$allowed_html, $tags );
		} else {
			self::$allowed_html = $tags;
		}
	}

	/**
	 * Get the current allowed HTML configuration
	 *
	 * @return array Current allowed HTML tags and attributes
	 */
	public static function get_allowed_html(): array {
		self::init_allowed_html();

		return self::$allowed_html;
	}

	/**
	 * Add allowed attributes to a specific HTML tag
	 *
	 * @param string $tag        The HTML tag
	 * @param array  $attributes Array of attributes to allow
	 *
	 * @return void
	 */
	public static function add_allowed_attributes( string $tag, array $attributes ): void {
		self::init_allowed_html();

		if ( ! isset( self::$allowed_html[ $tag ] ) ) {
			self::$allowed_html[ $tag ] = [];
		}

		self::$allowed_html[ $tag ] = array_merge( self::$allowed_html[ $tag ], $attributes );
	}

	/**
	 * Create an arbitrary HTML element with opening and closing tags.
	 *
	 * @param string $tag     The tag name of the element.
	 * @param array  $attrs   An associative array of attributes for the element.
	 * @param string $content The content to be placed between the opening and closing tags.
	 *
	 * @return string The HTML string for the element.
	 */
	public static function create( string $tag, array $attrs = [], string $content = '' ): string {
		self::init_allowed_html();
		$attr_string  = self::build_attribute_string( self::escape_attributes( $attrs ) );
		$safe_content = wp_kses( $content, self::$allowed_html );

		return "<{$tag}{$attr_string}>{$safe_content}</{$tag}>";
	}

	/**
	 * Create an arbitrary void HTML element (elements without a closing tag).
	 *
	 * @param string $tag   The tag name of the void element.
	 * @param array  $attrs An associative array of attributes for the element.
	 *
	 * @return string The HTML string for the void element.
	 */
	public static function create_void( string $tag, array $attrs = [] ): string {
		self::init_allowed_html();
		$attr_string = self::build_attribute_string( self::escape_attributes( $attrs ) );

		return "<{$tag}{$attr_string} />";
	}

	/**
	 * Escape HTML attributes while preserving non-string values.
	 *
	 * @param array $attrs Array of attribute key-value pairs to escape.
	 *
	 * @return array Escaped attributes array with preserved non-string values.
	 * @since 1.0.0
	 *
	 */
	private static function escape_attributes( array $attrs ): array {
		$escaped_attrs = [];
		foreach ( $attrs as $key => $value ) {
			$escaped_attrs[ $key ] = is_string( $value ) ? esc_attr( $value ) : $value;
		}

		return $escaped_attrs;
	}

	/**
	 * Build an attribute string from an associative array of attributes.
	 *
	 * @param array $attrs An associative array of attributes.
	 *
	 * @return string The attribute string.
	 */
	private static function build_attribute_string( array $attrs ): string {
		$attr_pairs = [];
		foreach ( $attrs as $key => $value ) {
			if ( $value === true ) {
				$attr_pairs[] = esc_attr( $key );
			} elseif ( $value !== false && $value !== null ) {
				$attr_pairs[] = esc_attr( $key ) . '="' . $value . '"';
			}
		}

		return $attr_pairs ? ' ' . implode( ' ', $attr_pairs ) : '';
	}

	/**
	 * Parse a style attribute string into an associative array.
	 *
	 * @param string $style_string The style attribute string.
	 *
	 * @return array An associative array of style properties.
	 */
	public static function parse_style_attribute( string $style_string ): array {
		$styles = [];
		$parts  = explode( ';', $style_string );
		foreach ( $parts as $part ) {
			$part = trim( $part );
			if ( $part ) {
				list( $property, $value ) = explode( ':', $part, 2 );
				$styles[ trim( $property ) ] = trim( $value );
			}
		}

		return $styles;
	}

	/**
	 * Build a style string from an associative array of style properties.
	 *
	 * @param array $styles An associative array of style properties.
	 *
	 * @return string The built style string.
	 */
	public static function build_style_string( array $styles ): string {
		$style_parts = [];
		foreach ( $styles as $property => $value ) {
			$style_parts[] = $property . ': ' . $value;
		}

		return implode( '; ', $style_parts );
	}

	/**
	 * Merge new styles with existing styles.
	 *
	 * @param array  $new_styles     The new styles to add.
	 * @param string $existing_style The existing style string.
	 *
	 * @return string The merged style string.
	 */
	public static function merge_styles( array $new_styles, string $existing_style ): string {
		$existing_styles = self::parse_style_attribute( $existing_style );
		$merged_styles   = array_merge( $new_styles, $existing_styles );

		return self::build_style_string( $merged_styles );
	}

	/**
	 * Create an HTML heading element (h1-h6).
	 *
	 * @param int    $level   The heading level (1-6)
	 * @param string $content The heading content
	 * @param array  $attrs   Additional attributes
	 *
	 * @return string The HTML heading element
	 */
	public static function heading( int $level, string $content, array $attrs = [] ): string {
		$level = max( 1, min( 6, $level ) );

		return self::create( "h{$level}", $attrs, $content );
	}

	/**
	 * Creates an H1 heading element.
	 *
	 * Primary heading that should typically be used once per page to maintain proper
	 * document outline and accessibility hierarchy. Should represent the main topic
	 * of the page or section.
	 *
	 * @param string $content The text content of the heading.
	 * @param array  $attrs   Optional. Additional HTML attributes for the heading element.
	 *                        Accepts 'class', 'id', 'style', and ARIA attributes.
	 *
	 * @return string HTML markup for the h1 heading.
	 */
	public static function h1( string $content, array $attrs = [] ): string {
		return self::heading( 1, $content, $attrs );
	}

	/**
	 * Creates an H2 heading element.
	 *
	 * Secondary heading that represents main sections of content. Should be used to
	 * break up content under the main H1 heading into logical sections.
	 *
	 * @param string $content The text content of the heading.
	 * @param array  $attrs   Optional. Additional HTML attributes for the heading element.
	 *                        Accepts 'class', 'id', 'style', and ARIA attributes.
	 *
	 * @return string HTML markup for the h2 heading.
	 */
	public static function h2( string $content, array $attrs = [] ): string {
		return self::heading( 2, $content, $attrs );
	}

	/**
	 * Creates an H3 heading element.
	 *
	 * Tertiary heading that represents subsections of content. Should be used to
	 * further organize content under H2 headings.
	 *
	 * @param string $content The text content of the heading.
	 * @param array  $attrs   Optional. Additional HTML attributes for the heading element.
	 *                        Accepts 'class', 'id', 'style', and ARIA attributes.
	 *
	 * @return string HTML markup for the h3 heading.
	 */
	public static function h3( string $content, array $attrs = [] ): string {
		return self::heading( 3, $content, $attrs );
	}

	/**
	 * Creates an H4 heading element.
	 *
	 * Fourth-level heading that represents deeper subsections. Should be used for
	 * content organization under H3 headings when additional hierarchy is needed.
	 *
	 * @param string $content The text content of the heading.
	 * @param array  $attrs   Optional. Additional HTML attributes for the heading element.
	 *                        Accepts 'class', 'id', 'style', and ARIA attributes.
	 *
	 * @return string HTML markup for the h4 heading.
	 */
	public static function h4( string $content, array $attrs = [] ): string {
		return self::heading( 4, $content, $attrs );
	}

	/**
	 * Creates an H5 heading element.
	 *
	 * Fifth-level heading for fine-grained content organization. Should be used
	 * sparingly and only when the content structure requires this level of depth.
	 *
	 * @param string $content The text content of the heading.
	 * @param array  $attrs   Optional. Additional HTML attributes for the heading element.
	 *                        Accepts 'class', 'id', 'style', and ARIA attributes.
	 *
	 * @return string HTML markup for the h5 heading.
	 */
	public static function h5( string $content, array $attrs = [] ): string {
		return self::heading( 5, $content, $attrs );
	}

	/**
	 * Creates an H6 heading element.
	 *
	 * Lowest-level heading for the most detailed content sections. Should be used
	 * very rarely and only when absolutely necessary for proper content hierarchy.
	 *
	 * @param string $content The text content of the heading.
	 * @param array  $attrs   Optional. Additional HTML attributes for the heading element.
	 *                        Accepts 'class', 'id', 'style', and ARIA attributes.
	 *
	 * @return string HTML markup for the h6 heading.
	 */
	public static function h6( string $content, array $attrs = [] ): string {
		return self::heading( 6, $content, $attrs );
	}

	/**
	 * Create an HTML article element.
	 *
	 * @param string $content The content
	 * @param array  $attrs   Additional attributes
	 *
	 * @return string The HTML article element
	 */
	public static function article( string $content, array $attrs = [] ): string {
		return self::create( 'article', $attrs, $content );
	}

	/**
	 * Create an HTML section element.
	 *
	 * @param string $content The content
	 * @param array  $attrs   Additional attributes
	 *
	 * @return string The HTML section element
	 */
	public static function section( string $content, array $attrs = [] ): string {
		return self::create( 'section', $attrs, $content );
	}

	/**
	 * Create an HTML header element.
	 *
	 * @param string $content The content
	 * @param array  $attrs   Additional attributes
	 *
	 * @return string The HTML header element
	 */
	public static function header( string $content, array $attrs = [] ): string {
		return self::create( 'header', $attrs, $content );
	}

	/**
	 * Create an HTML main element.
	 *
	 * @param string $content The content
	 * @param array  $attrs   Additional attributes
	 *
	 * @return string The HTML main element
	 */
	public static function main( string $content, array $attrs = [] ): string {
		return self::create( 'main', $attrs, $content );
	}

	/**
	 * Create an HTML footer element.
	 *
	 * @param string $content The content
	 * @param array  $attrs   Additional attributes
	 *
	 * @return string The HTML footer element
	 */
	public static function footer( string $content, array $attrs = [] ): string {
		return self::create( 'footer', $attrs, $content );
	}

	/**
	 * Create an HTML figure element with optional figcaption.
	 *
	 * @param string $content The figure content
	 * @param string $caption Optional caption text
	 * @param array  $attrs   Additional attributes
	 *
	 * @return string The HTML figure element
	 */
	public static function figure( string $content, string $caption = '', array $attrs = [] ): string {
		if ( ! empty( $caption ) ) {
			$content .= self::create( 'figcaption', [], $caption );
		}

		return self::create( 'figure', $attrs, $content );
	}

	/**
	 * Create an HTML blockquote element with optional citation.
	 *
	 * @param string $content The quote content
	 * @param string $cite    Optional citation URL
	 * @param array  $attrs   Additional attributes
	 *
	 * @return string The HTML blockquote element
	 */
	public static function blockquote( string $content, string $cite = '', array $attrs = [] ): string {
		if ( ! empty( $cite ) ) {
			$attrs['cite'] = esc_url( $cite );
		}

		return self::create( 'blockquote', $attrs, $content );
	}

	/**
	 * Create an HTML datalist element.
	 *
	 * @param array $options Array of options
	 * @param array $attrs   Additional attributes
	 *
	 * @return string The HTML datalist element
	 */
	public static function datalist( array $options, array $attrs = [] ): string {
		$content = '';
		foreach ( $options as $value => $label ) {
			$option_attrs = [ 'value' => $value ];
			$content      .= self::create( 'option', $option_attrs, $label );
		}

		return self::create( 'datalist', $attrs, $content );
	}

	/**
	 * Create an HTML progress element.
	 *
	 * @param int   $value The current value
	 * @param int   $max   The maximum value
	 * @param array $attrs Additional attributes
	 *
	 * @return string The HTML progress element
	 */
	public static function progress( int $value, int $max = 100, array $attrs = [] ): string {
		$attrs['value'] = $value;
		$attrs['max']   = $max;

		return self::create( 'progress', $attrs, '' );
	}

	/**
	 * Create an HTML meter element.
	 *
	 * @param float $value The current value
	 * @param float $min   The minimum value
	 * @param float $max   The maximum value
	 * @param array $attrs Additional attributes
	 *
	 * @return string The HTML meter element
	 */
	public static function meter( float $value, float $min = 0, float $max = 100, array $attrs = [] ): string {
		$attrs['value'] = $value;
		$attrs['min']   = $min;
		$attrs['max']   = $max;

		return self::create( 'meter', $attrs, '' );
	}

	/**
	 * Create an HTML output element.
	 *
	 * @param string $content The output content
	 * @param array  $attrs   Additional attributes
	 *
	 * @return string The HTML output element
	 */
	public static function output( string $content, array $attrs = [] ): string {
		return self::create( 'output', $attrs, $content );
	}

	/**
	 * Create an HTML details element with summary.
	 *
	 * @param string $summary The summary content
	 * @param string $content The details content
	 * @param array  $attrs   Additional attributes
	 *
	 * @return string The HTML details element
	 */
	public static function details( string $summary, string $content, array $attrs = [] ): string {
		$summary_element = self::create( 'summary', [], $summary );

		return self::create( 'details', $attrs, $summary_element . $content );
	}

	/**
	 * Create an HTML dialog element.
	 *
	 * @param string $content The dialog content
	 * @param array  $attrs   Additional attributes
	 *
	 * @return string The HTML dialog element
	 */
	public static function dialog( string $content, array $attrs = [] ): string {
		return self::create( 'dialog', $attrs, $content );
	}

	/**
	 * Create an HTML picture element.
	 *
	 * @param array  $sources Array of source elements with media and srcset
	 * @param string $img     Fallback img element
	 * @param array  $attrs   Additional attributes
	 *
	 * @return string The HTML picture element
	 */
	public static function picture( array $sources, string $img, array $attrs = [] ): string {
		$content = '';
		foreach ( $sources as $source ) {
			$source_attrs = [
				'srcset' => $source['srcset'],
				'media'  => $source['media'] ?? '',
				'type'   => $source['type'] ?? ''
			];
			$content      .= self::create_void( 'source', $source_attrs );
		}
		$content .= $img;

		return self::create( 'picture', $attrs, $content );
	}

	/**
	 * Create an HTML track element.
	 *
	 * @param string $src   The track source URL
	 * @param string $kind  The track kind
	 * @param array  $attrs Additional attributes
	 *
	 * @return string The HTML track element
	 */
	public static function track( string $src, string $kind = 'subtitles', array $attrs = [] ): string {
		$attrs['src']  = esc_url( $src );
		$attrs['kind'] = $kind;

		return self::create_void( 'track', $attrs );
	}

	/**
	 * Create an HTML iframe element.
	 *
	 * @param string $src   The iframe source URL
	 * @param array  $attrs Additional attributes
	 *
	 * @return string The HTML iframe element
	 */
	public static function iframe( string $src, array $attrs = [] ): string {
		$attrs['src'] = esc_url( $src );

		return self::create( 'iframe', $attrs, '' );
	}

	/**
	 * Creates a strong element to indicate strong importance, seriousness, or urgency.
	 *
	 * The strong element represents content of strong importance, seriousness, or urgency.
	 * This element does not just make text bold visually - it indicates that the text
	 * has stronger emphasis compared to surrounding text, which is particularly important
	 * for screen readers and accessibility.
	 *
	 * @param string $content The text content to be marked as strongly important.
	 * @param array  $attrs   Optional. Additional HTML attributes for the strong element.
	 *                        Accepts 'class', 'id', 'style', and ARIA attributes.
	 *                        Default empty array.
	 *
	 * @return string HTML markup for the strong element.
	 *
	 * @example
	 * ```php
	 * // Basic usage
	 * $html = Element::strong('Important notice');
	 *
	 * // With additional attributes
	 * $html = Element::strong('Critical warning', ['class' => 'alert-text']);
	 * ```
	 */
	public static function strong( string $content, array $attrs = [] ): string {
		return self::create( 'strong', $attrs, $content );
	}

	/**
	 * Creates an em (emphasis) element to indicate stress emphasis.
	 *
	 * The em element represents stress emphasis of its contents, which can change
	 * the meaning of a sentence when read aloud by screen readers. This is different
	 * from just italicizing text visually - it indicates that the text has special
	 * meaning or emphasis in its context.
	 *
	 * @param string $content The text content to be emphasized.
	 * @param array  $attrs   Optional. Additional HTML attributes for the em element.
	 *                        Accepts 'class', 'id', 'style', and ARIA attributes.
	 *                        Default empty array.
	 *
	 * @return string HTML markup for the em element.
	 *
	 * @example
	 * ```php
	 * // Basic usage
	 * $html = Element::em('crucial');
	 *
	 * // With additional attributes
	 * $html = Element::em('special note', ['class' => 'highlight']);
	 * ```
	 */
	public static function em( string $content, array $attrs = [] ): string {
		return self::create( 'em', $attrs, $content );
	}

	/**
	 * Create an HTML time element.
	 *
	 * @param string $content  The displayed content
	 * @param string $datetime The machine-readable datetime
	 * @param array  $attrs    Additional attributes
	 *
	 * @return string The HTML time element
	 */
	public static function time( string $content, string $datetime, array $attrs = [] ): string {
		$attrs['datetime'] = $datetime;

		return self::create( 'time', $attrs, $content );
	}

	/**
	 * Create an HTML mark element.
	 *
	 * @param string $content The content to highlight
	 * @param array  $attrs   Additional attributes
	 *
	 * @return string The HTML mark element
	 */
	public static function mark( string $content, array $attrs = [] ): string {
		return self::create( 'mark', $attrs, $content );
	}

	/**
	 * Create an HTML table caption element.
	 *
	 * @param string $content The caption content
	 * @param array  $attrs   Additional attributes
	 *
	 * @return string The HTML caption element
	 */
	public static function caption( string $content, array $attrs = [] ): string {
		return self::create( 'caption', $attrs, $content );
	}

	/**
	 * Create an HTML link element.
	 *
	 * @param string $url   The URL for the link.
	 * @param string $text  The text content of the link.
	 * @param array  $attrs Additional attributes for the link.
	 *
	 * @return string The HTML string for the link.
	 */
	public static function link( string $url, string $text, array $attrs = [] ): string {
		$attrs['href'] = esc_url( $url );

		return self::create( 'a', $attrs, $text );
	}

	/**
	 * Create an HTML button element.
	 *
	 * @param string $text  The text content of the button.
	 * @param array  $attrs Additional attributes for the button.
	 *
	 * @return string The HTML string for the button.
	 */
	public static function button( string $text, array $attrs = [] ): string {
		return self::create( 'button', $attrs, $text );
	}

	/**
	 * Create an HTML div element.
	 *
	 * @param string $content The content of the div.
	 * @param array  $attrs   Additional attributes for the div.
	 *
	 * @return string The HTML string for the div.
	 */
	public static function div( string $content, array $attrs = [] ): string {
		return self::create( 'div', $attrs, $content );
	}

	/**
	 * Create an HTML span element.
	 *
	 * @param string $content The content of the span.
	 * @param array  $attrs   Additional attributes for the span.
	 *
	 * @return string The HTML string for the span.
	 */
	public static function span( string $content, array $attrs = [] ): string {
		return self::create( 'span', $attrs, $content );
	}

	/**
	 * Create an HTML paragraph element.
	 *
	 * @param string $content The content of the paragraph.
	 * @param array  $attrs   Additional attributes for the paragraph.
	 *
	 * @return string The HTML string for the paragraph.
	 */
	public static function p( string $content, array $attrs = [] ): string {
		return self::create( 'p', $attrs, $content );
	}

	/**
	 * Create an HTML image element.
	 *
	 * @param string $src   The source URL of the image.
	 * @param string $alt   The alternative text for the image.
	 * @param array  $attrs Additional attributes for the image.
	 *
	 * @return string The HTML string for the image.
	 */
	public static function img( string $src, string $alt, array $attrs = [] ): string {
		$attrs['src'] = esc_url( $src );
		$attrs['alt'] = esc_attr( $alt );

		return self::create_void( 'img', $attrs );
	}

	/**
	 * Create an HTML unordered list element.
	 *
	 * @param array $items    An array of list items.
	 * @param array $attrs    Additional attributes for the ul element.
	 * @param array $li_attrs Additional attributes for each li element.
	 *
	 * @return string The HTML string for the unordered list.
	 */
	public static function ul( array $items, array $attrs = [], array $li_attrs = [] ): string {
		$content = '';
		foreach ( $items as $item ) {
			$content .= self::create( 'li', $li_attrs, $item );
		}

		return self::create( 'ul', $attrs, $content );
	}

	/**
	 * Create an HTML ordered list element.
	 *
	 * @param array $items    An array of list items.
	 * @param array $attrs    Additional attributes for the ol element.
	 * @param array $li_attrs Additional attributes for each li element.
	 *
	 * @return string The HTML string for the ordered list.
	 */
	public static function ol( array $items, array $attrs = [], array $li_attrs = [] ): string {
		$content = '';
		foreach ( $items as $item ) {
			$content .= self::create( 'li', $li_attrs, $item );
		}

		return self::create( 'ol', $attrs, $content );
	}

	/**
	 * Create an HTML input element.
	 *
	 * @param string $type  The type of the input.
	 * @param array  $attrs Additional attributes for the input.
	 *
	 * @return string The HTML string for the input.
	 */
	public static function input( string $type, array $attrs = [] ): string {
		$attrs['type'] = esc_attr( $type );

		return self::create_void( 'input', $attrs );
	}

	/**
	 * Create an HTML label element.
	 *
	 * @param string $for     The ID of the form element this label is for.
	 * @param string $content The content of the label.
	 * @param array  $attrs   Additional attributes for the label.
	 *
	 * @return string The HTML string for the label.
	 */
	public static function label( string $for, string $content, array $attrs = [] ): string {
		$attrs['for'] = esc_attr( $for );

		return self::create( 'label', $attrs, $content );
	}

	/**
	 * Create an HTML textarea element.
	 *
	 * @param string $content The content of the textarea.
	 * @param array  $attrs   Additional attributes for the textarea.
	 *
	 * @return string The HTML string for the textarea.
	 */
	public static function textarea( string $content = '', array $attrs = [] ): string {
		return self::create( 'textarea', $attrs, $content );
	}

	/**
	 * Create an HTML select element.
	 *
	 * @param array       $options  An associative array of options (value => label).
	 * @param array       $attrs    Additional attributes for the select.
	 * @param string|null $selected The value of the option that should be selected (optional).
	 *
	 * @return string The HTML string for the select.
	 */
	public static function select( array $options, array $attrs = [], ?string $selected = null ): string {
		$content = '';
		foreach ( $options as $value => $label ) {
			$option_attrs = [ 'value' => $value ];
			if ( $value === $selected ) {
				$option_attrs['selected'] = 'selected';
			}
			$content .= self::create( 'option', $option_attrs, $label );
		}

		return self::create( 'select', $attrs, $content );
	}

	/**
	 * Create an HTML form element.
	 *
	 * @param string $content The content of the form.
	 * @param string $action  The form action URL.
	 * @param string $method  The form method (get/post).
	 * @param array  $attrs   Additional attributes for the form.
	 *
	 * @return string The HTML string for the form.
	 */
	public static function form( string $content, string $action = '', string $method = 'post', array $attrs = [] ): string {
		$attrs['action'] = esc_url( $action );
		$attrs['method'] = in_array( strtolower( $method ), [ 'get', 'post' ] ) ? $method : 'post';

		if ( ! isset( $attrs['id'] ) ) {
			$attrs['id'] = 'form-' . wp_rand();
		}

		return self::create( 'form', $attrs, $content );
	}

	/**
	 * Create an HTML fieldset element.
	 *
	 * @param string $content The content of the fieldset.
	 * @param string $legend  The legend text (optional).
	 * @param array  $attrs   Additional attributes for the fieldset.
	 *
	 * @return string The HTML string for the fieldset.
	 */
	public static function fieldset( string $content, string $legend = '', array $attrs = [] ): string {
		if ( ! empty( $legend ) ) {
			$content = self::create( 'legend', [], esc_html( $legend ) ) . $content;
		}

		return self::create( 'fieldset', $attrs, $content );
	}

	/**
	 * Create a form group with label and input.
	 *
	 * @param string $label      The label text.
	 * @param string $input_type The type of input.
	 * @param string $name       The input name attribute.
	 * @param array  $attrs      Additional attributes for the input.
	 *
	 * @return string The HTML string for the form group.
	 */
	public static function form_group( string $label, string $input_type, string $name, array $attrs = [] ): string {
		$id            = $attrs['id'] ?? 'field-' . wp_rand();
		$attrs['id']   = $id;
		$attrs['name'] = $name;

		$label_html = self::label( $id, esc_html( $label ) );
		$input_html = self::input( $input_type, $attrs );

		return self::div( $label_html . $input_html, [ 'class' => 'form-group' ] );
	}

	/**
	 * Create an HTML table element.
	 *
	 * @param array $data    Array of row data.
	 * @param array $headers Table headers.
	 * @param array $attrs   Additional attributes for the table.
	 *
	 * @return string The HTML string for the table.
	 */
	public static function table( array $data, array $headers = [], array $attrs = [] ): string {
		$content = '';

		if ( ! empty( $headers ) ) {
			$content .= self::thead( $headers );
		}

		$content .= self::tbody( $data );

		$default_attrs = [ 'class' => 'wp-list-table widefat' ];
		$attrs         = array_merge( $default_attrs, $attrs );

		return self::create( 'table', $attrs, $content );
	}

	/**
	 * Create an HTML table header.
	 *
	 * @param array $headers Array of header cells.
	 * @param array $attrs   Additional attributes for the thead.
	 *
	 * @return string The HTML string for the table header.
	 */
	public static function thead( array $headers, array $attrs = [] ): string {
		$header_cells = '';
		foreach ( $headers as $header ) {
			$cell_attrs   = is_array( $header ) ? ( $header['attrs'] ?? [] ) : [];
			$cell_content = is_array( $header ) ? ( $header['content'] ?? '' ) : $header;
			$header_cells .= self::create( 'th', $cell_attrs, $cell_content );
		}

		return self::create( 'thead', $attrs, self::create( 'tr', [], $header_cells ) );
	}

	/**
	 * Create an HTML table body.
	 *
	 * @param array $rows  Array of row data.
	 * @param array $attrs Additional attributes for the tbody.
	 *
	 * @return string The HTML string for the table body.
	 */
	public static function tbody( array $rows, array $attrs = [] ): string {
		$content = '';
		foreach ( $rows as $row ) {
			$cells = '';
			foreach ( $row as $cell ) {
				$cell_attrs   = is_array( $cell ) ? ( $cell['attrs'] ?? [] ) : [];
				$cell_content = is_array( $cell ) ? ( $cell['content'] ?? '' ) : $cell;
				$cells        .= self::create( 'td', $cell_attrs, $cell_content );
			}
			$content .= self::create( 'tr', [], $cells );
		}

		return self::create( 'tbody', $attrs, $content );
	}

	/**
	 * Create an HTML table footer element.
	 *
	 * @param array $rows  Array of row data
	 * @param array $attrs Additional attributes
	 *
	 * @return string The HTML tfoot element
	 */
	public static function tfoot( array $rows, array $attrs = [] ): string {
		$content = '';
		foreach ( $rows as $row ) {
			$cells = '';
			foreach ( $row as $cell ) {
				$cell_attrs   = is_array( $cell ) ? ( $cell['attrs'] ?? [] ) : [];
				$cell_content = is_array( $cell ) ? ( $cell['content'] ?? '' ) : $cell;
				$cells        .= self::create( 'td', $cell_attrs, $cell_content );
			}
			$content .= self::create( 'tr', [], $cells );
		}

		return self::create( 'tfoot', $attrs, $content );
	}

	/**
	 * Create an HTML video element.
	 *
	 * @param string|array $src   Video source URL or array of sources.
	 * @param array        $attrs Additional attributes for the video element.
	 *
	 * @return string The HTML string for the video element.
	 */
	public static function video( $src, array $attrs = [] ): string {
		$content = '';

		// Handle multiple sources
		if ( is_array( $src ) ) {
			foreach ( $src as $source ) {
				$type    = ! empty( $source['type'] ) ? $source['type'] : 'video/mp4';
				$content .= self::create_void( 'source', [
					'src'  => esc_url( $source['url'] ),
					'type' => $type
				] );
			}
		} else {
			$content .= self::create_void( 'source', [
				'src'  => esc_url( $src ),
				'type' => 'video/mp4'
			] );
		}

		$default_attrs = [
			'controls' => true,
			'width'    => '100%',
			'preload'  => 'metadata'
		];

		$attrs = array_merge( $default_attrs, $attrs );

		return self::create( 'video', $attrs, $content );
	}

	/**
	 * Create an HTML audio element.
	 *
	 * @param string|array $src   Audio source URL or array of sources.
	 * @param array        $attrs Additional attributes for the audio element.
	 *
	 * @return string The HTML string for the audio element.
	 */
	public static function audio( $src, array $attrs = [] ): string {
		$content = '';

		// Handle multiple sources
		if ( is_array( $src ) ) {
			foreach ( $src as $source ) {
				$type    = ! empty( $source['type'] ) ? $source['type'] : 'audio/mpeg';
				$content .= self::create_void( 'source', [
					'src'  => esc_url( $source['url'] ),
					'type' => $type
				] );
			}
		} else {
			$content .= self::create_void( 'source', [
				'src'  => esc_url( $src ),
				'type' => 'audio/mpeg'
			] );
		}

		$default_attrs = [
			'controls' => true,
			'preload'  => 'metadata'
		];

		$attrs = array_merge( $default_attrs, $attrs );

		return self::create( 'audio', $attrs, $content );
	}

	/**
	 * Create an HTML navigation element.
	 *
	 * @param string $content The content of the nav element.
	 * @param array  $attrs   Additional attributes for the nav element.
	 *
	 * @return string The HTML string for the nav element.
	 */
	public static function nav( string $content, array $attrs = [] ): string {
		return self::create( 'nav', $attrs, $content );
	}

}