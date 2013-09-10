<?php
$GIGYA_STATIC_DATA = array();
$GIGYA_STATIC_DATA["login_button_style"] = array(
  "default" => "fullLogo",
  "options" => array(
    "fullLogo" => "Full Logo",
    "standard" => "Standard",
    "signInWith" => "Sign In With"
  )
);


$GIGYA_STATIC_DATA["short_url"] = array(
  "default" => "never",
  "options" => array(
    "always" => "Always",
    "whenRequired" => "When Required",
    "never" => "Never"
  )
);

$GIGYA_STATIC_DATA["connect_without_login_behavior"] = array(
  "default" => "loginExistingUser",
  "options" => array(
    "tempUser" => "Temp User",
    "alwaysLogin" => "Always Login",
    "loginExistingUser" => "Login Existing User",
  )
);

$GIGYA_STATIC_DATA["login_term_link"] = array("default" => "0");
$GIGYA_STATIC_DATA["comments_enable_share_providers"] = array("default" => "*");
$GIGYA_STATIC_DATA["comments_enable_share_activity"] = $GIGYA_STATIC_DATA["reaction_enable_share_activity"] = array(
  "default" => "'external",
  "options" => array(
    "both" => "both",
    "external" => "External"
  )
);

$GIGYA_STATIC_DATA["share_show_counts"] = $GIGYA_STATIC_DATA["reaction_show_counts"] = array(
  "default" => "right",
  "options" => array(
    "right" => "Right",
    "top" => "Top",
    "none" => "None"
  )
);

$GIGYA_STATIC_DATA["reaction_providers"] = array("default" => "*");
$GIGYA_STATIC_DATA["reaction_multiple"] = array("default" => "1");
$GIGYA_STATIC_DATA["reaction_plugin"] = array("default" => "0");
$GIGYA_STATIC_DATA["reaction_layout"] = array(
  "default" => "horizontal",
  "options" => array(
    "horizontal" => "Horizontal",
    "vertical" => "Vertical"
  )
);

$GIGYA_STATIC_DATA["reaction_count_type"] = array(
  "default" => "number",
  "options" => array(
    "number" => "Number",
    "percentage" => "Percentage"
  )
);

$GIGYA_STATIC_DATA["reaction_position"] = array(
  "default" => "bottom",
  "options" => array(
    "bottom" => "Bottom",
    "top" => "Top",
    "both" => "Both"
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
    "private" => "Private",
    "public" => "Public",
    "friends" => "Friends"
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
    'us1.gigya.com' => 'US Data Center',
    'eu1.gigya.com' => 'EU Data Center',
  )
);