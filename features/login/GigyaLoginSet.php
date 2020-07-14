<?php

namespace Gigya\WordPress;

use Gigya\CMSKit\GigyaCMS;
use GigyaLogin_Widget;

/**
 * @file
 * GigyaLoginSet.php
 * Adds Gigya login option to WP login/register form.
 */
class GigyaLoginSet {
	private $login_options;
	private $global_options;

	public function __construct() {

		// Gigya configuration values.
		$this->login_options  = get_option( GIGYA__SETTINGS_LOGIN );
		$this->global_options = get_option( GIGYA__SETTINGS_GLOBAL );

		// Load custom Gigya login script.
		wp_enqueue_script( 'gigya_login_js', GIGYA__PLUGIN_URL . 'features/login/gigya_login.js' );
		wp_enqueue_style( 'gigya_login_css', GIGYA__PLUGIN_URL . 'features/login/gigya_login.css' );

	}

	/**
	 * Initialize Gigya login block at Wp login/register.
	 */
	function init() {

		$params = $this->getParams();

		// Load params to be available to client-side script.
		wp_localize_script( 'gigya_login_js', 'gigyaLoginParams', $params );

	}

	/**
	 * Generate the parameters for the login plugin.
	 * @return array
	 */
	public function getParams() {

		// Parameters to be sent to the DOM.
		$params = array(
				'actionLogin'       => 'gigya_login',
				'actionCustomLogin' => 'custom_login',
				'redirect'          => _gigParam( $this->login_options, 'redirect', user_admin_url() ),
		);

		$params['ui']                  = array();
		$params['ui']['showTermsLink'] = false;
		$params['ui']['version']       = 2;

		if ( ! empty ( $this->login_options['width'] ) ) {
			$params['ui']['width'] = $this->login_options['width'];
		}
		if ( ! empty ( $this->login_options['height'] ) ) {
			$params['ui']['height'] = $this->login_options['height'];
		}
		if ( ! empty ( $this->login_options['showTermsLink'] ) ) {
			$params['ui']['showTermsLink'] = $this->login_options['showTermsLink'];
		}
		if ( ! empty ( $this->login_options['enabledProviders'] ) ) {
			$params['ui']['enabledProviders'] = $this->login_options['enabledProviders'];
		}
		if ( ! empty ( $this->login_options['buttonsStyle'] ) ) {
			$params['ui']['buttonsStyle'] = $this->login_options['buttonsStyle'];
		}
		if ( ! empty ( $this->login_options['advancedLoginUI'] ) ) {
			$arr = gigyaCMS::parseJSON( $this->login_options['advancedLoginUI'] );
			if ( ! empty( $arr ) ) {
				foreach ( $arr as $key => $val ) {
					$params['ui'][$key] = $val;
				}
			}
		}
		if ( ! empty ( $this->login_options['advancedAddConnectionsUI'] ) ) {
			$arr = gigyaCMS::parseJSON( $this->login_options['advancedAddConnectionsUI'] );
			if ( ! empty( $arr ) ) {
				foreach ( $arr as $key => $val ) {
					$params['addConnection'][$key] = $val;
				}
			}
		}

		// Let others plugins to modify the login parameters.
		$params = apply_filters( 'gigya_login_params', $params );

		return $params;
	}

	/**
	 * Set the Gigya's login and register default position.
	 *
	 * @return string
	 */
	public function setDefaultPosition() {
		// Get the reactions widget content.
		$args = array(
				'before_widget' => '<div class="widget">',
				'after_widget'  => "</div>",
				'before_title'  => '<h2 class="widgettitle">',
				'after_title'   => '</h2>'
		);

		// Add param to instance.
		$instance = array(
				'params' => $this->getParams()
		);

		// Get the widget.
		$widget = new GigyaLogin_Widget();
		return $widget->getContent( $args, $instance );
	}
}