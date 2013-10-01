<?php
class GigyaSO_Widget extends GigyaSO_Core {
  private $options = NULL;

  public function __construct($options = array()) {
    $this->options = $options;
    $this->cmpId = generate_random_div_id();
  }

  public function render_css($css = array()) {
    parent::render_css();
    ?>
    <link media="all" type="text/css" href="<?php echo GIGYA_PLUGIN_URL . "/css/widget.css"; ?>" rel="stylesheet">
  <?php
  }

  public function conf_and_params($params = array()) {
    $options = $this->options;
    $params = array();
    $params = array(
      "button_size" => $options['button_size'],
      "height" => $options['height'],
      "width" => $options['width'],
      "header_text" => $options['header_text'],
      "enabledProviders" => $options['enabledProviders']
    );
    $params["header_text"] = esc_attr($options['header_text']);
    $width = esc_attr($options['width']);
    if (empty($width) || !is_numeric($width)) {
      $width = "180";
    }
    $params["width"] = $width;
    $height = esc_attr($options['height']);
    if (empty($height) || !is_numeric($height)) {
      $height = "50";
    }
    $params["height"] = $height;
    $button_size = esc_attr($options['button_size']);
    if (empty($button_size) || !is_numeric($button_size)) {
      $button_size = "24";
    }
    $params["button_size"] = $button_size;
    $enabledProviders = esc_attr($options['enabledProviders']);
    if (empty($enabledProviders)) {
      $enabledProviders = "*";
    }
    $params["enabledProviders"] = $enabledProviders;
    $bgColor = trim($options["bgColor"]);
    if (!empty($bgColor)) {
      $params["bgColor"] = $bgColor;
    }
    $params["container_id"] = $this->cmpId;
    parent::conf_and_params($params);
  }

  public function login() {
    ?>

    <div id="<?php echo $this->cmpId; ?>"></div>
    <script type="text/javascript">
      //<![CDATA[
      jQuery(document).ready(function ($) {
        Gigya.Ajax.setUrl("<?php echo admin_url("admin-ajax.php"); ?>");
        Gigya.Ajax.onSignIn = function () {
          window.location.reload(true);
        };
        <?php $this->conf_and_params();?>
        gigya.services.socialize.showLoginUI(login_params);
      });
      //]]>
    </script>
  <?php
  }

  public function is_logged_in($user) {
    $bgColor = trim($this->options["bgColor"]);
    $bgColor = !empty($bgColor) ? $bgColor : "#FFFFFF;";

    ?>
    <div class="widget_gigya_user ui-helper-clearfix" style='background-color:<?php echo $bgColor; ?>'>
      <div class="thumbnail"><?php echo get_avatar($user->ID, 42, TRUE); ?></div>
      <div class="text">
        Hello <?php echo $user->nickname; ?><br/>
        <a id="gigya-logout" class="logout" href="#" title="Logout">Logout</a>
        <script type="text/javascript">
          //<![CDATA[
          jQuery(document).ready(function ($) {
            $("#gigya-logout").click(function () {
              var win = window;
              gigya.socialize.logout({
                callback: function () {
                  var iframe = $("<iframe src='<?php echo wp_logout_url(); ?>'/>").hide();
                  iframe.appendTo("body")
                  iframe.load(function () {
                    win.location.href = "<?php echo home_url()?>";
                    iframe.destroy();
                  });
                }
              });

              return false;
            });
          });
          //]]>
        </script>
      </div>
    </div>
  <?php
  }
}

class GigyaFollowBar_Widget extends GigyaSO_Core {
  private $options = NULL;

  public function __construct($options = array()) {
    $this->options = $options;
    $this->cmpId = generate_random_div_id();
  }

  public function render() {
    $options = $this->options;

    $buttons = empty($options['buttons']) ? get_followbar_default_buttons() : $options['buttons'];
    $icon_size = $options['iconSize'];
    $layout = $options['layout'];

    if (empty($icon_size) || !is_numeric($icon_size)) {
      $icon_size = "32";
    }
    if (empty($layout) || $layout != "vertical") {
      $layout = "horizontal";
    }

    ?>

    <div id="<?php echo $this->cmpId; ?>"></div>
    <script type="text/javascript">
      //<![CDATA[
      jQuery(document).ready(function ($) {
        var params = {
          "containerID": "<?php echo $this->cmpId;?>",
          "buttons": <?php echo $buttons;?>,
          "iconSize": <?php echo $icon_size;?>
        }
        gigya.services.socialize.showFollowBarUI(params);
      });
      //]]>
    </script>
  <?php

  }
}

class GigyaActivityFeed_Widget extends GigyaSO_Core {
  private $options = NULL;

  public function __construct($options = array()) {
    $this->options = $options;

  }

  public function render() {
    $options = $this->options;

    echo gigya_activity_plugin($options);
    ?>


  <?php

  }
}

class GigyaGamification_Widget extends GigyaSO_Core {
  private $options = NULL;

  public function __construct($options = array()) {
    $this->options = $options;
  }

  public function render() {
    $options = $this->options;
    echo gigya_gamification_plugin($options);

  }
}