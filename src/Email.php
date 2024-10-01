<?php
/**
 * Email Utility Class for WordPress
 *
 * This class provides utility methods for handling and manipulating email addresses,
 * including validation, anonymization, domain extraction, and generation of test emails.
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
 * Check if the class `Email` is defined, and if not, define it.
 */
if ( ! class_exists( 'Email' ) ) :

	/**
	 * Email Utility Class
	 */
	class Email {

		/**
		 * Common email provider domains
		 *
		 * @var array
		 */
		private static array $common_domains = [
			'gmail.com',
			'yahoo.com',
			'hotmail.com',
			'outlook.com',
			'aol.com',
			'icloud.com',
			'protonmail.com',
			'mail.com',
			'zoho.com',
			'live.com',
			'yandex.com',
			'gmx.com',
			'fastmail.com',
			'me.com',
			'tutanota.com',
			'verizon.net',
			'qq.com',
			'126.com',
			'163.com',
			'sina.com'
		];

		/**
		 * Authoritative email domains
		 *
		 * @var array
		 */
		private static array $authority_domains = [
			'gmail.com',
			'icloud.com',
			'outlook.com',
			'hotmail.com',
			'yahoo.com'
		];

		/**
		 * List of common Top-Level Domains (TLDs)
		 *
		 * @var array
		 */
		private static array $common_tlds = [
			'com',
			'org',
			'net',
			'edu',
			'gov',
			'mil',
			'io',
			'co',
			'info',
			'biz',
			'eu',
			'de',
			'uk',
			'fr',
			'it',
			'nl',
			'es',
			'ca',
			'au',
			'jp',
			'ru',
			'ch',
			'se',
			'no',
			'dk',
			'fi',
			'pl',
			'br',
			'in',
			'mx',
			'kr',
			'cn',
			'tw',
			'sg',
			'nz',
			'tech',
			'app',
			'dev',
			'online',
			'store',
			'shop',
			'blog',
			'cloud',
			'me',
			'us',
			'tv',
			'ai'
		];

		/**
		 * List of suspicious keywords often found in spam or fraudulent emails
		 *
		 * @var array
		 */
		private static array $suspicious_keywords = [
			'admin',
			'info',
			'paypal',
			'security',
			'update',
			'account',
			'verify',
			'bank',
			'secure',
			'login',
			'password',
			'credit',
			'billing',
			'support',
			'service',
			'customer',
			'help',
			'assistance',
			'team',
			'staff',
			'official',
			'important',
			'urgent',
			'alert',
			'notification',
			'confirm',
			'validation',
			'authenticate',
			'restore',
			'recover',
			'unlock',
			'upgrade',
			'suspended',
			'limited',
			'unusual',
			'activity',
			'verify',
			'authorize',
			'unauthorised',
			'fraud',
			'suspicious',
			'risk',
			'security',
			'protect',
			'safeguard',
			'warning',
			'caution',
			'attention',
			'immediate',
			'action',
			'required',
			'respond',
			'click',
			'link',
			'attachment'
		];

		/**
		 * Common first names for generating random names.
		 *
		 * @var array
		 */
		private static array $first_names = [
			'John',
			'Jane',
			'Michael',
			'Emily',
			'David',
			'Sarah',
			'James',
			'Emma',
			'William',
			'Olivia'
		];

		/**
		 * Common last names for generating random names.
		 *
		 * @var array
		 */
		private static array $last_names = [
			'Smith',
			'Johnson',
			'Williams',
			'Brown',
			'Jones',
			'Garcia',
			'Miller',
			'Davis',
			'Rodriguez',
			'Martinez'
		];

		/**
		 * Check if an email address matches any in a given list of emails or domains.
		 *
		 * This method checks whether the provided email address matches any in the list
		 * of emails, which may include full addresses, domain blocks, or TLD blocks.
		 *
		 * @param string $email  The email address to check.
		 * @param array  $emails List of allowed email addresses, domains, or TLDs.
		 *
		 * @return bool Whether the email address matches any in the allowed list.
		 */
		public static function matches_allowed_pattern( string $email, array $emails ): bool {
			$email = strtolower( trim( $email ) );
			if ( empty( $email ) ) {
				return false;
			}

			if ( empty( $emails ) ) {
				return false;
			}

			foreach ( $emails as $allowed_item ) {
				$allowed_item = strtolower( trim( $allowed_item ) );

				if ( is_email( $allowed_item ) ) {
					// Complete email address match
					if ( $allowed_item === $email ) {
						return true;
					}
				} elseif ( str_starts_with( $allowed_item, '.' ) ) {
					// TLD block match
					if ( str_ends_with( $email, $allowed_item ) ) {
						return true;
					}
				} else {
					// Domain block match
					if ( str_contains( $email, $allowed_item ) ) {
						return true;
					}
				}
			}

			return false;
		}

		/**
		 * Validate an email address.
		 *
		 * @param string $email The email address to validate.
		 *
		 * @return bool Whether the email is valid.
		 */
		public static function is_valid( string $email ): bool {
			return is_email( $email ) !== false;
		}

		/**
		 * Get the domain part of an email address.
		 *
		 * @param string $email The email address.
		 *
		 * @return string|null The domain part of the email or null if invalid.
		 */
		public static function get_domain( string $email ): ?string {
			if ( ! self::is_valid( $email ) ) {
				return null;
			}

			return substr( strrchr( $email, "@" ), 1 );
		}

		/**
		 * Anonymize an email address.
		 *
		 * @param string $email Email address to anonymize.
		 *
		 * @return string Anonymized email address.
		 */
		public static function anonymize( string $email ): string {
			if ( ! self::is_valid( $email ) ) {
				return '';
			}

			list( $name, $domain ) = explode( '@', $email );
			$domain_parts = explode( '.', $domain );

			$anonymized_name   = substr( $name, 0, 2 ) . str_repeat( '*', max( strlen( $name ) - 2, 3 ) );
			$anonymized_domain = substr( $domain_parts[0], 0, 2 ) . str_repeat( '*', max( strlen( $domain_parts[0] ) - 2, 3 ) );

			return $anonymized_name . '@' . $anonymized_domain . '.' . end( $domain_parts );
		}

		/**
		 * Check if an email address is anonymized.
		 *
		 * @param string $email Email address to check.
		 *
		 * @return bool Whether the email is anonymized.
		 */
		public static function is_anonymized( string $email ): bool {
			return $email === 'deleted@site.invalid' || strpos( $email, '***' ) !== false;
		}

		/**
		 * Generate a random email address.
		 *
		 * @param string|null $name   Optional. The name part of the email. If null, a random name will be generated.
		 * @param string|null $domain Optional. The domain part of the email. If null, a random domain will be used.
		 *
		 * @return string A randomly generated email address.
		 */
		public static function generate_random( string $name = null, string $domain = null ): string {
			$name   = $name ?? self::generate_random_name( true );
			$domain = $domain ?? self::$common_domains[ array_rand( self::$common_domains ) ];

			$email = strtolower( str_replace( ' ', '.', $name ) ) . '@' . $domain;

			return sanitize_email( $email );
		}

		/**
		 * Generate a random name.
		 *
		 * @param bool $full Whether to generate a full name (first and last) or just a first name.
		 *
		 * @return string A randomly generated name.
		 */
		public static function generate_random_name( bool $full = false ): string {
			$first_name = self::$first_names[ array_rand( self::$first_names ) ];
			if ( ! $full ) {
				return $first_name;
			}
			$last_name = self::$last_names[ array_rand( self::$last_names ) ];

			return $first_name . ' ' . $last_name;
		}

		/**
		 * Mask an email address for display.
		 *
		 * @param string $email      The email address to mask.
		 * @param int    $show_first Number of characters to show at the start of the local part.
		 * @param int    $show_last  Number of characters to show at the end of the local part.
		 *
		 * @return string The masked email address.
		 */
		public static function mask_for_display( string $email, int $show_first = 1, int $show_last = 1 ): string {
			if ( ! self::is_valid( $email ) ) {
				return '';
			}

			list( $name, $domain ) = explode( '@', $email );
			$name_length = strlen( $name );

			if ( $show_first + $show_last >= $name_length ) {
				return $email; // If showing all characters, return the original email
			}

			$masked_length = $name_length - $show_first - $show_last;
			$masked_name   = substr( $name, 0, $show_first ) . str_repeat( '*', $masked_length ) . substr( $name, - $show_last );

			return $masked_name . '@' . $domain;
		}

		/**
		 * Extract all email addresses from a given text.
		 *
		 * @param string $text The text to extract email addresses from.
		 *
		 * @return array An array of extracted email addresses.
		 */
		public static function extract_from_text( string $text ): array {
			return Extract::emails( $text );
		}

		/**
		 * Convert an email address to its ASCII representation (Punycode).
		 *
		 * @param string $email The email address to convert.
		 *
		 * @return string The email address in ASCII representation.
		 */
		public static function to_ascii( string $email ): string {
			if ( ! self::is_valid( $email ) ) {
				return '';
			}

			list( $local, $domain ) = explode( '@', $email );
			if ( function_exists( 'idn_to_ascii' ) ) {
				$ascii_domain = idn_to_ascii( $domain, IDNA_NONTRANSITIONAL_TO_ASCII, INTL_IDNA_VARIANT_UTS46 );
				if ( $ascii_domain !== false ) {
					return $local . '@' . $ascii_domain;
				}
			}

			// If idn_to_ascii is not available or fails, return the original email
			return $email;
		}

		/**
		 * Normalize an email address (convert to lowercase and trim).
		 *
		 * @param string $email The email address to normalize.
		 *
		 * @return string The normalized email address.
		 */
		public static function normalize( string $email ): string {
			return strtolower( trim( $email ) );
		}

		/**
		 * Check if an email address is subaddressed (contains a plus sign).
		 *
		 * @param string $email The email address to check.
		 *
		 * @return bool Whether the email is subaddressed.
		 */
		public static function is_subaddressed( string $email ): bool {
			if ( ! self::is_valid( $email ) ) {
				return false;
			}

			list( $local, $domain ) = explode( '@', $email );

			return strpos( $local, '+' ) !== false;
		}

		/**
		 * Extract the base email address from a subaddressed email.
		 *
		 * @param string $email The subaddressed email.
		 *
		 * @return string The base email address without subaddressing.
		 */
		public static function get_base_address( string $email ): string {
			if ( ! self::is_valid( $email ) ) {
				return $email;
			}

			list( $local, $domain ) = explode( '@', $email );
			$base_local = explode( '+', $local )[0];

			return $base_local . '@' . $domain;
		}

		/**
		 * Extract the subaddress (tag) from a subaddressed email.
		 *
		 * @param string $email The subaddressed email.
		 *
		 * @return string|null The subaddress (tag) or null if not subaddressed.
		 */
		public static function get_subaddress( string $email ): ?string {
			if ( ! self::is_subaddressed( $email ) ) {
				return null;
			}

			list( $local, $domain ) = explode( '@', $email );
			$parts = explode( '+', $local );

			return $parts[1] ?? null;
		}

		/**
		 * Create a subaddressed email by adding a tag to a base email address.
		 *
		 * @param string $email The base email address.
		 * @param string $tag   The tag to add.
		 *
		 * @return string The subaddressed email.
		 */
		public static function add_subaddress( string $email, string $tag ): string {
			if ( ! self::is_valid( $email ) ) {
				return $email;
			}

			list( $local, $domain ) = explode( '@', $email );

			return $local . '+' . $tag . '@' . $domain;
		}

		/**
		 * Compare two email addresses, ignoring subaddressing.
		 *
		 * @param string $email1 The first email address.
		 * @param string $email2 The second email address.
		 *
		 * @return bool Whether the base email addresses are the same.
		 */
		public static function compare_ignoring_subaddress( string $email1, string $email2 ): bool {
			return self::get_base_address( $email1 ) === self::get_base_address( $email2 );
		}

		/**
		 * Check if the domain of an email has valid MX records.
		 *
		 * @param string $email The email address to check.
		 *
		 * @return bool True if valid MX records exist, false otherwise.
		 */
		public static function has_valid_mx( string $email ): bool {
			if ( ! self::is_valid( $email ) ) {
				return false;
			}

			$domain = self::get_domain( $email );

			return checkdnsrr( $domain, 'MX' );
		}

		/**
		 * Check if an email is from a common provider.
		 *
		 * @param string $email The email address to check.
		 *
		 * @return bool True if the email is from a common provider, false otherwise.
		 */
		public static function is_common_provider( string $email ): bool {
			$domain = self::get_domain( $email );

			return in_array( $domain, self::$common_domains );
		}

		/**
		 * Check if an email is from an authoritative domain.
		 *
		 * @param string $email The email address to check.
		 *
		 * @return bool True if the email is from an authoritative domain, false otherwise.
		 */
		public static function is_authority_provider( string $email ): bool {
			$domain = self::get_domain( $email );

			return in_array( $domain, self::$authority_domains );
		}

		/**
		 * Calculate a spam score for an email address.
		 *
		 * @param string $email    The email address to check.
		 * @param bool   $check_mx Whether to perform MX record check. Default is true.
		 *
		 * @return int A score from 0 to 100, with higher scores indicating higher spam likelihood.
		 */
		public static function get_spam_score( string $email, bool $check_mx = false ): int {
			if ( ! self::is_valid( $email ) ) {
				return 100; // Invalid emails get the highest score
			}

			$score = 0;
			list( $local, $domain ) = explode( '@', $email );

			// Check for excessive numbers in local part
			$number_count = preg_match_all( '/\d/', $local );
			if ( $number_count > 3 ) {
				$score += 10;
			}

			// Check for excessive dots in local part
			$dot_count = substr_count( $local, '.' );
			if ( $dot_count > 2 ) {
				$score += 5 + ( $dot_count * 5 ); // Base 5 points plus 5 for each dot
			}

			// Check local part length
			if ( strlen( $local ) > 20 ) {
				$score += 10;
			}

			// Check for uncommon TLDs
			$tld = substr( strrchr( $domain, '.' ), 1 );
			if ( ! in_array( $tld, self::$common_tlds ) ) {
				$score += 15;
			}

			// Check if it's from a common provider (less likely to be spam)
			if ( self::is_common_provider( $email ) ) {
				$score -= 10;
			}

			// Check for suspicious keywords in local part and domain
			$keyword_count = 0;
			foreach ( self::$suspicious_keywords as $keyword ) {
				if ( stripos( $local, $keyword ) !== false || stripos( $domain, $keyword ) !== false ) {
					$keyword_count ++;
				}
			}
			$score += min( 30, $keyword_count * 10 );  // Cap at 30

			// Additional check for multiple hyphens or underscores (often used in spam)
			if ( substr_count( $local, '-' ) > 1 || substr_count( $local, '_' ) > 1 ) {
				$score += 10;
			}

			// Additional check for long domain names (often used in phishing)
			$domain_parts = explode( '.', $domain );
			if ( strlen( $domain_parts[0] ) > 15 ) {
				$score += 10;
			}

			// Check for valid MX records if $check_mx is true
			if ( $check_mx && ! self::has_valid_mx( $email ) ) {
				$score += 25;
			}

			// Normalize score to be between 0 and 100
			return max( 0, min( 100, $score ) );
		}

	}

endif;