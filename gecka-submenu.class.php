<?php

/**
 * Main Plugin class
 * @author lox
 *
 */
class Gecka_Submenu {
	
	const Domain = 'gecka-submenu';
	
	/**
	 * Constructor
	 */
	public function __construct() {

		load_plugin_textdomain(self::Domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages');
		
		// load widgets
		add_action('widgets_init', array($this, 'widgetsInit') );
		
		// filter to show portions of nav menus
	    add_filter('wp_get_nav_menu_items', array($this, 'wp_get_nav_menu_items' ), 10, 3);
	    
	    // filter to show the description of menu items if asked
        add_filter('walker_nav_menu_start_el', array($this, 'walker_nav_menu_start_el'), 10, 4);
    
		if( !is_admin() )  {
			require_once  GKSM_PATH . '/models/Shortcodes.php';
			new Gecka_Submenu_Shortcodes();
		}
        
	}
	
	/**
	 * Init widgets
	 */
    public function widgetsInit () {

        // Check for the required plugin functions. This will prevent fatal
        // errors occurring when you deactivate the dynamic-sidebar plugin.
        if ( !function_exists('register_widget') )
            return;
        
        // Submenu widget
        include_once dirname(__FILE__) . '/widgets/Submenu.php';
        register_widget("GKSM_Widget_Submenu");
            
        // Auto submenu widget
        include_once dirname(__FILE__) . '/widgets/AutoSubmenu.php';
        register_widget("GKSM_Widget_AutoSubmenu");
    }
    
    /**
     * Retrieve child navmenu items from list of menus items matching menu ID.
     *
     * @param int $menu_id Menu Item ID.
     * @param array $items List of nav-menu items objects.
     * @return array
     */
    public function wp_get_nav_menu_items($items, $menu, $args) {
        global $GKSM_ID, $GKSM_MENUID;
        
        if( isset($GKSM_ID) && $GKSM_ID
        	&& isset($GKSM_MENUID) && $GKSM_MENUID==$menu->term_id ) $items = $this->wp_nav_menu_items_children( $GKSM_ID, $items );
    
        return $items;
    }
    
    public function wp_nav_menu_items_children($item_id, $items) {
    
        $item_list = array();
        foreach ( (array) $items as $item ) {
            if ( $item->menu_item_parent == $item_id ) {
                $item_list[] = $item;
                
                $children = $this->wp_nav_menu_items_children($item->db_id, $items);
                if ( $children ) {
                    $item_list = array_merge($item_list, $children);
                }
            }
        }
        
        return $item_list;
    }
    
    /**
     * Filter to show nav-menu items description
     *       
     * @param $item_output
     * @param $item
     * @param $depth
     * @param $args
     * @return $item_output
     */
    public function walker_nav_menu_start_el ($item_output, $item, $depth, $args) {
        if($args->show_description) {
          
            $desc .= ! empty( $item->description ) ? '<span class="description">'    . esc_html( $item->description        ) .'</span>' : '';
              
            if($desc) $item_output = str_replace('</a>', $desc.'</a>', $item_output);
        
        }
        return $item_output;
    }
}

/**
 * Walker to show menu items as a select box, used by widgets
 */
if(!class_exists('Walker_Nav_Menu_DropDown') && is_admin() ) {
    
    class Walker_Nav_Menu_DropDown extends Walker {
        /**
         * @see Walker::$tree_type
         * @since 3.0.0
         * @var string
         */
        var $tree_type = array( 'post_type', 'taxonomy', 'custom' );

        /**
         * @see Walker::$db_fields
         * @since 3.0.0
         * @todo Decouple this.
         * @var array
         */
        var $db_fields = array( 'parent' => 'menu_item_parent', 'id' => 'db_id' );

           /**
         * @see Walker::start_el()
         * @since 3.0.0
         *
         * @param string $output Passed by reference. Used to append additional content.
         * @param object $item Menu item data object.
         * @param int $depth Depth of menu item. Used for padding.
         * @param int $current_page Menu item ID.
         * @param object $args
         */
        function start_el(&$output, $item, $depth, $args) {
        	
            global $wp_query;
            $pad = str_repeat('&nbsp;', $depth * 3);
            
            $output .= "\t<option class=\"level-$depth\" value=\"".esc_attr($item->ID)."\"";
            if ( (int)$item->ID === (int)$args['selected'] )
                $output .= ' selected="selected"';
            $output .= '>';
            $output .= esc_html($pad . apply_filters( 'the_title', $item->title, $item->term_id ));

            $output .= "</option>\n";
        }
    }
}
