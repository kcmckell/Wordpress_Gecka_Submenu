<?php 


Class Gecka_Submenu_Submenu {
	
	private $Options;
	private $DefaultOptions = array( 	'menu'		=> null,
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
									
	public function __construct ($Options = array()) 
	{
		
		$this->Options = wp_parse_args($Options, $this->DefaultOptions);
		
	}
	
	public function Show($Options = null) 
	{
		
		echo $this->Build($Options);
		
	}
	
	
	public function Get($Options = null) 
	{
		
		return $this->Build($Options);
	}
	
	public function GetTopLevelItem () 
	{
		return $this->TopLevelItem;
	}
	
	public function Widget($args, $instance) 
	{

    	extract($args, EXTR_SKIP);

    	$out = $this->Get($instance);

    	if($out) {
    		 
    		$auto_title = isset($instance['auto_title']) && $instance['auto_title'] ? true : false;
    		
    		$title = '';
    		if( $auto_title && is_a($this->TopLevelItem, 'StdClass') ) {
    			
    			$title = $this->TopLevelItem->title;
    		}
    		else $title = $instance['title'];
       
    		$title = apply_filters('widget_title', $title, $instance);

    		echo $before_widget;

    		if($title) {
    			echo $before_title . apply_filters('widget_title', $title, $instance) . $after_title;
    		}
    		 
    		echo $out;
    		 
    		echo $after_widget;
    	}
	}
	
	private function Build($Options = null) 
	{
		
		if( $Options !== null ) extract( wp_parse_args($Options, $this->Options) );
		else extract($this->Options);
		
		$depth = (int)$depth ? (int)$depth : 0;
        $show_description = $show_description ? true : false;
        $submenu = (int)$submenu;
		
		// menu is mandatory
		if(!$menu || !is_nav_menu($menu)) return;
		
		// if not in auto mode and no submenu specified, we use the current post
		// as the top level element
		if( !$auto && !$submenu ) {
		    
		    global $post;
			if( is_a($post, 'stdClass') && (int)$post->ID ) {
				$submenu = $post->ID;
			}
		    
		}
				
		// verify submenu ID, if provided
		if( $submenu && ( !is_nav_menu_item($submenu) && !is_page($submenu) ) ) return;
		
		$TopLevelElementId = null;
		$FallbackToPages = false;
		
		// a submenu has been specified
		if($submenu) {
		    
		    $TopLevelElementId = $submenu;
		    
		    // it is not a nav menu item, we need to guess if an existing menu item
		    // points to it
		    if( !is_nav_menu_item($submenu) ) {

		    	$AssociatedMenuItems = $this->get_associated_nav_menu_items( $submenu , 'post_type',$menu );
		         
		        // no associated menu item found, falling back to wp_list_pages
		        if( empty($AssociatedMenuItems) ) {
		        	$FallbackToPages = true;
		        }
		    }
		
		}
		
		// get menu item ancestor for the given or the current post_id in provided menu
		if( $auto ) {
			global $post;
			
			if( is_a($post, 'stdClass') && (int)$post->ID ) {
				$TopLevelItem = $this->get_ancestor ($menu, $post->ID);
			}
			if(!$TopLevelItem) return;
			
		}
		
		// builds the submenu
		$this->TopLevelItem = isset($TopLevelItem) ? $TopLevelItem : wp_setup_nav_menu_item( get_post( $TopLevelElementId ) );
		
        if( $FallbackToPages || (isset($this->TopLevelItem->showsub) && $this->TopLevelItem->showsub) ) {
        	
        	return '<ul class="sub-menu" >' . wp_list_pages( array('echo'=>false, 'title_li'=>'', "depth"=>$depth, "child_of"=>$this->TopLevelItem->object_id) ). '</ul>';
        	
        }
        
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
	private function getMenuItem ($item_id, &$menu_items) 
	{
		if(!is_array($menu_items)) return false;
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
	private function get_ancestor ($menu, $postID) 
	{
        
        $MenuItems = wp_get_nav_menu_items($menu);
       
        if(!is_page($postID))
        	$AssociatedMenuItems = $this->get_associated_nav_menu_items( $postID );
        else
        	$AssociatedMenuItems = $this->get_page_associated_nav_menu_items( $postID );
        
        // uses the first associated menu item
        foreach($AssociatedMenuItems as $associated) {
        	$Item = $this->getMenuItem($associated, &$MenuItems);
        	if($Item) break;
        }

        if( !isset($Item) || !$Item ) return;
        
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
    
    function get_associated_nav_menu_items( $object_id = 0, $object_type = 'post_type', $menu_id = 0) {
	    $object_id = (int) $object_id;
	    $menu_item_ids = array();

	    if($menu_id)
	    	$objects = get_objects_in_term( $menu_id, 'nav_menu'  );
	    
	    $query = new WP_Query;
	    $menu_items = $query->query(
		    array(
			    'meta_key' => '_menu_item_object_id',
			    'meta_value' => $object_id,
			    'post_status' => 'any',
			    'post_type' => 'nav_menu_item',
			    'showposts' => -1,
		    )
	    );
	    
	    foreach( (array) $menu_items as $menu_item ) {
		    if ( isset( $menu_item->ID ) && is_nav_menu_item( $menu_item->ID ) ) {
			    if ( get_post_meta( $menu_item->ID, '_menu_item_type', true ) != $object_type )
				    continue;

				if( $menu_id && !in_array($menu_item->ID, $objects) ) continue; 
				    
			    $menu_item_ids[] = (int) $menu_item->ID;
		    }
	    }

	    return array_unique( $menu_item_ids );  
    }
    
    
    private function get_page_associated_nav_menu_items( $postID ) 
    {
    	$AssociatedMenuItems = array();
    	if(!is_page($postID)) return $AssociatedMenuItems;
        
    	$ancestors = array_reverse( get_post_ancestors( $postID ));
    		
    	foreach ($ancestors as $ancestor) {
    		$AssociatedMenuItems = wp_get_associated_nav_menu_items( $ancestor );
    		if(sizeof($AssociatedMenuItems)) break;
    	}
    	
    	return $AssociatedMenuItems;
    }
    
}
