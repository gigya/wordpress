<?php 
$settings = $this->data->getSettings();
$apiKey = $settings['gs-for-wordpress-api-key'];
?>
<link rel="stylesheet" href="<?php echo $this->info->pluginUrl; ?>/resources/gs-for-wordpress.css?ver=<?php echo $this->info->version; ?>" type="text/css" media="" />
<script type="text/javascript" src="<?php echo $this->info->socializeUrl; ?>"></script>
<script type="text/javascript">
	var gigya_blog_homepage =  '<?php echo site_url('/'); ?>';
	var gigyaSocializeGeneralConfiguration = { 
		APIKey: '<?php echo $apiKey; ?>' 
	};
</script>
<?php
if (is_user_logged_in() && $this->user->hasGigyaConnection()) {
    $user = wp_get_current_user();
    $commentId = get_usermeta($user->ID, $this->user->_meta_RecentCommentPostedId);
    if (! empty($commentId)) {
        delete_usermeta($user->ID, $this->_metaRecentCommentPostedId);
        $usersComment = get_comment($commentId);
        $text = __('I just commented on %s: %s (%s)');
        if ('' != $settings['gs-for-wordpress-status-update-via']) {
            $text .= ' via '.wp_specialchars($settings['gs-for-wordpress-status-update-via']);
        }
        $status = htmlentities(sprintf($text, get_bloginfo(), html_entity_decode(get_comment_link($usersComment)), get_the_title($usersComment->comment_post_ID)));
        include ($this->info->pluginFolder . '/views/comment-notification.php');
    }
}

if ($_GET['just-logged-out'] == '1') {
    include ($this->info->pluginFolder . '/views/logmeout.php');
}
?>
