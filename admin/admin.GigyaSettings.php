<?php
define( "GIGYA__PERMISSION_LEVEL", "manage_options" );

class GigyaSettings {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'gigya_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'gigya_admin_init' ) );
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
						'slug'  => 'gigya-global'
				),
				'gigya_login_settings'     => array(
						'title' => 'Social Login Settings',
						'func'  => 'loginSettingsForm',
						'slug'  => 'gigya-social-login'
				),
				'gigya_share_settings'     => array(
						'title' => 'Share Settings',
						'func'  => 'shareSettingsForm',
						'slug'  => 'gigya-share'
				),
				'gigya_comments_settings'  => array(
						'title' => 'Comments Settings',
						'func'  => 'commentsSettingsForm',
						'slug'  => 'gigya-comments'
				),
				'gigya_reactions_settings' => array(
						'title' => 'Reactions Settings',
						'func'  => 'reactionsSettingsForm',
						'slug'  => 'gigya-reactions'
				),
				'gigya_gm_settings'        => array(
						'title' => 'Gamification Settings',
						'func'  => 'gmSettingsForm',
						'slug'  => 'gigya-gm'
				),
				'gigya_raas_settings'      => array(
						'title' => 'RASS Settings',
						'func'  => 'raasSettingsForm',
						'slug'  => 'gigya-raas'
				),
		);
	}

	/**
	 * Hook admin_menu callback.
	 * Set Gigya Setting area.
	 */
	public function gigya_admin_menu() {

		// Register the main Gigya setting route page.
		add_menu_page( 'Gigya', 'Gigya', GIGYA__PERMISSION_LEVEL, 'gigya-global', array($this, '_gigya_admin_page'), plugin_dir_url( __FILE__ ) . 'images/favicon_28px.png', '70.1' );

		// Register the sub-menus Gigya setting pages.
		foreach ($this->getSections() as $section) {
			require_once( GIGYA__PLUGIN_DIR . 'admin/forms/' . $section['func'] . '.php' );
			add_submenu_page( 'gigya-global', __( $section['title'], $section['title'] ), __( $section['title'], $section['title'] ), GIGYA__PERMISSION_LEVEL, $section['slug'], array($this, '_gigya_admin_page') );
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
		foreach ($this->getSections() as $id => $section) {
			add_settings_section( $id, $section['title'], $section['func'], $section['slug'] );
			register_setting( $section['slug'] . '-settings-group', $section['slug'] . '-settings' );
		}
	}

	/**
	 * Render the Gigya admin pages wrapper (Tabs, Form, etc.).
	 */
	public static function _gigya_admin_page() {
		$page   = $_GET['page'];
		$render = '';

		_gigya_render_tpl( 'admin/tpl/adminPage-wrapper.tpl.php', array( 'page' => $page ) );
		settings_errors();

		echo '<form class="gigya-settings" action="options.php" method="post">';

		 	wp_nonce_field( 'update-options' );
		 	settings_fields( $page . '-settings-group' );
		 	do_settings_sections( $page );
		 	submit_button();

		echo '</form>';

		return $render;
	}

	/**
	 * Render a form.
	 *
	 * @param $form
	 *
	 * @return string
	 */
	public static function _gigya_form_render( $form ) {
		$render = '';

		foreach ( $form as $el ) {
			// Add a section param. to be added to the element attribute NAME.
			// Tells to WP under which option to save this field.
			$el['section'] = $_GET['page'] . '-settings';

			// Render each element.
			$render .= _gigya_render_tpl( 'admin/tpl/formEl-' . $el['type'] . '.tpl.php', $el );
		}

		return $render;
	}
}

