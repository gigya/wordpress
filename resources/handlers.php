<?php
# get config parameters for gigya, and set it as global
if(!function_exists('gigya_init_options') ) :
	function gigya_init_options(){
		global $GIGYA_OPTIONS,$wpdb;
		
		$current_options = get_option(GIGYA_SETTINGS_PREFIX); 
		
		if(!$current_options) {
			$options = array();
			# check settings of old version of plugin
			$old_options = get_option("GS for WordPress Settings");
			if($old_options) {
				if(!empty($old_options["gs-for-wordpress-api-key"])) $options["api_key"] = $old_options["gs-for-wordpress-api-key"];
				if(!empty($old_options["gs-for-wordpress-secret-key"])) $options["secret_key"] = $old_options["gs-for-wordpress-secret-key"];
				if(!empty($old_options["gs-for-wordpress-post-login-redirect"])) $options["post_login_redirect"] = $old_options["gs-for-wordpress-post-login-redirect"]; 
				if(!empty($old_options["gs-for-wordpress-sign-in-component-ui"])) $options["login_ui"] = $old_options["gs-for-wordpress-sign-in-component-ui"];
			}
			
			$current_options = array_merge($options,array(
				"force_email"     => "1",
				"account_linking" => "1"
			));
			update_option(GIGYA_SETTINGS_PREFIX,$current_options);
		}
		
		$GIGYA_OPTIONS = $current_options;
		
		
	}
endif;





# register js files
if(!function_exists('gigya_enque_js') ) :
	function gigya_enque_js($use_script){
		$raw_js_files = array(
			array("name"=>"jquery","is_enque"=>1,"url"=>"jquery/jquery.js","is_admin"=>0),
			array("name"=>"jquery.tmpl","is_enque"=>0,"url"=>GIGYA_PLUGIN_URL."/js/jquery.tmpl.js","is_admin"=>0),
			array("name"=>"json2","is_enque"=>1,"url"=>"json2.js","is_admin"=>0),
			array("name"=>"jquery-ui-core","is_enque"=>1,"url"=>"jquery/ui.core.js","is_admin"=>0),
			array("name"=>"jquery-ui-draggable","is_enque"=>1,"url"=>"jquery/ui.draggable.js","is_admin"=>0),
			array("name"=>"jquery-ui-resizable","is_enque"=>1,"url"=>"jquery/ui.resizable.js","is_admin"=>0),
			array("name"=>"jquery-ui-dialog","is_enque"=>1,"url"=>"jquery/ui.dialog.js","is_admin"=>0),
			array("name"=>"gigya","is_enque"=>0,"url"=>GIGYA_PLUGIN_URL."/js/gigya.js","is_admin"=>0),
			array("name"=>"gigya-socialize","is_enque"=>0,"url"=>"http://cdn.gigya.com/JS/socialize.js?apikey=".gigya_get_option("api_key"),"is_admin"=>1)
		
		);
		
		$is_admin = is_admin(); 
		if(!$use_script) {
			foreach($raw_js_files as $file) {
				if(($is_admin && $file["is_admin"]) || !$is_admin) {
					if(!$file["is_enque"]) {
						wp_register_script($file["name"],$file["url"]);		
					}
					wp_enqueue_script($file["name"]);
				}
			} 	
		} else {
			$wp_js_path = get_bloginfo('wpurl').'/'.WPINC.'/js';
			foreach($raw_js_files as $file) {
				$path = $file["is_enque"] ? "$wp_js_path/$file[url]" : $file[url]; 
				echo "<script type='text/javascript' src='$path'></script>";
			}
		}
			
	}
endif;
# get config params from gogya global options config
if(!function_exists('gigya_get_option') ) :
	function gigya_get_option($ns=null){
		global $GIGYA_OPTIONS;
		if($ns) return $GIGYA_OPTIONS[$ns];
		return !is_array($GIGYA_OPTIONS) ? array() :  $GIGYA_OPTIONS;
	}
endif;

if(!function_exists('gigya_msg') ) :
	function gigya_msg($error = null,$params = array()){
		$error = new GigyaSO_Msg($error);
		$error->render($params);
	}
endif;
#handle request for each user request to login
if(!function_exists('gigya_user_login')) :
	function gigya_user_login(){
		if(isset($_POST["userObject"]) && !empty($_POST["userObject"])) {
			$data = json_decode(stripslashes($_POST["userObject"]));
			if(is_object($data)) {
				// check if site allows registration of new users 
				if('0'== get_option( 'users_can_register' )) {
					gigya_msg(new WP_Error('error',__("New user registration is currently disabled for this site")));
					die();
				} else {
					$user = new GigyaSO_User($data);
					switch($_POST["actionType"]) {
						case "register-email": 
							$login = $user->register_email($_POST["email"]);
						break;
						case "link-account":
							$login = $user->link_account($_POST["email"],$_POST["password"]);	
						break;
						default: 
							$login = $user->login();
					}
					
					if(is_wp_error($login)) {
	   					gigya_msg($login,array("api_key"=>$user->api_key,"secret_key"=>$user->secret_key,"force_email"=>$user->force_email ? true : false,"account_linking"=>$user->account_linking ? true : false));
	   					die();
					} else {
						gigya_msg();		
					}
				}
			} else {
				gigya_msg(new WP_Error('error',__("Gigya Error")));
				die();
			}
		}
				
		die();
	}
endif;

if(!function_exists('gigya_notify_user_login')) :
	function gigya_notify_user_login($user_name){
		$user = get_userdatabylogin($user_name);
		GigyaSO_Util::notify_login($user->ID);
	}
endif;


if(!function_exists('gigya_notify_user_logout')) :
	function gigya_notify_user_logout($user_id){
		if($user_id) {
			GigyaSO_Util::notify_logout($user_id);
		}
	}
endif;

if(!function_exists('gigya_delete_account')) :
	function gigya_delete_account($user_id){
		if($user_id) {
			GigyaSO_Util::delete_account($user_id);
		}
	}
endif;

if(!function_exists('gigya_user_profile_extra')) :
	function gigya_user_profile_extra($user) {
	?>
		<h3><?php _e("Manage social connection", "blank"); ?></h3>
		<table class="form-table">
			<tr>
				<th></th>
				<td><div id="gigya-div-connect"></div></td>
			</tr>
		</table>
	 	<script type="text/javascript" lang="javascript">
		 	var conf = {   
				APIKey:'<?php echo gigya_get_option("api_key");?>'  
			};  
	
		 	gigya.services.socialize.showAddConnectionsUI(conf, {   
		 		height:65,
		 		width:175,
		 		showTermsLink:false,
		 		hideGigyaLink:true,
		 		useHTML:true,
		 		containerID: "gigya-div-connect"
		 	});  
	 	</script>
	<?php 
	}
endif;

if(!function_exists('gigya_get_first_image')) :
	function gigya_get_first_image($post) {
		$first_img = "";
		$output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i',$post->post_content,$matches);
		$first_img = $matches[1][0];
		if(empty($first_img))
			$first_img = get_bloginfo('wpurl').'/'.WPINC.'/images/blank.gif';
		return $first_img;
	}
endif;

if(!function_exists('gigya_share_plugin')) :
	function gigya_share_plugin($content){
		global $post;
		if(gigya_get_option("share_plugin")==1){
			$id = $post->ID;		
			$permalink = get_permalink($id);
			$title =  htmlspecialchars_decode(esc_js($post->post_title));//esc_js($post->post_title);
			$content .= "<script type='text/javascript'>";
			$content .= "var act$id = new gigya.services.socialize.UserAction();";
			$content .= "act$id.setUserMessage('');";
			$content .= "act$id.setTitle('".$title."');";
			
			$defaultContent = htmlspecialchars_decode(esc_js("Read more on ". get_bloginfo("name")));
			 
			 
			$content .= "act$id.setDescription('".$defaultContent."');";
			$content .= 'act'. $id . '.setLinkBack("'. $permalink .'");';
			$content .= 'act'. $id . '.addActionLink("Read more" ,"'.  $permalink .'");';	
			
			
			$first_img_url = gigya_get_first_image($post);
			
			if(empty($first_img_url)) $first_img_url = get_bloginfo('wpurl').'/'.WPINC.'/images/blank.gif';
			
			$content .= "var image$id = {src:'$first_img_url',href:'$permalink',type:'image'};";
			$content .= "act$id.addMediaItem(image$id);";	
					
			$content .= "var share_params$id ={userAction: act$id,showEmailButton: true	,showMoreButton: true ,operationMode: 'autoDetect'};";
			$content .= "function ShowGSShareUI(share_params){gigya.services.socialize.showShareUI({APIKey:'".gigya_get_option("api_key")."'},share_params);}</script>";
			$content .= "</script>";
			
			$content .= '<div><a href="javascript:void(0)" onclick="ShowGSShareUI('. share_params. $id .');" ><img id="gigBtnShare" src="'. GIGYA_PLUGIN_URL . '/resources/ShareButton.png" /></a></div>';
		}
		return $content;
	}
endif;

if(!function_exists('gigya_update_avatar_image')) :
	function gigya_update_avatar_image($avatar,$id_or_email, $size, $default, $alt ){
		if( is_object( $id_or_email))
			$id_or_email = $id_or_email->user_id;
			
		if(is_numeric($id_or_email)) {
			$thumb = get_user_meta($id_or_email,"avatar",1);
			if(!empty($thumb)) {
				$avatar = preg_replace( "/src='*?'/", "src='$thumb'", $avatar );
			}
		}
		
		return $avatar;
	}
endif;	