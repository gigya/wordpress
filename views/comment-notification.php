<?php /** The variables $usersComment are available here. **/ ?>
<script type="text/javascript">
jQuery(document).ready(function() { 
	var theStatus = '<?php echo $status; ?>';
	if( typeof( gigya ) != 'undefined' ) {
		gigya.services.socialize.setStatus(gsConf, {status:theStatus});
	}
});
</script>