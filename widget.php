<?php
class WP_Widget_Gigya extends WP_Widget {
    function WP_Widget_Gigya() {
        parent::WP_Widget(false, $name = 'Gigya Social Optimization');	
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {
    	require_once(GIGYA_PLUGIN_PATH.'/resources/widget.php');
    	extract($args);
        $gigya_widget = new GigyaSO_Widget($instance);
        $title = apply_filters('widget_title',$instance['title']);
        
        ?>
              <?php echo $before_widget; ?>
                <?php if ( $title ) echo $before_title . $title . $after_title; ?>
                
                <?php
                $gigya_widget->render_css();
                global $current_user;
                wp_get_current_user();
                // check logged in
                  if( 0 == $current_user->ID):
                  	$gigya_widget->render_js();
					$gigya_widget->render_tmpl();
					$gigya_widget->login();
                  else:
			 		$gigya_widget->is_logged_in($current_user);
			  endif;
			  ?>          
              <?php echo $after_widget; ?>
        <?php
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
        return $new_instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {				
        $title = esc_attr($instance['title']);
        $width = esc_attr($instance['width']);
        $height = esc_attr($instance['height']);
        $button_size = esc_attr($instance['button_size']);
        $header_text = esc_attr($instance['header_text']);
        $enabledProviders = esc_attr($instance['enabledProviders']);
        
        //enabledProviders: 'facebook,twitter,yahoo,messenger,linkedin,myspace,aol,orkut'
        ?>
            <p>
            	<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> 
            	<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
            	</label>
            </p>
            <p>
            	<label for="<?php echo $this->get_field_id('header_text'); ?>"><?php _e('Header Text:'); ?> 
            	<input class="widefat" id="<?php echo $this->get_field_id('header_text'); ?>" name="<?php echo $this->get_field_name('header_text'); ?>" type="text" value="<?php echo $header_text; ?>" />
            	</label>
            </p>
            <p>
            	<label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Width:'); ?> 
            	<input class="widefat" id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" value="<?php echo $width; ?>" />
            	</label>
            </p>
            <p>
            	<label for="<?php echo $this->get_field_id('height'); ?>"><?php _e('Height:'); ?> 
            	<input class="widefat" id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="text" value="<?php echo $height; ?>" />
            	</label>
            </p>
            <p>
            	<label for="<?php echo $this->get_field_id('button_size'); ?>"><?php _e('Button Size:'); ?> 
            	<input class="widefat" id="<?php echo $this->get_field_id('button_size'); ?>" name="<?php echo $this->get_field_name('button_size'); ?>" type="text" value="<?php echo $button_size; ?>" />
            	</label>
            </p>
            <p>
            	<label for="<?php echo $this->get_field_id('enabledProviders'); ?>"><?php _e('Enabled Providers:'); ?> 
            	<input class="widefat" id="<?php echo $this->get_field_id('enabledProviders'); ?>" name="<?php echo $this->get_field_name('enabledProviders'); ?>" type="text" value="<?php echo $enabledProviders; ?> "/>
            	</label>
            </p>
        <?php 
    }

}