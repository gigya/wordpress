<?php
class GigyaSO_Core {
	public function __construct() {}
	# script tag for gigya api
	public function render_gigya_js(){
	?>
		<script type="text/javascript" src="//cdn.gigya.com/JS/socialize.js?apikey=<?php echo gigya_get_option("api_key");?>"></script>
	<?php 
	}
	# render gigya plugin css
	public function render_css(){
	?>
		<link media="all" type="text/css" href="<?php echo GIGYA_PLUGIN_URL;?>/css/gigya.css" rel="stylesheet">
		<link media="all" type="text/css" href="<?php echo GIGYA_PLUGIN_URL;?>/css/jqueryui/custom-theme/jquery-ui-1.8.7.custom.css" rel="stylesheet">
	<?php 	
	}
	# render all required javascript for plugin
	public function render_js(){
		//include GIGYA_PLUGIN_PATH.
		require_once "handlers.php";
		gigya_enque_js(1); 	
	}
	
	public function conf_and_params($params = array()){
		# return array("width"=>"","height=>"","header_text"=>"","bgColor"=>"","container_id"=>"","button_siz"=>"")
		$params = apply_filters("login_params",$params);
		
		# return @string
		$conf_and_params = apply_filters("conf_and_params_vars","");
		if(!empty($conf_and_params)) {
			echo $conf_and_params;
			return false;	
		}
		
		$header_text = (isset($params["header_text"]) ? $params["header_text"] : __("Sign in with your Social Network:"));
		
		if($params["width"]) {
			$width = $params["width"];		
		} else {
			$width = is_numeric(gigya_get_option("login_width")) ? gigya_get_option("login_width") : 345;
		}
		
		if($params["height"]) {
			$height = $params["height"];		
		} else {
			$height = is_numeric(gigya_get_option("login_height")) ? gigya_get_option("login_height") : 145;
		}
		
		$button_style = gigya_get_option("login_button_style") ? gigya_get_option("login_button_style") : gigya_get_field_default("login_button_style");
		
		$custom = gigya_parse_key_pair(gigya_get_option("login_custom_code"));
		$custom = $custom ? json_encode($custom) : 0; 
		
		
		$bgColor = isset($params["bgColor"]) && !empty($params["bgColor"])  ? $params["bgColor"] : "#FFFFFF" ;
		$term_link = gigya_get_option("login_term_link") ? gigya_get_option("login_term_link") : gigya_get_field_default("login_term_link");
		
	?>
		var conf = {};
		var login_params = {
			showTermsLink: <?php echo $term_link == "0" ? "false" : "true"; ?>,
			buttonsStyle : '<?php echo $button_style ?>',
			headerText:'<label><?php echo $header_text; ?></label>',
			height:<?php echo $height;?>,
			width:<?php echo $width; ?>,
			context:'GigLogin',
			pendingRegistration : true,
			UIConfig:'<config><body><controls><snbuttons buttonsize=\"<?php echo $params["button_size"] ? $params["button_size"] : 42 ;?>\"></snbuttons></controls><background  background-color=\"<?php echo $bgColor?>\"></background></body></config>',
			containerID: '<?php echo isset($params["container_id"]) ? $params["container_id"] : 'componentDiv' ;?>'
		};
	<?php 
		if($custom):
	?>
		var adParams = <?php echo $custom; ?>;
		for (var prop in adParams) {
            	login_params[prop] = adParams[prop];
        };
	<?php 	
		endif;
	}
	
	public function render_tmpl(){
	?>
		<script id="gigya-new-user-tmpl" type="text/x-jquery-tmpl">
    	<div id='gigya-new-user-wrap' class='float-left'>
			<h3 class='label'><?php echo __('Please provide your email address to join'); ?></h3>
			<p>
				<label><?php echo __('Email') ?><br>
					<input type='text' name='email' size='20' value='' class='input'>
				</label>
			</p>
			<div class='button-wrap'>
				<input id='gigya-new-user-button' style='width:auto;' type='button' value='<?php echo __('Register'); ?>' class='button-primary'>
			</div>
		</div>
		</script>
	
		<script id="gigya-account-linking-tmpl" type="text/x-jquery-tmpl">
		<div id='gigya-sep-wrap'>
			<div class="sep-line"></div>
			<h3>OR</h3>
			<div class="sep-line"></div>
		</div>
    	<div id='gigya-account-linking-wrap'>
			<h3 class='label'>Yes, Please link my existing account with ${user.loginProvider} for quick and secure access</h3>
			<p>
				<label>Email<br>
					<input type='text' size='20' value='' class='input' name='email'>
				</label>
			</p>
			<p>
				<label>Password<br>
					<input type='password' size='20' value='' class='input' name='password'>
				</label>
			</p>
			<div class='button-wrap'>
				<input id='gigya-new-account-button' style='width:auto;' type='button' value='Link Accounts' class='button-primary'>
			</div>
		</div>
		</script>
	
		<script id="gigya-header-tmpl" type="text/x-jquery-tmpl">
		<div id='dialog-header'>
			<div class="ui-helper-clearfix">
			<img class='thumbnail' src='${user.thumbnailURL}'/>
			<p class='text'>
				<b>Hi ${user.firstName}</b>
					{{if isEmailExist}}, The email is already used, please provide a new email or link to an existing account.{{/if}}
					{{if isNewUser}}{{/if}}
			</p>
			</div>	
		</div>
		</script>
	<?php 
	} 	
	
	public function render_profile_connect(){
	?>
		
	<?php 	
	}
}