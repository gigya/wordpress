<?php

class GigyaSO_Util {
	public static function validate_user_signature($UID, $timestamp,$signature){
		require_once(GIGYA_PLUGIN_PATH.'/sdk/GSSDK.php');
		$secret_key = gigya_get_option("secret_key");
		$is_valid = SigUtils::validateUserSignature($UID, $timestamp,$secret_key, $signature);
		if(!$is_valid)
			return new WP_Error("error","<strong>ERROR: </strong>signature is not valid");  
		       
		return 1;
	} 
	
	public static function notify_registration($user_id = 0,$uid){
		require_once(GIGYA_PLUGIN_PATH.'/sdk/GSSDK.php');
		if(!$user_id) return new WP_Error("error","Error registering to gigya, user id is missing");
		$api_key = gigya_get_option("api_key");
		$secret_key = gigya_get_option("secret_key");
		$request = new GSRequest($api_key,$secret_key,"socialize.notifyRegistration");
		$request->setParam("uid",$uid);
		$request->setParam("siteUID",$user_id);
		$response = $request->send();  
		//echo date("F j, Y, g:i a",$_SERVER['REQUEST_TIME']);     
		if($response->getErrorCode()!=0)
			return new WP_Error("error","<strong>ERROR: </strong>".$response->getErrorMessage().$uid);         
		
		do_action("notify_registration",$user_id);	
			
		return 1;
	} 
	
	public static function setUID($user_id = 0,$uid){
		require_once(GIGYA_PLUGIN_PATH.'/sdk/GSSDK.php');
		if(!$user_id) return new WP_Error("error","Error registering to gigya, user id is missing");
		$api_key = gigya_get_option("api_key");
		$secret_key = gigya_get_option("secret_key");
		$request = new GSRequest($api_key,$secret_key,"socialize.setUID");
		$request->setParam("uid",$uid);
		$request->setParam("siteUID",$user_id);
		
		$response = $request->send();  
		if($response->getErrorCode()!=0)
			return new WP_Error("error","<strong>ERROR: </strong>".$response->getErrorMessage());         
		
		do_action("setUID",$user_id);
			
		return 1;
	}
	
	public static function notify_login($user_id,$is_new_user = 0){
		require_once(GIGYA_PLUGIN_PATH.'/sdk/GSSDK.php');
		/* Set as global identification in user.php signon & signon_gigya_user */
		global $is_gigya_user;
		//global $is_new_gigya_user;
		if($user_id && !$is_gigya_user) {
			$api_key = gigya_get_option("api_key");
			$secret_key = gigya_get_option("secret_key");
			$request = new GSRequest($api_key,$secret_key,"socialize.notifyLogin");
			$request->setParam("siteUID",$user_id);
			// user not registered with gigya login widget - with regular wordpress sign up form
			if($is_new_user) $request->setParam("newUser",true);
			
			if(!$is_gigya_user) {
				$current_user = get_userdata($user_id);
			
				$userInfo = (object) array(
					"nickname"  => $current_user->user_login,
					"email"     => $current_user->user_email,
					"firstName" => $current_user->user_firstname,
					"lastName"  => $current_user->user_lastname,
					"profileURL"=> $current_user->user_url,
					"photoURL"  => gigya_get_avatar_url($user_id)
				);
				
				$userInfo = apply_filters('notify_login_user_info',$userInfo,$user_id);
			
				$request->setParam("userInfo",json_encode($userInfo));
			}
			
			$response = $request->send();
			
			if($response->getErrorCode()!=0)
				return new WP_Error("error","<strong>ERROR: </strong>".$response->getErrorMessage());

			try {
				setcookie ($response->getString("cookieName"), $response->getString("cookieValue"),0,$response->getString("cookiePath"),$response->getString("cookieDomain"));
			} catch (Exception $e) {}

			do_action("notify_login",$user_id);
			
			return 1;
			
		}
		return 0;
	}
	
	public static function notify_logout($user_id){
		require_once(GIGYA_PLUGIN_PATH.'/sdk/GSSDK.php');	
		if($user_id) {
			$api_key = gigya_get_option("api_key");
			$secret_key = gigya_get_option("secret_key");
			$request = new GSRequest($api_key,$secret_key,"socialize.logout");
			$request->setParam("uid",$user_id);
			$response = $request->send();
			if($response->getErrorCode()!=0)
				return new WP_Error("error","<strong>ERROR: </strong>".$response->getErrorMessage());
			
			do_action("notify_logout",$user_id);
			return 1;
		}
		return 0;
	}
	
	public static function delete_account($user_id){
		require_once(GIGYA_PLUGIN_PATH.'/sdk/GSSDK.php');	
		if($user_id) {
			delete_user_meta($user_id,"avatar");
			$api_key = gigya_get_option("api_key");
			$secret_key = gigya_get_option("secret_key");
			$request = new GSRequest($api_key,$secret_key,"socialize.deleteAccount");
			$request->setParam("uid",$user_id);
			$response = $request->send();
			if($response->getErrorCode()!=0)
				return new WP_Error("error","<strong>ERROR: </strong>".$response->getErrorMessage());
				
			do_action("delete_account",$user_id);	
			return 1;
		}
		return 0;
	}
}

