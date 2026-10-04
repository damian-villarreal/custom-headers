<?php
/**
 * Admin menu, rules UI, and help page.
 *
 * @package Custom_Headers
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Custom_Headers_Admin {

	public function init(): void {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	public function add_menu(): void {
		add_menu_page(
			__( 'Custom Headers', 'custom-headers' ),
			__( 'Custom Headers', 'custom-headers' ),
			'manage_options',
			Custom_Headers_Settings::MENU_SLUG,
			array( $this, 'render_rules_page' ),
			'dashicons-admin-generic',
			81
		);

		add_submenu_page(
			Custom_Headers_Settings::MENU_SLUG,
			__( 'Rules', 'custom-headers' ),
			__( 'Rules', 'custom-headers' ),
			'manage_options',
			Custom_Headers_Settings::MENU_SLUG,
			array( $this, 'render_rules_page' )
		);

		add_submenu_page(
			Custom_Headers_Settings::MENU_SLUG,
			__( 'Help', 'custom-headers' ),
			__( 'Help', 'custom-headers' ),
			'manage_options',
			Custom_Headers_Settings::PAGE_HELP,
			array( $this, 'render_help_page' )
		);
	}

	public function register_settings(): void {
		register_setting(
			Custom_Headers_Settings::GROUP,
			Custom_Headers_Settings::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( Custom_Headers_Settings::class, 'sanitize' ),
				'default'           => array( 'rules' => array() ),
			)
		);
	}

	public function enqueue_assets( string $hook ): void {
		$allowed_hooks = array(
			'toplevel_page_' . Custom_Headers_Settings::MENU_SLUG,
			Custom_Headers_Settings::MENU_SLUG . '_page_' . Custom_Headers_Settings::PAGE_HELP,
		);
		if ( ! in_array( $hook, $allowed_hooks, true ) ) {
			return;
		}

		wp_enqueue_style(
			'custom-headers-admin',
			CUSTOM_HEADERS_URL . 'assets/css/admin.css',
			array(),
			CUSTOM_HEADERS_VERSION
		);

		if ( 'toplevel_page_' . Custom_Headers_Settings::MENU_SLUG !== $hook ) {
			return;
		}

		wp_enqueue_script(
			'custom-headers-admin',
			CUSTOM_HEADERS_URL . 'assets/js/admin.js',
			array( 'jquery', 'jquery-ui-sortable' ),
			CUSTOM_HEADERS_VERSION,
			true
		);

		wp_localize_script(
			'custom-headers-admin',
			'customHeaders',
			array(
				'strings' => array(
					'validationRules' => __( 'Each rule must have at least one header with both a name and a value.', 'custom-headers' ),
				),
			)
		);
	}

	public function render_rules_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'custom-headers' ), 403 );
		}

		$rules = Custom_Headers_Settings::get_rules();
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php settings_errors(); ?>
			<p><?php esc_html_e( 'Define HTTP header rules by context and URI. All matching rules will send their headers.', 'custom-headers' ); ?></p>

			<form action="options.php" method="post">
				<?php settings_fields( Custom_Headers_Settings::GROUP ); ?>

				<div id="ch-rules" class="ch-rules">
					<?php foreach ( $rules as $idx => $rule ) : ?>
						<?php $this->render_rule_block( (string) $idx, is_array( $rule ) ? $rule : array() ); ?>
					<?php endforeach; ?>
				</div>

				<p class="ch-empty ch-rules-empty"<?php echo empty( $rules ) ? '' : ' hidden'; ?>>
					<?php esc_html_e( 'No rules configured.', 'custom-headers' ); ?>
				</p>

				<p class="ch-footer">
					<button type="button" class="button" id="ch-add-rule"><?php esc_html_e( '+ Add rule', 'custom-headers' ); ?></button>
				</p>

				<datalist id="ch-common-headers">
					<option value="Cache-Control"></option>
					<option value="Access-Control-Allow-Origin"></option>
					<option value="X-Frame-Options"></option>
					<option value="X-Content-Type-Options"></option>
					<option value="Referrer-Policy"></option>
					<option value="Content-Security-Policy"></option>
					<option value="Strict-Transport-Security"></option>
					<option value="Vary"></option>
				</datalist>

				<template id="tmpl-ch-rule"><?php $this->render_rule_block( '__RULE_INDEX__', array() ); ?></template>
				<template id="tmpl-ch-header-row"><?php $this->render_header_row( '__RULE_INDEX__', '__HEADER_INDEX__', array() ); ?></template>

				<?php submit_button( __( 'Save Changes', 'custom-headers' ) ); ?>
			</form>
		</div>
		<?php
	}

	public function render_help_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'custom-headers' ), 403 );
		}

		require CUSTOM_HEADERS_DIR . 'includes/views/help-page.php';
	}

	/**
	 * @param array<string, mixed> $rule Rule data.
	 */
	private function render_rule_block( string $rule_index, array $rule ): void {
		$context = isset( $rule['context'] ) ? (string) $rule['context'] : Custom_Headers_Settings::CONTEXT_ALL;
		$uri     = isset( $rule['uri'] ) ? (string) $rule['uri'] : '';
		$headers = isset( $rule['headers'] ) && is_array( $rule['headers'] ) ? $rule['headers'] : array(
			array(
				'name'  => '',
				'value' => '',
			),
		);
		if ( empty( $headers ) ) {
			$headers = array(
				array(
					'name'  => '',
					'value' => '',
				),
			);
		}

		$base = Custom_Headers_Settings::OPTION . '[rules][' . $rule_index . ']';
		?>
		<div class="ch-rule" data-rule-index="<?php echo esc_attr( $rule_index ); ?>">
			<div class="ch-rule-header">
				<div class="ch-rule-header-start">
					<button type="button" class="ch-drag-handle" title="<?php esc_attr_e( 'Drag to reorder', 'custom-headers' ); ?>" aria-label="<?php esc_attr_e( 'Drag to reorder', 'custom-headers' ); ?>">
						<span class="dashicons dashicons-menu" aria-hidden="true"></span>
					</button>
					<span class="ch-rule-badge">
						<?php esc_html_e( 'Rule', 'custom-headers' ); ?>
						<span class="ch-row-number"></span>
					</span>
				</div>
				<button type="button" class="button-link button-link-delete ch-remove-rule" aria-label="<?php esc_attr_e( 'Remove rule', 'custom-headers' ); ?>">
					<span class="dashicons dashicons-trash" aria-hidden="true"></span>
					<?php esc_html_e( 'Remove rule', 'custom-headers' ); ?>
				</button>
			</div>

			<div class="ch-rule-body">
				<div class="ch-rule-match">
					<label class="ch-field">
						<span class="ch-label"><?php esc_html_e( 'Context', 'custom-headers' ); ?></span>
						<select class="ch-context" name="<?php echo esc_attr( $base . '[context]' ); ?>">
							<?php foreach ( Custom_Headers_Settings::get_contexts() as $context_key => $context_label ) : ?>
								<option value="<?php echo esc_attr( $context_key ); ?>" <?php selected( $context, $context_key ); ?>><?php echo esc_html( $context_label ); ?></option>
							<?php endforeach; ?>
						</select>
					</label>
					<label class="ch-field ch-field-uri">
						<span class="ch-label"><?php esc_html_e( 'Request URI', 'custom-headers' ); ?></span>
						<input type="text" class="regular-text code ch-uri-pattern" name="<?php echo esc_attr( $base . '[uri]' ); ?>" value="<?php echo esc_attr( $uri ); ?>" placeholder="/wp-json/my-plugin/v1/*" aria-label="<?php esc_attr_e( 'Request URI pattern', 'custom-headers' ); ?>" />
						<span class="description"><?php esc_html_e( 'Empty = any URI in this context.', 'custom-headers' ); ?></span>
					</label>
				</div>

				<div class="ch-rule-headers">
					<div class="ch-rule-headers-head">
						<span class="ch-rule-headers-title"><?php esc_html_e( 'Headers', 'custom-headers' ); ?></span>
						<button type="button" class="button button-small ch-add-header-row"><?php esc_html_e( '+ Add header', 'custom-headers' ); ?></button>
					</div>
					<table class="widefat striped ch-header-table">
						<thead>
							<tr>
								<th scope="col"><?php esc_html_e( 'Header', 'custom-headers' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Value', 'custom-headers' ); ?></th>
								<th scope="col" class="ch-col-actions"><span class="screen-reader-text"><?php esc_html_e( 'Actions', 'custom-headers' ); ?></span></th>
							</tr>
						</thead>
						<tbody class="ch-header-rows">
							<?php foreach ( array_values( $headers ) as $header_index => $header ) : ?>
								<?php $this->render_header_row( $rule_index, (string) $header_index, is_array( $header ) ? $header : array() ); ?>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * @param array<string, mixed> $header Header row data.
	 */
	private function render_header_row( string $rule_index, string $header_index, array $header ): void {
		$base = Custom_Headers_Settings::OPTION . '[rules][' . $rule_index . '][headers][' . $header_index . ']';
		?>
		<tr class="ch-header-row">
			<td>
				<input type="text" class="regular-text code ch-header-name" name="<?php echo esc_attr( $base . '[name]' ); ?>" value="<?php echo esc_attr( isset( $header['name'] ) ? (string) $header['name'] : '' ); ?>" placeholder="X-Frame-Options" list="ch-common-headers" aria-label="<?php esc_attr_e( 'Header name', 'custom-headers' ); ?>" />
			</td>
			<td>
				<input type="text" class="large-text code ch-header-value" name="<?php echo esc_attr( $base . '[value]' ); ?>" value="<?php echo esc_attr( isset( $header['value'] ) ? (string) $header['value'] : '' ); ?>" placeholder="DENY" aria-label="<?php esc_attr_e( 'Header value', 'custom-headers' ); ?>" />
			</td>
			<td class="ch-col-actions">
				<button type="button" class="button-link button-link-delete ch-remove-header-row" aria-label="<?php esc_attr_e( 'Remove header', 'custom-headers' ); ?>"><span class="dashicons dashicons-trash"></span></button>
			</td>
		</tr>
		<?php
	}
}
