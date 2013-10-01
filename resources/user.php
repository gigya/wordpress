<?php

class GigyaSO_User {
  const GIGYA_ACTION_EMAIL_EXIST = "email_exist";
  const GIGYA_ACTION_EMAIL_REQUIRED = "new_user_email_required";
  # user data parameters
  private $data = NULL;
  # user already registered to site with gigya
  private $is_gigya = FALSE;
  # user already logged in to site
  private $is_logged_in = FALSE;
  private $user_name = 0;
  private $email = 0;
  private $password = 0;
  private $error = NULL;
  private $force_email = 0;
  private $account_linking = 1;
  private $api_key = 0;
  private $secret_key = 0;
  private $uid = NULL;

  private function is_user() {
    // check if already registered with gigya
    if (!empty($this->data->user->isSiteUID)) {
      return TRUE;
    }
    // check if user already registered in old version
    global $wpdb;
    $loginProvider = '_gsforwordpressuid' . sanitize_title_with_dashes($this->data->provider);
    $user_id = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value = %s", $this->data->UID, $loginProvider));
    if (!$user_id) {
      return 0;
    }
    $gigya = GigyaSO_Util::setUID($user_id, $this->data->UID);
    if (!is_wp_error($gigya)) {
      $this->data->UID = $user_id;
      $thumbnail = get_user_meta($user_id, "_gigya_socialize_thumbnail_url", 1);
      if ($thumbnail && !empty($thumbnail)) {
        update_user_meta($user_id, "avatar", $thumbnail);
      }
      return 1;
    }
    return 0;
  }

  public function __construct($data = NULL) {
    global $gigya_user_data;
    $gigya_user_data = $data;
    $this->data = $data;
    $this->is_gigya = $this->is_user();
    $this->uid = $data->UID;
    $this->is_logged_in = is_user_logged_in();
    $options = get_option(GIGYA_SETTINGS_PREFIX);
    $this->account_linking = 1;
    $this->api_key = !empty($options["api_key"]) ? $options["api_key"] : 0;
    $this->secret_key = !empty($options["secret_key"]) ? $options["secret_key"] : 0;
    $this->is_multisite = is_multisite();
    $this->user_id = 0;
    $this->redirectUrl = $options["post_login_redirect"] == "" ? home_url() : $options["post_login_redirect"];

    if ($this->is_multisite):
      global $blog_id;
      $this->blog_id = $blog_id;
    else:
      $this->blog_id = 0;
    endif;
  }

  public function __get($key) {
    if (property_exists($this, $key)) {
      return $this->$key;
    }
    return "";
  }

  private function signout() {
    $user = wp_get_current_user();
    if ($user) {
      wp_logout();
      do_action("wp_logout", $user->ID);
    }
  }

  private function signon($user_id, $user_name, $password) {
    // logout user if logged in to site
    global $is_gigya_user;
    $is_gigya_user = TRUE;
    if ($this->is_logged_in) {
      $this->signout();
    }
    // login
    $login = wp_set_current_user($user_id);
    //$login = wp_signon(array("user_login"=>$user_name,"user_password"=>$password,"remember"=>true),false);
    if (is_wp_error($login)) {
      return wp_send_json_error(array('error' => $login->get_error_message()));
    }
    wp_set_auth_cookie($user_id);
    return $login;
  }

  private function signon_gigya_user() {
    global $is_gigya_user;
    $is_gigya_user = TRUE;
    // logout user if logged in to site
    if ($this->is_logged_in) {
      $this->signout();
    }
    // get user data from site by siteUID
    $user = get_userdata($this->uid);
    if (!$user) {
      gigya_delete_account($this->uid);
      return wp_send_json_error(array('type' => 'error', 'text' => 'Can\'t find user in site, please try again'));
    }
    $login = wp_set_current_user($user->ID);
    if (!$login) {
      return wp_send_json_error(array('type' => 'error', 'text' => 'Can\'t login to site'));
    }
    wp_set_auth_cookie($user->ID);
    do_action('wp_login', $user->user_login);
    return 1;
  }

  /**
   * Start the proccess of user registration - validate and execute
   *
   * @return int|WP_Error Bool 1 if success or a WP_Error object if the user could not be created.
   */
  public function login() {
    // check if user has siteUID, if exist user registered to site and gigya and can login
    if (!empty($this->is_gigya)) {
      $signon = $this->signon_gigya_user();
      if (is_wp_error($signon)) {
        return wp_send_json_error(array('type' => 'error', 'text' => $signon->get_error_message()));
      }
      wp_send_json_success(array('type' => 'signin', 'params' => array('url' => $this->redirectUrl)));
    };

    // check if email exist in social user obj
    $email = $this->data->user->email;
    // return user id if exist
    $is_email_exist = empty($email) ? FALSE : email_exists($email);
    if ($this->is_multisite && $is_email_exist) {
      $blogs = get_blogs_of_user($is_email_exist);
      $is_exist_in_blog = 0;
      if ($blogs) {
        foreach ($blogs as $blog) {
          if ($this->blog_id == $blog->userblog_id) {
            $is_exist_in_blog = 1;
          }
        }
      }
      if (!$is_exist_in_blog) {
        $this->user_id = $is_email_exist;
        $is_email_exist = 0;
      }
    }
    // if exist - need to ask user if already registered - create new account or link account
    // TODO: check this flow with Shirly
    if ($is_email_exist) {
      //if ($this->force_email) {
      return wp_send_json_success(array(
          'type' => self::GIGYA_ACTION_EMAIL_EXIST,
          'params' => array(
            'account_linking' => $this->account_linking,
            'force_email' => true
          )
        )
      );
      //}
      /*      $link_a = $this->link_account($email, "", 1);
            if (is_wp_error($link_a)) {
              return wp_send_json_error($link_a->get_error_message());
            }
            return $link_a;*/
    }
    else {
      if (empty($this->data->user->email)) {
        return wp_send_json_success(array(
            'type' => self::GIGYA_ACTION_EMAIL_REQUIRED,
            'params' => array(
              'account_linking' => $this->account_linking,
              'force_email' => TRUE
            )
          )
        );
      }
      else {
        // if user id exist add it to current blog if not add a new user
        if ($this->user_id) {
          return $this->add_user_to_blog();
        }
        else {
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
  public function register_email($email) {
    // check if email address is valid
    $is_email = is_email($email);
    if ($is_email == false) {
      return wp_send_json_error(array('type' => 'error', 'text' => 'Email is not valid'));
    }
    // check if email doesnt belong to site user
    $user_id = email_exists($email);
    if ($user_id) {
      return wp_send_json_error(array(
          'type' => 'error',
          'text' => __('The email you provided is already used, Please provider a different email or link to an existing account')
        )
      );
    }
    // add new user to site
    $user = $this->add_new_user($email);
    if (is_wp_error($user)) {
      return wp_send_json_error(array('type' => 'error', 'text' => $user->get_error_message()));
    }
    return 1;
  }

  /**
   * Link social account to wordpress account
   *
   * @param string $email Email.
   * @param string $password Password.
   * @return int|WP_Error Bool 1 if success or a WP_Error object if the user could not be created.
   */
  public function link_account($email, $password = "", $force_login = 0) {
    # Before account linking validate if email is valid
    $is_email = is_email($email);
    if (is_wp_error($is_email)) {
      return $is_email;
    }
    // after email validation, check if it exist in the DB to retrieve userId
    $user_id = email_exists($email);
    if (!$user_id) {
      return wp_send_json_error(array(
          'type' => 'error',
          'text' => __('That E-mail doesn\'t belong to any registered users on this site')
        )
      );
    }
    // retrive user id
    $user = get_userdata($user_id);
    if (!$user) {
      return wp_send_json_error(array('type' => 'error', 'text' => __('Username Not In Use!')));
    }
    // login user to site
    if (wp_check_password($password, $user->user_pass, $user_id)) {
      $gigya = GigyaSO_Util::setUID($user_id, $this->uid);
      if (is_wp_error($gigya)) {
        return wp_send_json_error(array('type' => 'error', 'text' => $gigya->get_error_message()));
      }
      if (!$force_login) {
        $login = $this->signon($user_id, $user->user_login, $password);
        if (is_wp_error($login)) {
          return wp_send_json_error(array('type' => 'error', 'text' => $login->get_error_message()));
        }
      }
      else {
        $login = wp_set_current_user($user->ID);
        if (!$login) {
          return wp_send_json_error(array("error" => __("<strong>ERROR: </strong> Can't login to site")));
        }
      }
    }
    else {
      return wp_send_json_error(array('type' => 'error', 'text' => 'Wrong password'));
    }
    wp_set_auth_cookie($user->ID);
    do_action('wp_login', $user->user_login);
    return 1;
  }

  public function add_user_more_info($data, $moreInfo) {
    $this->add_new_user($moreInfo['user_email'], $moreInfo);
  }

  private function add_user_to_blog($notify = 1) {
    if ($this->blog_id && $this->user_id) {
      switch_to_blog($this->blog_id);
      $role = get_option("default_role");
      if ($role) {
        add_user_to_blog($this->blog_id, $this->user_id, $role);
      }
      //restore_current_blog();
      # regiter user with gigya
      if ($notify) {
        $gigya = GigyaSO_Util::notify_registration($this->user_id, $this->uid);
        return $gigya;
      }
    }
  }

  private function add_new_user($email = "", $moreInfo = NULL) {
    global $is_gigya_user;
    $is_gigya_user = TRUE;
    if (gigya_get_option("show_reg") && $moreInfo === NULL) {
      $reg_form = $this->gen_reg_form($email);
      do_shortcode($reg_form);
      $res = array(
        'type' => 'reg_form',
        'html' => $reg_form,
      );
      wp_send_json_success($res);
    }
    $user_name = $this->data->user->nickname;
    if (!validate_username($user_name)) {
      wp_send_json_error(array('type' => 'error', 'text' => 'User name is not valid'));
    }
    //fix email format
    $email = sanitize_email($email);
    if (empty($email)) {
      wp_send_json_error(array('type' => 'error', 'text' => 'Email is not valid'));
    }
    $password = $this->generate_password();
    if (is_wp_error($password)) {
      return $password;
    }
    //prepere user data
    $user_data = array(
      // Required
      'user_login' => $user_name,
      'user_pass' => $password,
      'user_email' => $email,
      // Not Required
      'user_url' => $this->data->user->profileURL,
      'display_name' => $this->data->user->nickname,
      'nickname' => $this->data->user->nickname,
      'first_name' => $this->data->user->firstName,
      'last_name' => $this->data->user->lastName
    );
    if (!empty($moreInfo)) {
      // remove required fields
      unset($moreInfo['user_login'], $moreInfo['user_pass'], $moreInfo['user_email']);
      foreach ($moreInfo as $field => $val) {
        $user_data[$field] = $val;
        // copy info to $_POST for validation
        $_POST[$field] = $val;
      }

      $errors = new WP_Error();
      $errors = apply_filters('registration_errors', $errors, $user_name, $email);
      if (count($errors->get_error_messages()) > 0) {
        $msg = "";
        foreach ($errors->get_error_messages() as $err) {
          $msg .= $err . '<br />';
        }
        return wp_send_json_error(array('type' => 'error', 'text' => $msg));
      }
    }
    // Do action for other plugins to interact
    do_action('gigya_pre_add_user', $this);
    // Apply filter for custom plugins to modify user data before adding to db
    apply_filters('gigya_pre_add_user', $user_data);
    # add new user to db
    $this->user_id = wp_insert_user($user_data);
    if (is_wp_error($this->user_id)) {
      return wp_send_json_error(array('type' => 'error', 'text' => $this->user_id->get_error_message()));
    }
    # add user to blog if multisite support
    $this->add_user_to_blog(0);
    # add user meta
    update_user_meta($this->user_id, "avatar", $this->data->user->thumbnailURL);
    // Do action after user added to db
    do_action('gigya_post_add_user', $this);
    # register user with gigya
    $gigya = GigyaSO_Util::notify_registration($this->user_id, $this->uid);
    if (is_wp_error($gigya)) {
      wp_delete_user($this->user_id);
      wp_send_json_error(array('type' => 'error', 'text' => $gigya->get_error_message()));
    }
    # login user to site
    $login = $this->signon($this->user_id, $user_name, $password);
    if (is_wp_error($login)) {
      return wp_send_json_error(array('error' => $login->get_error_message()));
    }
    wp_send_json_success(array('type' => 'signin', 'params' => array('url' => $this->redirectUrl)));
  }

  private function gen_reg_form($email = '') {
    if (!empty($email)) {
      $this->$email = $email;
    }
    ob_start();
    ?>
    <form name="registerform" class="gigya-reg-form" id="registerform"
          action="<?php echo esc_url(site_url('wp-login.php?action=register', 'login_post')); ?>" method="post">
      <div class="error-message"></div>
      <p>
        <label for="user_login"><?php _e('Username') ?><br/>
          <input type="text" name="user_login" id="user_login" class="input"
                 value="<?php echo $this->data->user->nickname ?>" size="20"/></label>
      </p>

      <p>
        <label for="user_email"><?php _e('E-mail') ?><br/>
          <input type="text" name="user_email" id="user_email" class="input"
                 value="<?php echo $this->$email; ?>" size="25"/></label>
      </p>
      <?php do_action('register_form'); ?>
      <p id="reg_passmail"><?php _e('A password will be e-mailed to you.') ?></p>
      <br class="clear"/>

      <p class="submit"><input type="submit" name="wp-submit" id="gigya-ajax-submit"
                               class="button button-primary button-large" value="<?php esc_attr_e('Register'); ?>"/></p>
    </form>
    <?php
    return ob_get_clean();
  }

  private function generate_password() {
    $password = wp_generate_password();
    if (empty($password)) {
      return new WP_Error('error', "<strong>ERROR: </strong>" . __('Error creating random password'));
    }
    return $password;
  }
}

