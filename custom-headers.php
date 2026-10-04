<?php
/**
 * Plugin Name: Custom Headers
 * Description: Configure HTTP headers with rules by URI and context (front-end, admin, login).
 * Version: 1.1.0
 * Author: Damian Villarreal
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Text Domain: custom-headers
 * Domain Path: /languages
 *
 * @package Custom_Headers
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CUSTOM_HEADERS_VERSION', '1.1.0' );
define( 'CUSTOM_HEADERS_FILE', __FILE__ );
define( 'CUSTOM_HEADERS_DIR', plugin_dir_path( __FILE__ ) );
define( 'CUSTOM_HEADERS_URL', plugin_dir_url( __FILE__ ) );

require_once CUSTOM_HEADERS_DIR . 'includes/class-custom-headers-settings.php';
require_once CUSTOM_HEADERS_DIR . 'includes/class-custom-headers-matcher.php';
require_once CUSTOM_HEADERS_DIR . 'includes/class-custom-headers-sender.php';
require_once CUSTOM_HEADERS_DIR . 'includes/class-custom-headers-admin.php';
require_once CUSTOM_HEADERS_DIR . 'includes/class-custom-headers-plugin.php';

Custom_Headers_Plugin::instance()->init();
