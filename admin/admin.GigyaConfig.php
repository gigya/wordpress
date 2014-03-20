<?php
define( "GIGYA__PERMISSION_LEVEL", "manage_options" );

class GigyaConfig {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'gigya_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'gigya_admin_init' ) );
	}

	public function getSections() {
		return array(
				'gigya_global_settings'    => array(
						'title' => 'Social Login Settings',
						'func'  => 'globalConfigForm',
						'slug'  => 'gigya'
				),
				'gigya_login_settings'     => array(
						'title' => 'Social Login Settings',
						'func'  => 'loginConfigForm',
						'slug'  => 'gigya-social-login'
				),
				'gigya_share_settings'     => array(
						'title' => 'Share Settings',
						'func'  => 'shareConfigForm',
						'slug'  => 'gigya-share'
				),
				'gigya_comments_settings'  => array(
						'title' => 'Comments Settings',
						'func'  => 'commentsConfigForm',
						'slug'  => 'gigya-comments'
				),
				'gigya_reactions_settings' => array(
						'title' => 'Reactions Settings',
						'func'  => 'reactionsConfigForm',
						'slug'  => 'gigya-reactions'
				),
				'gigya_gm_settings'        => array(
						'title' => 'Gamification Settings',
						'func'  => 'gmConfigForm',
						'slug'  => 'gigya-gm'
				),
				'gigya_raas_settings'      => array(
						'title' => 'RASS Settings',
						'func'  => 'raasConfigForm',
						'slug'  => 'gigya-raas'
				),
		);
	}
	/**
	 * Hook admin_menu callback.
	 * Set Gigya Setting area.
	 */
	public function gigya_admin_menu() {
		// Load Gigya setting forms definitions.
		require_once( GIGYA__PLUGIN_DIR . 'admin/forms/globalConfigForm.php' );
		require_once( GIGYA__PLUGIN_DIR . 'admin/forms/loginConfigForm.php' );
		require_once( GIGYA__PLUGIN_DIR . 'admin/forms/shareConfigForm.php' );
		require_once( GIGYA__PLUGIN_DIR . 'admin/forms/commentsConfigForm.php' );
		require_once( GIGYA__PLUGIN_DIR . 'admin/forms/reactionsConfigForm.php' );
		require_once( GIGYA__PLUGIN_DIR . 'admin/forms/gmConfigForm.php' );
		require_once( GIGYA__PLUGIN_DIR . 'admin/forms/raasConfigForm.php' );

		// Register the main Gigya setting route page.
		add_menu_page( 'Gigya', 'Gigya', GIGYA__PERMISSION_LEVEL, 'gigya', '_gigya_admin_page', plugin_dir_url( __FILE__ ) . 'images/favicon_28px.png', '70.1' );

		// Register the sub-menus Gigya setting pages.
//		add_submenu_page( 'gigya', __( 'Social Login', 'Social Login' ), __( 'Social Login', 'Social Login' ), GIGYA__PERMISSION_LEVEL, 'gigya-social-login', '_gigya_admin_page' );
//		add_submenu_page( 'gigya', __( 'Share', 'Share' ), __( 'Share', 'Share' ), GIGYA__PERMISSION_LEVEL, 'gigya-share', '_gigya_admin_page' );
//		add_submenu_page( 'gigya', __( 'Comments', 'Comments' ), __( 'Comments', 'Comments' ), GIGYA__PERMISSION_LEVEL, 'gigya-comments', '_gigya_admin_page' );
//		add_submenu_page( 'gigya', __( 'Reactions', 'Reaction' ), __( 'Reactions', 'Reactions' ), GIGYA__PERMISSION_LEVEL, 'gigya-reactions', '_gigya_admin_page' );
//		add_submenu_page( 'gigya', __( 'Gamification', 'Gamification' ), __( 'Gamification', 'Gamification' ), GIGYA__PERMISSION_LEVEL, 'gigya-gm', '_gigya_admin_page' );
//		add_submenu_page( 'gigya', __( 'RAAS Settings', 'RAAS Settings' ), __( 'RAAS Settings', 'RAAS Settings' ), GIGYA__PERMISSION_LEVEL, 'gigya-raas', '_gigya_admin_page' );

		foreach (GigyaConfig::getSections() as $section) {
			add_submenu_page( 'gigya', __( $section['title'], $section['title'] ), __( $section['title'], $section['title'] ), GIGYA__PERMISSION_LEVEL, $section['slug'], '_gigya_admin_page' );
		}

	}

	/**
	 * Hook admin_init callback.
	 * Initialize Admin section.
	 */
	public function gigya_admin_init() {

		// Add Javascript and css to admin page
		wp_enqueue_style( 'gigya_admin_css', plugins_url( 'styles/gigya_admin.css', __FILE__ ) );
		wp_enqueue_script( 'gigya_admin_js', plugins_url( 'scripts/gigya_admin.js', __FILE__ ) );

		// Add settings sections.
		foreach (GigyaConfig::getSections() as $id => $section) {
			add_settings_section( $id, $section['title'], $section['func'], $section['slug'] );
		}
		// Add Global Settings form section.
//		add_settings_section(
//				'gigya_global_settings',
//				'Global Settings',
//				'globalConfigForm',
//				'gigya'
//		);
//
//		// Add Social Login Settings form section.
//		add_settings_section(
//				'gigya_login_settings',
//				'Social Login Settings',
//				'loginConfigForm',
//				'gigya-social-login'
//		);
//
//		// Add Share Settings form section.
//		add_settings_section(
//				'gigya_share_settings',
//				'Share Settings',
//				'shareConfigForm',
//				'gigya-share'
//		);
//
//		// Add Comments Settings form section.
//		add_settings_section(
//				'gigya_comments_settings',
//				'Comments Settings',
//				'commentsConfigForm',
//				'gigya-comments'
//		);
//
//		// Add Reactions Settings form section.
//		add_settings_section(
//				'gigya_reactions_settings',
//				'Reactions Settings',
//				'reactionsConfigForm',
//				'gigya-reactions'
//		);
//
//		// Add Gamification Settings form section.
//		add_settings_section(
//				'gigya_gm_settings',
//				'Gamification Settings',
//				'gmConfigForm',
//				'gigya-gm'
//		);
//
//		// Add RASS Settings form section.
//		add_settings_section(
//				'gigya_raas_settings',
//				'RASS Settings',
//				'raasConfigForm',
//				'gigya-raas'
//		);

		register_setting( GIGYA__SETTINGS_PREFIX, GIGYA__SETTINGS_PREFIX);
	}
}

/**
 * Render the Gigya admin pages wrapper (Tabs, Form, etc.).
 */
function _gigya_admin_page() {
	$page   = $_GET['page'];
	$render = _gigya_render_tpl( 'admin/tpl/adminPage-wrapper.tpl.php', array( 'page' => $page ) );
	return $render;
}