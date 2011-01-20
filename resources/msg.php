<?php
class GigyaSO_Msg {
	private $wp_error; 
	public function __construct($wp_error = null) {
		if(!$wp_error) $wp_error = new WP_Error();
		$this->wp_error = $wp_error;
	}
	
	public function render($params = array()) {
		if($this->wp_error) {
			$codes =  $this->wp_error->get_error_codes();
			$msg = array();
			if(in_array("error", $codes)){
				$is_error = 1;
				$msg["type"] = "error";
				$msg["text"] = $this->wp_error->get_error_message();
			}
			
			if(in_array("action", $codes)){
				$is_action = 1;
				$msg["type"] = $this->wp_error->get_error_message();
			}
			if(!$is_error && !$is_action) {
				$msg["type"] = "signin";
				$options = get_option(GIGYA_SETTINGS_PREFIX);
				$msg["url"] = $options["post_login_redirect"] == "" ? home_url() : $options["post_login_redirect"];
			}
			$msg["params"] = $params;
			echo json_encode($msg);
		}
			
	}
	
}