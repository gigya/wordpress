<?php
define( "GIGYA__PERMISSION_LEVEL", "manage_options" );

class GigyaConfig {
	function gigya_admin_menu() {
		require_once( GIGYA__PLUGIN_DIR . 'admin/forms/globalConfigForm.php' );
		require_once( GIGYA__PLUGIN_DIR . 'admin/forms/loginConfigForm.php' );
		require_once( GIGYA__PLUGIN_DIR . 'admin/forms/shareConfigForm.php' );
		require_once( GIGYA__PLUGIN_DIR . 'admin/forms/commentsConfigForm.php' );
		require_once( GIGYA__PLUGIN_DIR . 'admin/forms/reactionsConfigForm.php' );
		require_once( GIGYA__PLUGIN_DIR . 'admin/forms/gmConfigForm.php' );
		require_once( GIGYA__PLUGIN_DIR . 'admin/forms/raasConfigForm.php' );

		add_menu_page( 'Gigya', 'Gigya', GIGYA__PERMISSION_LEVEL, 'gigya', '_gigya_admin_page', plugin_dir_url( __FILE__ ) . 'images/favicon_28px.png', '70.1' );

		add_submenu_page( 'gigya', __( 'Social Login', 'Social Login' ), __( 'Social Login', 'Social Login' ), GIGYA__PERMISSION_LEVEL, 'gigya-social-login', '_gigya_admin_page' );
		add_submenu_page( 'gigya', __( 'Share', 'Share' ), __( 'Share', 'Share' ), GIGYA__PERMISSION_LEVEL, 'gigya-share', '_gigya_admin_page' );
		add_submenu_page( 'gigya', __( 'Comments', 'Comments' ), __( 'Comments', 'Comments' ), GIGYA__PERMISSION_LEVEL, 'gigya-comments', '_gigya_admin_page' );
		add_submenu_page( 'gigya', __( 'Reactions', 'Reaction' ), __( 'Reactions', 'Reactions' ), GIGYA__PERMISSION_LEVEL, 'gigya-reactions', '_gigya_admin_page' );
		add_submenu_page( 'gigya', __( 'Gamification', 'Gamification' ), __( 'Gamification', 'Gamification' ), GIGYA__PERMISSION_LEVEL, 'gigya-gm', '_gigya_admin_page' );
		add_submenu_page( 'gigya', __( 'RAAS Settings', 'RAAS Settings' ), __( 'RAAS Settings', 'RAAS Settings' ), GIGYA__PERMISSION_LEVEL, 'gigya-raas', '_gigya_admin_page' );

		add_settings_section(
			'gigya_global_settings',
			'Global Settings',
			'globalConfigForm',
			'gigya'
		);
		add_settings_section(
				'gigya_login_settings',
				'Social Login Settings',
				'loginConfigForm',
				'gigya-social-login'
		);
		add_settings_section(
				'gigya_share_settings',
				'Share Settings',
				'shareConfigForm',
				'gigya-share'
		);
		add_settings_section(
				'gigya_comments_settings',
				'Comments Settings',
				'commentsConfigForm',
				'gigya-comments'
		);
		add_settings_section(
				'gigya_reactions_settings',
				'Reactions Settings',
				'reactionsConfigForm',
				'gigya-reactions'
		);
		add_settings_section(
				'gigya_gm_settings',
				'Gamification Settings',
				'gmConfigForm',
				'gigya-gm'
		);
		add_settings_section(
				'gigya_raas_settings',
				'RASS Settings',
				'raasConfigForm',
				'gigya-raas'
		);
	}

	public function gigya_admin_init() {
		register_setting( GIGYA__SETTINGS_PREFIX, GIGYA__SETTINGS_PREFIX);

		// Add Javascript and css to admin page
		wp_enqueue_style('gigya_admin_css', plugins_url('style/css/gigya_admin.css', __FILE__));
		wp_enqueue_script('gigya_admin_js', plugins_url('script/js/gigya_admin.js', __FILE__));
	}
}

/**
 * Render the Gigya admin pages wrapper (Tabs, Form, etc.).
 */
function _gigya_admin_page() {
	$page = $_GET['page'];
	$render = _gigya_render_tpl('admin/tpl/adminPage-wrapper.tpl.php', array('page' => $page));
	return $render;
}

add_action( 'admin_menu', array( 'GigyaConfig', 'gigya_admin_menu' ) );
add_action( 'admin_init', array( 'GigyaConfig', 'gigya_admin_init' ) );