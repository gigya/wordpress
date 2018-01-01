<?php

/*
 * Plugin editing permission levels
 */
define( "GIGYA__PERMISSION_LEVEL", "manage_options" );
define( "GIGYA__SECRET_PERMISSION_LEVEL", "install_plugins" ); // Network super admin + single site admin
// custom Gigya capabilities are added separately on installation
define( "CUSTOM_GIGYA_EDIT", 'edit_gigya' );
define( "CUSTOM_GIGYA_EDIT_SECRET", 'edit_gigya_secret' );

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
            $option_group = $section['slug'] . '-group';
			add_settings_section( $id, $section['title'], $section['func'], $section['slug'] );
			register_setting( $option_group, $section['slug'], array( $this, 'validate' ) );
            add_filter("option_page_capability_{$option_group}", array( $this, 'addGigyaCapabilities') );
		}
	}

    /**
     * Add gigya edit capability to allow custom roles to edit Gigya
     */
    public function addGigyaCapabilities() {
        return CUSTOM_GIGYA_EDIT;
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

		// Default admin capabilities
		if (current_user_can('GIGYA__PERMISSION_LEVEL')) {
			// Register the main Gigya setting route page.
			add_menu_page( 'Gigya', 'Gigya', GIGYA__PERMISSION_LEVEL, 'gigya_global_settings', array( $this, 'adminPage' ), GIGYA__PLUGIN_URL . 'admin/images/favicon_28px.png', '70.1' );

			// Register the sub-menus Gigya setting pages.
			foreach ( $this->getSections() as $section ) {

				require_once GIGYA__PLUGIN_DIR . 'admin/forms/' . $section['func'] . '.php';
				add_submenu_page( 'gigya_global_settings', __( $section['title'], $section['title'] ), __( $section['title'], $section['title'] ), GIGYA__PERMISSION_LEVEL, $section['slug'], array( $this, 'adminPage' ) );

			}
		} elseif ( current_user_can( CUSTOM_GIGYA_EDIT )) {
			// Register the main Gigya setting route page.
			add_menu_page( 'Gigya', 'Gigya', CUSTOM_GIGYA_EDIT, 'gigya_global_settings', array( $this, 'adminPage' ), GIGYA__PLUGIN_URL . 'admin/images/favicon_28px.png', '70.1' );

			// Register the sub-menus Gigya setting pages.
			foreach ( $this->getSections() as $section ) {

				require_once GIGYA__PLUGIN_DIR . 'admin/forms/' . $section['func'] . '.php';
				add_submenu_page( 'gigya_global_settings', __( $section['title'], $section['title'] ), __( $section['title'], $section['title'] ), CUSTOM_GIGYA_EDIT, $section['slug'], array( $this, 'adminPage' ) );

			}
		}

	}


	/**
	 * Returns the form sections definition.
	 * @return array
	 */
	public static function getSections() {
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
				'gigya_session_management'     => array(
					'title' => 'Session Management',
					'func'  => 'sessionManagementForm',
					'slug'  => 'gigya_session_management'
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
		if ( isset( $_POST['gigya_login_settings'] ) )
		{
			// When we turn on the Gigya's social login plugin,
			// We also turn on the WP 'Membership: Anyone can register' option.
			if ( $_POST['gigya_login_settings']['mode'] == 'wp_sl' ) {
				update_option( 'users_can_register', 1 );
			} elseif ( $_POST['gigya_login_settings']['mode'] == 'raas' ) {
				update_option( 'users_can_register', 0 );
			}

		}
		elseif ( isset( $_POST['gigya_global_settings'] ) )
		{
			$cms = new gigyaCMS();
			static::_setSecret();
			$res = $cms->apiValidate( $_POST['gigya_global_settings']['api_key'], $_POST['gigya_global_settings']['user_key'], $_POST['gigya_global_settings']['api_secret'], $_POST['gigya_global_settings']['data_center'] );
			if (!empty($res)) {
				$gigyaErrCode = $res->getErrorCode();
				if ( $gigyaErrCode > 0 ) {
                    $gigyaErrMsg = $res->getErrorMessage();
                    $errorsLink = "<a href='https://developers.gigya.com/display/GD/Response+Codes+and+Errors+REST' target='_blank' rel='noopener noreferrer'>Response_Codes_and_Errors</a>";
                    $message = "Gigya API error: {$gigyaErrCode} - {$gigyaErrMsg}. For more information please refer to {$errorsLink}";
					add_settings_error( 'gigya_global_settings', 'api_validate', $message, 'error' );
                    // prevent updating values
                    static::_keepOldApiValues();
				}
			} else {
				add_settings_error( 'gigya_global_settings', 'api_validate', 'Error sending request to gigya', 'error' );
			}
		}
	}

	/**
	 * Set the POST'ed secret key.
	 * If its not submitted, take it from DB.
	 */
	public static function _setSecret() {
		if ( empty($_POST['gigya_global_settings']['api_secret']) ) {
			$options = static::_setSiteOptions();
			$_POST['gigya_global_settings']['api_secret'] = $options['api_secret'];
		}
	}

    /**
     * Set the posted api related values to the old (from DB) values
     */
    public static function _keepOldApiValues() {
        $options = static::_setSiteOptions();
        $_POST['gigya_global_settings']['api_key'] = $options['api_key'];
        $_POST['gigya_global_settings']['api_secret'] = $options['api_secret'];
        $_POST['gigya_global_settings']['data_center'] = $options['data_center'];
    }

    /**
     * If multisite, get options from main site, else from current site
     */
    public static function _setSiteOptions() {
        if ( is_multisite() ) {
            $options = get_blog_option( 1, GIGYA__SETTINGS_GLOBAL );
        } else {
            $options = get_option( GIGYA__SETTINGS_GLOBAL );
        }
        return $options;
    }

}