<?php
/**
 * Help screen.
 *
 * @package Custom_Headers
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$version = defined( 'CUSTOM_HEADERS_VERSION' ) ? CUSTOM_HEADERS_VERSION : '';
?>
<div class="wrap ch-help">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<p class="description">
		<?php
		printf(
			/* translators: %s: plugin version suffix, e.g. " (v1.1.0)" or empty string */
			esc_html__( 'Custom Headers documentation%s.', 'custom-headers' ),
			$version !== '' ? ' (v' . esc_html( $version ) . ')' : ''
		);
		?>
	</p>

	<nav class="ch-help-toc" aria-label="<?php esc_attr_e( 'Table of contents', 'custom-headers' ); ?>">
		<strong><?php esc_html_e( 'Table of contents', 'custom-headers' ); ?></strong>
		<ol>
			<li><a href="#ch-help-overview"><?php esc_html_e( 'What this plugin does', 'custom-headers' ); ?></a></li>
			<li><a href="#ch-help-rules"><?php esc_html_e( 'How rules work', 'custom-headers' ); ?></a></li>
			<li><a href="#ch-help-contexts"><?php esc_html_e( 'Contexts', 'custom-headers' ); ?></a></li>
			<li><a href="#ch-help-uri"><?php esc_html_e( 'Request URI patterns', 'custom-headers' ); ?></a></li>
			<li><a href="#ch-help-examples"><?php esc_html_e( 'Examples', 'custom-headers' ); ?></a></li>
			<li><a href="#ch-help-page-cache"><?php esc_html_e( 'Page-cache plugins', 'custom-headers' ); ?></a></li>
			<li><a href="#ch-help-troubleshooting"><?php esc_html_e( 'Troubleshooting', 'custom-headers' ); ?></a></li>
		</ol>
	</nav>

	<section id="ch-help-overview" class="ch-help-section">
		<h2><?php esc_html_e( 'What this plugin does', 'custom-headers' ); ?></h2>
		<p><?php esc_html_e( 'Custom Headers sends custom HTTP headers on WordPress\' send_headers hook. It does not cache pages or purge caches—it only defines which headers the browser or CDN should receive based on the request context and URI.', 'custom-headers' ); ?></p>
	</section>

	<section id="ch-help-rules" class="ch-help-section">
		<h2><?php esc_html_e( 'How rules work', 'custom-headers' ); ?></h2>
		<p><?php esc_html_e( 'Each rule can include one or more headers (name + value). All rules are evaluated in the configured order. When a rule matches, its headers are sent. Multiple rules can match the same request.', 'custom-headers' ); ?></p>
	</section>

	<section id="ch-help-contexts" class="ch-help-section">
		<h2><?php esc_html_e( 'Contexts', 'custom-headers' ); ?></h2>
		<ul>
			<li><strong><?php esc_html_e( 'All requests', 'custom-headers' ); ?></strong> — <?php esc_html_e( 'front-end, admin, and login.', 'custom-headers' ); ?></li>
			<li><strong><?php esc_html_e( 'Front-end', 'custom-headers' ); ?></strong> — <?php esc_html_e( 'public site pages.', 'custom-headers' ); ?></li>
			<li><strong><?php esc_html_e( 'Admin', 'custom-headers' ); ?></strong> — <?php esc_html_e( 'WordPress dashboard (is_admin).', 'custom-headers' ); ?></li>
			<li><strong><?php esc_html_e( 'Login', 'custom-headers' ); ?></strong> — <?php esc_html_e( 'wp-login.php screen.', 'custom-headers' ); ?></li>
		</ul>
	</section>

	<section id="ch-help-uri" class="ch-help-section">
		<h2><?php esc_html_e( 'Request URI patterns', 'custom-headers' ); ?></h2>
		<p><?php esc_html_e( 'The pattern is compared against the request path (without the domain). Leave it empty to apply the rule to any URI within the selected context.', 'custom-headers' ); ?></p>
		<ul>
			<li><code>/contact/</code> — <?php esc_html_e( 'that exact URL only.', 'custom-headers' ); ?></li>
			<li><code>/news/*</code> — <?php esc_html_e( 'that path and its children.', 'custom-headers' ); ?></li>
			<li><code>/wp-json/my-plugin/v1/*</code> — <?php esc_html_e( 'custom REST API routes.', 'custom-headers' ); ?></li>
		</ul>
	</section>

	<section id="ch-help-examples" class="ch-help-section">
		<h2><?php esc_html_e( 'Examples', 'custom-headers' ); ?></h2>

		<h3><?php esc_html_e( 'X-Frame-Options on admin and login', 'custom-headers' ); ?></h3>
		<ul>
			<li><?php esc_html_e( 'Context: Admin — URI: empty — Header: X-Frame-Options = DENY', 'custom-headers' ); ?></li>
			<li><?php esc_html_e( 'Context: Login — URI: empty — Header: X-Frame-Options = DENY', 'custom-headers' ); ?></li>
		</ul>

		<h3><?php esc_html_e( 'CORS on REST API', 'custom-headers' ); ?></h3>
		<ul>
			<li><?php esc_html_e( 'Context: All — URI: /wp-json/my-plugin/v1/* — Header: Access-Control-Allow-Origin = https://example.com', 'custom-headers' ); ?></li>
		</ul>

		<h3><?php esc_html_e( 'Do not cache checkout', 'custom-headers' ); ?></h3>
		<ul>
			<li><?php esc_html_e( 'Context: Front-end — URI: /checkout — Header: Cache-Control = no-store, no-cache, must-revalidate, max-age=0', 'custom-headers' ); ?></li>
		</ul>
	</section>

	<section id="ch-help-page-cache" class="ch-help-section">
		<h2><?php esc_html_e( 'Page-cache plugins', 'custom-headers' ); ?></h2>
		<p><?php esc_html_e( 'This plugin sends headers when WordPress runs PHP. If a page-cache plugin serves HTML from its store, PHP may not run and your headers will not apply on that request.', 'custom-headers' ); ?></p>
		<ul>
			<li><?php esc_html_e( 'After purging cache: the next request is usually a miss → WordPress runs → your rules apply.', 'custom-headers' ); ?></li>
			<li><?php esc_html_e( 'Cached requests: the headers stored when the page was cached are served.', 'custom-headers' ); ?></li>
			<li><?php esc_html_e( 'If you change a rule on a cached path, purge the cache or exclude that path from page cache to see the change immediately.', 'custom-headers' ); ?></li>
		</ul>
		<p><?php esc_html_e( 'For security headers (CORS, X-Frame-Options, CSP) on cached static routes, verify that the cached response includes the correct headers. For full control, exclude those URLs from page cache.', 'custom-headers' ); ?></p>
	</section>

	<section id="ch-help-troubleshooting" class="ch-help-section">
		<h2><?php esc_html_e( 'Troubleshooting', 'custom-headers' ); ?></h2>

		<h3><?php esc_html_e( 'I do not see the header in the browser', 'custom-headers' ); ?></h3>
		<ul>
			<li><?php esc_html_e( 'Confirm that the context and URI pattern match the URL you are testing.', 'custom-headers' ); ?></li>
			<li><?php esc_html_e( 'Check developer tools → Network tab → response headers.', 'custom-headers' ); ?></li>
			<li><?php esc_html_e( 'If page cache is active, purge it or test on a URL excluded from cache.', 'custom-headers' ); ?></li>
		</ul>

		<h3><?php esc_html_e( 'I changed a rule and it did not update', 'custom-headers' ); ?></h3>
		<p><?php esc_html_e( 'On cached paths, purge the page-cache plugin or exclude the path. On uncached paths, the change applies on the next request.', 'custom-headers' ); ?></p>

		<h3><?php esc_html_e( 'Headers not allowed', 'custom-headers' ); ?></h3>
		<p><?php esc_html_e( 'For security, hop-by-hop headers cannot be configured (Connection, Transfer-Encoding, Content-Length, etc.).', 'custom-headers' ); ?></p>
	</section>
</div>
