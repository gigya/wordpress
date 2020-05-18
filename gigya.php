<?php
/**
 * Plugin Name: SAP Customer Data Cloud
 * Plugin URI: https://www.sap.com/products/crm/customer-data-management.html
 * Description: Allows sites to utilize the SAP Customer Data Cloud API for authentication and social network updates.
 * Version: 5.11
 * Author: SAP SE
 * Author URI: https://www.sap.com/products/crm/customer-data-management.html
 * License: Apache v2.0
 */
//
// --------------------------------------------------------------------

/**
 * Global constants.
 */
define( 'GIGYA__MINIMUM_WP_VERSION', '4.7' );
define( 'GIGYA__MINIMUM_PHP_VERSION', '5.6' );
define( 'GIGYA__VERSION', '5.11' );
define( 'GIGYA__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GIGYA__PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'GIGYA__CDN_PROTOCOL', ! empty( $_SERVER['HTTPS'] ) ? 'https://cdns' : 'http://cdn' );
define( 'GIGYA__JS_CDN', GIGYA__CDN_PROTOCOL . '.gigya.com/js/socialize.js?apiKey=' );
define( 'GIGYA__LOG_LIMIT', 50 );
define( 'GIGYA__DEFAULT_COOKIE_EXPIRATION', 1800 ); /* WordPress defaults to 172800 (48 hours) */
define( 'GIGYA__DEFAULT_REMEMBER_COOKIE_EXPIRATION', 20000000 ); /* For Remember Me sessions */
define( 'GIGYA__ERROR_UNAUTHORIZED_PARTNER', 403036 );

/**
 * Gigya constants for admin settings sections.
 */
define( 'GIGYA__SETTINGS_GLOBAL', 'gigya_global_settings' );
define( 'GIGYA__SETTINGS_LOGIN', 'gigya_login_settings' );
define( 'GIGYA__SETTINGS_FIELD_MAPPING', 'gigya_field_mapping_settings' );
define( 'GIGYA__SETTINGS_SCREENSETS', 'gigya_screenset_settings' );
define( 'GIGYA__SETTINGS_SESSION', 'gigya_session_management' );
define( 'GIGYA__SETTINGS_SHARE', 'gigya_share_settings' );
define( 'GIGYA__SETTINGS_COMMENTS', 'gigya_comments_settings' );
define( 'GIGYA__SETTINGS_REACTIONS', 'gigya_reactions_settings' );
define( 'GIGYA__SETTINGS_GM', 'gigya_gm_settings' );

/**
 * Session constants
 */
define( 'GIGYA__SESSION_DEFAULT', 0 );
define( 'GIGYA__SESSION_SLIDING', -1 );
define( 'GIGYA__SESSION_FOREVER', -2 );

/** Offline sync constants */
define( 'GIGYA__OFFLINE_SYNC_MIN_FREQ', 5 );
define( 'GIGYA__OFFLINE_SYNC_MAX_USERS', 1000 );
define( 'GIGYA__OFFLINE_SYNC_UPDATE_DELAY', 10 );

/**
 * Register activation hook
 */
register_activation_hook( __FILE__, 'gigyaActivationHook' );
function gigyaActivationHook() {
	require_once GIGYA__PLUGIN_DIR . 'install.php';
	$install = new GigyaInstall();
	$install->init();
	$install->add_gigya_caps();
}

require_once( GIGYA__PLUGIN_DIR . 'GigyaAction.php' );

/**
 * Lets start.
 */
new GigyaAction;

if ( ! function_exists( 'wp_new_user_notification' ) )
{
	$login_opts = get_option( GIGYA__SETTINGS_LOGIN );
	if ( isset($login_opts['mode']) and $login_opts['mode'] == 'raas' )
	{
		/**
		 * If we're on raas mode we disabled new user notifications from WP.
		 *
		 * @param        $user_id
		 * @param string $plaintext_pass
		 */
		function wp_new_user_notification( $user_id, $plaintext_pass = '' ) {
			/* Set default_password_nag to false, for prevent a user asked to change his password */
			update_user_option( $user_id, 'default_password_nag', false, true );
			return;
		}
	}
}

/**
 * Renders a default template
 *
 * @param $template_file
 *   The filename of the template to render.
 * @param $variables
 *   A keyed array of variables that will appear in the output.
 *
 * @return string The output generated by the template.
 */
function _gigya_render_tpl( $template_file, $variables = array() ) {
	// Extract the variables to a local namespace
	extract( $variables, EXTR_SKIP );

	// Start output buffering
	ob_start();

	// Include the template file
	if ( file_exists( GIGYA__PLUGIN_DIR . '/' . $template_file ) ) {
		include GIGYA__PLUGIN_DIR . '/' . $template_file;
	}

	// End buffering and return its contents
	return ob_get_clean();
}

//--------------------------------------------------------------------

/**
 * @param $el
 *  the element inside the form
 * @param $id
 *  the index of the element inside the form
 * @param string $name_prefix
 *
 * @return string
 */
function _gigya_element_render( $el, $id, $name_prefix = '' ) {
	$allowed_form_elements = array('checkbox', 'customText', 'hidden', 'password', 'radio', 'select', 'text', 'textarea');
	$render = '';
		if ( empty( $el['type'] ) || $el['type'] == 'markup' ) {
		$render .= $el['markup'];
	} elseif ( $el['type'] == 'table' ) {
		$el['name_prefix'] = $name_prefix;
		if ( empty( $el['name'] ) ) { /* Name field is required for the table, and it will be propagated to child elements */
			$el['name'] = $id;
		}
		$render .= _gigya_render_tpl( 'admin/tpl/table.tpl.php', $el ) . PHP_EOL;
	} elseif ( $el['type'] == 'dynamic_field_line' ) {
		/* give a name for each field */
		foreach ( $el['fields'] as $key => $field ) {
			$el['fields'][ $key ]['name'] = $name_prefix . '[' . $id . ']' . '[' . $field['name'] . ']';
		}

		$render .= _gigya_render_tpl( 'admin/tpl/dynamic-field-line.tpl.php', $el ) . PHP_EOL;
	} elseif ( in_array( $el['type'], $allowed_form_elements ) ) {
		if ( empty( $el['name'] ) ) {
			if ( ! empty( $name_prefix ) ) {
				/*
				 * In cases like on admin multi-page the element
				 * name is built from the section and the ID.
				 * This tells WP under which option to save this field value.
				 */
				$el['name'] = $name_prefix . '[' . $id . ']';
			} else {
				/* Usually the element name is just the ID */
				$el['name'] = $id;
			}
		}

		/* Add the ID value to the array */
		$el['id'] = $id;

		/* Render each element */
		$render .= _gigya_render_tpl( 'admin/tpl/formEl-' . $el['type'] . '.tpl.php', $el ) . PHP_EOL;
	}

	return $render;
}

/**
 * Render a form.
 *
 * @param        $form
 *
 * @param string $name_prefix
 *
 * @return string
 */
function _gigya_form_render( $form, $name_prefix = '' ) {
	$render = '';

	/* Inject display dependencies */
	foreach ( $form as $id => $el ) {
		if ( isset( $el['depends_on'] ) ) {
			$search = str_replace( '][', '-', $el['depends_on'][0] );
			$search = preg_replace( '/(^\[)|(\]$)/', '', $search );
			$search = preg_replace( '/[\[\]]/', '-', $search );

			$dependee_values = array_slice( $el['depends_on'], 1 );
			if ( ! empty( $form[ $search ] ) and in_array( $form[ $search ]['value'], $dependee_values ) ) {
				$form[ $id ]['display'] = true;
			}
		}
	}

	foreach ( $form as $id => $el ) {
		$render .= _gigya_element_render( $el, $id, $name_prefix );
	}

	return $render;
}

//--------------------------------------------------------------------

/**
 * Loads JSON string from file.
 *
 * @param $file | relative path from Gigya plugin root.
 *              DO NOT include filename extension.
 *
 * @return bool|string
 */
function _gigya_get_json( $file ) {
	$path = GIGYA__PLUGIN_DIR . $file . '.json';
	$json = file_get_contents( $path );
	return ! empty( $json ) ? $json : FALSE;
}

//--------------------------------------------------------------------

/**
 * Helper
 * return value for given key in input array or object
 *
 * @param array|object	$array
 * @param string		$key
 * @param string|int	$default
 * @param boolean		$obfuscate
 *
 * @return mixed $default - $array value (if $array is not empty)
 */
function _gigParam( $array, $key, $default = null, $obfuscate = false ) {
	if ( is_array( $array ) )
		$return = (isset( $array[$key] ) and ($array[$key] or $array[$key] === "0")) ? $array[$key] : $default;
	elseif ( is_object( $array ) )
		$return = (isset( $array->$key ) and ($array->$key or $array->$key === "0")) ? $array->$key : $default;
	else
		$return = $default;

	/* API secret key requires decryption */
	if ($key === 'api_secret')
	{
		$return = GigyaApiHelper::decrypt($return, SECURE_AUTH_KEY);
	}

	if ($obfuscate)
	{
		if (!strlen($return))
			$multiplier = 6;
		else
			$multiplier = strlen($return) - 4;
		$return = substr($return, 0, 2).str_repeat('*', $multiplier).substr($return, -2);
	}

	return $return;
}

/**
 * Helper
 * Returns a JSON string based on the Gigya parameters given.
 *
 * Example:
 * $params = ['a', 'b', 'c']
 * $labels = ['name1', 'name2']
 * $more_params = [['x', 'y', 'z']]
 * Returns: [{"name1":"a", "name2":"x"},{"name1":"b", "name2":"y"},{"name1":"c", "name2":"z"}]
 *
 * @param	array	$params			Array of parameters to build JSON
 * @param	array	$labels			Labels for given parameters--if only $params is given, $label should be an array of one
 * @param	array	$more_params	More parameter arrays
 *
 * @return string|false	Final JSON, or false on failure
 */
function _gigParamsBuildJson( $params, $labels, ...$more_params ) {
	if (empty($more_params))
		$more_params = array();
	if (count($labels) !== (1 + count($more_params)))
		return false;
	array_unshift($more_params, $params);

	$build_array = array();
	$json_array = array();
	foreach ($more_params as $i => $param)
	{
		$build_array[$labels[$i]] = $param;
	}
	foreach ($build_array as $label => $label_set)
	{
		foreach ($label_set as $j => $value)
		{
			$json_array[$j][$label] = $value;
		}
	}

	return json_encode($json_array);
}

function _gigParamsBuildLegacyJson($params) {
	$values = get_option( GIGYA__SETTINGS_LOGIN );

	$json_array = array();
	for ($i = 0; $i < count($params); $i++)
	{
		$prefix = _gigya_get_mode_prefix();
		if (!empty($values[$params[$i]]) or !empty($values[$prefix.$params[$i]]))
		{
			$cms_name = str_replace($prefix, '', $params[$i]);
			$gigya_name = _wp_key_to_gigya_key($cms_name);
			$json_array[$i] = array(
				'cmsName' => $cms_name,
				'gigyaName' => $gigya_name,
			);
		}
	}
	$json_array = array_values($json_array); /* Flattens the array to hide keys in JSON */

	return json_encode($json_array);
}

//--------------------------------------------------------------------

/**
 * Helper
 *
 * @param array	$array
 * @param $key
 *
 * @return integer
 */
function _gigParamDefaultOn( $array, $key ) {
	return ( isset( $array[$key] ) && $array[$key] === '0' ) ? '0' : '1';
}

/**
 * Helper
 * Flattens array into dot notation.
 *
 * @param	array	$array	Input array
 *
 * @return array
 */
function _gigArrayFlatten( $array )
{
	$iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($array));
	$result = array();
	$valueIsArray = false;
	foreach ($iterator as $leafValue) {
		$keys = array();
		foreach (range(0, $iterator->getDepth()) as $depth) {
			$key = $iterator->getSubIterator($depth)->key();
			if (gettype($key) === 'string')
			{
				$keys[] = $key;
				$valueIsArray = false;
			}
			else
				$valueIsArray = true;
		}

		$keyString = join('.', $keys);
		if (!$valueIsArray)
			$result[$keyString] = $leafValue;
		else
		{
			if (empty($result[$keyString]) or !is_array($result[$keyString]))
				$result[$keyString] = array();
			$result[$keyString][] = $leafValue;
		}
	}

	return $result;
}

// --------------------------------------------------------------------

/**
 * Helper for form formatting, check for default values and set selected values
 *     check if role belongs to default. if so set default value to checked, for all other roles set default to not-checked.
 *	   set selected value (using _gigparam )
 *
 * @param array $values - gigya login settings
 * @param string $role
 * @param string $settings_role_name
 *
 * @return bool $value
 */
function _DefaultAdminValue( $values, $role, $settings_role_name ) {
	if ( $role == 'Editor' || $role == 'Author' || $role == 'Contributor' ) {
		$value = _gigParam( $values, $settings_role_name, 1 );
	} else {
		$value = _gigParam( $values, $settings_role_name, 0 );
	}
	return $value;
}

// --------------------------------------------------------------------

/**
 * Implements _gigya_error_log from gigyaCMS().
 *
 * @param $new_log
 *
 * @internal param $log
 */
function _gigya_error_log( $new_log ) {
	/* Get global debug */
	$gigya_debug = GIGYA__API_DEBUG;
	if ( ! empty( $gigya_debug ) && is_array( $new_log ) )
	{
		// Get to existing log from DB.
		$exist_log = get_option( 'gigya_log' );

		// Initialize if there is no existing one.
		if ( ! is_array( $exist_log ) ) {
			$exist_log = array();
		}

		// Push new entries to the beginning of the array.
		foreach ( $new_log as $entry ) {
			array_unshift( $exist_log, $entry );
		}

		// Cut the array to max limit log entries.
		$log = array_slice( $exist_log, 0, GIGYA__LOG_LIMIT );

		// Update the DB with new entries.
		update_option( 'gigya_log', $log );
	}
}

// --------------------------------------------------------------------

/**
 * @param $options
 *
 * @return array
 */
function _gigya_get_session_expiration( $options ) {
	if ( ! isset( $options['remember_session_type_numeric'] ) ) {
		$options['remember_session_type_numeric'] = 1;
		$options['remember_session_duration'] = GIGYA__DEFAULT_REMEMBER_COOKIE_EXPIRATION;
	}

	return [
		'sessionExpiration'         => ( $options['session_type_numeric'] > 0 ) ? $options['session_duration'] : $options['session_type_numeric'],
		'rememberSessionExpiration' => ( $options['remember_session_type_numeric'] > 0 ) ? $options['remember_session_duration'] : $options['remember_session_type_numeric'],
	];
}

function _gigya_get_session_remember() {
	return ( ! empty( $_COOKIE[ 'gigya_remember_' . GIGYA__API_KEY ] ) );
}

function _gigya_set_session_remember( $remember ) {
	if ( ! $host = $_SERVER['SERVER_NAME'] ) {
		$host = $_SERVER['SERVER_ADDR'];
	}
	setcookie( 'gigya_remember_' . GIGYA__API_KEY, $remember, time() + YEAR_IN_SECONDS, '/', $host );
}

function _gigya_remove_session_remember() {
	if ( isset( $_COOKIE[ 'gigya_remember_' . GIGYA__API_KEY ] ) ) {
		if ( ! $host = $_SERVER['SERVER_NAME'] ) {
			$host = $_SERVER['SERVER_ADDR'];
		}
		unset( $_COOKIE[ 'gigya_remember_' . GIGYA__API_KEY ] );
		setcookie( 'gigya_remember_' . GIGYA__API_KEY, null, -1, '/', $host );
	}
}

/**
 * @param $cookie
 * @param $expiration
 */
function updateCookie( $cookie, $expiration ) {
	if ( isset( $_COOKIE[ LOGGED_IN_COOKIE ] ) ) {
		$_COOKIE[ LOGGED_IN_COOKIE ] = $cookie;
	}
}

// --------------------------------------------------------------------

/**
 * Gets RaaS or SL prefix, depending on what the user is using
 *
 * @return string
 */
function _gigya_get_mode_prefix()
{
	$login_opts = get_option( GIGYA__SETTINGS_LOGIN );
	if ( empty( $login_opts ) ) {
		return '';
	}

	if ($login_opts['mode'] == "wp_sl") {
		$prefix = "map_social_";
	} elseif ($login_opts['mode'] == "raas") {
		$prefix = "map_raas_";
	} else {
		return '';
	}
	return $prefix;
}

add_action( 'gigya_after_raas_login', 'gigyaAfterRaasLogin', 10, 2 );
function gigyaAfterRaasLogin( $gig_user, $wp_user ) {
	// Update the WP nickname from Gigya's nickname.
	if (!empty($gig_user['profile']['nickname']))
		update_user_meta( $wp_user->ID, 'nickname', $gig_user['profile']['nickname'] );
}

/**
 * Map social user fields to WordPress user fields
 *
 * @param array|GSResponse|GSObject $gigya_object
 * @param string $user_id
 *
 * @throws GigyaHookException If there are problems with the hook or data
 */
function _gigya_add_to_wp_user_meta( $gigya_object, $user_id ) {
	if ( $gigya_object instanceof GSResponse ) {
		$gigya_object = $gigya_object->getData()->serialize();
	} elseif ( $gigya_object instanceof GSObject ) {
		$gigya_object = $gigya_object->serialize();
	}

	$gigya_object = _gigArrayFlatten( $gigya_object );
	$field_mapping_opts = get_option( GIGYA__SETTINGS_FIELD_MAPPING );
	$prefix = _gigya_get_mode_prefix();
	if ( ! $prefix ) {
		return;
	}

	if ( ! empty( $field_mapping_opts['map_raas_full_map'] ) ) /* Fully customized field mapping options */ {
		/* Hook for modifying the data from Gigya before it is mapped */
		$gigya_object_orig = $gigya_object;
		try {
			$gigya_object = apply_filters( 'gigya_pre_field_mapping', $gigya_object_orig, get_userdata( $user_id ) );
			if ( array_keys( $gigya_object_orig ) != array_keys( $gigya_object ) ) {
				throw new GigyaHookException( 'Invalid data returned by the hook. Return array must have the same keys as the input array.' );
			}
		} catch ( Exception $e ) {
			throw new GigyaHookException( 'Exception while running hook. Error message: ' . $e->getMessage() );
		}

		foreach ( json_decode( $field_mapping_opts['map_raas_full_map'] ) as $meta_key ) {
			$meta_key = (array) $meta_key;
			if ( ! isset( $gigya_object[ $meta_key['gigyaName'] ] ) ) {
				$gigya_object[ $meta_key['gigyaName'] ] = '';
				/*
				 * Uncomment this line if you want to send a notice to the WordPress log about *every* field mapping failure on *every* user login/registration.
				 *
				 * trigger_error('The Gigya field '.$meta_key['gigyaName'].', specified in the field mapping, does not exist. WP user ID: '.$user_id, E_USER_NOTICE);
				 */
			}
			update_user_meta( $user_id, $meta_key['cmsName'], sanitize_text_field( $gigya_object[ $meta_key['gigyaName'] ] ) );
		}
	} elseif ( is_array( $field_mapping_opts ) ) /* Legacy field mapping options */ {
		foreach ( $field_mapping_opts as $key => $opt ) {
			if ( strpos( $key, $prefix ) === 0 && $opt == 1 ) {
				$k         = str_replace( $prefix, "", $key );
				$gigya_key = 'profile.' . _wp_key_to_gigya_key( $k );
				if ( ! isset( $gigya_object[ $gigya_key ] ) ) {
					$gigya_object[ $gigya_key ] = '';
				}
				update_user_meta( $user_id, $k, sanitize_text_field( $gigya_object[ $gigya_key ] ) );
			}
		}
	}
}

function _wp_key_to_gigya_key( $wp_key ) {
	$convert = array(
		'first_name'   => 'firstName',
		'last_name'    => 'lastName',
		'display_name' => 'nickname',
		'description'  => 'bio',
	    'profile_image' => 'photoURL'
	);
	return empty($convert[$wp_key]) ? $wp_key : $convert[$wp_key];
}

function _underscore_to_camelcase( $str ) {
	$parts = explode('_', $str);
	$string = $parts[0];
	for ( $i = 1; $i <= count($parts); $i ++ ) {
		if ($parts[$i] == 'id') {
			$string .= strtoupper($parts[$i]);
		} else {
			$string .= ucfirst( $parts[ $i ] );
		}
	}
	return $string;
}

/**
 * Check if this is Multi site setup,
 *  if multi site, check if settings were already saved once in the sub site
 *  if so set default values to main site values
 *  Once settings are saved on sub site once, they are independent from main site.
 *
 * @param string $settings_section
 *
 * @return array $values
 */
function _getGigyaSettingsValues( $settings_section ) {
	/* Get section settings for the site */
	$section_values = get_option( $settings_section );
	if ( is_multisite() ) {
		$sub_site_settings_saved = ( !empty($section_values['sub_site_settings_saved']) ) ? $section_values['sub_site_settings_saved'] : false;
		if ( $sub_site_settings_saved == true ) {
			$values = $section_values;
		} else {
			$values = get_blog_option( 1, GIGYA__SETTINGS_GLOBAL );
		}
	} else { /* This is a standard installation, so just use site values */
		$values = $section_values;
	}

	return $values;
}