<?php
define( "GIGYA_PERMISSION_LEVEL", "manage_options" );

class GigyaConfig {
	function gigya_admin_menu() {


		add_menu_page( 'Gigya', 'Gigya', GIGYA_PERMISSION_LEVEL, 'gigya', '_gigya_admin_page', plugin_dir_url( __FILE__ ) . 'images/favicon_28px.png', '70.1' );

		add_submenu_page( 'gigya', __( 'Social Login', 'Social Login' ), __( 'Social Login', 'Social Login' ), GIGYA_PERMISSION_LEVEL, 'gigya-social-login', '_gigya_admin_page' );
		add_submenu_page( 'gigya', __( 'Share', 'Share' ), __( 'Share', 'Share' ), GIGYA_PERMISSION_LEVEL, 'gigya-share', '_gigya_admin_page' );
		add_submenu_page( 'gigya', __( 'Comments', 'Comments' ), __( 'Comments', 'Comments' ), GIGYA_PERMISSION_LEVEL, 'gigya-comments', '_gigya_admin_page' );
		add_submenu_page( 'gigya', __( 'Reactions', 'Reaction' ), __( 'Reactions', 'Reactions' ), GIGYA_PERMISSION_LEVEL, 'gigya-reactions', '_gigya_admin_page' );
		add_submenu_page( 'gigya', __( 'Gamification', 'Gamification' ), __( 'Gamification', 'Gamification' ), GIGYA_PERMISSION_LEVEL, 'gigya-gm', '_gigya_admin_page' );
		add_submenu_page( 'gigya', __( 'RAAS Settings', 'RAAS Settings' ), __( 'RAAS Settings', 'RAAS Settings' ), GIGYA_PERMISSION_LEVEL, 'gigya-raas', '_gigya_admin_page' );

		add_settings_section(
			'gigya_global_settings',
			'Global Settings',
			'globalSectionCallback',
			'gigya'
		);
		add_settings_section(
				'gigya_login_settings',
				'Social Login Settings',
				'\GigyaConfigPages::loginSectionCallback',
				'gigya-social-login'
		);
		add_settings_section(
				'gigya_share_settings',
				'Share Settings',
				'\GigyaConfigPages::shareSectionCallback',
				'gigya-share'
		);
		add_settings_section(
				'gigya_comments_settings',
				'Comments Settings',
				'\GigyaConfigPages::commentsSectionCallback',
				'gigya-comments'
		);
		add_settings_section(
				'gigya_reactions_settings',
				'Reactions Settings',
				'\GigyaConfigPages::reactionSectionCallback',
				'gigya-reactions'
		);
		add_settings_section(
				'gigya_gm_settings',
				'Gamification Settings',
				'\GigyaConfigPages::gmSectionCallback',
				'gigya-gm'
		);
		add_settings_section(
				'gigya_raas_settings',
				'RASS Settings',
				'\GigyaConfigPages::raasSectionCallback',
				'gigya-raas'
		);
	}

	public function gigya_admin_init() {
		$page = $_GET['page'];
		require_once( GIGYA__PLUGIN_DIR . 'admin/forms/globalForm.php' );
		register_setting( $page, 'gigya_settings_fields');

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