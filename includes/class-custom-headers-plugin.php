<?php
/**
 * Plugin bootstrap.
 *
 * @package Custom_Headers
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Custom_Headers_Plugin {

	/**
	 * @var self|null
	 */
	private static $instance = null;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function init(): void {
		add_action( 'init', array( $this, 'load_textdomain' ) );
		( new Custom_Headers_Admin() )->init();
		( new Custom_Headers_Sender() )->init();
	}

	public function load_textdomain(): void {
		load_plugin_textdomain(
			'custom-headers',
			false,
			dirname( plugin_basename( CUSTOM_HEADERS_FILE ) ) . '/languages'
		);
	}
}
