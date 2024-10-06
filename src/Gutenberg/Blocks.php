<?php
/**
 * Block Collection Utilities for WordPress
 *
 * This class provides a set of utility functions for working with collections of WordPress blocks.
 * It includes methods for retrieving, filtering, and manipulating multiple blocks, as well as
 * handling block-related operations at a higher level.
 *
 * @package       ArrayPress/WP-Utils
 * @version       1.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Gutenberg;

/**
 * Check if the class `Blocks` is defined, and if not, define it.
 */
if ( ! class_exists( 'Blocks' ) ) :

	/**
	 * Blocks utility class for WordPress.
	 *
	 * This class provides utility methods for working with collections of blocks in WordPress,
	 * including parsing, filtering, and manipulating groups of blocks.
	 */
	class Blocks {

		/**
		 * Parse blocks from content.
		 *
		 * @param string $content The content to parse.
		 *
		 * @return array An array of parsed blocks.
		 */
		public static function parse( string $content ): array {
			return parse_blocks( $content );
		}

		/**
		 * Render an array of blocks.
		 *
		 * @param array $blocks Array of parsed blocks.
		 *
		 * @return string The rendered blocks as a string.
		 */
		public static function render( array $blocks ): string {
			return array_reduce( $blocks, function ( $content, $block ) {
				return $content . render_block( $block );
			}, '' );
		}

		/**
		 * Filter blocks based on a callback function.
		 *
		 * @param array    $blocks   Array of parsed blocks.
		 * @param callable $callback The callback function to filter blocks.
		 *
		 * @return array The filtered blocks.
		 */
		public static function filter( array $blocks, callable $callback ): array {
			return array_filter( $blocks, $callback );
		}

		/**
		 * Get all blocks of a specific type from the content.
		 *
		 * @param string      $block_name The full block type name, or a partial match.
		 * @param string|null $content    The content to search in. Use null for get_the_content().
		 *
		 * @return array An array of matching blocks.
		 */
		public static function get_of_type( string $block_name, ?string $content = null ): array {
			$content = $content ?? get_the_content();
			$blocks  = self::parse( $content );

			return self::filter( $blocks, function ( $block ) use ( $block_name ) {
				return Block::is_matching( $block, $block_name );
			} );
		}

		/**
		 * Convert Classic Editor content to Blocks.
		 *
		 * @param string $content The classic editor content.
		 *
		 * @return string The content converted to blocks.
		 */
		public static function convert_classic_to_blocks( string $content ): string {
			if ( ! function_exists( 'parse_blocks' ) ) {
				return $content;
			}

			$blocks = parse_blocks( $content );

			return self::render( $blocks );
		}

		/**
		 * Get all registered block types.
		 *
		 * @return array An array of registered block types.
		 */
		public static function get_registered_types(): array {
			return \WP_Block_Type_Registry::get_instance()->get_all_registered();
		}

		/**
		 * Get all blocks from a post.
		 *
		 * @param int|\WP_Post $post The post ID or post object.
		 *
		 * @return array An array of blocks.
		 */
		public static function get_from_post( $post ): array {
			$post = get_post( $post );

			return $post ? self::parse( $post->post_content ) : [];
		}

		/**
		 * Replace a block in a post's content.
		 *
		 * @param int|\WP_Post $post       The post ID or post object.
		 * @param string       $block_name The name of the block to replace.
		 * @param array        $new_block  The new block to insert.
		 *
		 * @return bool True if the block was replaced, false otherwise.
		 */
		public static function replace_in_post( $post, string $block_name, array $new_block ): bool {
			$post = get_post( $post );
			if ( ! $post ) {
				return false;
			}

			$blocks   = self::get_from_post( $post );
			$replaced = false;

			foreach ( $blocks as &$block ) {
				if ( $block['blockName'] === $block_name ) {
					$block    = $new_block;
					$replaced = true;
					break;
				}
			}

			if ( $replaced ) {
				$new_content = serialize_blocks( $blocks );
				wp_update_post( [
					'ID'           => $post->ID,
					'post_content' => $new_content,
				] );

				return true;
			}

			return false;
		}

		/**
		 * Get block categories.
		 *
		 * @return array An array of block categories.
		 */
		public static function get_categories(): array {
			return function_exists( 'get_block_categories' ) ? get_block_categories( get_post() ) : [];
		}

		/**
		 * Check if Gutenberg is active.
		 *
		 * @return bool True if Gutenberg is active, false otherwise.
		 */
		public static function is_gutenberg_active(): bool {
			return function_exists( 'register_block_type' );
		}

		/**
		 * Count the total number of blocks in the content.
		 *
		 * @param string|null $content The content to count blocks in. Use null for get_the_content().
		 *
		 * @return int The total number of blocks.
		 */
		public static function count_total( string $content = null ): int {
			$content = $content ?? get_the_content();

			return count( self::parse( $content ) );
		}

		/**
		 * Get the most used block type in the content.
		 *
		 * @param string|null $content The content to analyze. Use null for get_the_content().
		 *
		 * @return string|null The name of the most used block type, or null if no blocks found.
		 */
		public static function get_most_used_type( string $content = null ): ?string {
			$content      = $content ?? get_the_content();
			$blocks       = self::parse( $content );
			$block_counts = array_count_values( array_column( $blocks, 'blockName' ) );
			arsort( $block_counts );

			return key( $block_counts ) ?: null;
		}

		/**
		 * Check if the content uses a specific block type.
		 *
		 * @param string      $block_name The name of the block type to check for.
		 * @param string|null $content    The content to check. Use null for get_the_content().
		 *
		 * @return bool True if the content uses the specified block type, false otherwise.
		 */
		public static function uses_block_type( string $block_name, string $content = null ): bool {
			$content = $content ?? get_the_content();
			$blocks  = self::parse( $content );

			return ! empty( self::filter( $blocks, function ( $block ) use ( $block_name ) {
				return $block['blockName'] === $block_name;
			} ) );
		}
	}

endif;