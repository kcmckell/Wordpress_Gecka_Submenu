<?php

class GKSM_Widget_autosubmenu extends WP_Widget {
	
	protected $default_options = array( 
	                                    'auto_title'=>0, 
	                                    'show_parent'=>0,
	                                    'show_description'=>0, 
	                                     );
	
	public function GKSM_Widget_autosubmenu () {
		$widget_ops = array('classname' => 'gksm-autosubmenu-widget', 'description' => __('Automatically shows a sub-menu starting from the ancestor menu item of the currently viewed page. ', Gecka_Submenu::Domain));
		parent::WP_Widget('gksm-autosubmenu', __('Auto Sub-menu', Gecka_Submenu::Domain), $widget_ops);
	}
    
	public function widget($args, $instance) { 
		
		extract($args, EXTR_SKIP);

		// we get the primary menu
		if(!$instance['menu'] || !is_nav_menu($instance['menu'])) return;
		$menu = $instance['menu'];
		
		global $post;
        $Ancestor = $this->get_ancestror ($menu, $post->ID);
		
		if($Ancestor) {
			
			$auto_title = isset($instance['auto_title']) && $instance['auto_title'] ? true : false;
            $depth = isset( $instance['depth'] ) && (int)$instance['depth']  ? $instance['depth'] : 0;
            $show_parent = isset( $instance['show_parent'] ) && $instance['show_parent']  ? true : false;
            $show_description = isset( $instance['show_description'] ) && (int)$instance['show_description']  ? $instance['show_description'] : 0;
            
            global $GKSM_ID, $GKSM_MENUID;
			$GKSM_ID = $Ancestor->ID; $GKSM_MENUID = $menu;
            $out = wp_nav_menu( array( 'menu'=> $menu, 'fallback_cb'=>'', 'echo'=>false, 'show_description'=> $show_description, "depth"=>$depth ) );
            $GKSM_ID = $GKSM_MENUID = null;
			
            if($out) {
	            
            	$title = '';
                if( $auto_title ) {
                    $title = $Ancestor->title;
                }
                else $title = $instance['title'];
            	
                $title = apply_filters('widget_title', $title, $instance, $this->id_base);
                
            	echo $before_widget; 
				
	            if($title) {
	            	echo $before_title . apply_filters('widget_title', $title, $instance, $this->id_base) . $after_title;
	            }
	            
	            echo $out;           
	            
				echo $after_widget;
				
			}
		}
	}
    
    public function update( $new_instance, $old_instance ) {
        $new_instance = (array) $new_instance;
        
        $instance['title'] = strip_tags( stripslashes($new_instance['title']) );
        $instance['menu']  = (int) $new_instance['menu'];
        
        foreach ( $this->default_options as $field => $val ) {
            if ( isset($new_instance[$field]) )
                $instance[$field] = 1;
        }
        
        $instance['depth'] = (int) $new_instance['depth'];
        
        return $instance;
    }
	
    public function form($instance) {              
        
    	$instance = wp_parse_args( (array) $instance, $default_options);
        
    	$title  = isset( $instance['title'] ) ? $instance['title'] : '';
        $menu   = isset( $instance['menu'] ) ? $instance['menu'] : '';
        $depth  = isset( $instance['depth'] ) ? $instance['depth'] : 0;
        
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
            <label for="<?php echo $this->get_field_id('auto_title'); ?>"><?php _e('Use parent item title', Gecka_Submenu::Domain); ?></label><br />
            </p>
            
            <p><label for="<?php echo $this->get_field_id('nav_menu'); ?>"><?php _e('Select Menu:'); ?></label>
            <select id="<?php echo $this->get_field_id('menu'); ?>" name="<?php echo $this->get_field_name('menu'); ?>" >
            <?php
            foreach ( $menus as $_menu ) {
                $selected = $menu == $_menu->term_id ? ' selected="selected"' : '';
                echo '<option'. $selected .' value="'. $_menu->term_id .'">'. $_menu->name .'</option>';
            }
            ?>
            </select></p>
            
            <p><label for="<?php echo $this->get_field_id('depth'); ?>"><?php _e( 'Depth:', Gecka_Submenu::Domain ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('depth'); ?>" name="<?php echo $this->get_field_name('depth'); ?>" type="text" value="<?php echo $depth; ?>" /></p>
                        
            <!-- <p><input class="checkbox" type="checkbox" <?php checked($instance['show_parent'], true) ?> id="<?php echo $this->get_field_id('show_parent'); ?>" name="<?php echo $this->get_field_name('show_parent'); ?>" />
	        <label for="<?php echo $this->get_field_id('show_parent'); ?>"><?php _e('Show parent item', Gecka_Submenu::Domain); ?></label><br />
	         -->
	        <input class="checkbox" type="checkbox" <?php checked($instance['show_description'], true) ?> id="<?php echo $this->get_field_id('show_description'); ?>" name="<?php echo $this->get_field_name('show_description'); ?>" />
	        <label for="<?php echo $this->get_field_id('show_description'); ?>"><?php _e('Show description', Gecka_Submenu::Domain); ?></label><br />
	        </p>
        
        <?php 
    }
	
    private function getMenuItem ($item_id, &$menu_items) {
       foreach($menu_items as $Item) {
            if($Item->ID == $item_id) return $Item;
       }
       return false;
    }
	
	private function get_ancestror ($menu, $postID) {
        
        $MenuItems = wp_get_nav_menu_items($menu);
        $AssociatedMenuItems = wp_get_associated_nav_menu_items( $postID );
        
        foreach($AssociatedMenuItems as $associated) {
        	$Item = $this->getMenuItem($associated, &$MenuItems);
        	if($Item) break;
        }

        if( ! $Item ) return;
        
        $Ancestror = $Item;
        while(1) {
            if($Item->menu_item_parent) {
                $Item = $this->getMenuItem($Item->menu_item_parent, &$MenuItems);
                continue;
            }
            break;
        }
        
        return $Item;
    }
	
}
