<?php
	require_once(GIGYA_PLUGIN_PATH.'/resources/login.php');
	$gigya_login = new GigyaSO_Login();
	$gigya_login->render_js();
	$gigya_login->render_css();
	$gigya_login->render_tmpl();
?>

<script type="text/javascript">
//<![CDATA[
jQuery(document).ready(function($) {
	// Login Dom Manipulation - Add gigya html panel
	(function(elem){
		elem.wrap("<div class='login-panel login-panel-wp'></div>")
		.after($("#nav")).parent()
		.after("<div class='login-panel login-panel-gigya'><form id='componentDiv'></form>")
		.after("<div class='login-sep-text float-left'><h3>OR</h3></div>");
	}($("#loginform").length > 0 ? $("#loginform") : $("#registerform")));
	// Set Ajax Url
	Gigya.Ajax.setUrl("<?php echo admin_url("admin-ajax.php"); ?>");
	<?php 
		$gigya_login->logout();
		$gigya_login->login(); 
	?>
});
//]]>
</script>

