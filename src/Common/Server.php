<?php
/**
 * Server Utilities for WordPress
 *
 * This class provides utility functions for retrieving server-related information
 * and checking various server configurations. It includes methods for retrieving
 * PHP version, MySQL version, web server software, and more, offering an easy way to
 * gather essential details about the server environment.
 *
 * @package       ArrayPress/WP-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       0.5.0
 */

declare( strict_types=1 );

namespace ArrayPress\Utils\Common;

class Server {

	/**
	 * Cached server software information.
	 *
	 * @var string|null
	 */
	private static ?string $server_software = null;

	/**
	 * Get the server software information.
	 *
	 * @return string The server software information or an empty string if not available.
	 */
	private static function get_server_software(): string {
		if ( self::$server_software === null ) {
			self::$server_software = isset( $_SERVER['SERVER_SOFTWARE'] )
				? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) )
				: '';
		}

		return self::$server_software;
	}

	/**
	 * Checks if the server is running on Apache.
	 *
	 * @return boolean Whether or not it is running on Apache.
	 */
	public static function is_apache(): bool {
		return stripos( self::get_server_software(), 'apache' ) !== false;
	}

	/**
	 * Checks if the server is running on Nginx.
	 *
	 * @return boolean Whether or not it is running on Nginx.
	 */
	public static function is_nginx(): bool {
		$server = self::get_server_software();

		return stripos( $server, 'nginx' ) !== false || stripos( $server, 'flywheel' ) !== false;
	}

	/**
	 * Checks if the server is running on LiteSpeed.
	 *
	 * @return boolean Whether or not it is running on LiteSpeed.
	 */
	public static function is_litespeed(): bool {
		return stripos( self::get_server_software(), 'litespeed' ) !== false;
	}

	/**
	 * Checks if the server is running on IIS (Internet Information Services).
	 *
	 * @return boolean Whether or not it is running on IIS.
	 */
	public static function is_iis(): bool {
		return stripos( self::get_server_software(), 'microsoft-iis' ) !== false;
	}

	/**
	 * Get the server type and full software information.
	 *
	 * @return array An array containing 'type' and 'software' keys.
	 */
	public static function get_server_info(): array {
		$software = self::get_server_software();
		$type     = 'Unknown';

		if ( self::is_apache() ) {
			$type = 'Apache';
		} elseif ( self::is_nginx() ) {
			$type = 'Nginx';
		} elseif ( self::is_litespeed() ) {
			$type = 'LiteSpeed';
		} elseif ( self::is_iis() ) {
			$type = 'IIS';
		}

		return [
			'type'     => $type,
			'software' => $software
		];
	}

	/**
	 * Get the server hostname.
	 *
	 * @return string|null The server hostname or null if not available.
	 */
	public static function get_server_hostname(): ?string {
		return gethostname() ?: null;
	}

	/**
	 * Get the PHP version running on the server.
	 *
	 * @return string The PHP version.
	 */
	public static function get_php_version(): string {
		return phpversion();
	}

	/**
	 * Check if the server is running on Windows.
	 *
	 * @return boolean Whether the server is running on Windows.
	 */
	public static function is_windows(): bool {
		return strtoupper( substr( PHP_OS, 0, 3 ) ) === 'WIN';
	}

	/**
	 * Get the server's operating system information.
	 *
	 * @return string The server's operating system information.
	 */
	public static function get_os_info(): string {
		return php_uname();
	}

	/**
	 * Checks for localhost environment.
	 *
	 * @return bool
	 */
	public static function is_local_host(): bool {
		$is_local = false;

		$domains_to_check = array_unique(
			array(
				'siteurl' => wp_parse_url( get_site_url(), PHP_URL_HOST ),
				'homeurl' => wp_parse_url( get_home_url(), PHP_URL_HOST ),
			)
		);

		$forbidden_domains = array(
			'wordpress.com',
			'localhost',
			'localhost.localdomain',
			'127.0.0.1',
			'::1',
			'local.wordpress.test',         // VVV pattern.
			'local.wordpress-trunk.test',   // VVV pattern.
			'src.wordpress-develop.test',   // VVV pattern.
			'build.wordpress-develop.test', // VVV pattern.
		);

		foreach ( $domains_to_check as $domain ) {
			// If it's empty, just fail out.
			if ( ! $domain ) {
				$is_local = true;
				break;
			}

			// None of the explicit localhosts.
			if ( in_array( $domain, $forbidden_domains, true ) ) {
				$is_local = true;
				break;
			}

			// No .test or .local domains.
			if ( preg_match( '#\.(test|local)$#i', $domain ) ) {
				$is_local = true;
				break;
			}
		}

		return $is_local;
	}

	/**
	 * Checks if the current PHP environment is an Apache web server that supports .htaccess files.
	 *
	 * @return bool True if running on an Apache web server with .htaccess support, false otherwise.
	 */
	public static function has_apache_mod_rewrite(): bool {
		if ( function_exists( 'apache_get_modules' ) ) {
			return in_array( 'mod_rewrite', apache_get_modules(), true );
		}

		return false;
	}

	/**
	 * Checks if the current PHP environment has a specific extension loaded.
	 *
	 * @param string $extension The name of the PHP extension to check.
	 *
	 * @return bool True if the extension is loaded, false otherwise.
	 */
	public static function is_php_extension_loaded( string $extension ): bool {
		return extension_loaded( $extension );
	}

	/**
	 * Retrieves the current memory limit set in PHP configuration.
	 *
	 * @return string The memory limit.
	 */
	public static function get_memory_limit(): string {
		return ini_get( 'memory_limit' );
	}

	/**
	 * Retrieves the maximum execution time set in PHP configuration.
	 *
	 * @return int The maximum execution time in seconds.
	 */
	public static function get_max_execution_time(): int {
		return (int) ini_get( 'max_execution_time' );
	}

	/**
	 * Checks if a given function is available and enabled in the current PHP environment.
	 *
	 * @param string $function       The name of the function to check.
	 * @param bool   $check_disabled Whether to also check if the function is disabled.
	 *
	 * @return bool True if the function is available and enabled, false otherwise.
	 */
	public static function is_function_available( string $function, bool $check_disabled = true ): bool {
		if ( ! function_exists( $function ) ) {
			return false;
		}

		if ( $check_disabled ) {
			$disabled_functions = explode( ',', ini_get( 'disable_functions' ) );

			return ! in_array( $function, $disabled_functions );
		}

		return true;
	}

	/**
	 * Checks if a given PHP configuration directive is enabled.
	 *
	 * @param string $directive The name of the directive to check.
	 *
	 * @return bool True if the directive is enabled, false otherwise.
	 */
	public static function is_php_directive_enabled( string $directive ): bool {
		return filter_var( ini_get( $directive ), FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Retrieves the upload maximum filesize set in PHP configuration.
	 *
	 * @return string The upload maximum filesize.
	 */
	public static function get_upload_max_filesize(): string {
		return ini_get( 'upload_max_filesize' );
	}

	/**
	 * Retrieves the post maximum size set in PHP configuration.
	 *
	 * @return string The post maximum size.
	 */
	public static function get_post_max_size(): string {
		return ini_get( 'post_max_size' );
	}

	/**
	 * Retrieves the server's IP address.
	 *
	 * @return string The server's IP address.
	 */
	public static function get_server_ip(): string {
		return $_SERVER['SERVER_ADDR'] ?? gethostbyname( gethostname() );
	}

	/**
	 * Retrieves the server's current load average.
	 *
	 * @return array|null An array containing load averages (1 min, 5 min, 15 min) or null if not available.
	 */
	public static function get_load_average(): ?array {
		if ( function_exists( 'sys_getloadavg' ) ) {
			return sys_getloadavg();
		}

		return null;
	}

	/**
	 * Retrieves the server's total and free disk space.
	 *
	 * @param string $directory The directory to check. Default is the WordPress root directory.
	 *
	 * @return array An array containing total and free disk space in bytes.
	 */
	public static function get_disk_space( string $directory = ABSPATH ): array {
		return [
			'total' => disk_total_space( $directory ),
			'free'  => disk_free_space( $directory )
		];
	}

	/**
	 * Checks if the server supports SSL/TLS connections.
	 *
	 * @return bool True if SSL is supported, false otherwise.
	 */
	public static function is_ssl_supported(): bool {
		return extension_loaded( 'openssl' );
	}

	/**
	 * Retrieves the server's current time.
	 *
	 * @param string $format The format of the time. Default is 'Y-m-d H:i:s'.
	 *
	 * @return string The formatted server time.
	 */
	public static function get_server_time( string $format = 'Y-m-d H:i:s' ): string {
		return date( $format );
	}

	/**
	 * Retrieves the server's temporary directory path.
	 *
	 * @return string The path to the temporary directory.
	 */
	public static function get_temp_dir(): string {
		return sys_get_temp_dir();
	}

	/**
	 * Checks if the server has cURL installed.
	 *
	 * @return bool True if cURL is installed, false otherwise.
	 */
	public static function has_curl(): bool {
		return function_exists( 'curl_version' );
	}

	/**
	 * Retrieves information about loaded PHP extensions.
	 *
	 * @return array An array of loaded PHP extensions.
	 */
	public static function get_loaded_extensions(): array {
		return get_loaded_extensions();
	}

	/**
	 * Converts a string representation of file size to bytes.
	 *
	 * @param string $size The size string (e.g., '2M', '1G').
	 *
	 * @return int The size in bytes.
	 */
	public static function convert_to_bytes( string $size ): int {
		$unit  = strtolower( substr( $size, - 1 ) );
		$value = intval( substr( $size, 0, - 1 ) );

		switch ( $unit ) {
			case 'g':
				$value *= 1024;
				break;
			case 'm':
				$value *= 1024;
				break;
			case 'k':
				$value *= 1024;
		}

		return $value;
	}

	/**
	 * Checks if the server meets the minimum PHP version requirement.
	 *
	 * @param string $required_version The minimum required PHP version.
	 *
	 * @return bool True if the server meets the requirement, false otherwise.
	 */
	public static function meets_php_requirement( string $required_version ): bool {
		return version_compare( PHP_VERSION, $required_version, '>=' );
	}

	/**
	 * Checks if the server meets the minimum MySQL version requirement.
	 *
	 * @param string $required_version The minimum required MySQL version.
	 *
	 * @return bool True if the server meets the requirement, false otherwise.
	 */
	public static function meets_mysql_requirement( string $required_version ): bool {
		global $wpdb;

		if ( method_exists( $wpdb, 'db_version' ) ) {
			$mysql_version = $wpdb->db_version();

			return version_compare( $mysql_version, $required_version, '>=' );
		}

		return false;
	}

	/**
	 * Checks if the server is running in a Docker container.
	 *
	 * @return bool True if running in a Docker container, false otherwise.
	 */
	public static function is_docker(): bool {
		return file_exists( '/.dockerenv' );
	}

}