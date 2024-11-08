<?php
/**
 * DOM Utilities
 *
 * This class provides comprehensive utility functions for handling DOM operations,
 * including element creation, manipulation, and traversal with CSS selector support.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\DOM;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use Exception;
use WP_Error;

class DOM {

	/**
	 * Default DOM loading options
	 *
	 * @var array
	 */
	private const DEFAULT_OPTIONS = [
		'preserve_white_space' => false,
		'format_output'        => true,
		'encoding'             => 'UTF-8',
		'version'              => '1.0',
		'convert_encoding'     => true
	];

	/**
	 * Creates a new DOMDocument instance with the given HTML content.
	 *
	 * @param string $html    HTML content to load
	 * @param array  $options Configuration options for DOM creation
	 *
	 * @return DOMDocument
	 *
	 * Example:
	 * ```php
	 * // Basic usage
	 * $dom = DOM::create('<div>Hello World</div>');
	 *
	 * // With custom options
	 * $dom = DOM::create('<div>Content</div>', [
	 *     'preserve_white_space' => true,
	 *     'format_output' => true,
	 *     'encoding' => 'UTF-8'
	 * ]);
	 * ```
	 */
	public static function create( string $html, array $options = [] ): DOMDocument {
		$options = array_merge( self::DEFAULT_OPTIONS, $options );

		$dom = new DOMDocument( $options['version'], $options['encoding'] );

		if ( empty( $html ) ) {
			return $dom;
		}

		$dom->preserveWhiteSpace = $options['preserve_white_space'];
		$dom->formatOutput       = $options['format_output'];

		// Set libxml options
		$libxml_options = LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD;

		libxml_use_internal_errors( true );

		// Handle encoding if needed
		if ( $options['convert_encoding'] ) {
			$html = self::convert_unicode_to_html_entities( $html );
		}

		// Load HTML with proper UTF-8 encoding
		$html = '<?xml encoding="UTF-8">' . $html;
		$dom->loadHTML( $html, $libxml_options );

		// Clean up
		libxml_clear_errors();
		libxml_use_internal_errors( false );

		return $dom;
	}

	/**
	 * Returns a formatted DOMElement object from a DOMDocument object.
	 *
	 * @param string $tag            HTML tag
	 * @param mixed  $dom_or_element DOMDocument or DOMElement
	 * @param int    $index          Index of element to return
	 *
	 * @return null|DOMElement Returns DOMElement if found, null otherwise
	 */
	public static function get_element( string $tag, $dom_or_element, int $index = 0 ): ?DOMElement {
		if ( ! is_a( $dom_or_element, DOMDocument::class ) && ! is_a( $dom_or_element, DOMElement::class ) ) {
			return null;
		}

		$element = $dom_or_element->getElementsByTagName( $tag )->item( $index );

		if ( ! $element ) {
			return null;
		}

		return self::node_to_element( $element );
	}

	/**
	 * Finds elements using CSS selector syntax.
	 *
	 * @param string                 $selector CSS selector
	 * @param DOMDocument|DOMElement $context  Context to search within
	 *
	 * @return DOMNodeList
	 *
	 * Example:
	 * ```php
	 * // Find all paragraphs with a specific class
	 * $elements = DOM::query_selector_all('p.highlight', $dom);
	 *
	 * // Find elements within a specific context
	 * $container = DOM::query_selector('.container', $dom);
	 * $items = DOM::query_selector_all('.item', $container);
	 * ```
	 */
	public static function query_selector_all( string $selector, $context ): DOMNodeList {
		$xpath = new DOMXPath( $context instanceof DOMDocument ? $context : $context->ownerDocument );

		return $xpath->query( self::css_to_xpath( $selector ) );
	}

	/**
	 * Finds first element using CSS selector syntax.
	 *
	 * @param string                 $selector CSS selector
	 * @param DOMDocument|DOMElement $context  Context to search within
	 *
	 * @return null|DOMElement Returns DOMElement if found, null otherwise
	 */
	public static function query_selector( string $selector, $context ): ?DOMElement {
		$elements = self::query_selector_all( $selector, $context );

		return $elements->length ? self::node_to_element( $elements->item( 0 ) ) : null;
	}


	/**
	 * Casts a DOMNode to a DOMElement.
	 *
	 * @param mixed $node DOMNode to cast to DOMElement
	 *
	 * @return null|DOMElement Returns DOMElement if node is an element node, null otherwise
	 */
	public static function node_to_element( $node ): ?DOMElement {
		if ( $node && $node->nodeType === XML_ELEMENT_NODE ) {
			/** @var DOMElement $node DOM Element node */
			return $node;
		}

		return null;
	}

	/**
	 * Creates a new element with attributes and content.
	 *
	 * @param DOMDocument          $dom        Document to create element in
	 * @param string               $tag        Tag name
	 * @param array                $attributes Element attributes
	 * @param string|DOMNode|array $content    Element content or child nodes
	 *
	 * @return null|DOMElement Returns DOMElement if created successfully, null on failure
	 */
	public static function create_element( DOMDocument $dom, string $tag, array $attributes = [], $content = '' ): ?DOMElement {
		try {
			$element = $dom->createElement( $tag );

			// Set attributes
			foreach ( $attributes as $name => $value ) {
				$element->setAttribute( $name, (string) $value );
			}

			// Add content
			if ( is_string( $content ) ) {
				if ( ! empty( $content ) ) {
					$element->appendChild( $dom->createTextNode( $content ) );
				}
			} elseif ( $content instanceof DOMNode ) {
				$element->appendChild( $content );
			} elseif ( is_array( $content ) ) {
				foreach ( $content as $child ) {
					if ( $child instanceof DOMNode ) {
						$element->appendChild( $child );
					}
				}
			}

			return $element;
		} catch ( Exception $e ) {
			new WP_Error( 'invalid_dom_element', $e->getMessage() );

			return null;
		}
	}

	/**
	 * Wraps an element with a new parent element.
	 *
	 * @param DOMElement $element     Element to wrap
	 * @param string     $wrapper_tag Tag name for wrapper
	 * @param array      $attributes  Attributes for wrapper
	 *
	 * @return null|DOMElement Returns the wrapper element if successful, null on failure
	 *
	 * Example:
	 * ```php
	 * // Wrap a paragraph in a div with class
	 * $p = DOM::query_selector('p', $dom);
	 * DOM::wrap($p, 'div', ['class' => 'wrapper']);
	 *
	 * // Wrap multiple elements
	 * $elements = DOM::query_selector_all('.wrap-me', $dom);
	 * foreach ($elements as $element) {
	 *     DOM::wrap($element, 'section', [
	 *         'class' => 'section-wrapper',
	 *         'data-wrapped' => 'true'
	 *     ]);
	 * }
	 * ```
	 */
	public static function wrap( DOMElement $element, string $wrapper_tag, array $attributes = [] ): ?DOMElement {
		if ( ! $element->parentNode ) {
			return null;
		}

		$wrapper = self::create_element( $element->ownerDocument, $wrapper_tag, $attributes );
		if ( ! $wrapper ) {
			return null;
		}

		$element->parentNode->replaceChild( $wrapper, $element );
		$wrapper->appendChild( $element );

		return $wrapper;
	}

	/**
	 * Removes an element while preserving its children.
	 *
	 * @param DOMElement $element Element to unwrap
	 *
	 * @return bool Success status
	 */
	public static function unwrap( DOMElement $element ): bool {
		if ( ! $element->parentNode ) {
			return false;
		}

		$fragment = $element->ownerDocument->createDocumentFragment();
		while ( $element->firstChild ) {
			$fragment->appendChild( $element->firstChild );
		}

		$element->parentNode->replaceChild( $fragment, $element );

		return true;
	}

	/**
	 * Returns an HTML element with a replaced tag.
	 *
	 * Creates a new element with the specified tag name while preserving all
	 * children and attributes from the original element.
	 *
	 * @param string     $name    Tag name, e.g: 'div'
	 * @param DOMElement $element DOM Element to change
	 *
	 * @return null|DOMElement Returns new DOMElement with changed tag, null if element has no owner document
	 */
	public static function change_tag_name( string $name, DOMElement $element ): ?DOMElement {
		if ( ! $element->ownerDocument ) {
			return null;
		}

		$child_nodes = [];

		foreach ( $element->childNodes as $child ) {
			$child_nodes[] = $child;
		}

		$new_element = $element->ownerDocument->createElement( $name );

		foreach ( $child_nodes as $child ) {
			$child2 = $element->ownerDocument->importNode( $child, true );
			$new_element->appendChild( $child2 );
		}

		foreach ( $element->attributes as $attr_node ) {
			$attr_name  = $attr_node->nodeName;
			$attr_value = $attr_node->nodeValue;

			$new_element->setAttribute( $attr_name, $attr_value );
		}

		if ( $element->parentNode ) {
			$element->parentNode->replaceChild( $new_element, $element );
		}

		return $new_element;
	}

	/**
	 * Returns an array of DOM elements by class name.
	 *
	 * @param string      $class_name Element class name
	 * @param DOMDocument $dom        DOM document
	 * @param string      $tag        Element tag name (optional)
	 *
	 * @return array
	 */
	public static function get_elements_by_class_name( string $class_name, DOMDocument $dom, string $tag = '*' ): array {
		$elements = self::query_selector_all( "$tag.$class_name", $dom );

		return iterator_to_array( $elements );
	}

	/**
	 * Returns an array of DOM elements that contain the specified text content.
	 *
	 * @param DOMDocument $dom  The DOM document to search within
	 * @param string      $text Text to search for in elements
	 * @param string      $tag  Optional. The tag name to limit the search; default is '*' (all elements)
	 *
	 * @return DOMElement[]
	 */
	public static function get_elements_by_content( DOMDocument $dom, string $text, string $tag = '*' ): array {
		$xpath    = new DOMXPath( $dom );
		$query    = sprintf( "//%s[contains(., '%s')]", $tag, addslashes( $text ) );
		$nodes    = $xpath->query( $query );
		$elements = [];

		if ( ! $nodes ) {
			return $elements;
		}

		foreach ( $nodes as $node ) {
			if ( $node instanceof DOMElement ) {
				$elements[] = $node;
			}
		}

		return $elements;
	}

	/**
	 * Adds or removes classes from an element.
	 *
	 * @param DOMElement $element        Target element
	 * @param array      $add_classes    Classes to add
	 * @param array      $remove_classes Classes to remove
	 *
	 * @return void
	 *
	 * Example:
	 * ```php
	 * // Add and remove classes
	 * $element = DOM::query_selector('.my-element', $dom);
	 * DOM::modify_classes(
	 *     $element,
	 *     ['active', 'visible'], // Classes to add
	 *     ['hidden', 'disabled'] // Classes to remove
	 * );
	 *
	 * // Only add classes
	 * DOM::modify_classes($element, ['highlight', 'selected']);
	 *
	 * // Only remove classes
	 * DOM::modify_classes($element, [], ['temporary', 'draft']);
	 * ```
	 */
	public static function modify_classes( DOMElement $element, array $add_classes = [], array $remove_classes = [] ): void {
		$classes = self::get_classes( $element );

		// Remove specified classes
		$classes = array_diff( $classes, $remove_classes );

		// Add new classes
		$classes = array_merge( $classes, $add_classes );

		// Remove duplicates and empty values
		$classes = array_unique( array_filter( $classes ) );

		if ( ! empty( $classes ) ) {
			$element->setAttribute( 'class', implode( ' ', $classes ) );
		} else {
			$element->removeAttribute( 'class' );
		}
	}

	/**
	 * Adds CSS classes to a DOM element.
	 *
	 * @param DOMElement $element DOM element
	 * @param array      $classes Classes to add
	 *
	 * @return void
	 */
	public static function add_classes( DOMElement $element, array $classes ): void {
		self::modify_classes( $element, $classes );
	}

	/**
	 * Gets classes from a DOM element.
	 *
	 * @param DOMElement $element DOM element
	 *
	 * @return array
	 */
	public static function get_classes( DOMElement $element ): array {
		return array_filter( explode( ' ', $element->getAttribute( 'class' ) ) );
	}

	/**
	 * Adds CSS styles to a DOM element.
	 *
	 * @param DOMElement $element DOM element
	 * @param array      $styles  Styles to add
	 *
	 * @return void
	 */
	public static function add_styles( DOMElement $element, array $styles ): void {
		$element->setAttribute(
			'style',
			CSS::array_to_string(
				array_merge(
					self::get_styles( $element ),
					$styles
				)
			)
		);
	}

	/**
	 * Gets styles from a DOM element.
	 *
	 * @param DOMElement $element DOM element
	 *
	 * @return array
	 */
	public static function get_styles( DOMElement $element ): array {
		return CSS::string_to_array( $element->getAttribute( 'style' ) );
	}

	/**
	 * Converts a DOM tree to clean HTML string.
	 *
	 * @param DOMDocument|DOMElement $node       Node to convert
	 * @param bool                   $inner_html Whether to get inner HTML only
	 *
	 * @return string
	 */
	public static function to_html( $node, bool $inner_html = false ): string {
		if ( $node instanceof DOMDocument ) {
			$html = $node->saveHTML();
		} elseif ( $node instanceof DOMElement ) {
			if ( $inner_html ) {
				$html = '';
				foreach ( $node->childNodes as $child ) {
					$html .= $node->ownerDocument->saveHTML( $child );
				}
			} else {
				$html = $node->ownerDocument->saveHTML( $node );
			}
		} else {
			return '';
		}

		// Clean up any XML declaration
		return preg_replace( '/^<!DOCTYPE.+?>/', '', str_replace(
			[ '<?xml encoding="UTF-8">', '<?xml version="1.0" encoding="UTF-8"?>' ],
			'',
			$html
		) );
	}

	/**
	 * Safely gets attribute value with optional default.
	 *
	 * @param DOMElement $element   Target element
	 * @param string     $attribute Attribute name
	 * @param mixed      $default   Default value
	 *
	 * @return mixed
	 */
	public static function get_attribute( DOMElement $element, string $attribute, $default = null ) {
		return $element->hasAttribute( $attribute ) ? $element->getAttribute( $attribute ) : $default;
	}

	/**
	 * Safely sets multiple attributes at once.
	 *
	 * @param DOMElement $element    Target element
	 * @param array      $attributes Associative array of attributes
	 *
	 * @return void
	 */
	public static function set_attributes( DOMElement $element, array $attributes ): void {
		foreach ( $attributes as $name => $value ) {
			if ( $value === null ) {
				$element->removeAttribute( $name );
			} else {
				$element->setAttribute( $name, (string) $value );
			}
		}
	}

	/**
	 * Removes specified attributes from an element.
	 *
	 * @param DOMElement $element    Target element
	 * @param array      $attributes Array of attribute names to remove
	 *
	 * @return void
	 */
	public static function remove_attributes( DOMElement $element, array $attributes ): void {
		foreach ( $attributes as $attribute ) {
			$element->removeAttribute( $attribute );
		}
	}

	/**
	 * Checks if element has all specified classes.
	 *
	 * @param DOMElement $element Target element
	 * @param array      $classes Array of classes to check
	 *
	 * @return bool True if element has all specified classes
	 */
	public static function has_classes( DOMElement $element, array $classes ): bool {
		$element_classes = self::get_classes( $element );

		return empty( array_diff( $classes, $element_classes ) );
	}

	/**
	 * Removes specified classes from an element.
	 *
	 * @param DOMElement $element Target element
	 * @param array      $classes Classes to remove
	 *
	 * @return void
	 */
	public static function remove_classes( DOMElement $element, array $classes ): void {
		self::modify_classes( $element, [], $classes );
	}

	/**
	 * Replaces specified classes on an element.
	 *
	 * @param DOMElement $element     Target element
	 * @param array      $old_classes Classes to replace
	 * @param array      $new_classes Replacement classes
	 *
	 * @return void
	 */
	public static function replace_classes( DOMElement $element, array $old_classes, array $new_classes ): void {
		self::modify_classes( $element, $new_classes, $old_classes );
	}

	/**
	 * Converts CSS selector to XPath.
	 *
	 * @param string $selector CSS selector
	 *
	 * @return string XPath expression
	 */
	private static function css_to_xpath( string $selector ): string {
		$xpath = $selector;

		// Handle ID
		$xpath = preg_replace( '/#([a-zA-Z0-9_-]+)/', '[@id="$1"]', $xpath );

		// Handle classes
		$xpath = preg_replace( '/\.([a-zA-Z0-9_-]+)/', '[contains(concat(" ",normalize-space(@class)," ")," $1 ")]', $xpath );

		// Handle attributes
		$xpath = preg_replace( '/\[([a-zA-Z0-9_-]+)="([^"]+)"]/', '[@$1="$2"]', $xpath );

		// Handle direct child selector
		$xpath = str_replace( ' > ', '/', $xpath );

		// Add // for general searches if not starting with specific path
		if ( ! str_starts_with( $xpath, '/' ) ) {
			$xpath = '//' . $xpath;
		}

		return $xpath;
	}

	/**
	 * Converts unicode characters to HTML entities.
	 *
	 * @param string $html Input HTML
	 *
	 * @return string Processed HTML
	 */
	private static function convert_unicode_to_html_entities( string $html ): string {
		return preg_replace_callback(
			'/[\x{80}-\x{10FFFF}]/u',
			static fn( array $matches ): string => sprintf(
				'&#x%s;',
				ltrim(
					strtoupper(
						bin2hex(
							iconv( 'UTF-8', 'UCS-4', current( $matches ) )
						)
					),
					'0'
				)
			),
			$html
		);
	}

	/**
	 * Safely inserts HTML content before a target element.
	 *
	 * @param DOMElement        $target  Target element
	 * @param string|DOMElement $content Content to insert
	 *
	 * @return bool Success status
	 */
	public static function insert_before( DOMElement $target, $content ): bool {
		if ( ! $target->parentNode ) {
			return false;
		}

		if ( is_string( $content ) ) {
			$temp_doc = self::create( $content );
			if ( ! $temp_doc->documentElement ) {
				return false;
			}
			$content = $target->ownerDocument->importNode( $temp_doc->documentElement, true );
		}

		$target->parentNode->insertBefore( $content, $target );

		return true;
	}

	/**
	 * Safely inserts HTML content after a target element.
	 *
	 * @param DOMElement        $target  Target element
	 * @param string|DOMElement $content Content to insert
	 *
	 * @return bool Success status
	 */
	public static function insert_after( DOMElement $target, $content ): bool {
		if ( ! $target->parentNode ) {
			return false;
		}

		if ( is_string( $content ) ) {
			$temp_doc = self::create( $content );
			if ( ! $temp_doc->documentElement ) {
				return false;
			}
			$content = $target->ownerDocument->importNode( $temp_doc->documentElement, true );
		}

		if ( $target->nextSibling ) {
			$target->parentNode->insertBefore( $content, $target->nextSibling );
		} else {
			$target->parentNode->appendChild( $content );
		}

		return true;
	}

	/**
	 * Safely replaces a target element with new content.
	 *
	 * @param DOMElement        $target  Target element
	 * @param string|DOMElement $content Replacement content
	 *
	 * @return bool Success status
	 */
	public static function replace_with( DOMElement $target, $content ): bool {
		if ( ! $target->parentNode ) {
			return false;
		}

		if ( is_string( $content ) ) {
			$temp_doc = self::create( $content );
			if ( ! $temp_doc->documentElement ) {
				return false;
			}
			$content = $target->ownerDocument->importNode( $temp_doc->documentElement, true );
		}

		$target->parentNode->replaceChild( $content, $target );

		return true;
	}

	/**
	 * Clones an element with optional deep cloning of children.
	 *
	 * @param DOMElement $element Element to clone
	 * @param bool       $deep    Whether to clone children
	 *
	 * @return DOMElement
	 */
	public static function clone_element( DOMElement $element, bool $deep = true ): DOMElement {
		return $element->cloneNode( $deep );
	}

}