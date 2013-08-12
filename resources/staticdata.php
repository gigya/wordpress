<?php
$GIGYA_STATIC_DATA = array();
$GIGYA_STATIC_DATA["login_button_style"] = array(
  "default" => "fullLogo",
  "options" => array(
    array("v" => "fullLogo", "t" => "Full Logo", "d" => TRUE),
    array("v" => "standard", "t" => "Standard"),
    array("v" => "signInWith", "t" => "Sign In With")
  )
);


$GIGYA_STATIC_DATA["short_url"] = array(
  "default" => "never",
  "options" => array(
    array("v" => "always", "t" => "Always"),
    array("v" => "whenRequired", "t" => "When Required"),
    array("v" => "never", "t" => "Never", "d" => TRUE)
  )
);

$GIGYA_STATIC_DATA["connect_without_login_behavior"] = array(
  "default" => "loginExistingUser",
  "options" => array(
    array("v" => "tempUser", "t" => "Temp User"),
    array("v" => "alwaysLogin", "t" => "Always Login"),
    array("v" => "loginExistingUser", "t" => "Login Existing User", "d" => TRUE)
  )
);

$GIGYA_STATIC_DATA["login_term_link"] = array("default" => "0");
$GIGYA_STATIC_DATA["comments_enable_share_providers"] = array("default" => "*");
$GIGYA_STATIC_DATA["comments_enable_share_activity"] = $GIGYA_STATIC_DATA["reaction_enable_share_activity"] = array(
  "default" => "'external",
  "options" => array(
    array("v" => "both", "t" => "both"),
    array("v" => "external", "t" => "External", "d" => TRUE)
  )
);

$GIGYA_STATIC_DATA["share_show_counts"] = $GIGYA_STATIC_DATA["reaction_show_counts"] = array(
  "default" => "right",
  "options" => array(
    array("v" => "right", "t" => "Right", "d" => TRUE),
    array("v" => "top", "t" => "Top"),
    array("v" => "none", "t" => "None")
  )
);

$GIGYA_STATIC_DATA["reaction_providers"] = array("default" => "*");
$GIGYA_STATIC_DATA["reaction_multiple"] = array("default" => "1");
$GIGYA_STATIC_DATA["reaction_plugin"] = array("default" => "0");
$GIGYA_STATIC_DATA["reaction_layout"] = array(
  "default" => "horizontal",
  "options" => array(
    array("v" => "horizontal", "t" => "Horizontal", "d" => TRUE),
    array("v" => "vertical", "t" => "Vertical")
  )
);

$GIGYA_STATIC_DATA["reaction_count_type"] = array(
  "default" => "number",
  "options" => array(
    array("v" => "number", "t" => "Number", "d" => TRUE),
    array("v" => "percentage", "t" => "Percentage")
  )
);

$GIGYA_STATIC_DATA["reaction_position"] = array(
  "default" => "bottom",
  "options" => array(
    array("v" => "bottom", "t" => "Bottom", "d" => TRUE),
    array("v" => "top", "t" => "Top"),
    array("v" => "both", "t" => "Both")
  )
);

$GIGYA_STATIC_DATA["gamification_notification"] = array("default" => "1");
$GIGYA_STATIC_DATA["gamification_period"] = array("default" => "7");
$GIGYA_STATIC_DATA["gamification_type"] = array("default" => "game");
$GIGYA_STATIC_DATA["gamification_count"] = array("default" => "12");
$GIGYA_STATIC_DATA["gamification_width"] = array("default" => "300");

$GIGYA_STATIC_DATA["activity_privacy"] = array(
  "default" => "private",
  "options" => array(
    array("v" => "private", "t" => "Private", "d" => TRUE),
    array("v" => "public", "t" => "Public"),
    array("v" => "friends", "t" => "Friends")
  )
);

$GIGYA_STATIC_DATA["activity_feed_id"] = array("default" => get_bloginfo(""));
$GIGYA_STATIC_DATA["activity_site_name"] = array("default" => get_bloginfo("name"));
$GIGYA_STATIC_DATA["activity_initial_tab"] = array("default" => "everyone");
$GIGYA_STATIC_DATA["activity_width"] = array("default" => "300");


$GIGYA_STATIC_DATA["load_jquery"] = array(
  "default" => "",
  "options" => array(
    array("v" => "", "t" => "No", "d" => TRUE),
    array("v" => "1", "t" => "Load From Site"),
    array("v" => "2", "t" => "Load From Google CDN")
  )
);

$GIGYA_STATIC_DATA['data_center'] = array(
  'default' => 'us1.gigya.com',
  'options' => array(
    array('v' => 'us1.gigya.com', 't' => 'US Data Center'),
    array('v' => 'eu1.gigya.com', 't' => 'EU Data Center'),
  )
);