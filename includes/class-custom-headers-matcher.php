<?php
/**
 * URI and context matching.
 *
 * @package Custom_Headers
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Custom_Headers_Matcher {

	public static function normalize_path( string $path ): string {
		$path = trim( $path );
		if ( '' === $path ) {
			return '/';
		}

		$parsed = wp_parse_url( $path, PHP_URL_PATH );
		if ( null !== $parsed && false !== $parsed && '' !== $parsed ) {
			$path = $parsed;
		}

		$path = '/' . trim( (string) $path, '/' );
		if ( '/' === $path ) {
			return '/';
		}

		return rtrim( $path, '/' );
	}

	public static function has_wildcard( string $pattern ): bool {
		return false !== strpos( $pattern, '*' );
	}

	public static function pattern_to_regex( string $pattern ): string {
		$pattern = trim( $pattern );
		if ( '' === $pattern ) {
			return '#^.*$#i';
		}
		if ( '/' !== substr( $pattern, 0, 1 ) ) {
			$pattern = '/' . $pattern;
		}

		if ( ! self::has_wildcard( $pattern ) ) {
			return '#^' . preg_quote( self::normalize_path( $pattern ), '#' ) . '$#i';
		}

		$pattern = preg_replace( '#/+$#', '', $pattern );
		if ( ! is_string( $pattern ) || '' === $pattern ) {
			$pattern = '/';
		}
		if ( '*' !== substr( $pattern, -1 ) && '/' === substr( $pattern, -1 ) ) {
			$pattern = rtrim( $pattern, '/' );
		}

		$regex = preg_quote( $pattern, '#' );
		$regex = str_replace( '\*', '.*', $regex );
		return '#^' . $regex . '$#i';
	}

	public static function uri_matches( string $request_uri, string $pattern ): bool {
		if ( '' === trim( $pattern ) ) {
			return true;
		}
		return (bool) preg_match( self::pattern_to_regex( $pattern ), self::normalize_path( $request_uri ) );
	}

	public static function context_matches( string $context ): bool {
		switch ( $context ) {
			case Custom_Headers_Settings::CONTEXT_ADMIN:
				return is_admin();
			case Custom_Headers_Settings::CONTEXT_LOGIN:
				return isset( $GLOBALS['pagenow'] ) && 'wp-login.php' === $GLOBALS['pagenow'];
			case Custom_Headers_Settings::CONTEXT_FRONT:
				if ( is_admin() ) {
					return false;
				}
				return ! ( isset( $GLOBALS['pagenow'] ) && 'wp-login.php' === $GLOBALS['pagenow'] );
			case Custom_Headers_Settings::CONTEXT_ALL:
			default:
				return true;
		}
	}

	/**
	 * @param array<string, mixed> $rule Rule with context, uri, and headers keys.
	 */
	public static function rule_matches( string $request_uri, array $rule ): bool {
		$context = isset( $rule['context'] ) ? (string) $rule['context'] : Custom_Headers_Settings::CONTEXT_ALL;
		if ( ! self::context_matches( $context ) ) {
			return false;
		}

		$pattern = isset( $rule['uri'] ) ? (string) $rule['uri'] : '';
		return self::uri_matches( $request_uri, $pattern );
	}
}
