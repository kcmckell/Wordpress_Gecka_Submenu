<?php

class GKSM_Widget_autosubmenu extends WP_Widget {
	
	protected $default_options = array( 
	                                    'auto_title'=>0, 
	                                    'show_parent'=>0,
	                                    'show_description'=>0, 
										'start_from'=>'top'
	                                     );
	
	public function GKSM_Widget_autosubmenu () {
		$widget_ops = array('classname' => 'gksm-autosubmenu-widget', 'description' => __('Automatically shows a sub-menu starting from the ancestor menu item of the currently viewed page. ', Gecka_Submenu::Domain));
		parent::WP_Widget('gksm-autosubmenu', __('Auto Sub-menu', Gecka_Submenu::Domain), $widget_ops);
	}
    
	public function widget($args, $instance) { 
		
		extract($args, EXTR_SKIP);

		$instance['auto'] = true;
		
		$Menu = new Gecka_Submenu_Submenu($instance);

		$Menu->Widget($args, $instance);
	}
    
    public function update( $new_instance, $old_instance ) {
    	
    	$new_instance = (array) $new_instance;
        
        $instance['title'] = strip_tags( stripslashes($new_instance['title']) );
        $instance['menu']  = (int) $new_instance['menu'];
        
        
        foreach ( $this->default_options as $field => $val ) {
            if ( isset($new_instance[$field]) )
                $instance[$field] = 1;
        }
        
        $instance['start_from']  = $new_instance['start_from'];
        
        $instance['depth'] = (int) $new_instance['depth'];
        
        return $instance;
    }
	
    public function form($instance) {              
        
    	$instance = wp_parse_args( (array) $instance, $this->default_options);
        
    	$title  = isset( $instance['title'] ) ? $instance['title'] : '';
        $menu   = isset( $instance['menu'] ) ? $instance['menu'] : '';
        $depth  = isset( $instance['depth'] ) ? $instance['depth'] : 0;
        $start_from   = isset( $instance['start_from'] ) ? $instance['start_from'] : '';
         // Get menus
        $menus = get_terms( 'nav_menu', array( 'hide_empty' => false ) );

        // If no menus exists, direct the user to go and create some.
        if ( !$menus ) {
            echo '<p>'. sprintf( __('No menus have been created yet. <a href="%s">Create some</a>.'), admin_url('nav-menus.php') ) .'</p>';
            return;
        }
        
        ?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

            <p><input class="checkbox" type="checkbox" <?php checked($instance['auto_title'], true) ?> id="<?php echo $this->get_field_id('auto_title'); ?>" name="<?php echo $this->get_field_name('auto_title'); ?>" />
            <label for="<?php echo $this->get_field_id('auto_title'); ?>"><?php _e('Use top parent item title', Gecka_Submenu::Domain); ?></label><br />
            </p>
            
            <p><label for="<?php echo $this->get_field_id('nav_menu'); ?>"><?php _e('Select Menu:'); ?></label>
            <select id="<?php echo $this->get_field_id('menu'); ?>" name="<?php echo $this->get_field_name('menu'); ?>" >
            <?php
            echo '<option value="">' . __('Select') . '</option>';
            foreach ( $menus as $_menu ) {
                $selected = $menu == $_menu->term_id ? ' selected="selected"' : '';
                echo '<option'. $selected .' value="'. $_menu->term_id .'">'. $_menu->name .'</option>';
            }
            ?>
            </select></p>
            
            <p><label for="<?php echo $this->get_field_id('start_from'); ?>"><?php _e('Start menu from:', Gecka_Submenu::Domain); ?></label>
            <select id="<?php echo $this->get_field_id('start_from'); ?>" name="<?php echo $this->get_field_name('start_from'); ?>" >
            <?php
            
            $ar = array('top'      => __('Menu root', Gecka_Submenu::Domain),
            			'slibling' => __('Current page parent', Gecka_Submenu::Domain),
            			'current'  => __('Current page', Gecka_Submenu::Domain));
            
            foreach ( $ar as $_k=>$_v ) {
            
                echo '<option'. selected($start_from, $_k) .' value="'. $_k .'">'. $_v . '</option>';
            }
            ?>
            
            </select></p>
            
            <p><label for="<?php echo $this->get_field_id('depth'); ?>"><?php _e( 'Depth:', Gecka_Submenu::Domain ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('depth'); ?>" name="<?php echo $this->get_field_name('depth'); ?>" type="text" value="<?php echo $depth; ?>" />
            </p>
                        
            <p>
            <!-- <p><input class="checkbox" type="checkbox" <?php checked($instance['show_parent'], true) ?> id="<?php echo $this->get_field_id('show_parent'); ?>" name="<?php echo $this->get_field_name('show_parent'); ?>" />
	        <label for="<?php echo $this->get_field_id('show_parent'); ?>"><?php _e('Show parent item', Gecka_Submenu::Domain); ?></label><br />
	         -->
	        
	        <input class="checkbox" type="checkbox" <?php checked($instance['show_description'], true) ?> id="<?php echo $this->get_field_id('show_description'); ?>" name="<?php echo $this->get_field_name('show_description'); ?>" />
	        <label for="<?php echo $this->get_field_id('show_description'); ?>"><?php _e('Show description', Gecka_Submenu::Domain); ?></label><br />
	        </p>
        
        <?php 
    }
	
}
