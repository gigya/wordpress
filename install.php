<?php

/**
 * Install.
 */
class GigyaInstall {

	/**
	 * Constructor.
	 */
	public function __construct() {

		// The default behavior of WP is to load all options with autoload='yes'
		// on each request. This behavior can not be change in update_option() function.
		// So, on installation we initialize our records with the desired value of autoload.
		$this->global_options = get_option( GIGYA__SETTINGS_GLOBAL );
		if ( empty ( $this->global_options ) ) {
			add_option( GIGYA__SETTINGS_GLOBAL, array(), '', 'yes' );
		}

		$this->login_options = get_option( GIGYA__SETTINGS_LOGIN );
		if ( empty ( $this->login_options ) ) {
			add_option( GIGYA__SETTINGS_LOGIN, array(), '', 'yes' );
		}

		$this->share_options = get_option( GIGYA__SETTINGS_SHARE );
		if ( empty ( $this->share_options ) ) {
			add_option( GIGYA__SETTINGS_SHARE, array(), '', 'no' );
		}

		$this->comments_options = get_option( GIGYA__SETTINGS_COMMENTS );
		if ( empty ( $this->comments_options ) ) {
			add_option( GIGYA__SETTINGS_COMMENTS, array(), '', 'no' );
		}

		$this->reactions_options = get_option( GIGYA__SETTINGS_REACTIONS );
		if ( empty ( $this->reactions_options ) ) {
			add_option( GIGYA__SETTINGS_REACTIONS, array(), '', 'no' );
		}

		$this->gm_options = get_option( GIGYA__SETTINGS_GM );
		if ( empty ( $this->gm_options ) ) {
			add_option( GIGYA__SETTINGS_GM, array(), '', 'no' );
		}

		$this->feed_options = get_option( GIGYA__SETTINGS_FEED );
		if ( empty ( $this->feed_options ) ) {
			add_option( GIGYA__SETTINGS_FEED, array(), '', 'no' );
		}

	}

	public function upgrade() {

		// Load v4.0 options
		$old = get_option( 'gigya_settings_fields' );
		if ( ! empty( $old ) ) {

			// Update new structure with
			// old values if exist.
			$this->upgradeGlobal( $old );
			$this->upgradeLogin( $old );
			$this->upgradeShare( $old );
			$this->upgradeComments( $old );
			$this->upgradeReactions( $old );
			$this->upgradeGamification( $old );

		}

		// Delete old Settings.
//		delete_option( 'gigya_settings_fields' );

		// Upgrade widgets.
		$this->upgradeWidgets();

	}

	/**
	 * Upgrade Global options.
	 *
	 * @param $old
	 */
	private function upgradeGlobal( $old ) {

		// Update old (v4.0) global options if exist.
		$this->setVar( $this->global_options, 'api_key', $old['api_key'] );
		$this->setVar( $this->global_options, 'api_secret', $old['secret_key'] );
		$this->setVar( $this->global_options, 'data_center', $old['data_center'] );
		$this->setVar( $this->global_options, 'providers', $old['providers'] );
		$this->setVar( $this->global_options, 'lang', $old['lang'] );
		$this->setVar( $this->global_options, 'google_analytics', $old['google_analytics'] );
		$this->setVar( $this->global_options, 'debug', $old['gigya_debug'] );

		update_option( GIGYA__SETTINGS_GLOBAL, $this->global_options );
	}

	/**
	 * upgrade Login options.
	 *
	 * @param $old
	 */
	private function upgradeLogin( $old ) {

		// Update old (v4.0) login options if exist.
		$login_options['mode'] = ! empty( $old['login_plugin'] ) ? 'wp_sl' : 'wp_only';
		$this->setVar( $this->login_options, 'buttonsStyle', $old['login_button_style'] );
		$this->setVar( $this->login_options, 'connectWithoutLoginBehavior', $old['connect_without'] );
		$this->setVar( $this->login_options, 'width', $old['login_width'] );
		$this->setVar( $this->login_options, 'height', $old['login_height'] );
		$this->setVar( $this->login_options, 'redirect', $old['post_login_redirect'] );
		$this->setVar( $this->login_options, 'enabledProviders', $old['login_providers'] );
		$this->setVar( $this->login_options, 'showTermsLink', $old['login_term_link'] );
		$this->setVar( $this->login_options, 'registerExtra', $old['show_reg'] );

		update_option( GIGYA__SETTINGS_LOGIN, $this->login_options );
	}

	/**
	 * Upgrade Share options.
	 *
	 * @param $old
	 */
	private function upgradeShare( $old ) {

		// Update old (v4.0) share options if exist.
		if ( ! empty( $old['share_plugin'] ) && $old['share_plugin'] != 'none' ) {
			$this->share_options['on']       = 1;
			$this->share_options['position'] = $old['share_plugin'] == 'both' ? 'top' : $old['share_plugin'];
		}
		$this->setVar( $this->share_options, 'showCounts', $old['share_show_counts'] );
		$this->setVar( $this->share_options, 'layout', $old['login_button_style'] );
		$this->setVar( $this->share_options, 'image', $old['login_button_style'] );
		$this->setVar( $this->share_options, 'imageURL', $old['login_button_style'] );
		$this->setVar( $this->share_options, 'shareButtons', $old['share_providers'] );
		$this->setVar( $this->share_options, 'shortURLs', $old['short_url'] );

		update_option( GIGYA__SETTINGS_SHARE, $this->share_options );
	}

	/**
	 * Upgrade Comments options.
	 *
	 * @param $old
	 */
	private function upgradeComments( $old ) {

		// Update old (v4.0) comments options if exist.
		$this->setVar( $this->comments_options, 'on', $old['comments_plugin'] );
		$this->setVar( $this->comments_options, 'categoryID', $old['comments_cat_id'] );

		update_option( GIGYA__SETTINGS_COMMENTS, $this->comments_options );
	}

	/**
	 * Upgrade Reactions options.
	 *
	 * @param $old
	 */
	private function upgradeReactions( $old ) {

		// Update old (v4.0) reactions options if exist.
		$this->setVar( $this->reactions_options, 'on', $old['reaction_plugin'] );
		$this->reactions_options['position'] = $old['reaction_position'] == 'both' ? 'top' : $old['reaction_position'];
		$this->setVar( $this->reactions_options, 'showCounts', $old['reaction_show_counts'] );
		$this->setVar( $this->reactions_options, 'layout', $old['reaction_layout'] );
		$this->setVar( $this->reactions_options, 'buttons', $old['reaction_buttons'] );
		$this->setVar( $this->reactions_options, 'enabledProviders', $old['reaction_providers'] );
		$this->setVar( $this->reactions_options, 'countType', $old['reaction_count_type'] );
		$this->setVar( $this->reactions_options, 'multipleReactions', $old['reaction_multiple'] );

		update_option( GIGYA__SETTINGS_REACTIONS, $this->reactions_options );
	}

	/**
	 * Upgrade Gamification options.
	 *
	 * @param $old
	 */
	private function upgradeGamification( $old ) {

		// Update old (v4.0) gamification options if exist.
		$this->setVar( $this->gm_options, 'notification', $old['gamification_notification'] );

		update_option( GIGYA__SETTINGS_GM, $this->gm_options );
	}

	/**
	 * Upgrade enable widgets.
	 */
	private function upgradeWidgets() {

		// Creating new widgets based on the old ones.
		$this->upgradeWidget( 'widget_gigya', 'widget_gigya_login' );
		$this->upgradeWidget( 'widget_gigyaactivityfeed', 'widget_gigya_feed' );
		$this->upgradeWidget( 'widget_gigyafollowbar', 'widget_gigya_follow' );
		$this->upgradeWidget( 'widget_gigyagamification', 'widget_gigya_gamification' );

		// Updating the sidebars.
		$sb = get_option( 'sidebars_widgets' );
		foreach ( $sb as $k => $sidebar ) {
			foreach ( $sidebar as $widget ) {
				$brk = explode( '-', $widget );
				if ( $brk[0] = 'gigya' ) {
					$sb[$k][] = 'gigya_login-' . $brk[1];
				} elseif ( $brk[0] = 'gigyaactivityfeed' ) {
					$sb[$k][] = 'gigya_feed-' . $brk[1];
				} elseif ( $brk[0] = 'gigyafollowbar' ) {
					$sb[$k][] = 'gigya_follow-' . $brk[1];
				} elseif ( $brk[0] = 'gigyagamification' ) {
					$sb[$k][] = 'gigya_gamification-' . $brk[1];
				}

//				if ( strpos( $widget, 'gigya-' ) === 0 ) {
//					$sb[$k][$l] = str_replace( 'gigya-', 'gigya_login-', $widget );
//				} elseif ( strpos( $widget, 'gigyaactivityfeed-' ) === 0 ) {
//					$sb[$k][$l] = str_replace( 'gigyaactivityfeed-', 'gigya_feed-', $widget );
//				} elseif ( strpos( $widget, 'gigyafollowbar-' ) === 0 ) {
//					$sb[$k][$l] = str_replace( 'gigyafollowbar-', 'gigya_follow-', $widget );
//				} elseif ( strpos( $widget, 'gigyagamification-' ) === 0 ) {
//					$sb[$k][$l] = str_replace( 'gigyagamification-', 'gigya_gamification-', $widget );
//				}
			}
		}

		update_option( 'sidebars_widgets', $sb );
	}

	/**
	 * Helper - Update from old variables.
	 *
	 * @param $options
	 * @param $new_name
	 * @param $old_value
	 */
	private function setVar( &$options, $new_name, $old_value ) {
		if ( ! empty( $old_value ) ) {
			$options[$new_name] = $old_value;
		}
	}

	/**
	 * Upgrade widgets.
	 *
	 * @param $old
	 * @param $new
	 */
	private function upgradeWidget( $old, $new ) {
		$old_widget = get_option( $old );
		if ( ! empty( $old_widget ) ) {
			add_option( $new, $old_widget );
//			delete_option('$old');
		}
	}
}

