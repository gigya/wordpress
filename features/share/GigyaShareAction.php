<?php

/**
 * @file
 * GigyaShareAction.php
 * An AJAX handler for login or register user to WP.
 */
class GigyaShareAction {

	public function __construct() {

		// Get settings variables.
		$this->SHARE_options = get_option( GIGYA__SETTINGS_SHARE );

	}

	/**
	 * This is Gigya login AJAX callback
	 */
	public function init() {

		global $post;
		$share = "";


		$advanced = gigya_parse_key_pair(gigya_get_option("share_advanced"));
		$advanced = $advanced ? json_encode($advanced) : 0;

		$id = $post->ID;

		$layout = gigya_get_option("share_layout");
		if (empty($layout)) {
			$layout = "horizontal";
		}

		$show_counts = gigya_get_option("share_show_counts");
		if (empty($show_counts)) {
			$show_counts = "right";
		}

		$share_buttons = trim(gigya_get_option("share_providers"));
		if (empty($share_buttons)) {
			$share_buttons = "share,facebook-like,google-plusone,twitter,email";
		}

		$privacy = gigya_get_option("share_privacy");
		if (empty($privacy)) {
			$privacy = gigya_get_field_default("activity_privacy");
		}

		$custom = gigya_get_option("share_custom");

		$share .= "<p class='gig-share-button gig-share-button-$pos' id='gig-div-buttons-$id-$pos'></p>";
		$share .= "<script language='javascript'>";
		$share .= "(function(){";
		$share .= get_user_action_embed($id);

		if (empty($custom)):
			$share .= "var params ={
						userAction:ua,
						layout    : '$layout',
						showCounts: '$show_counts',
						shareButtons:'$share_buttons', // list of providers
						containerID: 'gig-div-buttons-$id-$pos',
						privacy: '$privacy',
        				cid:''
					};";
			if ($advanced) {
				$share .= " var adParams = $advanced;
				  for (var prop in adParams) {
            				params[prop] = adParams[prop];
        		  };";
			};
			$share .= "gigya.services.socialize.showShareBarUI(params);";
		else:
			$share .= "$custom";
		endif;

		$share .= "}());";
		$share .= "</script>";

		$share = apply_filters("share_plugin", $share, array(
						"api" => gigya_get_option("api_key"),
						"post_id" => $id,
						"permalink" => get_permalink($id),
						"title" => $post->post_title,
						"first_img_url" => gigya_get_first_image($post)
				)
		);
		return $share;

	}
}