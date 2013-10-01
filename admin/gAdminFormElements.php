<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Yaniv Aran-Shamir
 * Date: 9/8/13
 * Time: 4:50 PM
 */

class gAdminFormElements {

  public static function gigya_input_field($id = "", $label = "", $default = NULL, $desc = NULL, $class = '') {
    $value = gigya_get_option($id);
    if ($default && empty($value)) {
      $value = $default;
    }
    ?>
    <div class="row text-field <?php echo $class ?>">
      <div class="span3">
        <label for="gigya_<?php echo $id; ?>">
          <?php _e($label); ?>
        </label>
      </div>
      <div class="span6">
        <input type="text" size="80" class="input-xlarge" value="<?php echo $value; ?>" id="gigya_<?php echo $id; ?>"
               name="<?php echo GIGYA_SETTINGS_PREFIX ?>[<?php echo $id; ?>]"/>
        <br/>
        <?php if (!empty($desc)): ?>
          <i>
            <small><?php echo $desc; ?></small>
          </i>
        <?php endif; ?>
      </div>
    </div>
  <?php
  }

  public static function gigya_checkbox_field($id = "", $label = "", $default = NULL, $desc = NULL, $class = '') {
    $value = gigya_get_option($id);
    if ($default && is_null($value)) {
      $value = $default;
    }
    ?>
    <div class="row checkbox <?php echo $class ?>">
      <div class="span3">
        <label for="gigya_<?php echo $id; ?>">
          <input type="hidden" value="0" id="gigya_<?php echo $id; ?>" name="<?php echo GIGYA_SETTINGS_PREFIX ?>[<?php echo $id; ?>]"/>
          <input type="checkbox" <?php echo($value || $value == "1" ? "checked" : ""); ?> value="1"
                 id="gigya_<?php echo $id; ?>" name="<?php echo GIGYA_SETTINGS_PREFIX ?>[<?php echo $id; ?>]"/>
          <?php _e($label); ?>
        </label>
        <?php if ($desc): ?>
          <br/>
          <i>
            <small><?php echo $desc; ?></small>
          </i>
        <?php endif; ?>
      </div>
    </div>
  <?php
  }

  public static function gigya_textarea_field($id = "", $label = "", $default = NULL, $desc = NULL, $class = '') {
    $value = gigya_get_option($id);
    if ($default && empty($value)) {
      $value = $default;
    }
    ?>
    <div class="row textarea <?php echo $class ?>">
      <div class="span3">
        <label for="gigya_<?php echo $id; ?>">
          <?php _e($label); ?>
        </label>
      </div>
      <div class="span6">
        <textarea rows="5" cols="20" class="large-text" id="gigya_<?php echo $id; ?>"
                  name="<?php echo GIGYA_SETTINGS_PREFIX ?>[<?php echo $id; ?>]"><?php echo $value; ?></textarea>
        <?php if ($desc): ?>
          <br/>
          <i>
            <small><?php echo $desc; ?></small>
          </i>
        <?php endif; ?>
      </div>
    </div>
  <?php
  }

  public static function gigya_select_field($id = "", $options = array(), $label = "", $default = NULL, $desc = NULL, $class = '') {
    $value = gigya_get_option($id);
    if ($default && empty($value)) {
      $value = $default;
    }
    ?>
    <div class="row select <?php echo $class ?>">
      <div class="span3">
        <label for="gigya_<?php echo $id; ?>">
          <?php _e($label); ?>
          <?php if ($desc): ?>
            <i class="icon-question-sign" data-html="1" data-content="<?php echo esc_html($desc); ?>"
               data-title="<?php echo $label; ?>" rel="popover"></i>
          <?php endif; ?>
        </label>
      </div>
      <div class="span6">
        <select id="gigya_<?php echo $id; ?>" name="<?php echo GIGYA_SETTINGS_PREFIX ?>[<?php echo $id; ?>]">
          <?php foreach ($options as $key => $option) { ?>
            <option  <?php if ($value == $key)
              echo "selected='true'"; ?> value="<?php echo $key; ?>"><?php echo $option; ?></option>
          <?php } ?>
        </select>
        <?php if ($desc): ?>
          <br/>
          <i>
            <small><?php echo $desc; ?></small>
          </i>
        <?php endif; ?>
      </div>
    </div>
  <?php
  }

}