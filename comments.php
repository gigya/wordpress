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
?>
	<div id='comments' class="gigya-comments-wrap"></div>
	<script type='text/javascript'>
		var conf_cmnts_<?php echo $post_id;?> = {
			APIKey: "<?php echo $api;?>"
    	};  
    	var params_cmnts_<?php echo $post_id;?> = {  
        	// Required parameters:  
        	categoryID: "<?php echo gigya_get_option("gigya_comments_cat_id")?>",  
        	containerID: "comments",  
        	streamTitle: "<?php echo $title;?>",  
        	streamID: "comments-<?php echo $post_id;?>",  
          	onCommentSubmitted: function(res){
          		var data = {
					action: "gigya_add_comment",
					nonce : "<?php echo wp_create_nonce('gigya-comment-nonce');?>",
					comment: res.commentText,
					post_id: <?php echo $post_id;?>,
					uid    : res.user.UID
				};

				jQuery.post("<?php echo admin_url( 'admin-ajax.php' );?>",data,function(response) {
					
				});
          	}
    	};  

    	gigya.services.socialize.showCommentsUI(conf_cmnts_<?php echo $post_id;?>,params_cmnts_<?php echo $post_id;?>);
    	
	</script>
<?php 
	endif;
?>					
			
				
		
		