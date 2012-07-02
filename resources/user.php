<?php

class GigyaSO_User {
	const GIGYA_ACTION_EMAIL_EXIST = "email_exist";
	const GIGYA_ACTION_EMAIL_REQUIRED = "new_user_email_required";
	# user data parameters
	private $data = null;
	# user already registered to site with gigya
	private $is_gigya = false;
	# user already logged in to site
	private $is_logged_in = false;
	private $user_name = 0;
	private $email = 0;
	private $password = 0;
	private $error = null;
	private $force_email = 0;
	private $account_linking = 0;
	private $api_key = 0;
	private $secret_key = 0;
	private $uid = null;
	
	private function is_user(){
		// check if already registered with gigya
		if(!empty($this->data->user->isSiteUID)) return 1;
		// check if user already registered in old version
		global $wpdb;
		$loginProvider = '_gsforwordpressuid'.sanitize_title_with_dashes($this->data->provider);
		$user_id = $wpdb->get_var($wpdb->prepare( "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value = %s",$this->data->UID,$loginProvider));
		if(!$user_id) return 0;
		$gigya = GigyaSO_Util::setUID($user_id,$this->data->UID);
		if(!is_wp_error($gigya)) {
			$this->data->UID = $user_id;
			$thumbnail = get_user_meta($user_id,"_gigya_socialize_thumbnail_url",1);
			if($thumbnail && !empty($thumbnail)) update_user_meta($user_id,"avatar",$thumbnail);
			return 1;
		}
		return 0;
	} 
	
	public function __construct($data=null) {
		global $gigya_user_data;
				$gigya_user_data = $data;
		$this->data = $data;
		$this->is_gigya     = $this->is_user();
		$this->uid          = $data->UID;
		$this->is_logged_in = is_user_logged_in(); 
		$options = get_option(GIGYA_SETTINGS_PREFIX);
		$this->force_email = $options["force_email"] == 1 ;
		$this->account_linking = $options["account_linking"] == 1 ;
		$this->api_key = !empty($options["api_key"]) ?  $options["api_key"] : 0;
		$this->secret_key = !empty($options["secret_key"]) ?  $options["secret_key"] : 0;
		$this->is_multisite = is_multisite();
		$this->user_id = 0;
		
		if($this->is_multisite):
			global $blog_id;
			$this->blog_id = $blog_id;		
		else:
			$this->blog_id = 0;
		endif;
		 
		function gigya_validate_email($valid,$email,$error){
			if($valid) {
				return $valid;
			} else {
				return new WP_Error('error',"<strong>ERROR: </strong>".$error);
			} 
		}
		add_filter('is_email','gigya_validate_email',0,3);
	}
	
	public function __get($key){
		if(property_exists($this,$key)) {
            return $this->$key;
        }
    } 
    
    private function signout(){
    	$user = wp_get_current_user();
    	if($user) {
    		wp_logout();
    		do_action("wp_logout",$user->ID);	
    	}
    }
    
    private function signon($user_id,$user_name,$password){
    	// logout user if logged in to site
    	global $is_gigya_user;
    	$is_gigya_user = true;
    	if($this->is_logged_in) $this->signout();	
    	// login
    	$login = wp_set_current_user($user_id);
		//$login = wp_signon(array("user_login"=>$user_name,"user_password"=>$password,"remember"=>true),false);
		if (is_wp_error($login)) 
			return new WP_Error('error',$login->get_error_message());
		wp_set_auth_cookie($user_id);		
		return $login;
    } 
    
     private function signon_gigya_user(){
     	global $is_gigya_user;
    	$is_gigya_user = true;
    	// logout user if logged in to site
    	if($this->is_logged_in) $this->signout();
    	// get user data from site by siteUID
    	$user = get_userdata($this->uid);
    	if(!$user) {
    		gigya_delete_account($this->uid);
    		return new WP_Error("error",__("<strong>ERROR: </strong> Can't find user in site, please try again"));
    	}
		$login = wp_set_current_user($user->ID);
		if(!$login)
    		return new WP_Error("error",__("<strong>ERROR: </strong> Can't login to site"));
		wp_set_auth_cookie($user->ID);
		do_action('wp_login',$user->user_login);
    	return 1;
    } 
    
	/**
 	* Start the proccess of user registration - validate and execute
 	*
 	* @return int|WP_Error Bool 1 if success or a WP_Error object if the user could not be created.
 	*/
    public function login() {
		require_once (ABSPATH.WPINC.'/registration.php');
		// check if user has siteUID, if exist user registered to site and gigya and can login
		if($this->is_gigya) {
			$signon = $this->signon_gigya_user();
			if(is_wp_error($signon)) return $signon;
			return 1;
		};			
		
		// check if email exist in social user obj
		$email = $this->data->user->email;
		// return user id if exist
		$is_email_exist = empty($email) ? 0 : email_exists($email);
		if($this->is_multisite && $is_email_exist) {
			 $blogs = get_blogs_of_user($is_email_exist);
			 $is_exist_in_blog = 0;
			 if($blogs) {
			 	foreach($blogs as $blog) {
			 		if($this->blog_id == $blog->userblog_id) {
			 			$is_exist_in_blog = 1;			
			 		}
			 	}
			 }
			 if(!$is_exist_in_blog) {
				$this->user_id = $is_email_exist; 
			 	$is_email_exist = 0;
			 }			
		}
//		// if exist - need to ask user if already registered - create new account or link account
		if($is_email_exist) {
			if($this->force_email) 
				return new WP_Error('action',self::GIGYA_ACTION_EMAIL_EXIST);	
			return $this->link_account($email,"",1);
		} else {
			if(empty($this->data->user->email) && $this->force_email) {
				return new WP_Error('action',self::GIGYA_ACTION_EMAIL_REQUIRED);	
			} else {
				// if user id exist add it to current blog if not add a new user
				if($this->user_id) {
					return $this->add_user_to_blog();					
				} else {
					return $this->add_new_user($this->data->user->email);	
				}
				
			}
		}
		return 1;
	}
	/**
 	* Register new user with email account entered  by user
 	*
 	* @param string $email Email.
 	* @return int|WP_Error Bool 1 if success or a WP_Error object if the user could not be created.
 	*/
	public function register_email($email){
		// check if email address is valid
		$is_email = is_email($email);
		if(is_wp_error($is_email)) 
			return $is_email;
		// check if email doesnt belong to site user
		$user_id = email_exists($email);
		if($user_id) 
			return new WP_Error('error',"<strong>ERROR: </strong>".__('The email you provided is already used, Please provider a different email or link to an existing account'));
		// add new user to site	
		$user = $this->add_new_user($email);
		if(is_wp_error($user)) 
			return $user;
				
		return 1;
	}
	/**
 	* Link social account to wordpress account
 	*
 	* @param string $email Email.
 	* @param string $password Password.
 	* @return int|WP_Error Bool 1 if success or a WP_Error object if the user could not be created.
 	*/
	public function link_account($email,$password="",$force_login = 0){
		# Before account linking validate if email is valid
		$is_email = is_email($email); 
		if(is_wp_error($is_email)) return $is_email;
		// after email validation, check if it exist in the DB to retrieve userId
		$user_id = email_exists($email);
		if(!$user_id) 
			return new WP_Error('error',"<strong>ERROR: </strong>".__('That E-mail doesn\'t belong to any registered users on this site'));
		// retrive user id
		$user = get_userdata($user_id);
		if(!$user) 
			return new WP_Error('error',"<strong>ERROR: </strong>".__('Username Not In Use!'));
		// login user to site
		$gigya = GigyaSO_Util::setUID($user_id,$this->uid);
		if(is_wp_error($gigya))
			return $gigya;
		if(!$force_login) {
			$login = $this->signon($user_id,$user->user_login,$password);	
			if (is_wp_error($login)) 
				return new WP_Error('error',$login->get_error_message());
		} else {
			$login = wp_set_current_user($user->ID);
			if(!$login)
    			return new WP_Error("error",__("<strong>ERROR: </strong> Can't login to site"));
			
    		wp_set_auth_cookie($user->ID);
			do_action('wp_login',$user->user_login);
    		return 1;	
		}	
	}
	
	private function add_user_to_blog($notify = 1) {
		if($this->blog_id && $this->user_id) {
			switch_to_blog($this->blog_id);
			$role = get_option("default_role");			
			if($role)
				add_user_to_blog($this->blog_id,$this->user_id,$role);
				//restore_current_blog();
			# regiter user with gigya
			if($notify) {
				$gigya = GigyaSO_Util::notify_registration($this->user_id,$this->uid);	
				return $gigya;
			}	
		}
	}
	
	private function add_new_user($email="") {
		global $is_gigya_user;
    	$is_gigya_user = true;
		$user_name = $this->generate_user_name($this->data->user->nickname);
		if(is_wp_error($user_name)) 
			return $user_name; 
		$email = $this->generate_email($email);
		if(is_wp_error($email)) 
			return $email;
		$password = $this->generate_password();
		if(is_wp_error($password)) 
			return $password;
		//prepere user data
		$user_data = array(
			// Required
			'user_login'   => $user_name,
			'user_pass'    => $password,
			'user_email'   => $email,
			// Not Required
			'user_url' 	   => $this->data->user->profileURL,
			'display_name' => $this->data->user->nickname,
			'nickname'     => $this->data->user->nickname,
			'first_name'   => $this->data->user->firstName,
			'last_name'    => $this->data->user->lastName
		);
		
		# add new user to db
		$this->user_id = wp_insert_user($user_data);
		if(is_wp_error($this->user_id)) 
			return new WP_Error('error',"<strong>ERROR: </strong>".$this->user_id->get_error_message());
		# add user to blog if multisite support
		$this->add_user_to_blog(0);	
		# add user meta
		update_user_meta($this->user_id,"avatar",$this->data->user->thumbnailURL);
		# regiter user with gigya
		$gigya = GigyaSO_Util::notify_registration($this->user_id,$this->uid);
		if(is_wp_error($gigya)) {
			wp_delete_user($this->user_id);
			return $gigya;
		}
		# login user to site
		$login = $this->signon($this->user_id,$user_name,$password);
		if (is_wp_error($login)) 
			return new WP_Error('error',$login->get_error_message());
	}
	
	private function generate_email($email = "") {
		require_once (ABSPATH.WPINC.'/registration.php');
		try {
			// user doesnt have an email address and system auto generate email
			if(empty($email)) {
				$email = md5(uniqid(wp_rand(10000,99000)))."@site.com";
				while(email_exists($email)) {
					$email = md5(uniqid(wp_rand(10000,99000)))."@site.com";
				}
			}
			//fix email format		
			$email = sanitize_email($email);
			//validate email
			$is_email = is_email($email); 
			if(is_wp_error($is_email)) 
				return $is_email;
			
			return $this->email = $email;
			
		} catch(Exception $e) {
			return new WP_Error('error',"<strong>ERROR: </strong>".__('Error creating  random email'));	
		}
	}
	
	private function generate_user_name($user_name = "") {
		try { 
			if(empty($user_name)) $user_name = md5(uniqid(wp_rand(10000,99000)));
			$user_name = sanitize_user($user_name);	
			// check if user already exist, if yes change it
			$temp_user_name = $user_name;
			$counter = 0;
			while(username_exists($temp_user_name)) {
				$temp_user_name = $user_name.$counter;
				$counter++;
			}
			// validate user name
			$user_name = $temp_user_name;
			$is_user = validate_username($user_name);
			if(!$is_user) 
				throw new Exception();
			
			return $this->user_name = $user_name;
		} catch(Exception $e) {
			return new WP_Error('error',"<strong>ERROR: </strong>".__('Error creating random user'));
		}
	}
	
	private function generate_password() {
		$password = wp_generate_password();
		if(empty($password))
			return new WP_Error('error',"<strong>ERROR: </strong>".__('Error creating random password'));
		
		return $password;
	}
}

