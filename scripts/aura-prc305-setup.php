<?php
/**
 * One-time Aura (PRC 305) site setup: MetForm, Elementor page data, kit colors, menu order, plugins.
 *
 * Run from CLI (XAMPP PHP):
 *   /Applications/XAMPP/xamppfiles/bin/php /Applications/XAMPP/xamppfiles/htdocs/wordpress/scripts/aura-prc305-setup.php
 *
 * Safe to re-run: overwrites Elementor data on Aura pages and recreates/refreshes the contact form.
 */

if ( php_sapi_name() !== 'cli' ) {
	exit( "CLI only.\n" );
}

define( 'WP_USE_THEMES', false );
require dirname( __DIR__ ) . '/wp-load.php';

// CLI has no logged-in user; impersonate first administrator for caps checks.
$admins = get_users( [ 'role' => 'administrator', 'number' => 1 ] );
if ( empty( $admins ) ) {
	exit( "No administrator user found.\n" );
}
wp_set_current_user( $admins[0]->ID );
if ( ! current_user_can( 'manage_options' ) ) {
	exit( "Administrator user cannot manage_options.\n" );
}

const AURA_PAGE_HOME     = 126;
const AURA_PAGE_ABOUT    = 128;
const AURA_PAGE_PRODUCTS = 130;
const AURA_PAGE_CONTACT  = 132;

/**
 * @return string[]
 */
function aura_cloudinary_urls(): array {
	return [
		'https://res.cloudinary.com/dbajirdzj/image/upload/q_auto/f_auto/v1774447830/Sample_1-01_svvlxu.jpg',
		'https://res.cloudinary.com/dbajirdzj/image/upload/q_auto/f_auto/v1774447825/Sample_2-02_swl7vw.jpg',
		'https://res.cloudinary.com/dbajirdzj/image/upload/q_auto/f_auto/v1774447823/Sample_1-01_yy0yfz.jpg',
		'https://res.cloudinary.com/dbajirdzj/image/upload/q_auto/f_auto/v1774447823/Sample_3-03_huxhsc.jpg',
		'https://res.cloudinary.com/dbajirdzj/image/upload/q_auto/f_auto/v1774447822/Green_Color-03_a0uqou.jpg',
		'https://res.cloudinary.com/dbajirdzj/image/upload/q_auto/f_auto/v1774447821/Monochrome-03_iqbi4q.jpg',
		'https://res.cloudinary.com/dbajirdzj/image/upload/q_auto/f_auto/v1774447820/Main_Color_-_White_t7yv0a.jpg',
		'https://res.cloudinary.com/dbajirdzj/image/upload/q_auto/f_auto/v1774447818/Sample_2-01_m6vkpv.jpg',
		'https://res.cloudinary.com/dbajirdzj/image/upload/q_auto/f_auto/v1774447817/Sample_2-02_zhrroc.jpg',
		'https://res.cloudinary.com/dbajirdzj/image/upload/q_auto/f_auto/v1774447815/Orange-Pink_Color-03_ampygk.jpg',
		'https://res.cloudinary.com/dbajirdzj/image/upload/q_auto/f_auto/v1774447784/Back_Pack_Bag_alwbfe.jpg',
		'https://res.cloudinary.com/dbajirdzj/image/upload/q_auto/f_auto/v1774447784/Sample_1_svgnqu.jpg',
		'https://res.cloudinary.com/dbajirdzj/image/upload/q_auto/f_auto/v1774447782/Black_White_Caps_igcvfn.jpg',
		'https://res.cloudinary.com/dbajirdzj/image/upload/q_auto/f_auto/v1774447781/Food_Bag_s1dgeh.jpg',
		'https://res.cloudinary.com/dbajirdzj/image/upload/q_auto/f_auto/v1774447781/Tote_Bag_d95y0z.jpg',
	];
}

function aura_eid( string $seed ): string {
	return substr( md5( 'aura-' . $seed ), 0, 8 );
}

function aura_save_elementor_page( int $post_id, array $document ): void {
	// Library export JSON includes title/type/version/page_settings/content, but post meta
	// _elementor_data must be ONLY the elements tree (the "content" array), same as core Elementor.
	$elements = isset( $document['content'] ) && is_array( $document['content'] ) ? $document['content'] : [];
	$json     = wp_json_encode( $elements, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $post_id, '_elementor_template_type', 'wp-page' );
	update_post_meta( $post_id, '_elementor_version', '4.0.0' );
	update_post_meta( $post_id, '_elementor_data', wp_slash( $json ) );
	if ( ! empty( $document['page_settings'] ) && is_array( $document['page_settings'] ) ) {
		update_post_meta( $post_id, '_elementor_page_settings', $document['page_settings'] );
	}
	delete_post_meta( $post_id, '_elementor_css' );
	delete_post_meta( $post_id, '_elementor_page_assets' );
	echo "Updated Elementor data for post {$post_id}\n";
}

/**
 * @param mixed $elements
 * @return mixed
 */
function aura_metform_strip_widgets( $elements ) {
	if ( ! is_array( $elements ) ) {
		return $elements;
	}
	$out = [];
	foreach ( $elements as $el ) {
		if ( ! is_array( $el ) ) {
			continue;
		}
		if ( ( $el['elType'] ?? '' ) === 'widget' ) {
			$wt = $el['widgetType'] ?? '';
			if ( $wt === 'mf-recaptcha' ) {
				continue;
			}
			if ( $wt === 'mf-text' && ( $el['settings']['mf_input_name'] ?? '' ) === 'mf-last-name' ) {
				continue;
			}
			if ( $wt === 'mf-text' && ( $el['settings']['mf_input_name'] ?? '' ) === 'mf-subject' ) {
				continue;
			}
		}
		if ( ! empty( $el['elements'] ) ) {
			$el['elements'] = aura_metform_strip_widgets( $el['elements'] );
		}
		$out[] = $el;
	}
	return $out;
}

/**
 * @param mixed $elements
 * @return void
 */
function aura_metform_normalize_grid( &$elements ): void {
	if ( ! is_array( $elements ) ) {
		return;
	}
	foreach ( $elements as &$el ) {
		if ( ! is_array( $el ) ) {
			continue;
		}
		$stype = $el['settings']['container_type'] ?? '';
		if ( ( $el['elType'] ?? '' ) === 'container' && $stype === 'grid' ) {
			$widgets = 0;
			if ( ! empty( $el['elements'] ) && is_array( $el['elements'] ) ) {
				foreach ( $el['elements'] as $ch ) {
					if ( ( $ch['elType'] ?? '' ) === 'widget' && ( $ch['widgetType'] ?? '' ) === 'mf-text' ) {
						++$widgets;
					}
				}
			}
			if ( $widgets === 1 && isset( $el['settings']['grid_columns_grid'] ) ) {
				$el['settings']['grid_columns_grid']['size'] = 1;
			}
		}
		if ( ! empty( $el['elements'] ) ) {
			aura_metform_normalize_grid( $el['elements'] );
		}
	}
	unset( $el );
}

/**
 * @param mixed $elements
 * @return void
 */
function aura_metform_patch_first_name( &$elements ): void {
	if ( ! is_array( $elements ) ) {
		return;
	}
	foreach ( $elements as &$el ) {
		if ( ! is_array( $el ) ) {
			continue;
		}
		if ( ( $el['elType'] ?? '' ) === 'widget' && ( $el['widgetType'] ?? '' ) === 'mf-text' ) {
			if ( ( $el['settings']['mf_input_name'] ?? '' ) === 'mf-first-name' ) {
				$el['settings']['mf_input_label']          = 'Full name';
				$el['settings']['mf_input_name']           = 'mf-name';
				$el['settings']['mf_input_placeholder']    = 'Your name';
				$el['settings']['mf_input_required']       = 'yes';
				$el['settings']['mf_input_validation_warning_message'] = 'Please enter your name.';
			}
		}
		if ( ( $el['elType'] ?? '' ) === 'widget' && ( $el['widgetType'] ?? '' ) === 'heading' ) {
			$ht = $el['settings']['title'] ?? '';
			if ( in_array( $ht, [ 'Simple Contact Form', 'Talk to Aura', 'Send a message' ], true ) ) {
				$el['settings']['title']       = 'Send a message';
				$el['settings']['title_color'] = '#060d18';
			}
		}
		if ( ! empty( $el['elements'] ) ) {
			aura_metform_patch_first_name( $el['elements'] );
		}
	}
	unset( $el );
}

function aura_create_or_refresh_metform(): int {
	global $wpdb;
	$existing = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_title = %s AND post_status IN ('publish','draft','private')",
			'metform-form',
			'Aura Contact'
		)
	);
	foreach ( $existing as $pid ) {
		wp_trash_post( (int) $pid );
	}

	$form_id = \MetForm\Core\Forms\Builder::instance()->create_form( 'Aura Contact', 1 );
	if ( is_wp_error( $form_id ) || ! $form_id ) {
		exit( "MetForm create_form failed.\n" );
	}

	$raw = get_post_meta( $form_id, '_elementor_data', true );
	$data = json_decode( $raw, true );
	if ( ! is_array( $data ) ) {
		exit( "Could not decode MetForm _elementor_data.\n" );
	}

	$data = aura_metform_strip_widgets( $data );
	aura_metform_normalize_grid( $data );
	aura_metform_patch_first_name( $data );

	update_post_meta( $form_id, '_elementor_data', wp_slash( wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) ) );

	$key  = \MetForm\Core\Forms\Base::instance()->form->get_key_form_settings();
	$settings = get_post_meta( $form_id, $key, true );
	if ( ! is_array( $settings ) ) {
		$settings = [];
	}
	$admin_email = get_option( 'admin_email' );
	$settings['success_message']              = 'Thank you. Your message is in—we’ll get back to you within one business day.';
	$settings['form_title']                   = 'Aura Contact';
	$settings['store_entries']                = '1';
	$settings['enable_admin_notification']    = '1';
	$settings['admin_email_to']               = $admin_email;
	$settings['admin_email_subject']          = '[Aura] New contact form message';
	$settings['admin_email_from']             = get_bloginfo( 'name' ) . ' <' . $admin_email . '>';
	$settings['admin_email_body']           = '{all_fields}';
	$settings['enable_recaptcha']           = '';
	update_post_meta( $form_id, $key, $settings );

	echo "MetForm form ID {$form_id} (Aura Contact)\n";
	return (int) $form_id;
}

function aura_update_kit(): void {
	$kit_id = (int) get_option( 'elementor_active_kit' );
	if ( ! $kit_id ) {
		echo "No elementor_active_kit; skip kit update.\n";
		return;
	}
	$settings = get_post_meta( $kit_id, '_elementor_page_settings', true );
	if ( ! is_array( $settings ) ) {
		$settings = [];
	}
	$settings['site_name']         = 'Aura';
	$settings['site_description']  = 'Premium functional beverages — clean focus, steady hydration, East African energy.';
	$settings['system_colors']     = [
		[ '_id' => 'primary', 'title' => 'Primary', 'color' => '#e3c76f' ],
		[ '_id' => 'secondary', 'title' => 'Secondary', 'color' => '#2dd4bf' ],
		[ '_id' => 'text', 'title' => 'Text', 'color' => '#c8d6df' ],
		[ '_id' => 'accent', 'title' => 'Accent', 'color' => '#5fd4c4' ],
	];
	$settings['custom_colors']     = [
		[ '_id' => 'aura_navy', 'title' => 'Navy Deep', 'color' => '#060d18' ],
		[ '_id' => 'aura_teal', 'title' => 'Teal Deep', 'color' => '#0f4a45' ],
		[ '_id' => 'aura_gold', 'title' => 'Champagne Gold', 'color' => '#e3c76f' ],
		[ '_id' => 'aura_muted', 'title' => 'Muted Blue-Gray', 'color' => '#94aab8' ],
		[ '_id' => 'aura_card', 'title' => 'Card / Panel', 'color' => '#152b40' ],
	];
	$font_stack = [
		'typography_typography'       => 'custom',
		'typography_font_family'    => 'Outfit',
		'typography_font_weight'    => '600',
	];
	$body_stack = [
		'typography_typography'       => 'custom',
		'typography_font_family'    => 'DM Sans',
		'typography_font_weight'    => '400',
	];
	$settings['system_typography'] = [
		array_merge( [ '_id' => 'primary', 'title' => 'Primary' ], $font_stack ),
		array_merge( [ '_id' => 'secondary', 'title' => 'Secondary' ], $body_stack ),
		array_merge( [ '_id' => 'text', 'title' => 'Text' ], $body_stack ),
		array_merge( [ '_id' => 'accent', 'title' => 'Accent' ], $font_stack, [ 'typography_font_weight' => '500' ] ),
	];
	update_post_meta( $kit_id, '_elementor_page_settings', $settings );
	echo "Updated Elementor kit {$kit_id}\n";
}

function aura_deactivate_sticky_menu(): void {
	$opt = get_option( 'active_plugins', [] );
	if ( ! is_array( $opt ) ) {
		return;
	}
	$next = array_values(
		array_filter(
			$opt,
			static function ( $p ) {
				return strpos( $p, 'mystickymenu/' ) === false;
			}
		)
	);
	if ( $next !== $opt ) {
		update_option( 'active_plugins', $next );
		echo "Deactivated My Sticky Menu.\n";
	}
}

function aura_menu_order(): void {
	global $wpdb;
	$order = [
		138 => 1,
		137 => 2,
		136 => 3,
		135 => 4,
	];
	foreach ( $order as $post_id => $menu_order ) {
		$wpdb->update( $wpdb->posts, [ 'menu_order' => $menu_order ], [ 'ID' => $post_id ], [ '%d' ], [ '%d' ] );
	}
	echo "Primary menu order: Home → About → Products → Contact\n";
}

function aura_write_template_file( string $name, array $document ): void {
	$dir = dirname( __DIR__ ) . '/elementor-templates';
	$path = $dir . '/' . $name;
	file_put_contents( $path, wp_json_encode( $document, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "\n" );
	echo "Wrote {$path}\n";
}

// --- Build Elementor documents (version 0.4 pattern, same as jak-portfolio-home-page.json) ---
$u   = home_url( '/' );
$img = aura_cloudinary_urls();

// Premium palette: deep navy, jewel teal, champagne gold, warm cream body copy.
$aura = [
	'navy'      => '#060d18',
	'navy_mid'  => '#0c1a2c',
	'slate'     => '#111f30',
	'teal_a'    => '#0f4a45',
	'teal_b'    => '#134e48',
	'mint'      => '#6ecfb8',
	'gold'      => '#e3c76f',
	'gold_soft' => '#d4b968',
	'cream'     => '#f7f4ef',
	'body'      => '#c8d6df',
	'muted'     => '#94aab8',
	'card'      => '#152b40',
	'ghost'     => '#1a354d',
	'ink'       => '#0a1219',
];

$contact_url = home_url( '/contact/' );
$about_url   = home_url( '/about/' );
$prod_url    = home_url( '/products/' );

$footer_editor = '<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;margin-top:40px;padding-top:28px;border-top:1px solid rgba(227,199,111,0.22)"><strong style="color:' . $aura['cream'] . ';font-size:17px;letter-spacing:0.14em;font-weight:700">AURA</strong><span style="color:' . $aura['muted'] . ';font-size:12px;line-height:1.5">© ' . gmdate( 'Y' ) . ' Aura Energy · Nairobi &amp; East Africa · <a href="' . esc_url( $contact_url ) . '" style="color:' . $aura['gold'] . ';text-decoration:none">Contact</a></span></div>';
$social_notice = '<p style="color:#94aab8;font-size:12px;line-height:1.55;margin:10px 0 0">Social accounts are pending launch. Follow links will be activated at brand launch.</p>';

$social = [
	'id'         => aura_eid( 'soc' ),
	'elType'     => 'widget',
	'widgetType' => 'social-icons',
	'isInner'    => false,
	'settings'   => [
		'align'                => 'left',
		'icon_color'           => 'custom',
		'icon_primary_color'   => $aura['gold'],
		'icon_secondary_color' => $aura['navy'],
		'icon_size'            => [ 'unit' => 'px', 'size' => 18, 'sizes' => [] ],
		'image_border_radius'  => [ 'unit' => 'px', 'top' => '8', 'right' => '8', 'bottom' => '8', 'left' => '8', 'isLinked' => true ],
		'social_icon_list'     => [
			[
				'social_icon' => [ 'value' => 'fab fa-instagram', 'library' => 'fa-brands' ],
				'link'        => [ 'url' => '#', 'is_external' => '', 'nofollow' => '', 'custom_attributes' => 'aria-label|Instagram - launching soon' ],
				'_id'         => aura_eid( 'ig' ),
			],
			[
				'social_icon' => [ 'value' => 'fab fa-facebook', 'library' => 'fa-brands' ],
				'link'        => [ 'url' => '#', 'is_external' => '', 'nofollow' => '', 'custom_attributes' => 'aria-label|Facebook - launching soon' ],
				'_id'         => aura_eid( 'fb' ),
			],
			[
				'social_icon' => [ 'value' => 'fab fa-youtube', 'library' => 'fa-brands' ],
				'link'        => [ 'url' => '#', 'is_external' => '', 'nofollow' => '', 'custom_attributes' => 'aria-label|YouTube - launching soon' ],
				'_id'         => aura_eid( 'yt' ),
			],
		],
	],
	'elements'   => [],
];

function aura_image_widget( string $seed, string $url ): array {
	return [
		'id'         => aura_eid( $seed ),
		'elType'     => 'widget',
		'widgetType' => 'image',
		'isInner'    => false,
		'settings'   => [
			'image'            => [ 'url' => $url, 'id' => '' ],
			'image_size'       => 'full',
			'lazyload'         => 'yes',
			'_border_radius'   => [ 'unit' => 'px', 'top' => '14', 'right' => '14', 'bottom' => '14', 'left' => '14', 'isLinked' => true ],
			'_border_border'   => 'solid',
			'_border_width'    => [ 'unit' => 'px', 'top' => '1', 'right' => '1', 'bottom' => '1', 'left' => '1', 'isLinked' => true ],
			'_border_color'    => 'rgba(227,199,111,0.38)',
			'_box_shadow_box_shadow_type' => 'yes',
			'_box_shadow_box_shadow'      => [
				'horizontal' => 0,
				'vertical'   => 14,
				'blur'       => 28,
				'spread'     => 0,
				'color'      => 'rgba(6,13,24,0.35)',
			],
		],
		'elements'   => [],
	];
}

function aura_btn( string $seed, string $text, string $url, string $bg = '#e3c76f', string $tc = '#0a1219' ): array {
	return [
		'id'         => aura_eid( $seed ),
		'elType'     => 'widget',
		'widgetType' => 'button',
		'isInner'    => false,
		'settings'   => [
			'text'               => $text,
			'link'               => [ 'url' => $url, 'is_external' => '', 'nofollow' => '', 'custom_attributes' => '' ],
			'align'              => 'left',
			'size'               => 'md',
			'button_text_color'  => $tc,
			'background_color'   => $bg,
			'hover_animation'    => 'grow',
			'typography_typography' => 'custom',
			'typography_font_family' => 'Outfit',
			'typography_font_weight' => '600',
			'typography_line_height' => [ 'unit' => 'em', 'size' => 1.2, 'sizes' => [] ],
			'border_radius'      => [ 'unit' => 'px', 'top' => '12', 'right' => '12', 'bottom' => '12', 'left' => '12', 'isLinked' => true ],
		],
		'elements'   => [],
	];
}

function aura_sitewide_css(): string {
	return <<<'CSS'
/* PRC 305 Aura polish pass: responsiveness, typography, visual consistency, nav clarity */
:root {
	--aura-bg: #060d18;
	--aura-body: #c8d6df;
	--aura-muted: #94aab8;
	--aura-gold: #e3c76f;
	--aura-card-radius: 14px;
	--aura-space-mobile: 20px;
}

body {
	background: var(--aura-bg);
	color: var(--aura-body);
	text-rendering: optimizeLegibility;
	-webkit-font-smoothing: antialiased;
}

h1,
h2,
h3,
h4,
h5,
h6 {
	line-height: 1.2;
	letter-spacing: 0.01em;
}

p,
li {
	line-height: 1.65;
}

.elementor-section {
	padding-left: var(--aura-space-mobile);
	padding-right: var(--aura-space-mobile);
}

.elementor-widget-image img {
	border-radius: var(--aura-card-radius);
}

.elementor-button,
.elementor-button-link {
	border-radius: 12px !important;
	padding: 12px 22px !important;
	font-weight: 600 !important;
	line-height: 1.2 !important;
	box-shadow: 0 10px 24px rgba(6, 13, 24, 0.25);
}

.elementor-button:hover,
.elementor-button-link:hover {
	transform: translateY(-1px);
	transition: transform 180ms ease, filter 180ms ease;
	filter: brightness(1.03);
}

/* Navigation clarity + active page state */
.main-header-menu .menu-item.current-menu-item > a,
.main-header-menu .menu-item.current_page_item > a {
	color: var(--aura-gold) !important;
	font-weight: 700 !important;
}

/* Give the final menu item a CTA treatment (usually Contact) */
.main-header-menu .menu-item:last-child > a {
	background: var(--aura-gold);
	color: #0a1219 !important;
	border-radius: 999px;
	padding: 9px 16px;
}

@media (max-width: 1024px) {
	h1 { font-size: clamp(2rem, 6vw, 2.8rem); }
	h2 { font-size: clamp(1.5rem, 4.5vw, 2.1rem); }
	.elementor-column { margin-bottom: 16px; }
}

@media (max-width: 767px) {
	.elementor-section {
		padding-left: 16px;
		padding-right: 16px;
	}
	.elementor-widget-heading h1,
	.elementor-widget-heading h2,
	.elementor-widget-heading h3 {
		margin-bottom: 10px;
	}
	.elementor-column-gap-default > .elementor-column > .elementor-element-populated {
		padding: 12px;
	}
	/* Better mobile image stacking */
	.elementor-widget-image {
		margin-bottom: 14px !important;
	}
}

/* Wider browser compatibility for focus visibility */
a:focus-visible,
button:focus-visible,
input:focus-visible,
textarea:focus-visible {
	outline: 2px solid var(--aura-gold);
	outline-offset: 2px;
}
CSS;
}

$home_doc = [
	'title'          => 'Aura — Home',
	'type'           => 'page',
	'version'        => '0.4',
	'page_settings'  => [
		'background_background' => 'classic',
		'background_color'      => '#060d18',
	],
	'content'        => [
		[
			'id'       => aura_eid( 'h-hero-sec' ),
			'elType'   => 'section',
			'isInner'  => false,
			'settings' => [
				'layout'                    => 'boxed',
				'content_width'             => [ 'unit' => 'px', 'size' => 1200, 'sizes' => [] ],
				'gap'                       => 'no',
				'structure'                 => '20',
				'padding'                   => [ 'unit' => 'px', 'top' => '72', 'right' => '24', 'bottom' => '56', 'left' => '24', 'isLinked' => false ],
				'background_background'     => 'gradient',
				'background_color'          => '#060d18',
				'background_color_b'        => '#0f4a45',
				'background_gradient_angle' => [ 'unit' => 'deg', 'size' => 152, 'sizes' => [] ],
			],
			'elements' => [
				[
					'id'       => aura_eid( 'h-hero-c1' ),
					'elType'   => 'column',
					'isInner'  => false,
					'settings' => [ '_column_size' => 100, 'width' => [ 'unit' => '%', 'size' => 52, 'sizes' => [] ] ],
					'elements' => [
						[
							'id' => aura_eid( 'h-tag' ), 'elType' => 'widget', 'widgetType' => 'text-editor', 'isInner' => false,
							'settings' => [ 'editor' => '<p style="color:#6ecfb8;font-size:11px;letter-spacing:0.24em;text-transform:uppercase;margin:0 0 16px;font-weight:600">East Africa · Premium functional drinks</p>' ],
							'elements' => [],
						],
						[
							'id' => aura_eid( 'h-h1' ), 'elType' => 'widget', 'widgetType' => 'heading', 'isInner' => false,
							'settings' => [ 'title' => 'Aura', 'header_size' => 'h1', 'align' => 'left', 'title_color' => '#f7f4ef', 'typography_typography' => 'custom', 'typography_font_size' => [ 'unit' => 'px', 'size' => 58, 'sizes' => [] ], 'typography_font_weight' => '700' ],
							'elements' => [],
						],
						[
							'id' => aura_eid( 'h-sub' ), 'elType' => 'widget', 'widgetType' => 'heading', 'isInner' => false,
							'settings' => [ 'title' => 'Focus, flow, and fuel for the long day.', 'header_size' => 'h3', 'align' => 'left', 'title_color' => '#e3c76f' ],
							'elements' => [],
						],
						[
							'id' => aura_eid( 'h-txt' ), 'elType' => 'widget', 'widgetType' => 'text-editor', 'isInner' => false,
							'settings' => [ 'editor' => '<p style="color:#c8d6df;max-width:36rem;line-height:1.7;font-size:17px;font-weight:400">Aura is a premium functional beverage line crafted for young professionals, creators, and night-shift hustlers. We balance clean focus and steady hydration with a bold East African sensibility—real ingredients, crisp taste, and none of the sugar-crash theatre.</p><p style="color:#94aab8;max-width:36rem;line-height:1.65;font-size:15px;margin-top:14px">Whether you are closing deals in Nairobi, building in Kampala, or editing until 3am, Aura is built to keep your edge sharp and your body honest.</p>' ],
							'elements' => [],
						],
						aura_btn( 'hb1', 'Explore the range', $prod_url ),
						aura_btn( 'hb2', 'Partner with us', $contact_url, '#1a354d', '#e3c76f' ),
					],
				],
				[
					'id'       => aura_eid( 'h-hero-c2' ),
					'elType'   => 'column',
					'isInner'  => false,
					'settings' => [ '_column_size' => 100, 'width' => [ 'unit' => '%', 'size' => 48, 'sizes' => [] ], 'content_position' => 'center' ],
					'elements' => [ aura_image_widget( 'heroimg', $img[6] ) ],
				],
			],
		],
		[
			'id'       => aura_eid( 'about-sec' ),
			'elType'   => 'section',
			'isInner'  => false,
			'settings' => [
				'layout'                => 'boxed',
				'content_width'         => [ 'unit' => 'px', 'size' => 1200, 'sizes' => [] ],
				'structure'             => '20',
				'padding'               => [ 'unit' => 'px', 'top' => '56', 'right' => '24', 'bottom' => '56', 'left' => '24', 'isLinked' => false ],
				'background_background' => 'classic',
				'background_color'      => '#0c1a2c',
			],
			'elements' => [
				[
					'id'       => aura_eid( 'ab-c1' ),
					'elType'   => 'column',
					'isInner'  => false,
					'settings' => [ '_column_size' => 100, 'width' => [ 'unit' => '%', 'size' => 50, 'sizes' => [] ] ],
					'elements' => [
						[ 'id' => aura_eid( 'abh' ), 'elType' => 'widget', 'widgetType' => 'heading', 'isInner' => false, 'settings' => [ 'title' => 'Born in Nairobi. Made for momentum.', 'header_size' => 'h2', 'title_color' => '#f7f4ef' ], 'elements' => [] ],
						[ 'id' => aura_eid( 'abp' ), 'elType' => 'widget', 'widgetType' => 'text-editor', 'isInner' => false, 'settings' => [ 'editor' => '<p style="color:#c8d6df;line-height:1.75;font-size:16px">Every formula starts with a simple question: what does a demanding day actually need? We layer botanicals, electrolytes, and carefully dosed caffeine into drinks that feel refined—not loud—so you can move from boardroom to studio without missing a beat.</p><p style="color:#94aab8;line-height:1.7;font-size:15px;margin-top:12px">Aura is tuned for East Africa’s pace: heat, hustle, and high expectations. Same discipline in the can as you bring to your craft.</p>' ], 'elements' => [] ],
						aura_btn( 'aba', 'Read our story', $about_url ),
					],
				],
				[
					'id'       => aura_eid( 'ab-c2' ),
					'elType'   => 'column',
					'isInner'  => false,
					'settings' => [ '_column_size' => 100, 'width' => [ 'unit' => '%', 'size' => 50, 'sizes' => [] ] ],
					'elements' => [ aura_image_widget( 'abimg', $img[4] ) ],
				],
			],
		],
		[
			'id'       => aura_eid( 'prod-sec' ),
			'elType'   => 'section',
			'isInner'  => false,
			'settings' => [
				'layout'                => 'boxed',
				'content_width'         => [ 'unit' => 'px', 'size' => 1200, 'sizes' => [] ],
				'structure'             => '30',
				'padding'               => [ 'unit' => 'px', 'top' => '48', 'right' => '24', 'bottom' => '32', 'left' => '24', 'isLinked' => false ],
				'background_background' => 'classic',
				'background_color'      => '#060d18',
			],
			'elements' => [
				[
					'id'       => aura_eid( 'p0' ),
					'elType'   => 'column',
					'isInner'  => false,
					'settings' => [ '_column_size' => 33 ],
					'elements' => [
						aura_image_widget( 'pimg0', $img[10] ),
						[ 'id' => aura_eid( 'pt0' ), 'elType' => 'widget', 'widgetType' => 'heading', 'isInner' => false, 'settings' => [ 'title' => 'Aura Focus', 'header_size' => 'h4', 'title_color' => '#e3c76f' ], 'elements' => [] ],
						[ 'id' => aura_eid( 'pd0' ), 'elType' => 'widget', 'widgetType' => 'text-editor', 'isInner' => false, 'settings' => [ 'editor' => '<p style="color:#94aab8;font-size:14px;line-height:1.65">Green tea, citrus peel, and a precise caffeine curve—built for deep work, not jitters.</p>' ], 'elements' => [] ],
					],
				],
				[
					'id'       => aura_eid( 'p1' ),
					'elType'   => 'column',
					'isInner'  => false,
					'settings' => [ '_column_size' => 33 ],
					'elements' => [
						aura_image_widget( 'pimg1', $img[9] ),
						[ 'id' => aura_eid( 'pt1' ), 'elType' => 'widget', 'widgetType' => 'heading', 'isInner' => false, 'settings' => [ 'title' => 'Aura Flow', 'header_size' => 'h4', 'title_color' => '#e3c76f' ], 'elements' => [] ],
						[ 'id' => aura_eid( 'pd1' ), 'elType' => 'widget', 'widgetType' => 'text-editor', 'isInner' => false, 'settings' => [ 'editor' => '<p style="color:#94aab8;font-size:14px;line-height:1.65">Electrolytes and coconut water notes for commutes, training blocks, and sun-heavy afternoons.</p>' ], 'elements' => [] ],
					],
				],
				[
					'id'       => aura_eid( 'p2' ),
					'elType'   => 'column',
					'isInner'  => false,
					'settings' => [ '_column_size' => 33 ],
					'elements' => [
						aura_image_widget( 'pimg2', $img[11] ),
						[ 'id' => aura_eid( 'pt2' ), 'elType' => 'widget', 'widgetType' => 'heading', 'isInner' => false, 'settings' => [ 'title' => 'Aura Night', 'header_size' => 'h4', 'title_color' => '#e3c76f' ], 'elements' => [] ],
						[ 'id' => aura_eid( 'pd2' ), 'elType' => 'widget', 'widgetType' => 'text-editor', 'isInner' => false, 'settings' => [ 'editor' => '<p style="color:#94aab8;font-size:14px;line-height:1.65">Lower stim, chamomile and adaptogenic botanicals—when the deadline is real but sleep still matters.</p>' ], 'elements' => [] ],
					],
				],
			],
		],
		[
			'id'       => aura_eid( 'gal-sec' ),
			'elType'   => 'section',
			'isInner'  => false,
			'settings' => [
				'layout'                => 'boxed',
				'content_width'         => [ 'unit' => 'px', 'size' => 1200, 'sizes' => [] ],
				'structure'             => '20',
				'padding'               => [ 'unit' => 'px', 'top' => '40', 'right' => '24', 'bottom' => '40', 'left' => '24', 'isLinked' => false ],
				'background_background' => 'classic',
				'background_color'      => '#0c1a2c',
			],
			'elements' => [
				[
					'id'       => aura_eid( 'g-full' ),
					'elType'   => 'column',
					'isInner'  => false,
					'settings' => [ '_column_size' => 100 ],
					'elements' => [
						[ 'id' => aura_eid( 'g-h' ), 'elType' => 'widget', 'widgetType' => 'heading', 'isInner' => false, 'settings' => [ 'title' => 'Design language', 'header_size' => 'h2', 'align' => 'center', 'title_color' => '#f7f4ef' ], 'elements' => [] ],
						[ 'id' => aura_eid( 'g-s' ), 'elType' => 'widget', 'widgetType' => 'text-editor', 'isInner' => false, 'settings' => [ 'editor' => '<p style="text-align:center;color:#94aab8;max-width:42rem;margin:0 auto 28px;line-height:1.65">From gradient cans to monochrome studies and carry systems—every touchpoint is meant to feel confident, contemporary, and unmistakably Aura.</p>' ], 'elements' => [] ],
					],
				],
			],
		],
		[
			'id'       => aura_eid( 'gal2-sec' ),
			'elType'   => 'section',
			'isInner'  => false,
			'settings' => [
				'layout'                => 'full_width',
				'gap'                   => 'no',
				'structure'             => '40',
				'padding'               => [ 'unit' => 'px', 'top' => '0', 'right' => '24', 'bottom' => '56', 'left' => '24', 'isLinked' => false ],
				'background_background' => 'classic',
				'background_color'      => '#0c1a2c',
			],
			'elements' => [
				[ 'id' => aura_eid( 'gg0' ), 'elType' => 'column', 'isInner' => false, 'settings' => [ '_column_size' => 25 ], 'elements' => [ aura_image_widget( 'g0', $img[0] ) ] ],
				[ 'id' => aura_eid( 'gg1' ), 'elType' => 'column', 'isInner' => false, 'settings' => [ '_column_size' => 25 ], 'elements' => [ aura_image_widget( 'g1', $img[1] ) ] ],
				[ 'id' => aura_eid( 'gg2' ), 'elType' => 'column', 'isInner' => false, 'settings' => [ '_column_size' => 25 ], 'elements' => [ aura_image_widget( 'g2', $img[2] ) ] ],
				[ 'id' => aura_eid( 'gg3' ), 'elType' => 'column', 'isInner' => false, 'settings' => [ '_column_size' => 25 ], 'elements' => [ aura_image_widget( 'g3', $img[3] ) ] ],
			],
		],
		[
			'id'       => aura_eid( 'cta-sec' ),
			'elType'   => 'section',
			'isInner'  => false,
			'settings' => [
				'layout'                    => 'boxed',
				'content_width'             => [ 'unit' => 'px', 'size' => 1200, 'sizes' => [] ],
				'structure'                 => '20',
				'padding'                   => [ 'unit' => 'px', 'top' => '48', 'right' => '24', 'bottom' => '72', 'left' => '24', 'isLinked' => false ],
				'background_background'     => 'gradient',
				'background_color'          => '#0f4a45',
				'background_color_b'        => '#060d18',
				'background_gradient_angle' => [ 'unit' => 'deg', 'size' => 95, 'sizes' => [] ],
			],
			'elements' => [
				[
					'id'       => aura_eid( 'cta-c' ),
					'elType'   => 'column',
					'isInner'  => false,
					'settings' => [ '_column_size' => 100 ],
					'elements' => [
						[ 'id' => aura_eid( 'cta-h' ), 'elType' => 'widget', 'widgetType' => 'heading', 'isInner' => false, 'settings' => [ 'title' => 'Stock Aura. Sponsor the hustle.', 'header_size' => 'h2', 'align' => 'center', 'title_color' => '#f7f4ef' ], 'elements' => [] ],
						[ 'id' => aura_eid( 'cta-p' ), 'elType' => 'widget', 'widgetType' => 'text-editor', 'isInner' => false, 'settings' => [ 'editor' => '<p style="text-align:center;color:#c8d6df;max-width:38rem;margin:0 auto 22px;line-height:1.65">Retail, hospitality, events, and creative partnerships—we work with teams who care about quality and consistency. Tell us what you are building; we will connect you with the right channel lead.</p>' ], 'elements' => [] ],
						[
							'id' => aura_eid( 'cta-bwrap' ), 'elType' => 'widget', 'widgetType' => 'text-editor', 'isInner' => false,
							'settings' => [ 'editor' => '<p style="text-align:center"><a class="elementor-button-link" style="display:inline-block;padding:15px 32px;background:#e3c76f;color:#0a1219;border-radius:12px;font-weight:600;text-decoration:none;letter-spacing:0.02em" href="' . esc_url( $contact_url ) . '">Get in touch</a></p>' ],
							'elements' => [],
						],
						[ 'id' => aura_eid( 'ft-e' ), 'elType' => 'widget', 'widgetType' => 'text-editor', 'isInner' => false, 'settings' => [ 'editor' => $footer_editor ], 'elements' => [] ],
						$social,
						[ 'id' => aura_eid( 'ft-social-note' ), 'elType' => 'widget', 'widgetType' => 'text-editor', 'isInner' => false, 'settings' => [ 'editor' => $social_notice ], 'elements' => [] ],
					],
				],
			],
		],
	],
];

$about_doc = [
	'title'         => 'Aura — About',
	'type'          => 'page',
	'version'       => '0.4',
	'page_settings' => [ 'background_background' => 'classic', 'background_color' => '#060d18' ],
	'content'       => [
		[
			'id' => aura_eid( 'ab-hero' ), 'elType' => 'section', 'isInner' => false,
			'settings' => [
				'layout' => 'boxed', 'content_width' => [ 'unit' => 'px', 'size' => 900, 'sizes' => [] ],
				'padding' => [ 'unit' => 'px', 'top' => '56', 'right' => '24', 'bottom' => '32', 'left' => '24', 'isLinked' => false ],
				'background_background' => 'classic', 'background_color' => '#060d18',
			],
			'elements' => [
				[
					'id' => aura_eid( 'ab-1c' ), 'elType' => 'column', 'isInner' => false, 'settings' => [ '_column_size' => 100 ],
					'elements' => [
						[ 'id' => aura_eid( 'a1' ), 'elType' => 'widget', 'widgetType' => 'heading', 'isInner' => false, 'settings' => [ 'title' => 'About Aura', 'header_size' => 'h1', 'title_color' => '#f7f4ef' ], 'elements' => [] ],
						[ 'id' => aura_eid( 'a2' ), 'elType' => 'widget', 'widgetType' => 'text-editor', 'isInner' => false, 'settings' => [ 'editor' => '<p style="color:#c8d6df;font-size:17px;line-height:1.75">Aura is a premium functional beverage house rooted in Nairobi and inspired by the pace of East Africa. We make drinks for people who treat their time as currency—founders, filmmakers, surgeons on night shift, DJs after soundcheck.</p><p style="color:#94aab8;line-height:1.75;font-size:16px;margin-top:14px">Our promise is straightforward: honest formulation, balanced stimulation, and flavor that belongs in a glass, not a chemistry set. The brand voice is warm precision—confident, never shouty—with a visual world of deep navy, jewel teal, and champagne gold.</p><p style="color:#94aab8;line-height:1.7;font-size:15px;margin-top:14px">From Westlands boardrooms to coastal runs in Mombasa, Aura is designed to move with you.</p>' ], 'elements' => [] ],
						aura_image_widget( 'about-hero', $img[5] ),
						aura_btn( 'ab-contact', 'Partner with Aura', $contact_url ),
						[ 'id' => aura_eid( 'a-ft' ), 'elType' => 'widget', 'widgetType' => 'text-editor', 'isInner' => false, 'settings' => [ 'editor' => $footer_editor ], 'elements' => [] ],
						$social,
						[ 'id' => aura_eid( 'a-social-note' ), 'elType' => 'widget', 'widgetType' => 'text-editor', 'isInner' => false, 'settings' => [ 'editor' => $social_notice ], 'elements' => [] ],
					],
				],
			],
		],
	],
];

$products_doc = [
	'title'         => 'Aura — Products',
	'type'          => 'page',
	'version'       => '0.4',
	'page_settings' => [ 'background_background' => 'classic', 'background_color' => '#060d18' ],
	'content'       => [
		[
			'id' => aura_eid( 'pr-sec1' ), 'elType' => 'section', 'isInner' => false,
			'settings' => [
				'layout' => 'boxed', 'content_width' => [ 'unit' => 'px', 'size' => 1200, 'sizes' => [] ],
				'padding' => [ 'unit' => 'px', 'top' => '56', 'right' => '24', 'bottom' => '24', 'left' => '24', 'isLinked' => false ],
				'background_background' => 'classic', 'background_color' => '#060d18',
			],
			'elements' => [
				[
					'id' => aura_eid( 'pr-c' ), 'elType' => 'column', 'isInner' => false, 'settings' => [ '_column_size' => 100 ],
					'elements' => [
						[ 'id' => aura_eid( 'pr-h' ), 'elType' => 'widget', 'widgetType' => 'heading', 'isInner' => false, 'settings' => [ 'title' => 'The lineup', 'header_size' => 'h1', 'title_color' => '#f7f4ef' ], 'elements' => [] ],
						[ 'id' => aura_eid( 'pr-le' ), 'elType' => 'widget', 'widgetType' => 'text-editor', 'isInner' => false, 'settings' => [ 'editor' => '<p style="color:#94aab8;max-width:44rem;line-height:1.65;font-size:16px">Three core SKUs anchor the range—each with a clear job: sharpen the mind, restore fluid balance, or wind down without checking out. Below is our hero can plus the carry systems we deploy for retail activations and sampling.</p>' ], 'elements' => [] ],
					],
				],
			],
		],
		[
			'id' => aura_eid( 'pr-row' ), 'elType' => 'section', 'isInner' => false,
			'settings' => [
				'layout' => 'boxed', 'content_width' => [ 'unit' => 'px', 'size' => 1200, 'sizes' => [] ],
				'structure' => '20',
				'padding' => [ 'unit' => 'px', 'top' => '16', 'right' => '24', 'bottom' => '40', 'left' => '24', 'isLinked' => false ],
				'background_background' => 'classic', 'background_color' => '#0c1a2c',
			],
			'elements' => [
				[ 'id' => aura_eid( 'pr-l' ), 'elType' => 'column', 'isInner' => false, 'settings' => [ '_column_size' => 50 ], 'elements' => [ aura_image_widget( 'prl', $img[12] ) ] ],
				[ 'id' => aura_eid( 'pr-r' ), 'elType' => 'column', 'isInner' => false, 'settings' => [ '_column_size' => 50 ], 'elements' => [
					[ 'id' => aura_eid( 'pr-h2' ), 'elType' => 'widget', 'widgetType' => 'heading', 'isInner' => false, 'settings' => [ 'title' => 'Aura Original', 'header_size' => 'h2', 'title_color' => '#e3c76f' ], 'elements' => [] ],
					[ 'id' => aura_eid( 'pr-t2' ), 'elType' => 'widget', 'widgetType' => 'text-editor', 'isInner' => false, 'settings' => [ 'editor' => '<p style="color:#c8d6df;line-height:1.65;margin-bottom:12px">The signature can: balanced, sessionable, and sharp enough for morning briefs.</p><ul style="color:#94aab8;line-height:1.85"><li>330ml slim aluminum · refined carbonation</li><li>Reduced sugar · no artificial colors</li><li>Caffeine from green coffee &amp; cold-brewed tea</li><li>Lime, sea salt, and a clean mineral finish</li></ul>' ], 'elements' => [] ],
				] ],
			],
		],
		[
			'id' => aura_eid( 'pr-row2' ), 'elType' => 'section', 'isInner' => false,
			'settings' => [
				'layout' => 'boxed', 'content_width' => [ 'unit' => 'px', 'size' => 1200, 'sizes' => [] ],
				'structure' => '20',
				'padding' => [ 'unit' => 'px', 'top' => '24', 'right' => '24', 'bottom' => '40', 'left' => '24', 'isLinked' => false ],
				'background_background' => 'classic', 'background_color' => '#060d18',
			],
			'elements' => [
				[ 'id' => aura_eid( 'p2-l' ), 'elType' => 'column', 'isInner' => false, 'settings' => [ '_column_size' => 50 ], 'elements' => [
					[ 'id' => aura_eid( 'p2-h' ), 'elType' => 'widget', 'widgetType' => 'heading', 'isInner' => false, 'settings' => [ 'title' => 'Carry & activation', 'header_size' => 'h2', 'title_color' => '#e3c76f' ], 'elements' => [] ],
					[ 'id' => aura_eid( 'p2-t' ), 'elType' => 'widget', 'widgetType' => 'text-editor', 'isInner' => false, 'settings' => [ 'editor' => '<p style="color:#c8d6df;line-height:1.65">Premium backpacks, totes, and insulated carriers for field teams, festivals, and retail launches—designed to look as considered as what is inside the can.</p>' ], 'elements' => [] ],
				] ],
				[ 'id' => aura_eid( 'p2-r' ), 'elType' => 'column', 'isInner' => false, 'settings' => [ '_column_size' => 50 ], 'elements' => [ aura_image_widget( 'p2i', $img[13] ), aura_image_widget( 'p2i2', $img[14] ) ] ],
			],
		],
		[
			'id' => aura_eid( 'pr-foot' ), 'elType' => 'section', 'isInner' => false,
			'settings' => [
				'layout' => 'boxed', 'content_width' => [ 'unit' => 'px', 'size' => 1200, 'sizes' => [] ],
				'padding' => [ 'unit' => 'px', 'top' => '32', 'right' => '24', 'bottom' => '64', 'left' => '24', 'isLinked' => false ],
				'background_background' => 'classic', 'background_color' => '#0c1a2c',
			],
			'elements' => [
				[ 'id' => aura_eid( 'pf-c' ), 'elType' => 'column', 'isInner' => false, 'settings' => [ '_column_size' => 100 ], 'elements' => [
					[ 'id' => aura_eid( 'pf-t' ), 'elType' => 'widget', 'widgetType' => 'text-editor', 'isInner' => false, 'settings' => [ 'editor' => $footer_editor ], 'elements' => [] ],
					$social,
					[ 'id' => aura_eid( 'p-social-note' ), 'elType' => 'widget', 'widgetType' => 'text-editor', 'isInner' => false, 'settings' => [ 'editor' => $social_notice ], 'elements' => [] ],
				] ],
			],
		],
	],
];

$form_id = aura_create_or_refresh_metform();

$contact_doc = [
	'title'         => 'Aura — Contact',
	'type'          => 'page',
	'version'       => '0.4',
	'page_settings' => [ 'background_background' => 'classic', 'background_color' => '#060d18' ],
	'content'       => [
		[
			'id' => aura_eid( 'co-sec' ), 'elType' => 'section', 'isInner' => false,
			'settings' => [
				'layout' => 'boxed', 'content_width' => [ 'unit' => 'px', 'size' => 800, 'sizes' => [] ],
				'padding' => [ 'unit' => 'px', 'top' => '56', 'right' => '24', 'bottom' => '64', 'left' => '24', 'isLinked' => false ],
				'background_background' => 'classic', 'background_color' => '#060d18',
			],
			'elements' => [
				[
					'id' => aura_eid( 'co-c' ), 'elType' => 'column', 'isInner' => false, 'settings' => [ '_column_size' => 100 ],
					'elements' => [
						[ 'id' => aura_eid( 'co-h' ), 'elType' => 'widget', 'widgetType' => 'heading', 'isInner' => false, 'settings' => [ 'title' => 'Let’s talk', 'header_size' => 'h1', 'title_color' => '#f7f4ef' ], 'elements' => [] ],
						[ 'id' => aura_eid( 'co-p' ), 'elType' => 'widget', 'widgetType' => 'text-editor', 'isInner' => false, 'settings' => [ 'editor' => '<p style="color:#94aab8;line-height:1.65;font-size:16px">Wholesale, events, press, or creative partnerships—send a note and we will route it to the right lead. Prefer email? Use the form; replies come from our team inbox.</p>' ], 'elements' => [] ],
						[
							'id' => aura_eid( 'co-mf' ), 'elType' => 'widget', 'widgetType' => 'metform', 'isInner' => false,
							'settings' => [ 'mf_form_id' => (string) $form_id ],
							'elements' => [],
						],
						[ 'id' => aura_eid( 'co-map' ), 'elType' => 'widget', 'widgetType' => 'text-editor', 'isInner' => false, 'settings' => [ 'editor' => '<p style="margin-top:28px;color:#94aab8;font-size:14px;line-height:1.6"><strong style="color:#c8d6df">Aura Studio</strong><br>Nairobi, Kenya · Mon–Fri 9:00–18:00 EAT</p><div style="margin-top:16px;border-radius:12px;overflow:hidden;border:1px solid rgba(227,199,111,0.28);max-width:100%"><iframe title="Nairobi map" style="border:0;width:100%;height:280px" loading="lazy" allowfullscreen src="https://maps.google.com/maps?q=Nairobi%20Kenya&amp;z=11&amp;output=embed"></iframe></div>' ], 'elements' => [] ],
						[ 'id' => aura_eid( 'co-ft' ), 'elType' => 'widget', 'widgetType' => 'text-editor', 'isInner' => false, 'settings' => [ 'editor' => $footer_editor ], 'elements' => [] ],
						$social,
						[ 'id' => aura_eid( 'c-social-note' ), 'elType' => 'widget', 'widgetType' => 'text-editor', 'isInner' => false, 'settings' => [ 'editor' => $social_notice ], 'elements' => [] ],
					],
				],
			],
		],
	],
];

update_option( 'blogname', 'Aura' );
update_option( 'blogdescription', 'Premium functional beverages — clean focus, steady hydration, East African energy.' );
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', AURA_PAGE_HOME );

aura_update_kit();
aura_deactivate_sticky_menu();
aura_menu_order();

$tpl_dir = dirname( __DIR__ ) . '/elementor-templates';
aura_write_template_file( 'aura-home-page.json', $home_doc );
aura_write_template_file( 'aura-about-page.json', $about_doc );
aura_write_template_file( 'aura-products-page.json', $products_doc );
aura_write_template_file( 'aura-contact-page.json', $contact_doc );

aura_save_elementor_page( AURA_PAGE_HOME, $home_doc );
aura_save_elementor_page( AURA_PAGE_ABOUT, $about_doc );
aura_save_elementor_page( AURA_PAGE_PRODUCTS, $products_doc );
aura_save_elementor_page( AURA_PAGE_CONTACT, $contact_doc );

// Apply Additional CSS for global polish and consistency.
$css_post_id = (int) get_theme_mod( 'custom_css_post_id', 0 );
if ( $css_post_id ) {
	wp_update_post(
		[
			'ID'           => $css_post_id,
			'post_content' => aura_sitewide_css() . "\n",
		]
	);
	echo "Updated custom_css post {$css_post_id} with Aura polish styles.\n";
}

delete_option( '_elementor_global_css' );

echo "\nDone. Visit: {$u}\n";
echo "Form ID for reference: {$form_id}\n";
