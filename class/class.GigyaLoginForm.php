<?php

/**
 * @file
 * class.GigyaLoginForm.php
 * Adds Gigya login option to WP login/register form.
 */
class GigyaLoginForm {

	public function __construct() {
	}

	/**
	 * Initialize Gigya login block at Wp login/register.
	 */
	function init() {

		// Gigya configuration values.
		$login_options  = get_option( GIGYA__SETTINGS_LOGIN );
		$global_options = get_option( GIGYA__SETTINGS_GLOBAL );

		// Check Gigya's social login is turn on and there an API key filled.
		if ( ! empty( $login_options['login_plugin'] ) && ! empty( $global_options['global_api_key'] ) ) {

			// Load custom Gigya login script.
			wp_enqueue_script( 'gigya_login_js', GIGYA__PLUGIN_URL . 'assets/scripts/gigya_login.js' );
			wp_enqueue_style( 'gigya_login_css', GIGYA__PLUGIN_URL . 'assets/styles/gigya_login.css' );

			// Parameters to be sent to the DOM.
			$params = array(
					'ajaxurl'  => admin_url( 'admin-ajax.php' ),
					'action'   => 'gigya_login',
					'redirect' => ! empty ( $login_options['login_redirect'] ) ? $login_options['login_redirect'] : user_admin_url(),
			);

			if ( ! empty ( $login_options['login_width'] ) ) {
				$params['width'] = $login_options['login_width'];
			}
			if ( ! empty ( $login_options['login_height'] ) ) {
				$params['height'] = $login_options['login_term_link'];
			}
			if ( ! empty ( $login_options['login_term_link'] ) ) {
				$params['showTermsLink'] = $login_options['login_term_link'];
			}
			if ( ! empty ( $login_options['login_providers'] ) ) {
				$params['enabledProviders'] = $login_options['login_providers'];
			}
			if ( ! empty ( $login_options['login_ui'] ) ) {
				$params['loginUI'] = json_encode( $this->advanced_values_parser( $login_options['login_ui'] ) );
			}

			// Load params to be available to client-side script.
			wp_localize_script( 'gigya_login_js', 'gigyaLoginParams', $params );
		}
	}

	/**
	 * Deal with missing fields on registration.
	 */
	private function registerExtra() {

		// Set submit button value.
		$submit_value = sprintf( __( 'Register %s' ), ! empty( $this->gigya_user['loginProvider'] ) ? ' ' . __( 'with' ) . ' ' . $this->gigya_user['loginProvider'] : '' );
		$output       = '';

		// Set form.
		$output .= '<form name="registerform" class="gigya-register-extra" id="registerform" action="' . wp_registration_url() . '" method="post">';
		$output .= '<h4 class="title">' . __( 'Please fill required field' ) . '</h4>';

		// Set form elements.
		$form               = array();
		$form['user_login'] = array(
				'type'  => 'text',
				'id'    => 'user_login',
				'label' => __( 'Username' ),
				'value' => ! empty( $this->gigya_user['nickname'] ) ? $this->gigya_user['nickname'] : '',
		);
		$form['user_email'] = array(
				'type'  => 'text',
				'id'    => 'user_email',
				'label' => __( 'E-mail' ),
				'value' => ! empty( $this->gigya_user['email'] ) ? $this->gigya_user['email'] : '',
		);

		// Render form elements.
		$output .= _gigya_form_render( $form );

		// Get other plugins register form implementation.
		$output .= do_action( 'register_form' );
		$output .= '<input type="hidden" name="gigyaUID" value="' . $this->gigya_user['UID'] . '">';

		// Add submit buttom.
		$output .= '<input type="submit" name="wp-submit" id="gigya-submit" class="button button-primary button-large" value="' . $submit_value . '">';
		$output .= '</form>';

		// Tokens replace.
		do_shortcode( $output );

		// Set a return array.
		$ret = array(
				'type' => 'register_form',
				'html' => $output,
		);

		// Return JSON to client.
		wp_send_json_success( $ret );

		exit;
	}

	/**
	 * Parser for the 'key=value | key=value' format.
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