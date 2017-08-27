<?php
class GigyaSO_Login extends GigyaSO_Core {
	public function render_css($css = array()){
		parent::render_css();
	?>
		<link media="all" type="text/css" href="<?php echo GIGYA_PLUGIN_URL."/css/login.css";?>" rel="stylesheet">
	<?php 
	}
	
	public function conf_and_params($params = array()){
		$login_ui = gigya_get_option("login_ui");
		
		if(!empty($login_ui)) {
			echo $login_ui;
			return;
		}
		parent::conf_and_params($params);
	}
	
	public function logout($params = array()){
		if(isset($_REQUEST["loggedout"])) {
			$lang = gigya_get_option("lang");
			if(empty($lang)) $lang = "en"; 
	?>
		gigya.services.socialize.logout({
			APIKey: '<?php echo gigya_get_option("api_key");?>',
			lang  : '<?php echo $lang;?>'
		});
	<?php
		}
	}
	
	public function login(){ 
	?>
		<?php $this->conf_and_params();?>
		gigya.services.socialize.showLoginUI(login_params);
		gigya.services.socialize.addEventHandlers(conf,{
			onLogin:function(userObject){
				Gigya.Ajax.setUserObject(userObject);
				Gigya.Ajax.onSignIn = function(r){
					window.document.location.href = r.url;
				};
				Gigya.Ajax.login();		
			}
		});
	<?php
	}	

}