<?php 


Class Gecka_Submenu_Submenu {
	
	private $Options;
	private $DefaultOptions = array( 'menu'		=> null,
									 'submenu'	=> null,
									 'auto' 	=> false,
									 'post_id'	=> null,
									 'title'	=> '',
									 'depth'	=> 0,
									 'auto_title' 	=> false,
									 'show_description'		=> false
									);
	
	// Always holds the latest Top level menu Item
	private $TopLevelItem;
									
	public function __construct ($Options = array()) {
		
		$this->Options = wp_parse_args($Options, $this->DefaultOptions);
		
	}
	
	public function Show($Options = null) {
		
		echo $this->Build($Options);
		
	}
	
	
	public function Get($Options = null) {
		
		return $this->Build($Options);
	}
	
	public function GetTopLevelItem () {
		return $this->TopLevelItem;
	}
	
	public function Widget($args, $instance) {

    	extract($args, EXTR_SKIP);

    	$out = $this->Get($instance);

    	if($out) {
    		 
    		$auto_title = isset($instance['auto_title']) && $instance['auto_title'] ? true : false;
    		
    		$title = '';
    		if( $auto_title && is_a($this->TopLevelItem, 'StdClass') ) {
    			
    			$title = $this->TopLevelItem->title;
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
	
	private function Build($Options = null) {
		
		if( $Options !== null ) extract( wp_parse_args($Options, $this->Options) );
		else extract($this->Options);
		
		// menu is mandatory
		if(!$menu || !is_nav_menu($menu)) return;
		
		// verify submenu id if provided
		if( (int)$submenu && !is_nav_menu_item($submenu)) return;
		
		// not auto or no submenu given, makes it an auto submenu with current post as parent
		if( !$auto && !(int)$submenu) {
			
			global $post;
			if( is_a($post, 'stdClass') && (int)$post->ID ) {
				$auto = true;	
				$post_id = $post->ID;
			}
			
		}
		
		// get menu item ancestor for the given or the current post_id in provided menu
		if( $auto ) {
			
			if( (int)$post_id ) $post_id = (int)$post_id;	
			else {
				global $post;
				$post_id = (int)$post->ID;
			}
			
			$Ancestor = $this->get_ancestor ($menu, $post_id);
		}
		
		if( ($auto && !$Ancestor) && !(int)$submenu) return;
		
		
		// builds the submenu
		$depth 		= (int)$depth ? (int)$depth : 0;
        $show_description = $show_description ? true : false;
   
        $this->TopLevelItem = ($auto && $Ancestor) ? $Ancestor : wp_setup_nav_menu_item( get_post( $submenu ) );
		
        // global variable for the filter to use (see filter in gecka-submenu.class.php)
        global $GKSM_ID, $GKSM_MENUID;
		$GKSM_ID = $this->TopLevelItem->ID; $GKSM_MENUID = $menu;
        
		// gets the nav menu
		$out = wp_nav_menu( array( 'menu'=> $menu, 'fallback_cb'=>'', 'echo'=>false, 'show_description'=> $show_description, "depth"=>$depth ) );
        
		// reset global variables
		$GKSM_ID = $GKSM_MENUID = null;
			
		return $out;
	}
	
	/**
	 * Gets a menu item from a list of menu items, avoiding SQL queries
	 * @param int $item_id id of item to retreive
	 * @param array $menu_items array of menu items
	 * @return object $Item a menu item object or false
	 */
	private function getMenuItem ($item_id, &$menu_items) {
       foreach($menu_items as $Item) {
            if($Item->ID == $item_id) return $Item;
       }
       return false;
    }
	
    /**
     * Gets the top parent menu item of a given post from a specific menu
     * @param int $menu menu ID to seach for post
     * @param int $postID post ID to look for
     * @return object $Item a menu item object or false
     */
	private function get_ancestor ($menu, $postID) {
        
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