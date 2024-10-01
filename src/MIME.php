<?php
/**
 * E-commerce Math Utility Class
 *
 * This class provides a set of utility methods for performing calculations
 * commonly used in e-commerce, affiliation, and related applications.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils;

/**
 * Check if the class `MIME` is defined, and if not, define it.
 */
if ( ! class_exists( 'MIME' ) ) :

	/**
	 * MIME utility class for WordPress.
	 *
	 * This class provides utility methods for working with MIME types in WordPress.
	 */
	class MIME {

		/**
		 * Get the MIME type for a given file extension.
		 *
		 * @param string $extension The file extension.
		 *
		 * @return string|null The MIME type or null if not found.
		 */
		public static function get_type_from_extension( string $extension ): ?string {
			$mime_types = wp_get_mime_types();
			$extension  = strtolower( ltrim( $extension, '.' ) );

			foreach ( $mime_types as $exts => $mime ) {
				if ( preg_match( '!^(' . $exts . ')$!i', $extension ) ) {
					return $mime;
				}
			}

			return null;
		}

		/**
		 * Get the file extension for a given MIME type.
		 *
		 * @param string $mime_type The MIME type.
		 *
		 * @return string|null The file extension or null if not found.
		 */
		public static function get_extension_from_type( string $mime_type ): ?string {
			$mime_types = array_flip( wp_get_mime_types() );

			return $mime_types[ $mime_type ] ?? null;
		}

		/**
		 * Check if a MIME type is in a specific category.
		 *
		 * @param string $mime_type The MIME type to check.
		 * @param string $category  The category to check against ('image', 'audio', 'video', 'document', 'archive',
		 *                          'code').
		 *
		 * @return bool True if the MIME type is in the specified category, false otherwise.
		 */
		public static function is_type( string $mime_type, string $category ): bool {
			$categories = [
				'image'        => [ 'image/' ],
				'audio'        => [ 'audio/' ],
				'video'        => [ 'video/' ],
				'document'     => [
					'application/pdf',
					'application/msword',
					'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
					'text/'
				],
				'spreadsheet'  => [
					'application/vnd.ms-excel',
					'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
				],
				'presentation' => [
					'application/vnd.ms-powerpoint',
					'application/vnd.openxmlformats-officedocument.presentationml.presentation'
				],
				'archive'      => [ 'application/zip', 'application/x-rar-compressed', 'application/x-7z-compressed' ],
				'code'         => [
					'text/html',
					'text/css',
					'application/javascript',
					'application/json',
					'application/xml'
				],
			];

			if ( ! isset( $categories[ $category ] ) ) {
				return false;
			}

			foreach ( $categories[ $category ] as $prefix ) {
				if ( strpos( $mime_type, $prefix ) === 0 ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Get a human-readable description of a MIME type.
		 *
		 * @param string $mime_type The MIME type.
		 *
		 * @return string A human-readable description of the MIME type.
		 */
		public static function get_description( string $mime_type ): string {
			$descriptions = [
				'image/jpeg'                                                              => 'JPEG Image',
				'image/png'                                                               => 'PNG Image',
				'image/gif'                                                               => 'GIF Image',
				'image/webp'                                                              => 'WebP Image',
				'audio/mpeg'                                                              => 'MP3 Audio',
				'audio/wav'                                                               => 'WAV Audio',
				'video/mp4'                                                               => 'MP4 Video',
				'video/quicktime'                                                         => 'QuickTime Video',
				'application/pdf'                                                         => 'PDF Document',
				'application/msword'                                                      => 'Microsoft Word Document',
				'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'Microsoft Word Document (DOCX)',
				'application/vnd.ms-excel'                                                => 'Microsoft Excel Spreadsheet',
				'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'       => 'Microsoft Excel Spreadsheet (XLSX)',
				'application/zip'                                                         => 'ZIP Archive',
				'text/html'                                                               => 'HTML Document',
				'text/css'                                                                => 'CSS Stylesheet',
				'application/javascript'                                                  => 'JavaScript Code',
			];

			return $descriptions[ $mime_type ] ?? 'Unknown File Type';
		}

		/**
		 * Get the general type of a file based on its MIME type.
		 *
		 * @param string $mime_type The MIME type.
		 *
		 * @return string The general file type ('image', 'audio', 'video', 'document', 'spreadsheet', 'presentation',
		 *                'archive', 'code', or 'other').
		 */
		public static function get_general_type( string $mime_type ): string {
			if ( strpos( $mime_type, 'text/' ) === 0 ) {
				return 'text';
			}

			$types = [ 'image', 'audio', 'video', 'document', 'spreadsheet', 'presentation', 'archive', 'code' ];

			foreach ( $types as $type ) {
				if ( self::is_type( $mime_type, $type ) ) {
					return $type;
				}
			}

			return 'other';
		}

		/**
		 * Check if a file is allowed based on its MIME type and a list of allowed types.
		 *
		 * @param string $mime_type     The MIME type of the file.
		 * @param array  $allowed_types An array of allowed MIME types or general types.
		 *
		 * @return bool True if the file is allowed, false otherwise.
		 */
		public static function is_allowed( string $mime_type, array $allowed_types ): bool {
			$general_type = self::get_general_type( $mime_type );

			return in_array( $mime_type, $allowed_types ) || in_array( $general_type, $allowed_types );
		}

		/**
		 * Get the MIME type of a file.
		 *
		 * @param string $file_path The path to the file.
		 *
		 * @return string|false The MIME type of the file, or false on failure.
		 */
		public static function get_file_type( string $file_path ) {
			$file_type = wp_check_filetype( $file_path );
			if ( $file_type['type'] === false && pathinfo( $file_path, PATHINFO_EXTENSION ) === 'txt' ) {
				return 'text/plain';
			}

			return $file_type['type'];
		}

		/**
		 * Get an array of common MIME types grouped by category.
		 *
		 * @return array An array of MIME types grouped by category.
		 */
		public static function get_common_types(): array {
			return [
				'image'        => [ 'image/jpeg', 'image/png', 'image/gif', 'image/webp' ],
				'audio'        => [ 'audio/mpeg', 'audio/wav', 'audio/ogg' ],
				'video'        => [ 'video/mp4', 'video/quicktime', 'video/webm' ],
				'document'     => [
					'application/pdf',
					'application/msword',
					'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
				],
				'spreadsheet'  => [
					'application/vnd.ms-excel',
					'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
				],
				'presentation' => [
					'application/vnd.ms-powerpoint',
					'application/vnd.openxmlformats-officedocument.presentationml.presentation'
				],
				'archive'      => [ 'application/zip', 'application/x-rar-compressed', 'application/x-7z-compressed' ],
				'code'         => [
					'text/html',
					'text/css',
					'application/javascript',
					'application/json',
					'application/xml'
				],
			];
		}
	}
endif;