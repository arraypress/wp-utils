<?php
/**
 * File Utilities
 *
 * This class provides utility methods for working with files in WordPress. It includes functions
 * for retrieving file types, checking MIME types, managing files (like reading, writing, deleting),
 * and verifying file permissions and existence. It also supports conversions for file sizes
 * and human-readable formatting of bytes.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.2.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Common;

if ( ! class_exists( 'File' ) ) :

	/**
	 * File utility class for WordPress.
	 *
	 * This class provides a range of utility methods for working with files in WordPress. It can
	 * check file types, read/write contents, manage permissions, and handle common file system operations.
	 */
	class File {

		/**
		 * Get the MIME type for a given file path.
		 *
		 * @param string $file_path The path to the file.
		 *
		 * @return string|false The MIME type of the file, or false on failure.
		 */
		public static function get_mime_type( string $file_path ) {
			return MIME::get_file_type( $file_path );
		}

		/**
		 * Get the file type based on the file path.
		 *
		 * @param string $file_path The path to the file.
		 *
		 * @return string The general file type.
		 */
		public static function get_file_type( string $file_path ): string {
			$mime_type = self::get_mime_type( $file_path );

			return MIME::get_general_type( $mime_type );
		}

		/**
		 * Check if a file is an image.
		 *
		 * @param string $file_path The path to the file.
		 *
		 * @return bool True if the file is an image, false otherwise.
		 */
		public static function is_image( string $file_path ): bool {
			$mime_type = self::get_mime_type( $file_path );

			return MIME::is_type( $mime_type, 'image' );
		}

		/**
		 * Check if a file is an audio file.
		 *
		 * @param string $file_path The path to the file.
		 *
		 * @return bool True if the file is an audio file, false otherwise.
		 */
		public static function is_audio( string $file_path ): bool {
			$mime_type = self::get_mime_type( $file_path );

			return MIME::is_type( $mime_type, 'audio' );
		}

		/**
		 * Check if a file is a video file.
		 *
		 * @param string $file_path The path to the file.
		 *
		 * @return bool True if the file is a video file, false otherwise.
		 */
		public static function is_video( string $file_path ): bool {
			$mime_type = self::get_mime_type( $file_path );

			return MIME::is_type( $mime_type, 'video' );
		}

		/**
		 * Check if a file is a document (PDF, Word, etc.).
		 *
		 * @param string $file_path The path to the file.
		 *
		 * @return bool True if the file is a document, false otherwise.
		 */
		public static function is_document( string $file_path ): bool {
			$mime_type = self::get_mime_type( $file_path );

			return MIME::is_type( $mime_type, 'document' );
		}

		/**
		 * Check if a file is an archive (ZIP, RAR, etc.).
		 *
		 * @param string $file_path The path to the file.
		 *
		 * @return bool True if the file is an archive, false otherwise.
		 */
		public static function is_archive( string $file_path ): bool {
			$mime_type = self::get_mime_type( $file_path );

			return MIME::is_type( $mime_type, 'archive' );
		}

		/**
		 * Check if a file is a spreadsheet (Excel, etc.).
		 *
		 * @param string $file_path The path to the file.
		 *
		 * @return bool True if the file is a spreadsheet, false otherwise.
		 */
		public static function is_spreadsheet( string $file_path ): bool {
			$mime_type = self::get_mime_type( $file_path );

			return MIME::is_type( $mime_type, 'spreadsheet' );
		}

		/**
		 * Check if a file is a presentation (PowerPoint, etc.).
		 *
		 * @param string $file_path The path to the file.
		 *
		 * @return bool True if the file is a presentation, false otherwise.
		 */
		public static function is_presentation( string $file_path ): bool {
			$mime_type = self::get_mime_type( $file_path );

			return MIME::is_type( $mime_type, 'presentation' );
		}

		/**
		 * Check if a file is a text file.
		 *
		 * @param string $file_path The path to the file.
		 *
		 * @return bool True if the file is a text file, false otherwise.
		 */
		public static function is_text( string $file_path ): bool {
			$mime_type = self::get_mime_type( $file_path );

			return strpos( $mime_type, 'text/' ) === 0;
		}

		/**
		 * Check if a file is a code file (HTML, CSS, JavaScript, etc.).
		 *
		 * @param string $file_path The path to the file.
		 *
		 * @return bool True if the file is a code file, false otherwise.
		 */
		public static function is_code( string $file_path ): bool {
			$mime_type = self::get_mime_type( $file_path );

			return MIME::is_type( $mime_type, 'code' );
		}

		/**
		 * Get the file size in a human-readable format.
		 *
		 * @param string $file_path The path to the file.
		 *
		 * @return string|null The formatted file size or null if the file doesn't exist.
		 */
		public static function get_file_size( string $file_path ): ?string {
			if ( ! file_exists( $file_path ) ) {
				return null;
			}

			$bytes = filesize( $file_path );

			return size_format( $bytes );
		}

		/**
		 * Check if a file is within a specific directory.
		 *
		 * @param string $file_path The path to the file.
		 * @param string $directory The directory to check.
		 *
		 * @return bool True if the file is within the specified directory, false otherwise.
		 */
		public static function is_in_directory( string $file_path, string $directory ): bool {
			$real_file_path = realpath( $file_path );
			$real_directory = realpath( $directory );

			if ( $real_file_path === false || $real_directory === false ) {
				return false;
			}

			return strpos( $real_file_path, $real_directory ) === 0;
		}

		/**
		 * Create a directory if it doesn't exist.
		 *
		 * @param string $file_path   The path of the directory to create.
		 * @param int    $permissions The permissions to set for the new directory.
		 * @param bool   $recursive   Whether to create parent directories if they don't exist.
		 *
		 * @return bool True if the directory was created or already exists, false otherwise.
		 */
		public static function create_directory( string $file_path, int $permissions = 0755, bool $recursive = true ): bool {
			if ( file_exists( $file_path ) && is_dir( $file_path ) ) {
				return true;
			}

			return mkdir( $file_path, $permissions, $recursive );
		}

		/**
		 * Safely delete a file.
		 *
		 * @param string $file_path The path to the file to delete.
		 *
		 * @return bool True if the file was deleted successfully, false otherwise.
		 */
		public static function delete_file( string $file_path ): bool {
			if ( ! file_exists( $file_path ) ) {
				return false;
			}

			return unlink( $file_path );
		}

		/**
		 * Get the contents of a file.
		 *
		 * @param string $file_path The path to the file.
		 *
		 * @return string|null The contents of the file, or null if the file doesn't exist.
		 */
		public static function get_contents( string $file_path ): ?string {
			if ( ! file_exists( $file_path ) ) {
				return null;
			}

			return file_get_contents( $file_path );
		}

		/**
		 * Write contents to a file.
		 *
		 * @param string $file_path The path to the file.
		 * @param string $contents  The contents to write to the file.
		 *
		 * @return bool True if the contents were written successfully, false otherwise.
		 */
		public static function put_contents( string $file_path, string $contents ): bool {
			return file_put_contents( $file_path, $contents ) !== false;
		}

		/**
		 * Check if a file is writable.
		 *
		 * @param string $file_path The path to the file.
		 *
		 * @return bool True if the file is writable, false otherwise.
		 */
		public static function is_writable( string $file_path ): bool {
			return is_writable( $file_path );
		}

		/**
		 * Check if a file is readable.
		 *
		 * @param string $file_path The path to the file.
		 *
		 * @return bool True if the file is readable, false otherwise.
		 */
		public static function is_readable( string $file_path ): bool {
			return is_readable( $file_path );
		}

		/**
		 * Get the last modified time of a file.
		 *
		 * @param string $file_path The path to the file.
		 *
		 * @return int|null The last modified time as a Unix timestamp, or null if the file doesn't exist.
		 */
		public static function get_modified_time( string $file_path ): ?int {
			if ( ! file_exists( $file_path ) ) {
				return null;
			}

			return filemtime( $file_path );
		}

		/**
		 * Copy a file.
		 *
		 * @param string $source      The source file path.
		 * @param string $destination The destination file path.
		 *
		 * @return bool True if the file was copied successfully, false otherwise.
		 */
		public static function copy( string $source, string $destination ): bool {
			return copy( $source, $destination );
		}

		/**
		 * Move a file.
		 *
		 * @param string $source      The source file path.
		 * @param string $destination The destination file path.
		 *
		 * @return bool True if the file was moved successfully, false otherwise.
		 */
		public static function move( string $source, string $destination ): bool {
			return rename( $source, $destination );
		}

		/**
		 * Get the file extension.
		 *
		 * @param string $file_path The path to the file.
		 *
		 * @return string The file extension.
		 */
		public static function get_extension( string $file_path ): string {
			return pathinfo( $file_path, PATHINFO_EXTENSION );
		}

		/**
		 * Check if a file exists and is a regular file.
		 *
		 * @param string $file_path The path to the file.
		 *
		 * @return bool True if the file exists and is a regular file, false otherwise.
		 */
		public static function exists( string $file_path ): bool {
			return file_exists( $file_path ) && is_file( $file_path );
		}

		/**
		 * Convert bytes to a human-readable format.
		 *
		 * @param int|string $bytes     The number of bytes.
		 * @param int        $precision The number of decimal places (default 2).
		 *
		 * @return string The formatted string.
		 */
		public static function bytes_to_human( $bytes, int $precision = 2 ): string {
			return size_format( max( (int) $bytes, 0 ), $precision );
		}

	}
endif;