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
        $bgColor = esc_attr($instance['bgColor']);
        
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
            	<input class="widefat" id="<?php echo $this->get_field_id('enabledProviders'); ?>" name="<?php echo $this->get_field_name('enabledProviders'); ?>" type="text" value="<?php echo $enabledProviders; ?>"/>
            	</label>
            </p>
             <p>
            	<label for="<?php echo $this->get_field_id('bgColor'); ?>"><?php _e('Background Color (#FFFFFF): '); ?> <br/>
            	<input class="widefat" id="<?php echo $this->get_field_id('bgColor'); ?>" name="<?php echo $this->get_field_name('bgColor'); ?>" type="text" value="<?php echo $bgColor; ?>"/>
            	</label>
            </p>
        <?php 
    }
}

class WP_Widget_GigyaActivityFeed extends WP_Widget {
    function WP_Widget_GigyaActivityFeed() {
        parent::WP_Widget(false, $name = 'Gigya Activity Feed');	
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {
    	require_once(GIGYA_PLUGIN_PATH.'/resources/widget.php');
    	extract($args);
        $gigya_widget = new GigyaActivityFeed_Widget($instance);
        $title = apply_filters('widget_title',$instance['title']);
        
        ?>
              <?php echo $before_widget; ?>
                <?php if ( $title ) echo $before_title . $title . $after_title; ?>
                <?php $gigya_widget->render(); ?>
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
        $feed_id = esc_attr($instance['feed_id']);
        if(empty($initial_tab)) $feed_id = "";
        $initial_tab = esc_attr($instance['initial_tab']);
        if(empty($initial_tab)) $initial_tab = "everyone";
        
        ?>
            <p>
            	<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> 
            		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
            	</label>
            </p>
            
            <p>
            	<label for="<?php echo $this->get_field_id('initial_tab'); ?>"><?php _e('Initial Tab:'); ?> 
	            	<select class="widefat" id="<?php echo $this->get_field_id('initial_tab'); ?>" name="<?php echo $this->get_field_name('initial_tab'); ?>">
	            		<option <?php if($initial_tab == "everyone") echo "selected='true'" ?> value="everyone">Everyone</option>
	            		<option <?php if($initial_tab == "friends") echo "selected='true'" ?> value="friends">Friends</option>
	            		<option <?php if($initial_tab == "me") echo "selected='true'" ?> value="me">Me</option>
	            	</select>	
            	</label>
            </p>
            
            <p>
            	<label for="<?php echo $this->get_field_id('feed_id'); ?>"><?php _e('Feed ID:'); ?> 
            		<input class="widefat" id="<?php echo $this->get_field_id('feed_id'); ?>" name="<?php echo $this->get_field_name('feed_id'); ?>" type="text" value="<?php echo $feed_id; ?>" />
            	</label>
            </p>
        <?php 
    }

}

class WP_Widget_GigyaFollowBar extends WP_Widget {
    function WP_Widget_GigyaFollowBar() {
        parent::WP_Widget(false, $name = 'Gigya Follow Bar');	
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {
    	require_once(GIGYA_PLUGIN_PATH.'/resources/widget.php');
    	extract($args);
        $gigya_widget = new GigyaFollowBar_Widget($instance);
        $title = apply_filters('widget_title',$instance['title']);
        ?>
              <?php echo $before_widget; ?>
                <?php if ( $title ) echo $before_title . $title . $after_title; ?>
                
                <?php
               $gigya_widget->render();
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
        $buttons = esc_attr($instance['buttons']);
        $layout = esc_attr($instance['layout']);
        if($layout != "horizontal" && $layout != "vertical") $layout = "horizontal"; 
        
        
        $icon_size = esc_attr($instance['iconSize']);
        if(empty($icon_size) || !is_numeric($icon_size)) $icon_size = 32;
        if(empty($buttons)) $buttons = get_followbar_default_buttons();
        
        ?>
        	<p>
            	<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> 
            	<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
            	</label>
            </p>
            <p>
            	<label for="<?php echo $this->get_field_id('buttons'); ?>"><?php _e('Buttons:'); ?> 
            	<textarea style="width:100%;height:200px;" id="<?php echo $this->get_field_id('buttons'); ?>" name="<?php echo $this->get_field_name('buttons'); ?>">
            	<?php echo $buttons; ?>
            	</textarea>
            	</label>
            </p>
            
            <p>
            	<label for="<?php echo $this->get_field_id('layout'); ?>"><?php _e('Layout:'); ?> 
            	<select class="widefat" id="<?php echo $this->get_field_id('layout'); ?>" name="<?php echo $this->get_field_name('layout'); ?>">
            		<option <?php if($layout == "horizontal") echo "selected='true'" ?> value="horizontal">Horizontal</option>
            		<option <?php if($layout == "vertical") echo "selected='true'" ?> value="vertical">Vertical</option>
            	</select>	
            	</label>
            </p>
            
             <p>
            	<label for="<?php echo $this->get_field_id('iconSize'); ?>"><?php _e('Icon Size:'); ?> 
            	<input class="widefat" id="<?php echo $this->get_field_id('iconSize'); ?>" name="<?php echo $this->get_field_name('iconSize'); ?>" value="<?php echo $icon_size;?>"/>	
            	</label>
            </p>
            
        <?php 
    }

}