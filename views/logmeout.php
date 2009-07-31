<script type="text/javascript">
	jQuery(document).ready(function() {
		if( typeof( gigya ) != 'undefined' ) {
			if( typeof( gsConf ) == 'undefined' ) {
				gigya.services.socialize.logout(conf,{});
			} else {
				gigya.services.socialize.logout(gsConf,{});
			}
		}
	});
</script>