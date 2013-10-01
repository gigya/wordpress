<?php



function gigya_admin_enqueue($hook) {
  if (!strpos($hook, "gigya")) {
    return;
  }
  //wp_enqueue_script('bootstrap', GIGYA_PLUGIN_URL . '/js/bootstrap/js/bootstrap.min.js', array("jquery"));
  //wp_enqueue_style('bootstrap', GIGYA_PLUGIN_URL . '/js/bootstrap/css/bootstrap.css');
}

# get config parameters for gigya, and set it as global
if (!function_exists('gigya_init_options')) :
  function gigya_init_options() {
    global $GIGYA_OPTIONS, $wpdb;

    $current_options = get_option(GIGYA_SETTINGS_PREFIX);

    if (!$current_options) {
      $options = array();
      # check settings of old version of plugin
      $old_options = get_option("GS for WordPress Settings");
      if ($old_options) {
        if (!empty($old_options["gs-for-wordpress-api-key"])) {
          $options["api_key"] = $old_options["gs-for-wordpress-api-key"];
        }
        if (!empty($old_options["gs-for-wordpress-secret-key"])) {
          $options["secret_key"] = $old_options["gs-for-wordpress-secret-key"];
        }
        if (!empty($old_options["gs-for-wordpress-post-login-redirect"])) {
          $options["post_login_redirect"] = $old_options["gs-for-wordpress-post-login-redirect"];
        }
        if (!empty($old_options["gs-for-wordpress-sign-in-component-ui"])) {
          $options["login_ui"] = $old_options["gs-for-wordpress-sign-in-component-ui"];
        }
      }

      $current_options = array_merge($options, array(
          "force_email" => "1",
          "account_linking" => "1",
          "login_plugin" => "1",
          "login_plugin_startup" => "1",
          "share_providers" => "share,email,pinterest,twitter-tweet,google-plusone,facebook-like",
          "share_providers_startup" => "1"
        )
      );

      update_option(GIGYA_SETTINGS_PREFIX, $current_options);

    }
    else {
      $update = 0;
      if (!isset($current_options["login_plugin_startup"])) {
        $update = 1;
        $current_options["login_plugin"] = "1";
        $current_options["login_plugin_startup"] = "1";
      }
      if (!isset($current_options["share_providers_startup"])) {
        $update = 1;
        $current_options["share_providers"] = "share,email,pinterest,twitter-tweet,google-plusone,facebook-like";
        $current_options["share_providers_startup"] = "1";
      }

      if ($update) {
        update_option(GIGYA_SETTINGS_PREFIX, $current_options);
      }
    }
    $GIGYA_OPTIONS = $current_options;

    if (!empty($_POST) && !empty($_POST['option_page'])) {
      if ($_POST['option_page'] == 'gigya_settings_fields' && $_POST['action'] == 'update') {
        $_POST['gigya_settings_fields'] = $_POST['gigya_settings_fields'] + $GIGYA_OPTIONS;
      }
    }
  }
endif;


# register js files
if (!function_exists('gigya_enque_js')) :
  function gigya_enque_js($use_script) {

    //if (gigya_get_option("load_jquery") == 1 || gigya_get_option("load_jquery") == 2) {
    /*      wp_deregister_script("jquery");

          //$jquery_path = gigya_get_option("load_jquery") == 1 ? GIGYA_PLUGIN_URL . "/js/jquery/jquery.1.7.1.min.js" : "https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js";
          //$jquery_ui_path = gigya_get_option("load_jquery") == 1 ? GIGYA_PLUGIN_URL . "/js/jquery/jquery-ui-1.8.16.min.js" : "https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js";

          wp_register_script("jquery", $jquery_path, array(), "1.7.1");
          wp_register_script("jquery-ui", $jquery_ui_path, array("jquery"), "1.8.16");

          $raw_js_files = array();
          array_push($raw_js_files, array("name" => "jquery", "is_enque" => 1, "url" => $jquery_path, "is_admin" => 0));
          if (!is_user_logged_in() && is_active_widget(FALSE, FALSE, "gigya", FALSE)) {
            array_push($raw_js_files, array(
                "name" => "jquery-ui",
                "is_enque" => 1,
                "url" => $jquery_ui_path,
                "is_admin" => 0
              )
            );
            array_push($raw_js_files, array(
                "name" => "jquery.tmpl",
                "is_enque" => 0,
                "url" => GIGYA_PLUGIN_URL . "/js/jquery.tmpl.js",
                "is_admin" => 0
              )
            );
            array_push($raw_js_files, array("name" => "json2", "is_enque" => 1, "url" => "json2.js", "is_admin" => 0));
          }
          array_push($raw_js_files, array(
              "name" => "gigya",
              "is_enque" => 0,
              "url" => GIGYA_PLUGIN_URL . "/js/gigya.js",
              "is_admin" => 0
            )
          );
          //array_push($raw_js_files,array("name"=>"gigya-socialize","is_enque"=>0,"url"=>"http://cdn.gigya.com/JS/socialize.js?apikey=".gigya_get_option("api_key"),"is_admin"=>1));

        }
        else {
          $raw_js_files = array();
          array_push($raw_js_files, array(
              "name" => "jquery",
              "is_enque" => 1,
              "url" => "jquery/jquery.js",
              "is_admin" => 0
            )
          );
          array_push($raw_js_files, array(
              "name" => "jquery.tmpl",
              "is_enque" => 0,
              "url" => GIGYA_PLUGIN_URL . "/js/jquery.tmpl.js",
              "is_admin" => 0
            )
          );
          array_push($raw_js_files, array("name" => "json2", "is_enque" => 1, "url" => "json2.js", "is_admin" => 0));
          array_push($raw_js_files, array(
              "name" => "jquery-ui-core",
              "is_enque" => 1,
              "url" => "jquery/ui.core.js",
              "is_admin" => 0
            )
          );
          array_push($raw_js_files, array(
              "name" => "jquery-ui-draggable",
              "is_enque" => 1,
              "url" => "jquery/ui.draggable.js",
              "is_admin" => 0
            )
          );
          array_push($raw_js_files, array(
              "name" => "jquery-ui-resizable",
              "is_enque" => 1,
              "url" => "jquery/ui.resizable.js",
              "is_admin" => 0
            )
          );
          array_push($raw_js_files, array(
              "name" => "jquery-ui-dialog",
              "is_enque" => 1,
              "url" => "jquery/ui.dialog.js",
              "is_admin" => 0
            )
          );
          array_push($raw_js_files, array(
              "name" => "gigya",
              "is_enque" => 0,
              "url" => GIGYA_PLUGIN_URL . "/js/gigya.js",
              "is_admin" => 0
            )
          );
          //array_push($raw_js_files,array("name"=>"gigya-socialize","is_enque"=>0,"url"=>"http://cdn.gigya.com/JS/socialize.js?apikey=".gigya_get_option("api_key"),"is_admin"=>1));
        }


        $is_admin = is_admin();
        if (!$use_script) {
          foreach ($raw_js_files as $file) {
            if (($is_admin && $file["is_admin"]) || !$is_admin) {
              if (!$file["is_enque"]) {
                wp_register_script($file["name"], $file["url"]);
              }
              wp_enqueue_script($file["name"]);
            }
          }
        }
        else {
          $wp_js_path = get_bloginfo('wpurl') . '/' . WPINC . '/js';
          gigya_enque_gigya_script();
          foreach ($raw_js_files as $file) {
            $path = $file["is_enque"] ? "$wp_js_path/$file[url]" : $file[url];
            echo "<script type='text/javascript' src='$path'></script>";
          }

        }*/

    wp_enqueue_script('gigya_local_script', GIGYA_PLUGIN_URL . '/js/gigya.js', array('jquery', 'jquery-ui-dialog'));
    wp_enqueue_script('gigya_tmpl_script', GIGYA_PLUGIN_URL . '/js/jquery.tmpl.js');
    $vars = array(
      'adminurl' => admin_url("admin-ajax.php")
    );
    wp_localize_script('gigya_local_script', 'gigyaVars', $vars);
  }
endif;


if (!function_exists('gigya_enque_gigya_script')) :
  function gigya_enque_gigya_script() {
    ?>
    <script type='text/javascript'
            src='http://cdn.gigya.com/js/socialize.js?apiKey=<?php echo gigya_get_option("api_key"); ?>&ver=<?php echo GIGYA_VERSION; ?>'>
      <?php

          $loginProviders = gigya_get_option("login_providers");
          $lang = gigya_get_option("lang");
          $short_url = gigya_get_option("short_url");
          $connect_without = gigya_get_option("connect_without");


          $global_params = gigya_parse_key_pair(gigya_get_option("global_params"));
          $global_params = $global_params ? json_encode($global_params) : 0;

          if(empty($loginProviders)) {$loginProviders = "*";
}
          if(empty($lang)) {$lang = "en";
}
          if(empty($short_url)) {$short_url = "never";
}
          if(empty($connect_without)) {$connect_without = gigya_get_field_default("connect_without_login_behavior");
}
          if(empty($global_params)):
      ?>
      {
        lang: '<?php echo $lang;?>',
          shortURLs
      :
        '<?php echo $short_url;?>',
          enabledProviders
      :
        '<?php echo $loginProviders;?>',
          connectWithoutLoginBehavior
      :
        '<?php echo $connect_without;?>'
      }
      <?php else: ?>
      <?php echo $global_params;?>
      <?php endif; ?>
    </script>

    <?php if (gigya_get_option("google_analytics") == 1): ?>
      <script type="text/javascript" src="http://cdn.gigya.com/js/gigyaGAIntegration.js"></>
    <?php endif; ?>

    <?php
    $gamification_notif = gigya_get_option("gamification_notification");
    if (empty($gamification_notif)) {
      $gamification_notif = gigya_get_field_default("gamification_notification");
    }

    if ($gamification_notif == "1"): ?>
      <script type="text/javascript">
        gigya.gm.showNotifications();
      </script>
    <?php endif; ?>
  <?php
  }
endif;

function gigya_enque_gigya_script_admin_init() {
  wp_enqueue_script('gigya-socialize', "http://cdn.gigya.com/js/socialize.js?apiKey=" . gigya_get_option("api_key"), array(), GIGYA_VERSION);
}

add_action('admin_enqueue_scripts', 'gigya_enque_gigya_script_admin_init');


# get config params from gigya global options config
if (!function_exists('gigya_get_option')) :
  function gigya_get_option($ns = NULL) {
    global $GIGYA_OPTIONS;
    if ($ns) {
      //gigya_get_field_default("reaction_position")
      return !empty($GIGYA_OPTIONS[$ns]) ? $GIGYA_OPTIONS[$ns] : "";
    }
    return !is_array($GIGYA_OPTIONS) ? array() : $GIGYA_OPTIONS;
  }
endif;


if (!function_exists('gigya_msg')) :
  function gigya_msg($error = NULL, $params = array()) {
    $error = new GigyaSO_Msg($error);
    $error->render($params);
  }
endif;
#handle request for each user request to login
if (!function_exists('gigya_user_login')) :
  function gigya_user_login() {
    if (gigya_get_option("login_plugin") == 1) {
      if (isset($_POST["userObject"]) && !empty($_POST["userObject"])) {
        $data = json_decode(stripslashes($_POST["userObject"]));
        if (is_object($data)) {
          // check if site allows registration of new users
          if ('0' == get_option('users_can_register')) {
            gigya_msg(new WP_Error('error', __("New user registration is currently disabled for this site")));
            die();
          }
          else {
            $valid = GigyaSO_Util::validate_user_signature($data->UID, $data->signatureTimestamp, $data->UIDSignature);
            if (!is_wp_error($valid)) {
              $user = new GigyaSO_User($data);
              $action = !empty($_POST['actionType']) ? $_POST['actionType'] : "";
              switch ($action) {
                case "register-email":
                  $login = $user->register_email($_POST["email"]);
                  break;
                case "link-account":
                  $login = $user->link_account($_POST["email"], $_POST["password"]);
                  break;
                case "moreInfo":
                  $login = $user->add_user_more_info($data, $_POST['info']);
                  break;
                default:
                  $login = $user->login();
              }
            }
            else {
              gigya_msg($valid);
              die();
            }
            if (is_wp_error($login)) {
              gigya_msg($login, array(
                  "force_email" => $user->force_email ? TRUE : FALSE,
                  "account_linking" => $user->account_linking ? TRUE : FALSE
                )
              );
              die();
            }
            else {
              gigya_msg();
            }
          }
        }
        else {
          gigya_msg(new WP_Error('error', __("Gigya Error")));
          die();
        }
      }
    }
    die();
  }
endif;

if (!function_exists('gigya_notify_user_login')) :
  function gigya_notify_user_login($user_name) {
    if (gigya_get_option("login_plugin") == 1):
      $user = get_user_by('login', $user_name);
      GigyaSO_Util::notify_login($user->ID);
    endif;
  }
endif;

if (!function_exists('gigya_notify_user_register')) :
  function gigya_notify_user_register($user_id) {
    if (gigya_get_option("login_plugin") == 1 && $user_id):
      global $is_gigya_user, $gigya_user_data;
      if (!$is_gigya_user) {
        GigyaSO_Util::notify_login($user_id, 1);
      }
    endif;
  }
endif;


if (!function_exists('gigya_notify_user_logout')) :
  function gigya_notify_user_logout() {
    if (gigya_get_option("login_plugin") == 1):
      $current_user = wp_get_current_user();
      $user_id = $current_user->ID;
      if ($user_id > 0) {
        GigyaSO_Util::notify_logout($user_id);
      }
    endif;
  }
endif;

if (!function_exists('gigya_delete_account')) :
  function gigya_delete_account($user_id) {
    if ($user_id) {
      GigyaSO_Util::delete_account($user_id);
    }
  }
endif;

if (!function_exists('gigya_user_profile_extra')) :
  function gigya_user_profile_extra($user) {
    $current_user = wp_get_current_user();
    echo get_avatar($user->ID, 96, 0);
    if ($current_user->ID == $user->ID):
      $custom = gigya_parse_key_pair(gigya_get_option("login_add_connection_custom"));
      $custom = $custom ? json_encode($custom) : 0;
      ?>

      <h3><?php _e("Manage social connection", "blank"); ?></h3>
       <table class="form-table">
    <tr>
    <th></th>
    <td>
    <div id="gigya-div-connect"></div>
    </td>
    </tr>
    </table>
    <script type="text/javascript" lang="javascript">
    (function () {
    var params = {
    height: 65,
    width: 175,
    showEditLink: true,
    showTermsLink: false,
    hideGigyaLink: true,
    useHTML: true,
    containerID: "gigya-div-connect"
    };

    <?php if ($custom): ?>
      var adParams = <?php echo $custom; ?>;
      for (var prop in adParams) {
      params[prop] = adParams[prop];
      }
      ;
    <?php endif ?>
    gigya.services.socialize.showAddConnectionsUI(params);
    })();
      </script>
    <?php
    endif;
  }
endif;


function gigya_get_first_image($post) {
  // check if post thumbnail
  if (has_post_thumbnail($post->ID)):
    return wp_get_attachment_url(get_post_thumbnail_id($post->ID));
  endif;

  // check if attachments
  $attachments = get_posts(array(
      'order' => 'ASC',
      'post_type' => 'attachment',
      'post_parent' => $post->ID,
      'post_mime_type' => 'image',
      'post_status' => NULL
    )
  );

  if ($attachments) {
    foreach ($attachments as $attachment) {
      return wp_get_attachment_url($attachment->ID, 'thumbnail', FALSE, FALSE);
    }
  }
  // search for image in code
  $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
  if (!empty($matches[1][0])) {
    return $matches[1][0];
  }
  // no image found use default blank image
  return get_bloginfo('wpurl') . '/' . WPINC . '/images/blank.gif';
}


function get_user_action_embed($id = 0) {
  $permalink = get_permalink($id);
  $post = get_post($id);
  $title = htmlspecialchars_decode(esc_js($post->post_title));
  $description = htmlspecialchars_decode(esc_js($post->post_excerpt));
  $first_img_url = gigya_get_first_image($post);


  //var params = {
  //	userAction:act,  // including the UserAction object
  //	scope: 'both'    // the user action will be published both to social networks and to the site's newsfeed.

  //};

  //gigya.socialize.publishUserAction(params);


  return "var image = {src:'$first_img_url',href:'$permalink',type:'image'};
			var ua = new gigya.services.socialize.UserAction(); 
			ua.setUserMessage('');  
			ua.setLinkBack('" . $permalink . "');
			ua.setTitle('" . $title . "');
			ua.addMediaItem(image);
			ua.setDescription('" . $description . "');
	";
}


function gigya_get_share_plugin($pos = "") { /* pos = bottom | top*/
  global $post;
  $share = "";


  $advanced = gigya_parse_key_pair(gigya_get_option("share_advanced"));
  $advanced = $advanced ? json_encode($advanced) : 0;

  $id = $post->ID;

  $layout = gigya_get_option("share_layout");
  if (empty($layout)) {
    $layout = "horizontal";
  }

  $show_counts = gigya_get_option("share_show_counts");
  if (empty($show_counts)) {
    $show_counts = "right";
  }

  $share_buttons = trim(gigya_get_option("share_providers"));
  if (empty($share_buttons)) {
    $share_buttons = "share,facebook-like,google-plusone,twitter,email";
  }

  $privacy = gigya_get_option("share_privacy");
  if (empty($privacy)) {
    $privacy = gigya_get_field_default("activity_privacy");
  }

  $custom = gigya_get_option("share_custom");

  $share .= "<p class='gig-share-button gig-share-button-$pos' id='gig-div-buttons-$id-$pos'></p>";
  $share .= "<script language='javascript'>";
  $share .= "(function(){";
  $share .= get_user_action_embed($id);

  if (empty($custom)):
    $share .= "     var params ={
						userAction:ua,
						layout    : '$layout',
						showCounts: '$show_counts',
						cssPrefix:'#gig-div-buttons-$id-$pos',
						shareButtons:'$share_buttons', // list of providers
						containerID: 'gig-div-buttons-$id-$pos',
						privacy: '$privacy',
        				cid:''
					};";

    if ($advanced) {
      $share .= " var adParams = $advanced;
				  for (var prop in adParams) {
            				params[prop] = adParams[prop];
        		  };";
    };

    $share .= "gigya.services.socialize.showShareBarUI(params);";

  else:
    $share .= "$custom";
  endif;

  $share .= "}());";
  $share .= "</script>";

  $share = apply_filters("share_plugin", $share, array(
      "api" => gigya_get_option("api_key"),
      "post_id" => $id,
      "permalink" => get_permalink($id),
      "title" => $post->post_title,
      "first_img_url" => gigya_get_first_image($post)
    )
  );


  return $share;
}

;


function gigya_share_plugin($content) {
  $gigya_share = gigya_get_option("share_plugin");
  if (empty($gigya_share) || $gigya_share == 3) {
    $bottomHTML = gigya_get_share_plugin("bottom");
    $content = $content . $bottomHTML;
  }

  if ($gigya_share == 2 || $gigya_share == 3) { /* Top */
    $topHTML = gigya_get_share_plugin("top");
    $content = $topHTML . $content;
  }

  if (gigya_get_option("share_plugin") == 1) { /* No Share Bar */

  }

  return $content;
}

function gigya_reaction_plugin($content) {
  $gigya_reaction = gigya_get_option("reaction_plugin");
  if ($gigya_reaction == 1) {
    $gigya_position = gigya_get_option("reaction_position");
    if (empty($gigya_position)) {
      $gigya_position = gigya_get_field_default("reaction_position");
    }
    if ($gigya_position == "bottom") {
      $content = $content . gigya_get_reaction_plugin();
    }
    else {
      if ($gigya_position == "top") {
        $content = gigya_get_reaction_plugin() . $content;
      }
      else {
        $content = gigya_get_reaction_plugin() . $content . gigya_get_reaction_plugin();
      }
    }
  }

  return $content;

}

function gigya_get_reaction_plugin() {
  global $post;

  $reactions = gigya_get_option("reaction_buttons");
  if (empty($reactions)) {
    $reactions = "{}";
  }

  $count = gigya_get_option("reaction_count_type");
  if (empty($count)) {
    $count = gigya_get_field_default("reaction_count_type");
  }

  $providers = gigya_get_option("reaction_providers");
  if (empty($providers)) {
    $providers = gigya_get_field_default("reaction_providers");
  }

  $layout = gigya_get_option("reaction_layout");
  if (empty($layout)) {
    $layout = gigya_get_field_default("reaction_layout");
  }

  $count_type = gigya_get_option("reaction_count_type");
  if (empty($count_type)) {
    $count_type = gigya_get_field_default("reaction_count_type");
  }

  $multiple = gigya_get_option("reaction_multiple");
  if (empty($multiple)) {
    $multiple = gigya_get_field_default("reaction_multiple");
  }
  $multiple = $multiple == "1" ? "true" : "false";

  $scope = gigya_get_option("reaction_enable_share_activity");
  if (empty($scope)) {
    $scope = gigya_get_field_default("reaction_enable_share_activity");
  }

  $advanced = gigya_parse_key_pair(gigya_get_option("reactions_custom_code"));
  $advanced = $advanced ? json_encode($advanced) : 0;

  $privacy = gigya_get_option("reaction_privacy");
  if (empty($privacy)) {
    $privacy = gigya_get_field_default("activity_privacy");
  }

  $id = $post->ID;
  $code = "";


  $code .= "<p id='gig-div-reactions-$id'></p>";
  $code .= "<script language='javascript'>";
  $code .= "{";
  $code .= get_user_action_embed($id);

  $code .= " var params = {
					barID      : 'gig-div-reactions-bar-$id',
					containerID: 'gig-div-reactions-$id',
					reactions  : [$reactions],
					privacy    : '$privacy',
					userAction : ua,
					enabledProviders : '$providers',
					showCounts       : '$count',
					layout           : '$layout',
					multipleReactions: $multiple,
					countType        : '$count_type',
					scope            : '$scope'            
			   };";

  if ($advanced) {
    $code .= " var adParams = $advanced;
				  for (var prop in adParams) {
            				params[prop] = adParams[prop];
        		  };";
  };

  $code .= "gigya.socialize.showReactionsBarUI(params)";

  $code .= "}";
  $code .= "</script>";

  return $code;
}

;


function gigya_gamification_plugin($params = array()) {

  $period = $params['period'];
  if (empty($period)) {
    $period = gigya_get_field_default("gamification_period");
  }

  $type = $params['type'];
  if (empty($type)) {
    $type = gigya_get_field_default("gamification_type");
  }

  $count = $params['count'];
  if (empty($count))
    $count = gigya_get_field_default("gamification_count");

  $width = $params['width'];
  if (empty($width))
    $width = gigya_get_field_default("gamification_width");

  $cmp_id = generate_random_div_id();

  $code = "<div id='$cmp_id'></div>";

  $code .= "<script type='text/javascript'>
			var params = {
				'containerID' : '$cmp_id',
				'width'       : '$width',
				'period'      : '$period',
				'totalCount'  : '$count'
			};";

  if ($type == "achievements"):
    $code .= "gigya.gm.showAchievementsUI(params);";
  endif;
  if ($type == "leaderboard"):
    $code .= "gigya.gm.showLeaderboardUI(params);";
  endif;
  if ($type == "challenge"):
    $code .= "gigya.gm.showChallengeStatusUI(params);";
  endif;
  if ($type == "game"):
    $code .= "gigya.gm.showUserStatusUI(params);";
  endif;

  $code .= "
</script>";

  return $code;
}

;

function gamification_shortcode($atts) {
  extract(shortcode_atts(array(
        'period' => gigya_get_field_default("gamification_period"),
        'type' => gigya_get_field_default("gamification_type"),
        'count' => gigya_get_field_default("gamification_count"),
        'width' => gigya_get_field_default("gamification_width")
      ), $atts
    )
  );

  return gigya_gamification_plugin($atts);
}

function gigya_activity_plugin($params = array()) {

  $feed_id = $params['feed_id'];
  if (empty($feed_id))
    $feed_id = gigya_get_field_default("activity_feed_id");

  $site_name = $params['site_name'];
  if (empty($site_name))
    $site_name = gigya_get_field_default("activity_site_name");

  $initial_tab = $params['initial_tab'];
  if (empty($initial_tab))
    $initial_tab = gigya_get_field_default("activity_initial_tab");

  $width = $params['width'];
  if (empty($width))
    $width = gigya_get_field_default("activity_width");

  $cmp_id = generate_random_div_id();


  $code = "<div id='$cmp_id'></div>
			 <script type='text/javascript'>
		
			 (function(){
				var params = {
					'containerID' : '$cmp_id',
					'initialTab'  : '$initial_tab',
					'feedID'      : '$feed_id',
					'siteName'    : '$site_name',
					'width'       : '$width'
				};

				gigya.socialize.showFeedUI(params);
		 	  }());
	    </script>";

  return $code;
}

;

function activity_shortcode($atts) {
  extract(shortcode_atts(array(
        'feed_id' => gigya_get_field_default("activity_feed_id"),
        'site_name' => gigya_get_field_default("activity_site_name"),
        'initial_tab' => gigya_get_field_default("activity_initial_tab"),
        'width' => gigya_get_field_default("activity_width")
      ), $atts
    )
  );

  return gigya_activity_plugin($atts);
}

#handle ajax request for comments plugin - add comments after cnew comment added with comments plugin
if (!function_exists('gigya_add_comment')) :
  function gigya_add_comment() {
    if (gigya_get_option("comments_plugin") == 1):
      if (!wp_verify_nonce($_POST["nonce"], 'gigya-comment-nonce'))
        die ('Busted!');

      if (isset($_POST["post_id"]) && !empty($_POST["comment"]) && !empty($_POST["uid"])) {
        $data = array(
          'comment_post_ID' => $_POST["post_id"],
          'comment_content' => $_POST["comment"],
          'comment_parent' => 0,
          'user_id' => $_POST["uid"],
          'comment_date' => current_time('mysql'),
          'comment_approved' => 1
        );

        wp_insert_comment($data);
        do_action("gigya_add_comment", $data);
      }
    endif;

    die();
  }
endif;

add_action('wp_ajax_gigya_add_comment', 'gigya_add_comment');
add_action('wp_ajax_nopriv_gigya_add_comment', 'gigya_add_comment');

function gigya_logout_user() {
  wp_logout();
  die();
}

add_action('wp_ajax_gigya_logout_user', 'gigya_logout_user');
add_action('wp_ajax_nopriv_gigya_logout_user', 'gigya_logout_user');


if (!function_exists('check_if_spider')) {
  function check_if_spider() {
    $spiders = array(
      'Googlebot',
      'Yammybot',
      'Openbot',
      'Yahoo',
      'Slurp',
      'msnbot',
      'Rambler',
      'AbachoBOT',
      'Accoona',
      'AcoiRobot',
      'ASPSeek',
      'CrocCrawler',
      'ia_archiver',
      'Lycos',
      'Scooter',
      'AltaVista',
      'Teoma',
      'Gigabot',
      'FAST-WebCrawler',
      'GeonaBot',
      'Gigabot',
      'Altavista robot',
      'Googlebot-Mobile'
    );

    foreach ($spiders as $spider) {
      if (eregi($spider, $_SERVER['HTTP_USER_AGENT'])) {
        return TRUE;
      }
    }

    return FALSE;
  }
}


function gigya_comments_template($value) {
  global $post;
  global $comments;

  if (!gigya_comments_can_replace() || check_if_spider()) {
    return $value;
  }


  return GIGYA_PLUGIN_PATH . '/comments.php';
}


add_filter('comments_template', 'gigya_comments_template');


function gigya_comments_can_replace() {
  return gigya_get_option("comments_plugin") == 1 ? 1 : 0;
}


//function gigya_comments_number($value) {
//    return gigya_get_comments_number($value);
//}

//add_filter('comments_number', 'gigya_comments_number');

//function gigya_get_comments_number($value) {
//	global $post;
//	
//	if(!gigya_comments_can_replace()) return $value;
//    
//	require_once(GIGYA_PLUGIN_PATH.'/sdk/GSSDK.php');
//	$api_key = gigya_get_option("api_key");
//	$secret_key = gigya_get_option("secret_key");
//	$request = new GSRequest($api_key,$secret_key,"comments.getStreamInfo");
//	$request->setParam("categoryID",gigya_get_option("gigya_comments_cat_id"));
//	$request->setParam("streamID","comments-".$post->ID);
//	
//	$response = $request->send();  
//	
//	if($response->getErrorCode()!=0)
//		return 0;
//	
//	$data_array = $response->getArray("streamInfo");
//	
//	return $data_array->getArray("commentCount");
//}
//
//add_filter('get_comments_number', 'gigya_get_comments_number');


if (!function_exists('gigya_update_avatar_image')) :
  function gigya_update_avatar_image($avatar, $id_or_email, $size, $default, $alt) {

    if (is_object($id_or_email))
      $id_or_email = $id_or_email->user_id;

    if (is_numeric($id_or_email)) {
      $thumb = get_user_meta($id_or_email, "avatar", 1);
      if (!empty($thumb)) {
        $avatar = preg_replace("/src='*?'/", "src='$thumb'", $avatar);
      }
    }

    return $avatar;
  }
endif;

if (!function_exists('gigya_get_avatar_url')) :

  function gigya_get_avatar_url($id_or_email, $size = '96', $default = '', $alt = FALSE) {
    if (!get_option('show_avatars'))
      return FALSE;

    if (FALSE === $alt)
      $safe_alt = '';
    else
      $safe_alt = esc_attr($alt);

    if (!is_numeric($size))
      $size = '96';

    $email = '';
    if (is_numeric($id_or_email)) {
      $id = (int) $id_or_email;
      $user = get_userdata($id);
      if ($user)
        $email = $user->user_email;
    }
    elseif (is_object($id_or_email)) {
      // No avatar for pingbacks or trackbacks
      $allowed_comment_types = apply_filters('get_avatar_comment_types', array('comment'));
      if (!empty($id_or_email->comment_type) && !in_array($id_or_email->comment_type, (array) $allowed_comment_types))
        return FALSE;

      if (!empty($id_or_email->user_id)) {
        $id = (int) $id_or_email->user_id;
        $user = get_userdata($id);
        if ($user)
          $email = $user->user_email;
      }
      elseif (!empty($id_or_email->comment_author_email)) {
        $email = $id_or_email->comment_author_email;
      }
    }
    else {
      $email = $id_or_email;
    }

    if (empty($default)) {
      $avatar_default = get_option('avatar_default');
      if (empty($avatar_default))
        $default = 'mystery';
      else
        $default = $avatar_default;
    }

    if (!empty($email))
      $email_hash = md5(strtolower($email));

    if (is_ssl()) {
      $host = 'https://secure.gravatar.com';
    }
    else {
      if (!empty($email))
        $host = sprintf("http://%d.gravatar.com", (hexdec($email_hash[0]) % 2));
      else
        $host = 'http://0.gravatar.com';
    }

    if ('mystery' == $default)
      $default = "$host/avatar/ad516503a11cd5ca435acc9bb6523536?s={$size}"; // ad516503a11cd5ca435acc9bb6523536 == md5('unknown@gravatar.com')
    elseif ('blank' == $default)
      $default = includes_url('images/blank.gif');
    elseif (!empty($email) && 'gravatar_default' == $default)
      $default = '';
    elseif ('gravatar_default' == $default)
      $default = "$host/avatar/s={$size}";
    elseif (empty($email))
      $default = "$host/avatar/?d=$default&amp;s={$size}";
    elseif (strpos($default, 'http://') === 0)
      $default = add_query_arg('s', $size, $default);

    if (!empty($email)) {
      $out = "$host/avatar/";
      $out .= $email_hash;
      $out .= '?s=' . $size;
      $out .= '&amp;d=' . urlencode($default);

      $rating = get_option('avatar_rating');
      if (!empty($rating))
        $out .= "&amp;r={$rating}";

      return $out;
    }
    else {
      return $default;
    }

    return apply_filters('get_avatar', $avatar, $id_or_email, $size, $default, $alt);
  }
endif;


/* width
   header_text
   height
   button_size
   enabledProviders
	bgColor
*/

function render_login_plugin($atts = array()) {
  require_once(GIGYA_PLUGIN_PATH . '/resources/widget.php');
  $gigya_widget = new GigyaSO_Widget($atts);
  $gigya_widget->render_css();
  global $current_user;
  wp_get_current_user();
  // check logged in
  if (0 == $current_user->ID):
    $gigya_widget->render_tmpl();
    $gigya_widget->login();
  else:
    $gigya_widget->is_logged_in($current_user);
  endif;
}

add_shortcode('login_plugin', 'render_login_plugin');

function generate_random_div_id($length = 6) {
  $range = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
  $max = strlen($range) - 1;

  // Generates random id of length $length.
  $id = '';
  for ($count = 0; $count < $length; $count++) {
    $id .= $range[rand(0, $max)];
  }
  return "cmp-$id";
}

/* Load external file for future extensions */
function gigya_load_external_file() {
  $path = ((!defined('WP_CONTENT_DIR') ? ABSPATH . 'wp-content' : WP_CONTENT_DIR)) . '/gigya-custom.php';
  if (file_exists($path)) {
    load_template($path, 1);
  }
}


function get_followbar_default_buttons() {
  $buttons = array();

  $defaults_button = array(
    array("provider" => "facebook", "actionURL" => "https://www.facebook.com/gigya", "action" => "dialog"),
    array("provider" => "twitter", "followUsers" => "gigya, gigyaDev", "action" => "dialog"),
    array(
      "provider" => "googleplus",
      "actionURL" => "https://plus.google.com/107788669599113247501/posts",
      "action" => "dialog"
    )
  );

  foreach ($defaults_button as $button_object) {
    foreach ($button_object as $key => $value) {
      $button[] = "$key:'$value'\n";
    }
    $buttons[] = "{\n" . implode(",", $button) . "}\n";
  }

  return "[" . implode(",", $buttons) . "]";
}

function gigya_parse_key_pair($str) {
  $reg = preg_match_all("/([^,= ]+)=([^,= ]+)/", $str, $r);
  if ($reg) {
    return array_combine($r[1], $r[2]);
  }
  return 0;
}


function gigya_get_field_options($field) {
  global $GIGYA_STATIC_DATA;
  return $GIGYA_STATIC_DATA[$field]["options"];
}

;

function gigya_get_field_default($field) {
  global $GIGYA_STATIC_DATA;
  if ($GIGYA_STATIC_DATA[$field]) {
    return $GIGYA_STATIC_DATA[$field]["default"];
  }

  return "";
}

;


function gigya_user_info_shortcode($attrs, $info = NULL) {
  if (NULL == $info) {
    $user_info = GigyaSO_Util::get_user_info();
  }
  return $user_info->getString(key($attrs), current($attrs));
}

add_shortcode('gigya', 'gigya_user_info_shortcode');