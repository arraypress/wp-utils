<?php
/*
Plugin Name: ArrayPress - WP Utilities
Description: A plugin that loads WordPress utility files from the src folder.
Version: 1.0
Author: ArrayPress
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class ArrayPress_WP_Utilities {

	private string $src_path;

	public function __construct() {
		$this->src_path = plugin_dir_path( __FILE__ ) . 'src/';
		$this->load_files();
	}

	private function load_files() {

		$files = [
			'Arr.php',
			'Attachment.php',
			'Block.php',
			'Blocks.php',
			'Cast.php',
			'Color.php',
			'Compare.php',
			'Convert.php',
			'Cookie.php',
			'Create.php',
			'Currency.php',
			'Database.php',
			'Date.php',
			'Email.php',
			'Extract.php',
			'File.php',
			'Format.php',
			'Generate.php',
			'Gutenberg.php',
			'Hashing.php',
			'IP.php',
			'Math.php',
			'Meta.php',
			'MIME.php',
			'Operators.php',
			'Option.php',
			'Options.php',
			'Page.php',
			'Post.php',
			'Posts.php',
			'Sanitize.php',
			'Server.php',
			'Site.php',
			'Social.php',
			'SQL.php',
			'Str.php',
			'Taxonomies.php',
			'Taxonomy.php',
			'Term.php',
			'Terms.php',
			'Theme.php',
			'TimeUnits.php',
			'Transient.php',
			'Transients.php',
			'UnitConversion.php',
			'URL.php',
			'User.php',
			'Users.php',
			'Validate.php',
			'Widget.php',
		];

		foreach ( $files as $file ) {
			$file_path = $this->src_path . $file;
			if ( file_exists( $file_path ) ) {
				require_once $file_path;
			} else {
				error_log("ArrayPress Utilities: File not found: $file_path");
			}
		}
	}

}

// Initialize the plugin
new ArrayPress_WP_Utilities();