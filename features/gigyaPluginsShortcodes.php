<?php

namespace Gigya\WordPress;

use Gigya\CMSKit\GigyaCMS;

class gigyaPluginsShortcodes {


	private $logger;

	public function __construct() {
		$this->logger = new GigyaLogger();
	}

	/**
	 * @param $attrs
	 *
	 * @return string
	 */
	public function gigyaSocialLoginScode( $attrs ) {

		require_once GIGYA__PLUGIN_DIR . 'features/login/GigyaLoginSet.php';
		$login    = new GigyaLoginSet();
		$defaults = $login->getParams();
		if (empty( $attrs )) {
			$attrs = $defaults;
			if ( isset( $attrs['advanced'] ) ) {
				$advanced = gigyaCms::jsonToArray( $attrs['advanced'] );
				if ( is_array( $advanced ) ) {
					$attrs = array_merge( $attrs, $advanced );
				} else if ( is_string( $advanced ) ) {
					$this->logger->error( "Error in " . __FUNCTION__ . " shortcode advanced parameters message: " . $advanced );
				}
			}
		} else {
			$attrs = $this->attrs_to_gigya($attrs);
			// If custom attributes are passed with shortcode, use them to replace the ui array of the default ui values
			$ui_arr = $defaults['ui'];
			$ui_arr_updated = array_replace( $ui_arr, $attrs );
			$defaults['ui'] = $ui_arr_updated;
			$attrs = $defaults;
		}
		if ( ! is_user_logged_in() ) {
			$output = '<div class="gigya-login-widget"></div>';
			$output .= '<script class="data-login" type="application/json">' . json_encode( $attrs ) . '</script>';
		} else {
			$current_user = wp_get_current_user();
			$output = '<div class="gigya-wp-account-widget">';
			$output .= '<a class="gigya-wp-avatar" href="' . user_admin_url( 'profile.php' ) . '">' . get_avatar( $current_user->ID ) . '</a>';
			$output .= '<div class="gigya-wp-info">';
			$output .= '<a class="gigya-wp-name" href="' . user_admin_url( 'profile.php' ) . '">' . $current_user->display_name . '</a>';
			$output .= '<a class="gigya-wp-logout" href="' . wp_logout_url() . '">' . __( 'Log Out' ) . '</a>';
			$output .= '</div></div>';
		}
		return $output;
	}

	public function gigyaRaas( $attrs, $content, $tag ) {
		require_once GIGYA__PLUGIN_DIR . 'features/raas/GigyaRaasSet.php';
		$login = new GigyaRaasSet();
		$login->init();
		$output = "";
		if ($tag == "gigya-raas-login" && !is_user_logged_in()) {
			$output .= '<div class="gigya-raas-widget">';
			$output .= '<a href="wp-login.php">' . __('Login') . '</a> | ';
			$output .= '<a href="wp-login.php?action=register">' . __('Register') . '</a>';
			$output .= '</div>';
		} elseif ($tag == "gigya-raas-profile" && is_user_logged_in()) {
			$current_user = wp_get_current_user();
			$output .= '<div class="gigya-wp-account-widget">';
			$output .= '<a class="gigya-wp-avatar" href="' . user_admin_url( 'profile.php' ) . '">' . get_avatar( $current_user->ID ) . '</a>';
			$output .= '<div class="gigya-wp-info">';
			$output .= '<a class="gigya-wp-name" href="' . user_admin_url( 'profile.php' ) . '">' . $current_user->display_name . '</a>';
			$output .= '<a class="gigya-wp-logout" href="' . wp_logout_url() . '">' . __( 'Log Out' ) . '</a>';
			$output .= '</div></div>';
		}

		return $output;
	}

	public function attrs_to_gigya( $attrs ) {
		foreach ( $attrs as $key => $val ) {
			$new_key = _underscore_to_camelcase($key);
			unset($attrs[$key]);
			$attrs[$new_key] =  _underscore_to_camelcase($val);
		}
		return $attrs;
	}

	private function processAttrs( $attr, $defaults ) {
		if ( empty( $attrs ) ) {
			$attrs = $defaults;
			if ( isset( $attrs['advanced'] ) ) {
				$advanced = gigyaCms::jsonToArray( $attrs['advanced'] );
				if ( is_array( $advanced ) ) {
					$attrs = array_merge( $attrs, $advanced );
				} else if ( is_string( $advanced ) ) {
					$this->logger->error( "Error in " . __FUNCTION__ . " shortcode advanced parameters message: " . $advanced );
				}
			}
		} else {
			$attrs = $this->attrs_to_gigya( $attrs );
			$attrs = array_merge( $defaults, $attrs );
		}

		return $attrs;
	}
} 
