<?php
define( "GIGYA__PERMISSION_LEVEL", "manage_options" );

class GigyaSettings {

	/**
	 * Constructor.
	 */
	public function __construct() {

		// Add Javascript and css to admin page
		wp_enqueue_style( 'gigya_admin_css', GIGYA__PLUGIN_URL . 'admin/gigya_admin.css' );
		wp_enqueue_script( 'gigya_admin_js', GIGYA__PLUGIN_URL . 'admin/gigya_admin.js' );
		wp_enqueue_script( 'gigya_jsonlint_js', GIGYA__PLUGIN_URL . 'admin/jsonlint.js' );

		// Actions.
		add_action( 'admin_init', array( $this, 'adminInit' ) );
		add_action( 'admin_menu', array( $this, 'adminMenu' ) );

	}

	/**
	 * Hook admin_init callback.
	 * Initialize Admin section.
	 */
	public function adminInit() {

		// Add settings sections.
		foreach ( $this->getSections() as $id => $section ) {
			add_settings_section( $id, $section['title'], $section['func'], $section['slug'] );
			register_setting( $section['slug'] . '-group', $section['slug'], array( $this, 'validate' ) );
		}
	}

	/**
	 * @param $input
	 *
	 * @return mixed
	 */
	public function validate( $input ) {
//		$cms = new gigyaCMS();
//		$res = $cms->apiValidate( $input['api_key'], $input['api_secret'], $input['data_center'] );
//		if ( $res['error'] == 301001 ) {
//			add_settings_error( 'gigiya_data_canter', 'validation', 'Incorrect value entered!', 'error' );
//		}
		return $input;
	}

	/**
	 * Hook admin_menu callback.
	 * Set Gigya's Setting area.
	 */
	public function adminMenu() {

		// Register the main Gigya setting route page.
		add_menu_page( 'Gigya', 'Gigya', GIGYA__PERMISSION_LEVEL, 'gigya_global_settings', array( $this, 'adminPage' ), GIGYA__PLUGIN_URL . 'admin/images/favicon_28px.png', '70.1' );

		// Register the sub-menus Gigya setting pages.
		foreach ( $this->getSections() as $section ) {

			require_once GIGYA__PLUGIN_DIR . 'admin/forms/' . $section['func'] . '.php';
			add_submenu_page( 'gigya_global_settings', __( $section['title'], $section['title'] ), __( $section['title'], $section['title'] ), GIGYA__PERMISSION_LEVEL, $section['slug'], array( $this, 'adminPage' ) );

		}
	}


	/**
	 * Returns the form sections definition.
	 * @return array
	 */
	public function getSections() {
		return array(
				'gigya_global_settings'    => array(
						'title' => 'Global Settings',
						'func'  => 'globalSettingsForm',
						'slug'  => 'gigya_global_settings'
				),
				'gigya_login_settings'     => array(
						'title' => 'User Management Settings',
						'func'  => 'loginSettingsForm',
						'slug'  => 'gigya_login_settings'
				),
				'gigya_share_settings'     => array(
						'title' => 'Share Settings',
						'func'  => 'shareSettingsForm',
						'slug'  => 'gigya_share_settings'
				),
				'gigya_comments_settings'  => array(
						'title' => 'Comments Settings',
						'func'  => 'commentsSettingsForm',
						'slug'  => 'gigya_comments_settings'
				),
				'gigya_reactions_settings' => array(
						'title' => 'Reactions Settings',
						'func'  => 'reactionsSettingsForm',
						'slug'  => 'gigya_reactions_settings'
				),
				'gigya_gm_settings'        => array(
						'title' => 'Gamification Settings',
						'func'  => 'gmSettingsForm',
						'slug'  => 'gigya_gm_settings'
				),
				'gigya_feed_settings'      => array(
						'title' => 'Activity Feed Settings',
						'func'  => 'feedSettingsForm',
						'slug'  => 'gigya_feed_settings'
				),
				'gigya_follow_settings'      => array(
					'title' => 'Follow Bar Settings',
					'func'  => 'followSettingsForm',
					'slug'  => 'gigya_follow_settings'
				),
		);
	}

	/**
	 * Render the Gigya admin pages wrapper (Tabs, Form, etc.).
	 */
	public static function adminPage() {
		$page   = $_GET['page'];
		$render = '';

		echo _gigya_render_tpl( 'admin/tpl/adminPage-wrapper.tpl.php', array( 'page' => $page ) );
		settings_errors();

		echo '<form class="gigya-settings" action="options.php" method="post">';
		echo '<input type="hidden" name="action" value="gigya_settings_submit">';

		wp_nonce_field( 'update-options' );
		settings_fields( $page . '-group' );
		do_settings_sections( $page );
		submit_button();

		echo '</form>';

		return $render;
	}

	/**
	 * On Setting page save event.
	 */
	public static function onSave() {
		// When a Gigya's setting page is submitted.
		if ( isset( $_POST['gigya_login_settings'] ) ) {
			// When we turn on the Gigya's social login plugin,
			// We also turn on the WP 'Membership: Anyone can register' option.
			if ( $_POST['gigya_login_settings']['mode'] == 'wp_sl' ) {
				update_option( 'users_can_register', 1 );
			} elseif ( $_POST['gigya_login_settings']['mode'] == 'raas' ) {
				update_option( 'users_can_register', 0 );
			}

		} elseif ( isset( $_POST['gigya_global_settings'] ) ) {
			$cms = new gigyaCMS();
			$res = $cms->apiValidate( $_POST['gigya_global_settings']['api_key'], $_POST['gigya_global_settings']['api_secret'], $_POST['gigya_global_settings']['data_center'] );
			if (!empty($res)) {
				$errorCode = $res->getErrorCode();
				if ( $errorCode == 301001 ) {
					$_POST['gigya_global_settings']['data_center'] = $res->apiDomain;
					$msg
					                                               =
						$res->getErrorMessage() . '. ' . 'This API key is served by: ' . $res->apiDomain;
					add_settings_error( 'gigiya_data_canter', 'validation', $msg, 'error' );
				} elseif ( $errorCode > 0 ) {
					add_settings_error( 'General setting error', $errorCode, $res->getErrorMessage(), 'error' );
				}
			} else{
				add_settings_error( 'General setting error', -1, 'Error sending request to gigya', 'error' );
			}

		}
	}

}