<?php

/**
 * @file
 * class.GigyaLoginForm.php
 * Adds Gigya login option to WP login/register form.
 */
class GigyaLoginForm {

	public function __construct() {

		// Gigya configuration values.
		$this->raas_options   = get_option( GIGYA__SETTINGS_RAAS );
		$this->login_options  = get_option( GIGYA__SETTINGS_LOGIN );
		$this->global_options = get_option( GIGYA__SETTINGS_GLOBAL );

	}

	/**
	 * Initialize Gigya login block at Wp login/register.
	 */
	function init() {

		// When there missing email or the admin check to
		// show the entire registration form to the user.
//		echo '<script id="data-form" type="application/json">' . $this->registerExtra() . '</script>';

		// Check Gigya's social login is turn on and there an API key filled.
		if ( ($this->login_options['login_mode'] == 'wp_sl' || $this->login_options['login_mode'] == 'sl_only') && ! empty( $this->global_options['global_api_key'] ) ) {

			// Load custom Gigya login script.
			wp_enqueue_script( 'gigya_login_js', GIGYA__PLUGIN_URL . 'assets/scripts/gigya_login.js' );
			wp_enqueue_style( 'gigya_login_css', GIGYA__PLUGIN_URL . 'assets/styles/gigya_login.css' );

			// Parameters to be sent to the DOM.
			$params = array(
					'ajaxurl'                     => admin_url( 'admin-ajax.php' ),
					'actionLogin'                 => 'gigya_login',
					'actionRaasLogin'             => 'gigya_raas_login',
					'redirect'                    => ! empty ( $this->login_options['login_redirect'] ) ? $this->login_options['login_redirect'] : user_admin_url(),
					'connectWithoutLoginBehavior' => ! empty ( $this->login_options['login_connect_without'] ) ? $this->login_options['login_connect_without'] : 'loginExistingUser',
					'loginMode' => ! empty ( $this->raas_options['raas_plugin'] ) ? 'raas_login' : 'social_login'
			);

			$params['ui']                  = array();
			$params['ui']['showTermsLink'] = false;

			if ( ! empty ( $this->login_options['login_width'] ) ) {
				$params['ui']['width'] = $this->login_options['login_width'];
			}
			if ( ! empty ( $this->login_options['login_height'] ) ) {
				$params['ui']['height'] = $this->login_options['login_height'];
			}
			if ( ! empty ( $this->login_options['login_term_link'] ) ) {
				$params['ui']['showTermsLink'] = $this->login_options['login_term_link'];
			}
			if ( ! empty ( $this->login_options['login_providers'] ) ) {
				$params['ui']['enabledProviders'] = $this->login_options['login_providers'];
			}
			if ( ! empty ( $this->login_options['login_ui'] ) ) {
				$arr = $this->advanced_values_parser( $this->login_options['login_ui'] );
				if ( ! empty( $arr ) ) {
					foreach ( $arr as $key => $val ) {
						$params['ui'][$key] = $val;
					}
				}
			}

			// Load params to be available to client-side script.
			wp_localize_script( 'gigya_login_js', 'gigyaLoginParams', $params );
		}
	}

	/**
	 * Deal with missing fields on registration.
	 */
//	private function registerExtra() {
//
//		// Set submit button value.
//		$submit_value = sprintf( __( 'Register %s' ), ! empty( $this->gigya_user['loginProvider'] ) ? ' ' . __( 'with' ) . ' ' . $this->gigya_user['loginProvider'] : '' );
//		$output       = '';
//
//		// Set form.
//		$output .= '<form name="registerform" class="gigya-register-extra" id="registerform" action="' . wp_registration_url() . '" method="post">';
//		$output .= '<h4 class="title">' . __( 'Please fill required field' ) . '</h4>';
//
//		// Set form elements.
//		$form               = array();
//		$form['user_login'] = array(
//				'type'  => 'text',
//				'id'    => 'user_login',
//				'label' => __( 'Username' ),
//				'value' => ! empty( $this->gigya_user['nickname'] ) ? $this->gigya_user['nickname'] : '',
//		);
//		$form['user_email'] = array(
//				'type'  => 'text',
//				'id'    => 'user_email',
//				'label' => __( 'E-mail' ),
//				'value' => ! empty( $this->gigya_user['email'] ) ? $this->gigya_user['email'] : '',
//		);
//
//		// Render form elements.
//		$output .= _gigya_form_render( $form );
//
//		// Get other plugins register form implementation.
//		$output .= do_action( 'register_form' );
//		$output .= '<input type="hidden" name="gigyaUID" value="' . $this->gigya_user['UID'] . '">';
//
//		// Add submit buttom.
//		$output .= '<input type="submit" name="wp-submit" id="gigya-submit" class="button button-primary button-large" value="' . $submit_value . '">';
//		$output .= '</form>';
//
//		// Tokens replace.
//		do_shortcode( $output );
//
//		$type = ! empty( $this->login_options['login_show_reg']) ? 'extra_form' : 'reg_form';
//		// Set a return array.
//		$ret = array(
//				'type' => $type,
//				'html' => $output,
//		);
//
//		// Return JSON to client.
//		return json_encode( $ret );
//	}

	/**
	 * Parser for the 'key=value | key=value' format.
	 *
	 * @param $str
	 *
	 * @return array|bool
	 */
	public static function advanced_values_parser( $str ) {
		$reg = preg_match_all( "/([^,= ]+)=([^,= ]+)/", $str, $r );
		if ( $reg ) {
			return array_combine( $r[1], $r[2] );
		}
		return false;
	}
}