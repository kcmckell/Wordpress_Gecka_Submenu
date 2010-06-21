<?php

class GKSM_Widget_Submenu extends WP_Widget {
	
	protected $default_options = array( 
	                                    'auto_title'=>0, 
	                                    'show_parent'=>0,
	                                    'show_description'=>0, 
	                                     );
	
	public function GKSM_Widget_submenu () {
		$widget_ops = array('classname' => 'gksm-submenu-widget', 'description' => __('Displays a Sub-Menu', Gecka_Submenu::Domain));
		parent::WP_Widget('gksm-submenu', __('Sub-menu', Gecka_Submenu::Domain), $widget_ops);
	    add_action('wp_ajax_gksm_update', array($this, 'submenuOptionsAjax'));
    }
    
	public function widget($args, $instance) { 
		
		extract($args, EXTR_SKIP);

		// we get the primary menu
		if(!$instance['menu'] || !is_nav_menu($instance['menu'])) return;
		$menu = $instance['menu'];
		
		// we get the primary menu
        if(!$instance['submenu'] || !is_nav_menu_item($instance['submenu'])) return;
        $submenu = $instance['submenu'];
		
		if($submenu) {
			
			$auto_title = isset($instance['auto_title']) && $instance['auto_title'] ? true : false;
            $depth = isset( $instance['depth'] ) && (int)$instance['depth']  ? $instance['depth'] : 0;
            $show_parent = isset( $instance['show_parent'] ) && $instance['show_parent']  ? true : false;
            $show_description = isset( $instance['show_description'] ) && (int)$instance['show_description']  ? $instance['show_description'] : 0;
            
            global $GKSM_ID, $GKSM_MENUID;
			$GKSM_ID = $submenu; $GKSM_MENUID = $menu;
            $out = wp_nav_menu( array( 'fallback_cb'=>'', 'echo'=>false, 'show_description'=> $show_description, "depth"=>$depth) );
            $GKSM_ID = $GKSM_MENUID = null;
			
            if($out) {
	            
            	$title = '';
                if( $auto_title ) {
                	$MenuItems = wp_get_nav_menu_items($menu);
                	$_item = $this->getMenuItem ($submenu, &$MenuItems);
                    $title = $_item->title;
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
        $instance['submenu']  = (int) $new_instance['submenu'];
        
        foreach ( $this->default_options as $field => $val ) {
            if ( isset($new_instance[$field]) )
                $instance[$field] = 1;
        }
        
        $instance['depth'] = (int) $new_instance['depth'];
        
        return $instance;
    }
	
    public function form($instance) {              
        
    	$instance = wp_parse_args( (array) $instance, $default_options);
        
    	$title     = isset( $instance['title'] ) ? $instance['title'] : '';
        $menu      = isset( $instance['menu'] ) ? $instance['menu'] : '';
        $submenu   = isset( $instance['submenu'] ) ? $instance['submenu'] : '';
        $depth     = isset( $instance['depth'] ) ? $instance['depth'] : 0;
        
         // Get menus
        $menus = get_terms( 'nav_menu', array( 'hide_empty' => false ) );

        // If no menus exists, direct the user to go and create some.
        if ( !$menus ) {
            echo '<p>'. sprintf( __('No menus have been created yet. <a href="%s">Create some</a>.'), admin_url('nav-menus.php') ) .'</p>';
            return;
        }
        
        ?>
            <script language="javascript" type="text/javascript">
				
				var gksm_update_menu_items = function (elem) {

					var data = {
				            action: 'gksm_update',
				            ID: elem.value,
				            _ajax_nonce: '<?php echo wp_create_nonce('gksm-ajax'); ?>'
				        };
				
				        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
				        jQuery.post(ajaxurl, data, function(response) {
					        
				            jQuery('#<?php echo $this->get_field_id('submenu'); ?>').html(response);
				        });
				};
            
            </script>
            
            <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
            </p>

            <p><input class="checkbox" type="checkbox" <?php checked($instance['auto_title'], true) ?> id="<?php echo $this->get_field_id('auto_title'); ?>" name="<?php echo $this->get_field_name('auto_title'); ?>" />
            <label for="<?php echo $this->get_field_id('auto_title'); ?>"><?php _e('Use parent item title', Gecka_Submenu::Domain); ?></label><br />
            </p>
            
            <p><label for="<?php echo $this->get_field_id('menu'); ?>"><?php _e('Select Menu:'); ?></label>
            <select id="<?php echo $this->get_field_id('menu'); ?>" name="<?php echo $this->get_field_name('menu'); ?>" onchange="gksm_update_menu_items(this);" >
            <?php 
            echo '<option value="">Séléctionnez</option>';
            
           
            foreach ( $menus as $_menu ) {
                $selected = $menu == $_menu->term_id ? ' selected="selected"' : '';
                echo '<option'. $selected .' value="'. $_menu->term_id .'">'. $_menu->name .'</option>';
            
            }	
            ?>
            </select></p>
            
            <p><label for="<?php echo $this->get_field_id('submenu'); ?>"><?php _e('Select Sub-Menu:'); ?></label>
            <select id="<?php echo $this->get_field_id('submenu'); ?>" name="<?php echo $this->get_field_name('submenu'); ?>" >
            
            <?php if(!sizeof($instance)) echo '<option'. $selected .' value="'. $_menu->term_id .'">Cliquez enregister</option>';
            else 
            echo $this->menu_items_options ($menu, $submenu)
            ?>
            </select></p>
            
            <p><label for="<?php echo $this->get_field_id('depth'); ?>"><?php _e( 'Depth:', Gecka_Submenu::Domain ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('depth'); ?>" name="<?php echo $this->get_field_name('depth'); ?>" type="text" value="<?php echo $depth; ?>" />
            </p>
            
            <p>         
            <!-- <input class="checkbox" type="checkbox" <?php checked($instance['show_parent'], true) ?> id="<?php echo $this->get_field_id('show_parent'); ?>" name="<?php echo $this->get_field_name('show_parent'); ?>" />
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
	
	function submenuOptionsAjax() {
        
    	check_ajax_referer('gksm-ajax');
    	
        $id = isset($_POST['ID'])? (int)$_POST['ID'] : null;
        if(!$id) die('<option>Sélectionnez un menu ci-dessus</option>');
        echo $this->menu_items_options($id);
        
        die();
    }
	    

    public function menu_items_options ($id, $default='') {
    	
    	if(!$id) return '<option>Sélectionnez un menu ci-dessus</option>';
    	
        // Get the nav menu based on the requested menu
        $menu = wp_get_nav_menu_object( $id );
        
        if ( $menu && ! is_wp_error($menu) && !isset($menu_items) )
            $menu_items = wp_get_nav_menu_items( $menu->term_id );
        else return '<option>Menu inconnu</option>';
        
        $walker = new Walker_Nav_Menu_DropDown;
        
        $args = array( $menu_items, 0, array( 'selected'=>$default ) );

        return call_user_func_array( array(&$walker, 'walk'), $args );  
    }
	
	
}
