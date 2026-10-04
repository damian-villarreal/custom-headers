<?php
/**
 * Sends HTTP headers on send_headers.
 *
 * @package Custom_Headers
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Custom_Headers_Sender {

	public function init(): void {
		add_action( 'send_headers', array( $this, 'send_headers' ) );
	}

	public function send_headers(): void {
		if ( headers_sent() ) {
			return;
		}

		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

		foreach ( Custom_Headers_Settings::get_rules() as $rule ) {
			if ( ! is_array( $rule ) || ! Custom_Headers_Matcher::rule_matches( $request_uri, $rule ) ) {
				continue;
			}
			$this->send_headers_list( Custom_Headers_Settings::get_rule_headers( $rule ) );
		}
	}

	/**
	 * @param array<int, array<string, string>> $headers List of { name, value } pairs.
	 */
	private function send_headers_list( array $headers ): void {
		foreach ( $headers as $header ) {
			if ( ! isset( $header['name'], $header['value'] ) ) {
				continue;
			}
			header( $header['name'] . ': ' . $header['value'] );
		}
	}
}
