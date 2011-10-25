<?php
class GigyaSO_Widget extends GigyaSO_Core {
	private $options = null;
	public function __construct($options = array()){
		$this->options = $options;
	}
	
	public function render_css($css = array()){
		parent::render_css();
	?>
		<link media="all" type="text/css" href="<?php echo GIGYA_PLUGIN_URL."/css/widget.css";?>" rel="stylesheet">
	<?php 
	}
	
	public function conf_and_params($params = array()){
		$options = $this->options;
		$params = array();
		/*$login_ui = esc_attr($options['login_ui']);
		if(!empty($login_ui)) {
			echo $login_ui;
			return;
		}*/
		
		$params = array(
			"button_size"=>$button_size,
			"height"=>$height,
			"width"=>$width,
			"header_text"=>$header_text,
			"enabledProviders"=>$enabledProviders
		);
		
		
		
		$params["header_text"] = esc_attr($options['header_text']);
		
		$width = esc_attr($options['width']);
		if(empty($width) || !is_numeric($width)) {
			$width = "180";
		}
		$params["width"] = $width;

    	$height = esc_attr($options['height']);
		if(empty($height) || !is_numeric($height)) {
			$height = "50";
		}
		$params["height"] = $height;
	
    	$button_size = esc_attr($options['button_size']);
		if(empty($button_size) || !is_numeric($button_size)) {
			$button_size = "24";
		}
		$params["button_size"] = $button_size;
		
		$enabledProviders = esc_attr($options['enabledProviders']);
		if(empty($enabledProviders)) {
			$enabledProviders = "*";
		}
		$params["enabledProviders"] = $enabledProviders;
		
		$bgColor = trim($options["bgColor"]); 
		if(!empty($bgColor)) $params["bgColor"] = $bgColor; 
		
		
		parent::conf_and_params($params);
	}
	
	public function login(){ 
	?>
		
		<div id="componentDiv"></div>
		<script type="text/javascript">
		//<![CDATA[
			jQuery(document).ready(function($) {
				Gigya.Ajax.setUrl("<?php echo admin_url("admin-ajax.php"); ?>");
				Gigya.Ajax.onSignIn = function(){
					window.location.reload(true);
				};
				<?php $this->conf_and_params();?>
				gigya.services.socialize.showLoginUI(conf,login_params);
				gigya.services.socialize.addEventHandlers(conf,{
					onLogin:function(userObject){
						Gigya.Ajax.setUserObject(userObject);
						Gigya.Ajax.login();		
					}
				});
			});
		//]]>
	    </script>
	<?php
	}	
	
	public function is_logged_in($user){
		$bgColor = trim($this->options["bgColor"]); 
		$bgColor = !empty($bgColor) ? $bgColor : "#FFFFFF;"; 
	?>
		
		<div class="widget_gigya_user ui-helper-clearfix" style='background-color:<?php echo $bgColor;?>'>
			<div class="thumbnail"><?php echo get_avatar($user->ID,42,true); ?></div>
			<div class="text">
				Hello <?php echo $user->nickname; ?><br/>
				<a class="logout" href="<?php echo wp_logout_url(home_url()); ?>" title="Logout">Logout</a>
			</div>
		</div>	
	<?php
	}	
}