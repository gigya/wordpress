<?php 
	global $post;
	$post_id = get_the_ID();
	$permalink = get_permalink($post_id);
	$title =  htmlspecialchars_decode(esc_js(get_the_title()));
	$api = gigya_get_option("api_key");
	
	$comments = apply_filters("comments_plugin","",array(
		"post_id"=>$post_id,
		"permalink"=>$permalink,
		"title"=>$title,
		"api"=>$api
	));
	
	if(!empty($comments)):
		echo $comments;
	else:
		require_once(GIGYA_PLUGIN_PATH.'/resources/login.php');
		$gigya_login = new GigyaSO_Login();
		$gigya_login->render_css();
		$gigya_login->render_tmpl();
		
?>
	<div id='comments' class="gigya-comments-wrap"></div>
	<a id="comments-logout-link" href="<?php echo wp_logout_url($permalink); ?>" style="display:dnone;">Logout</a>
	<script type='text/javascript'>
	
		jQuery(function($) {
			gigya.services.socialize.addEventHandlers({
			    onLogout: function(eventObj){
			    	if(eventObj.eventName == "logout" && eventObj.source == "showCommentsUI" ) {
			    		$.post("<?php echo admin_url( 'admin-ajax.php' );?>",{
							"action": "gigya_logout_user"
						},function(r){
			    			window.location = "<?php echo $permalink; ?>";
			    		});
			    	}
				},
				onLogin: function(eventObj){
					if(eventObj.eventName == "login" && eventObj.source == "showCommentsUI") {
						Gigya.Ajax.setUserObject(eventObj);
						Gigya.Ajax.setUrl("<?php echo admin_url( 'admin-ajax.php' );?>");
						Gigya.Ajax.onSignIn = function(r){
							$("#login-dialog").dialog("close");
						};
						Gigya.Ajax.login();		
					}	
				}
			});
		});
		
		(function(){		

			<?php 
			$share_providers = gigya_get_option("comments_enable_share_providers");
			if(empty($share_providers)) $share_providers = gigya_get_field_default("comments_enable_share_providers");
			$scope = gigya_get_option("comments_enable_share_activity");
			if(empty($scope)) $scope = gigya_get_field_default("comments_enable_share_activity");
			$privacy = gigya_get_option("comments_privacy");
			if(empty($privacy)) $privacy = gigya_get_field_default("activity_privacy");
			
			?>
	    	  
	    	var params = {  
	        	// Required parameters:  
	        	categoryID: "<?php echo gigya_get_option("comments_cat_id")?>",  
	        	containerID: "comments",  
	        	streamTitle: "<?php echo $title;?>",
	        	scope      : "<?php echo $scope;?>",
	        	enabledShareProviders : "<?php echo $share_providers; ?>",  
	        	streamID: "comments-<?php echo $post_id;?>",  
	        	onCommentSubmitted: function(res){
	          		var data = {
						action: "gigya_add_comment",
						privacy: "<?php echo $privacy;?>",
						nonce : "<?php echo wp_create_nonce('gigya-comment-nonce');?>",
						comment: res.commentText,
						post_id: <?php echo $post_id;?>,
						uid    : res.user.UID
					};
	
					jQuery.post("<?php echo admin_url( 'admin-ajax.php' );?>",data,function(response) {
						
					});
	          	}
	    	};  

	    	<?php 
	    	$custom = gigya_parse_key_pair(gigya_get_option("commets_custom_code"));
			$custom = $custom ? json_encode($custom) : 0;
			if($custom): 
	    	?>
			var adParams = <?php echo $custom; ?>;
			for (var prop in adParams) {
				params[prop] = adParams[prop];
			};
			<?php endif;?>
	    	gigya.services.socialize.showCommentsUI(params);
		})();
    	
	</script>
<?php 
	endif;
?>					
			
				
		
		