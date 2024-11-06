<?php
/**
 * Individual Block Utilities for WordPress
 *
 * This class provides a set of utility functions for working with individual WordPress blocks.
 * It includes methods for manipulating, analyzing, and transforming single blocks.
 *
 * @package       ArrayPress/WP-Utils
 * @version       1.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Blocks;

/**
 * Class Block
 *
 * Utility functions for working with a specific block.
 */
class Block {

	/**
	 * Get the attributes of a specific block.
	 *
	 * @param array $block The block to get attributes from.
	 *
	 * @return array The block's attributes.
	 */
	public static function get_attributes( array $block ): array {
		return $block['attrs'] ?? [];
	}

	/**
	 * Set or update a block's attribute.
	 *
	 * @param array  $block     The block to modify.
	 * @param string $attribute The attribute name.
	 * @param mixed  $value     The attribute value.
	 *
	 * @return array The modified block.
	 */
	public static function set_attribute( array $block, string $attribute, $value ): array {
		$block['attrs']               = $block['attrs'] ?? [];
		$block['attrs'][ $attribute ] = $value;

		return $block;
	}

	/**
	 * Remove an attribute from a block.
	 *
	 * @param array  $block     The block to modify.
	 * @param string $attribute The attribute name to remove.
	 *
	 * @return array The modified block.
	 */
	public static function remove_attribute( array $block, string $attribute ): array {
		unset( $block['attrs'][ $attribute ] );

		return $block;
	}

	/**
	 * Get the inner blocks of a block.
	 *
	 * @param array $block The block to get inner blocks from.
	 *
	 * @return array The inner blocks.
	 */
	public static function get_inner_blocks( array $block ): array {
		return $block['innerBlocks'] ?? [];
	}

	/**
	 * Set the inner blocks of a block.
	 *
	 * @param array $block        The block to modify.
	 * @param array $inner_blocks The new inner blocks.
	 *
	 * @return array The modified block.
	 */
	public static function set_inner_blocks( array $block, array $inner_blocks ): array {
		$block['innerBlocks'] = $inner_blocks;

		return $block;
	}

	/**
	 * Add an inner block to a block.
	 *
	 * @param array $block       The block to modify.
	 * @param array $inner_block The inner block to add.
	 *
	 * @return array The modified block.
	 */
	public static function add_inner_block( array $block, array $inner_block ): array {
		$block['innerBlocks']   = $block['innerBlocks'] ?? [];
		$block['innerBlocks'][] = $inner_block;

		return $block;
	}

	/**
	 * Remove an inner block from a block by index.
	 *
	 * @param array $block The block to modify.
	 * @param int   $index The index of the inner block to remove.
	 *
	 * @return array The modified block.
	 */
	public static function remove_inner_block( array $block, int $index ): array {
		unset( $block['innerBlocks'][ $index ] );
		$block['innerBlocks'] = array_values( $block['innerBlocks'] ); // Re-index array

		return $block;
	}

	/**
	 * Get the block comment delimiters.
	 *
	 * @return array An array containing the opening and closing delimiters.
	 */
	public static function get_delimiters(): array {
		return [
			'opener'      => '<!-- wp:',
			'closer'      => ' /-->',
			'full_closer' => '<!-- /wp:',
		];
	}

	/**
	 * Check if a string is a valid block.
	 *
	 * @param string $content The content to check.
	 *
	 * @return bool True if the content is a valid block, false otherwise.
	 */
	public static function is_valid( string $content ): bool {
		$delimiters = self::get_delimiters();

		return strpos( $content, $delimiters['opener'] ) === 0 &&
		       ( strpos( $content, $delimiters['closer'] ) !== false ||
		         strpos( $content, $delimiters['full_closer'] ) !== false );
	}

	/**
	 * Get the block name from a block string or array.
	 *
	 * @param string|array $block The block string or array.
	 *
	 * @return string|null The block name or null if not found.
	 */
	public static function get_name( $block ): ?string {
		if ( is_array( $block ) ) {
			return $block['blockName'] ?? null;
		}

		$delimiters = self::get_delimiters();
		$start      = strlen( $delimiters['opener'] );
		$end        = strpos( $block, ' ', $start );
		if ( $end === false ) {
			$end = strpos( $block, $delimiters['closer'], $start );
		}
		if ( $end === false ) {
			return null;
		}

		return substr( $block, $start, $end - $start );
	}

	/**
	 * Convert a block array to a block string.
	 *
	 * @param array $block The block array.
	 *
	 * @return string The block string.
	 */
	public static function to_string( array $block ): string {
		return serialize_block( $block );
	}

	/**
	 * Convert a block string to a block array.
	 *
	 * @param string $block_string The block string.
	 *
	 * @return array The block array.
	 */
	public static function to_array( string $block_string ): array {
		$blocks = parse_blocks( $block_string );

		return $blocks[0] ?? [];
	}

	/**
	 * Check if a block matches the given block name pattern.
	 *
	 * @param array  $block      The block to check.
	 * @param string $block_name The block name or pattern.
	 *
	 * @return bool True if the block matches the pattern, false otherwise.
	 */
	public static function is_matching( array $block, string $block_name ): bool {
		if ( substr( $block_name, - 1 ) === '*' ) {
			return strpos( $block['blockName'], rtrim( $block_name, '*' ) ) === 0;
		}

		return $block['blockName'] === $block_name;
	}

	/**
	 * Get block assets (JS and CSS files).
	 *
	 * @param string $block_name The name of the block.
	 *
	 * @return array An array of asset file URLs.
	 */
	public static function get_assets( string $block_name ): array {
		$block_type = \WP_Block_Type_Registry::get_instance()->get_registered( $block_name );
		if ( ! $block_type ) {
			return [];
		}

		$assets = [];
		if ( ! empty( $block_type->editor_script ) ) {
			$assets['js'] = wp_scripts()->registered[ $block_type->editor_script ]->src;
		}
		if ( ! empty( $block_type->editor_style ) ) {
			$assets['css'] = wp_styles()->registered[ $block_type->editor_style ]->src;
		}

		return $assets;
	}

	/**
	 * Check if a block is reusable.
	 *
	 * @param array $block The block to check.
	 *
	 * @return bool True if the block is reusable, false otherwise.
	 */
	public static function is_reusable( array $block ): bool {
		return isset( $block['blockName'] ) && $block['blockName'] === 'core/block';
	}

	/**
	 * Get the content of a reusable block.
	 *
	 * @param int $block_id The ID of the reusable block.
	 *
	 * @return string The content of the reusable block.
	 */
	public static function get_reusable_content( int $block_id ): string {
		$post = get_post( $block_id );

		return $post ? $post->post_content : '';
	}

	/**
	 * Extract the inner content of a block.
	 *
	 * @param array $block The block to extract content from.
	 *
	 * @return string The inner content of the block.
	 */
	public static function get_inner_content( array $block ): string {
		return $block['innerHTML'] ?? '';
	}

	/**
	 * Set the inner content of a block.
	 *
	 * @param array  $block   The block to modify.
	 * @param string $content The new inner content.
	 *
	 * @return array The modified block.
	 */
	public static function set_inner_content( array $block, string $content ): array {
		$block['innerHTML'] = $content;

		return $block;
	}

	/**
	 * Get the inner HTML content of a block.
	 *
	 * @param array $block The block to extract HTML content from.
	 *
	 * @return string The inner HTML content of the block.
	 */
	public static function get_inner_html( array $block ): string {
		return $block['innerContent'][0] ?? '';
	}

	/**
	 * Set the inner HTML content of a block.
	 *
	 * @param array  $block The block to modify.
	 * @param string $html  The new inner HTML content.
	 *
	 * @return array The modified block.
	 */
	public static function set_inner_html( array $block, string $html ): array {
		$block['innerContent'] = [ $html ];

		return $block;
	}

	/**
	 * Check if a block has inner blocks.
	 *
	 * @param array $block The block to check.
	 *
	 * @return bool True if the block has inner blocks, false otherwise.
	 */
	public static function has_inner_blocks( array $block ): bool {
		return ! empty( $block['innerBlocks'] );
	}

	/**
	 * Get the block type object.
	 *
	 * @param string $block_name The name of the block.
	 *
	 * @return \WP_Block_Type|null The block type object or null if not found.
	 */
	public static function get_type( string $block_name ): ?\WP_Block_Type {
		return \WP_Block_Type_Registry::get_instance()->get_registered( $block_name );
	}

	/**
	 * Check if a block type is dynamic (server-rendered).
	 *
	 * @param string $block_name The name of the block.
	 *
	 * @return bool True if the block is dynamic, false otherwise.
	 */
	public static function is_dynamic( string $block_name ): bool {
		$block_type = self::get_type( $block_name );

		return $block_type && $block_type->is_dynamic();
	}

	/**
	 * Get the category of a block.
	 *
	 * @param string $block_name The name of the block.
	 *
	 * @return string|null The category of the block or null if not found.
	 */
	public static function get_category( string $block_name ): ?string {
		$block_type = self::get_type( $block_name );

		return $block_type ? $block_type->category : null;
	}

	/**
	 * Get the description of a block.
	 *
	 * @param string $block_name The name of the block.
	 *
	 * @return string|null The description of the block or null if not found.
	 */
	public static function get_description( string $block_name ): ?string {
		$block_type = self::get_type( $block_name );

		return $block_type ? $block_type->description : null;
	}

	/**
	 * Check if a block supports a specific feature.
	 *
	 * @param array  $block   The block to check.
	 * @param string $feature The feature to check for.
	 *
	 * @return bool True if the block supports the feature, false otherwise.
	 */
	public static function supports_feature( array $block, string $feature ): bool {
		$block_type = self::get_type( $block['blockName'] );

		return $block_type && ! empty( $block_type->supports[ $feature ] );
	}

	/**
	 * Get the parent blocks of a given block.
	 *
	 * @param array $blocks All blocks in the content.
	 * @param array $block  The block to find parents for.
	 *
	 * @return array An array of parent blocks.
	 */
	public static function get_parent_blocks( array $blocks, array $block ): array {
		$parents = [];
		self::find_parents( $blocks, $block, $parents );

		return array_reverse( $parents );
	}

	/**
	 * Helper function to recursively find parent blocks.
	 *
	 * @param array  $blocks  All blocks in the content.
	 * @param array  $target  The block to find parents for.
	 * @param array &$parents The array to store parent blocks.
	 *
	 * @return bool True if the target block is found, false otherwise.
	 */
	private static function find_parents( array $blocks, array $target, array &$parents ): bool {
		foreach ( $blocks as $block ) {
			if ( $block === $target ) {
				return true;
			}
			if ( ! empty( $block['innerBlocks'] ) ) {
				if ( self::find_parents( $block['innerBlocks'], $target, $parents ) ) {
					$parents[] = $block;

					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Convert a classic block to blocks.
	 *
	 * @param array $classic_block The classic block to convert.
	 *
	 * @return array An array of converted blocks.
	 */
	public static function convert_classic_to_blocks( array $classic_block ): array {
		if ( $classic_block['blockName'] !== 'core/freeform' ) {
			return [ $classic_block ];
		}

		$content = $classic_block['innerHTML'];

		return parse_blocks( $content );
	}
}