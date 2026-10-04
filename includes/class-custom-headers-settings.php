<?php
/**
 * Settings retrieval and sanitization.
 *
 * @package Custom_Headers
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Custom_Headers_Settings {

	public const OPTION    = 'custom_headers_settings';
	public const GROUP     = 'custom_headers_settings_group';
	public const MENU_SLUG = 'custom-headers';
	public const PAGE_HELP = 'custom-headers-help';

	public const CONTEXT_ALL   = 'all';
	public const CONTEXT_FRONT = 'front';
	public const CONTEXT_ADMIN = 'admin';
	public const CONTEXT_LOGIN = 'login';

	/**
	 * @var array<int, string>
	 */
	private const BLOCKED_HEADER_NAMES = array(
		'connection',
		'keep-alive',
		'proxy-authenticate',
		'proxy-authorization',
		'te',
		'trailer',
		'transfer-encoding',
		'upgrade',
		'content-length',
		'host',
	);

	/**
	 * @return array<string, mixed>
	 */
	public static function get_all(): array {
		$options = get_option( self::OPTION, array() );
		return is_array( $options ) ? $options : array();
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_rules(): array {
		$options = self::get_all();
		$rules   = isset( $options['rules'] ) && is_array( $options['rules'] ) ? $options['rules'] : array();
		return array_values( $rules );
	}

	/**
	 * @return array<string, string>
	 */
	public static function get_contexts(): array {
		return array(
			self::CONTEXT_ALL   => __( 'All requests', 'custom-headers' ),
			self::CONTEXT_FRONT => __( 'Front-end', 'custom-headers' ),
			self::CONTEXT_ADMIN => __( 'Admin', 'custom-headers' ),
			self::CONTEXT_LOGIN => __( 'Login (wp-login.php)', 'custom-headers' ),
		);
	}

	/**
	 * @param mixed $context Submitted context value.
	 */
	public static function sanitize_context( $context ): string {
		$contexts = array_keys( self::get_contexts() );
		return in_array( $context, $contexts, true ) ? (string) $context : self::CONTEXT_ALL;
	}

	/**
	 * @param mixed $pattern Submitted URI pattern.
	 */
	public static function sanitize_uri_pattern( $pattern ): string {
		$pattern = sanitize_text_field( (string) $pattern );
		return (string) preg_replace( '/\s+/', '', $pattern );
	}

	/**
	 * @param mixed $name Header name.
	 */
	public static function sanitize_header_name( $name ): string {
		$name = trim( (string) $name );
		$name = preg_replace( '/[\r\n\x00]/', '', $name );
		if ( ! is_string( $name ) || '' === $name ) {
			return '';
		}
		if ( ! preg_match( '/^[A-Za-z0-9!#$&\'*+.^_`|~-]+$/', $name ) ) {
			return '';
		}
		if ( in_array( strtolower( $name ), self::BLOCKED_HEADER_NAMES, true ) ) {
			return '';
		}
		return $name;
	}

	/**
	 * @param mixed $value Header value.
	 */
	public static function sanitize_header_value( $value ): string {
		$value = sanitize_text_field( (string) $value );
		$value = preg_replace( '/[\r\n\x00]/', '', $value );
		return is_string( $value ) ? trim( $value ) : '';
	}

	/**
	 * @param array<int, array<string, mixed>> $headers Headers as { name, value } pairs.
	 * @return array<int, array<string, string>>
	 */
	public static function sanitize_headers_list( array $headers ): array {
		$sanitized = array();
		foreach ( $headers as $header ) {
			if ( ! is_array( $header ) ) {
				continue;
			}
			$name  = self::sanitize_header_name( isset( $header['name'] ) ? $header['name'] : '' );
			$value = self::sanitize_header_value( isset( $header['value'] ) ? $header['value'] : '' );
			if ( '' === $name || '' === $value ) {
				continue;
			}
			$sanitized[] = array(
				'name'  => $name,
				'value' => $value,
			);
		}
		return $sanitized;
	}

	/**
	 * @param array<string, mixed> $rule Rule data.
	 * @return array<int, array<string, string>>
	 */
	public static function get_rule_headers( array $rule ): array {
		if ( ! isset( $rule['headers'] ) || ! is_array( $rule['headers'] ) ) {
			return array();
		}
		return self::sanitize_headers_list( $rule['headers'] );
	}

	/**
	 * @param mixed $input Form submission data.
	 * @return array<string, mixed>
	 */
	public static function sanitize( $input ): array {
		if ( ! is_array( $input ) ) {
			return self::get_all();
		}

		$sanitized = array( 'rules' => array() );

		if ( ! isset( $input['rules'] ) || ! is_array( $input['rules'] ) ) {
			return $sanitized;
		}

		foreach ( $input['rules'] as $rule ) {
			if ( ! is_array( $rule ) ) {
				continue;
			}

			$context = self::sanitize_context( isset( $rule['context'] ) ? $rule['context'] : self::CONTEXT_ALL );
			$pattern = self::sanitize_uri_pattern( isset( $rule['uri'] ) ? $rule['uri'] : '' );
			$headers = isset( $rule['headers'] ) && is_array( $rule['headers'] ) ? self::sanitize_headers_list( $rule['headers'] ) : array();

			if ( empty( $headers ) ) {
				continue;
			}

			$sanitized['rules'][] = array(
				'context' => $context,
				'uri'     => $pattern,
				'headers' => $headers,
			);
		}

		return $sanitized;
	}
}
