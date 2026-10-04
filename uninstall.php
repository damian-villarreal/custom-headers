<?php
/**
 * Cleanup on plugin uninstall.
 *
 * @package Custom_Headers
 */

declare(strict_types=1);

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'custom_headers_settings' );
