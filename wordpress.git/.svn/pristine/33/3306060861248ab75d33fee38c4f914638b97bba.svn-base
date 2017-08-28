<?php
class GigyaSO_Widget extends GigyaSO_Core {
	private $options = null;
	public function __construct($options = array()){
		$this->options = $options;
		$this->cmpId = generate_random_div_id();
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
		
		$params["container_id"] = $this->cmpId ;
		
		parent::conf_and_params($params);
	}
	
	public function login(){ 
	?>
		
		<div id="<?php echo $this->cmpId; ?>"></div>
		<script type="text/javascript">
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
				<a id="gigya-logout" class="logout" href="#" title="Logout">Logout</a>
				<script type="text/javascript">
				//<![CDATA[
				jQuery(document).ready(function($) {
					$("#gigya-logout").click(function(){
						var win = window;
						gigya.socialize.logout({
							callback: function(){
								var iframe = $("<iframe src='<?php echo wp_logout_url(); ?>'/>").hide(); 
								iframe.appendTo("body")
								iframe.load(function(){
									win.location.href = "<?php echo home_url()?>";
									iframe.destroy();									
								});								 
							}
						});

						return false;
					});
				});
				//]]>
	    		</script>	
			</div>
		</div>	
	<?php
	}	
}

class GigyaFollowBar_Widget extends GigyaSO_Core {
	private $options = null;
	public function __construct($options = array()){
		$this->options = $options;
		$this->cmpId = generate_random_div_id();
	}
	
	public function render(){
		$options = $this->options;
		
		$buttons = empty($options['buttons']) ? get_followbar_default_buttons() : $options['buttons'];
		$icon_size = $options['iconSize'];
		$layout = $options['layout'];
	
		if(empty($icon_size) || !is_numeric($icon_size)) $icon_size = "32";
		if(empty($layout) || $layout!="vertical") $layout = "horizontal";
	
		?>
		
		<div id="<?php echo $this->cmpId; ?>"></div>
		<script type="text/javascript">
		//<![CDATA[
			jQuery(document).ready(function($) {
				var params = {
					"containerID": "<?php echo $this->cmpId;?>",
					"buttons"  : <?php echo $buttons;?>,
					"iconSize" : <?php echo $icon_size;?>
				}
				gigya.services.socialize.showFollowBarUI(params);
			});
		//]]>
	    </script>
	<?php
		
	}	
}

class GigyaActivityFeed_Widget extends GigyaSO_Core {
	private $options = null;
	public function __construct($options = array()){
		$this->options = $options;
		$this->cmpId = generate_random_div_id();
	}
	
	public function render(){
		$options = $this->options;
		
		$feed_id = empty($options['feed_id']) ? false : $options['feed_id'];
		$initial_tab = empty($options['initial_tab']) ? "everyone" : $options['initial_tab'];
		
		?>
		
		<div id="<?php echo $this->cmpId; ?>"></div>
		<script type="text/javascript">
		//<![CDATA[
			jQuery(document).ready(function($) {
				var params = {
					"containerID": "<?php echo $this->cmpId;?>",
					"initialTab"  : "<?php echo $initial_tab;?>",
					"feedID" : <?php echo (!$feed_id) ? "false" : "'".$feed_id."'";?>,
					"width"  : "100%"
				};

				gigya.socialize.showFeedUI(params);
			});
		//]]>
	    </script>
	<?php
		
	}	
}