<?php 
if(!function_exists("_e"))
{
	echo "Error";
}
else{
?>
<div class="wrap">
	<h2><?php _e( 'Gigya Help' ); ?></h2>
	<p><?php _e( 'This page provides all the information you need to get up and running using Gigya for WordPress plugin.' ); ?></p>
	
	<h3 id="general"><?php _e( 'General' ); ?></h3>
	<p><?php printf( __( 'To get started with Gigya, see the tutorial <a href="%s">here</a>.' ), 'http://wiki.gigya.com/030_Gigya_Socialize_API_2.0/020_Socialize_Setup' ); ?>
	<h3 id="login-ui"><?php _e( 'Login UI' ); ?></h3>
	<p><?php _e( 'For both the widget and main login configuration, you must enter the conf and login_params variables generated from the component designer.  The following is an example of what you might put in the settings textarea.  <strong>Note: Don\'t copy the entire code in the configuration page. Your code should look exactly like the example shown below.</strong>' ); ?></p>
	<pre><code>
var conf=
{
	APIKey:'YOUR_API_KEY_HERE',
	enabledProviders:'facebook,myspace,google,yahoo,aol'
};
var login_params=
{
	height:400,
	width:200,
	containerID:'componentDiv',
	UIConfig:'<?php echo htmlentities( '<config><body><captions background-color="#919148"></captions><texts color="#C876FF"></texts><background background-color="#FF6D6D" frame-color="#A4FFFF"></background></body></config>'); ?>'
};</code></pre>
</div>
<?php } ?>