<?

Class Gecka_Submenu_NavMenuHacks {

    function __construct() {
        
        if(is_admin() ) {
        
            /* use custom walker */     
            add_filter( 'wp_edit_nav_menu_walker', array($this, 'custom_walker') );
            
            /* add custom field */
            add_action('wp_nav_menu_item_custom_fields', array($this, 'wp_nav_menu_item_custom_fields'),10,4  );
            
            /* save custom field */
            add_action('wp_update_nav_menu_item', array($this, 'wp_update_nav_menu_item'),10,3  );
        
        }
        //add_filter( 'wp_get_nav_menu_items', array($this, 'wp_get_nav_menu_items'),15,3 );
            
        /* set up nav menu item with custom property */
        add_filter( 'wp_setup_nav_menu_item', array($this, 'wp_setup_nav_menu_item') );
    
        /* customize menu display */
        add_filter( 'walker_nav_menu_start_el', array($this, 'walker_nav_menu_start_el'), 10,4 );
        
         
    }
    function wp_get_nav_menu_items ($items, $menu, $args) 
    {
    	foreach ($items as $item) {
    		
    		if($item->showsub==='1') {
    			$pages = get_pages(array('child_of'=>$item->object_id, 'hierarchical' => 0) );
    			
    			foreach ($pages as $key=>$page) {
	    			$page = wp_setup_nav_menu_item($page);
	    			$page->menu_item_parent = $page->post_parent == $item->object_id ? $item->ID : $page->post_parent ;
	    			$page->db_id = $page->ID;
					$items[] = $page;
	    		}
    		}

    		
    		
    	}
/*    	foreach ($items as $item) {
    		
    		$item->description='';
    		$item->post_content='';
    		
    		echo $item->ID ."<br>";
    		echo $item->db_id ."<br>";
    		
    		echo $item->menu_item_parent ."<br>";
    		echo $item->post_parent ."<br>";
    		echo $item->object_id ."<br>";
    		
    		echo $item->object ."<br>";
    		echo $item->title ."<br>"."<br>";
    	}
*/
    	return $items;
    }
    
    function walker_nav_menu_start_el ( $item_output, $item, $depth, $args)
    {
 
    	 if( isset($item->showsub) && $item->showsub =='1') {
        	$args = array( 'depth'        => 0,
					        'child_of'     => 449,
					        'echo'         => 0, 'title_li' => '' );
        	
            $item_output = $item_output . '<ul class="sub-menu" >' . wp_list_pages($args) . '</ul>';
           
        }
	    return $item_output;
    }
    

    /**
     * Setup the nav menu object to have the showsub propertie
     */
    function wp_setup_nav_menu_item($menu_item) 
    {
        if ( isset( $menu_item->post_type ) ) {
		    if ( 'nav_menu_item' == $menu_item->post_type ) {
		       	$menu_item->showsub = empty( $menu_item->showsub ) ? get_post_meta( $menu_item->ID, '_menu_item_showsub', true ) : $menu_item->showsub;
		    }
	    }
        return $menu_item;
    }
   

    /**
     * Saves the new field
     */
    function wp_update_nav_menu_item($menu_id, $menu_item_db_id, $args) 
    {

        $args['menu-item-showsub'] = isset( $_POST['menu-item-showsub'][$menu_item_db_id] ) ? $_POST['menu-item-showsub'][$menu_item_db_id] : '';
        
        if ( empty( $args['menu-item-showsub'] ) ) {
		    $args['menu-item-showsub'] = '0';
        }

       update_post_meta( $menu_item_db_id, '_menu_item_showsub', $args['menu-item-showsub'] );
	
    }
    

    /**
     * Adds a custom field
     */
    function custom_walker($a) 
    {
        return 'Gecka_Walker_Nav_Menu_Edit';
    }
    
    function wp_nav_menu_item_custom_fields ( $item_id, $item, $depth, $args) {
    	if($item->object === 'page') {
        	?>
        		<p class="description description-wide">
					<label for="edit-menu-item-showsub-<?php echo $item_id; ?>">
						<input type="checkbox" id="edit-menu-item-showsub-<?php echo $item_id; ?>" class="widefat edit-menu-item-showsub" name="menu-item-showsub[<?php echo $item_id; ?>]" value="1" <?php if($item->showsub == '1') echo 'checked="checked"'; ?>/>
					    <?php _e( 'Automatically populate child pages', Gecka_Submenu::Domain ); ?>
					</label>
		        </p>
        	<?php
    	}
    }
     
}
/**
 * Create HTML list of nav menu input items.
 *
 * @package WordPress
 * @since 3.0.0
 * @uses Walker_Nav_Menu
 */
class Gecka_Walker_Nav_Menu_Edit extends Walker_Nav_Menu  {
	/**
	 * @see Walker_Nav_Menu::start_lvl()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference.
	 * @param int $depth Depth of page.
	 */
	function start_lvl(&$output) {}

	/**
	 * @see Walker_Nav_Menu::end_lvl()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference.
	 * @param int $depth Depth of page.
	 */
	function end_lvl(&$output) {
	}

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
	function start_el(&$output, $item, $depth, $args) 
	{
		global $_wp_nav_menu_max_depth;
		$_wp_nav_menu_max_depth = $depth > $_wp_nav_menu_max_depth ? $depth : $_wp_nav_menu_max_depth;

		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

		ob_start();
		$item_id = esc_attr( $item->ID );
		$removed_args = array(
			'action',
			'customlink-tab',
			'edit-menu-item',
			'menu-item',
			'page-tab',
			'_wpnonce',
		);

		$original_title = '';
		if ( 'taxonomy' == $item->type ) {
			$original_title = get_term_field( 'name', $item->object_id, $item->object, 'raw' );
		} elseif ( 'post_type' == $item->type ) {
			$original_object = get_post( $item->object_id );
			$original_title = $original_object->post_title;
		}

		$classes = array(
			'menu-item menu-item-depth-' . $depth,
			'menu-item-' . esc_attr( $item->object ),
			'menu-item-edit-' . ( ( isset( $_GET['edit-menu-item'] ) && $item_id == $_GET['edit-menu-item'] ) ? 'active' : 'inactive'),
		);

		$title = $item->title;

		if ( isset( $item->post_status ) && 'draft' == $item->post_status ) {
			$classes[] = 'pending';
			/* translators: %s: title of menu item in draft status */
			$title = sprintf( __('%s (Pending)'), $item->title );
		}

		$title = empty( $item->label ) ? $title : $item->label;

		?>
		<li id="menu-item-<?php echo $item_id; ?>" class="<?php echo implode(' ', $classes ); ?>">
			<dl class="menu-item-bar">
				<dt class="menu-item-handle">
					<span class="item-title"><?php echo esc_html( $title ); ?></span>
					<span class="item-controls">
						<span class="item-type"><?php echo esc_html( $item->type_label ); ?></span>
						<span class="item-order">
							<a href="<?php
								echo wp_nonce_url(
									add_query_arg(
										array(
											'action' => 'move-up-menu-item',
											'menu-item' => $item_id,
										),
										remove_query_arg($removed_args, admin_url( 'nav-menus.php' ) )
									),
									'move-menu_item'
								);
							?>" class="item-move-up"><abbr title="<?php esc_attr_e('Move up'); ?>">&#8593;</abbr></a>
							|
							<a href="<?php
								echo wp_nonce_url(
									add_query_arg(
										array(
											'action' => 'move-down-menu-item',
											'menu-item' => $item_id,
										),
										remove_query_arg($removed_args, admin_url( 'nav-menus.php' ) )
									),
									'move-menu_item'
								);
							?>" class="item-move-down"><abbr title="<?php esc_attr_e('Move down'); ?>">&#8595;</abbr></a>
						</span>
						<a class="item-edit" id="edit-<?php echo $item_id; ?>" title="<?php _e('Edit Menu Item'); ?>" href="<?php
							echo ( isset( $_GET['edit-menu-item'] ) && $item_id == $_GET['edit-menu-item'] ) ? admin_url( 'nav-menus.php' ) : add_query_arg( 'edit-menu-item', $item_id, remove_query_arg( $removed_args, admin_url( 'nav-menus.php#menu-item-settings-' . $item_id ) ) );
						?>"><?php _e( 'Edit Menu Item' ); ?></a>
					</span>
				</dt>
			</dl>

			<div class="menu-item-settings" id="menu-item-settings-<?php echo $item_id; ?>">
				<?php if( 'custom' == $item->type ) : ?>
					<p class="field-url description description-wide">
						<label for="edit-menu-item-url-<?php echo $item_id; ?>">
							<?php _e( 'URL' ); ?><br />
							<input type="text" id="edit-menu-item-url-<?php echo $item_id; ?>" class="widefat code edit-menu-item-url" name="menu-item-url[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->url ); ?>" />
						</label>
					</p>
				<?php endif; ?>
				<p class="description description-thin">
					<label for="edit-menu-item-title-<?php echo $item_id; ?>">
						<?php _e( 'Navigation Label' ); ?><br />
						<input type="text" id="edit-menu-item-title-<?php echo $item_id; ?>" class="widefat edit-menu-item-title" name="menu-item-title[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->title ); ?>" />
					</label>
				</p>
				<p class="description description-thin">
					<label for="edit-menu-item-attr-title-<?php echo $item_id; ?>">
						<?php _e( 'Title Attribute' ); ?><br />
						<input type="text" id="edit-menu-item-attr-title-<?php echo $item_id; ?>" class="widefat edit-menu-item-attr-title" name="menu-item-attr-title[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->post_excerpt ); ?>" />
					</label>
				</p>
				<p class="field-link-target description description-thin">
					<label for="edit-menu-item-target-<?php echo $item_id; ?>">
						<?php _e( 'Link Target' ); ?><br />
						<select id="edit-menu-item-target-<?php echo $item_id; ?>" class="widefat edit-menu-item-target" name="menu-item-target[<?php echo $item_id; ?>]">
							<option value="" <?php selected( $item->target, ''); ?>><?php _e('Same window or tab'); ?></option>
							<option value="_blank" <?php selected( $item->target, '_blank'); ?>><?php _e('New window or tab'); ?></option>
						</select>
					</label>
				</p>
				<p class="field-css-classes description description-thin">
					<label for="edit-menu-item-classes-<?php echo $item_id; ?>">
						<?php _e( 'CSS Classes (optional)' ); ?><br />
						<input type="text" id="edit-menu-item-classes-<?php echo $item_id; ?>" class="widefat code edit-menu-item-classes" name="menu-item-classes[<?php echo $item_id; ?>]" value="<?php echo esc_attr( implode(' ', $item->classes ) ); ?>" />
					</label>
				</p>
				<p class="field-xfn description description-thin">
					<label for="edit-menu-item-xfn-<?php echo $item_id; ?>">
						<?php _e( 'Link Relationship (XFN)' ); ?><br />
						<input type="text" id="edit-menu-item-xfn-<?php echo $item_id; ?>" class="widefat code edit-menu-item-xfn" name="menu-item-xfn[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->xfn ); ?>" />
					</label>
				</p>
				<p class="field-description description description-wide">
					<label for="edit-menu-item-description-<?php echo $item_id; ?>">
						<?php _e( 'Description' ); ?><br />
						<textarea id="edit-menu-item-description-<?php echo $item_id; ?>" class="widefat edit-menu-item-description" rows="3" cols="20" name="menu-item-description[<?php echo $item_id; ?>]"><?php echo esc_html( $item->description ); ?></textarea>
						<span class="description"><?php _e('The description will be displayed in the menu if the current theme supports it.'); ?></span>
					</label>
				</p>
                <p class="field-description description description-wide">
					<label for="edit-menu-item-description-<?php echo $item_id; ?>">
						<?php _e( 'Description' ); ?><br />
						<textarea id="edit-menu-item-description-<?php echo $item_id; ?>" class="widefat edit-menu-item-description" rows="3" cols="20" name="menu-item-description[<?php echo $item_id; ?>]"><?php echo esc_html( $item->description ); ?></textarea>
						<span class="description"><?php _e('The description will be displayed in the menu if the current theme supports it.'); ?></span>
					</label>
				</p>
				<?php
				do_action('wp_nav_menu_item_custom_fields', $item_id, $item, $depth, $args);
				?>
				<div class="menu-item-actions description-wide submitbox">
					<?php if( 'custom' != $item->type ) : ?>
						<p class="link-to-original">
							<?php printf( __('Original: %s'), '<a href="' . esc_attr( $item->url ) . '">' . esc_html( $original_title ) . '</a>' ); ?>
						</p>
					<?php endif; ?>
					<a class="item-delete submitdelete deletion" id="delete-<?php echo $item_id; ?>" href="<?php
					echo wp_nonce_url(
						add_query_arg(
							array(
								'action' => 'delete-menu-item',
								'menu-item' => $item_id,
							),
							remove_query_arg($removed_args, admin_url( 'nav-menus.php' ) )
						),
						'delete-menu_item_' . $item_id
					); ?>"><?php _e('Remove'); ?></a> <span class="meta-sep"> | </span> <a class="item-cancel submitcancel" id="cancel-<?php echo $item_id; ?>" href="<?php	echo add_query_arg( array('edit-menu-item' => $item_id, 'cancel' => time()), remove_query_arg( $removed_args, admin_url( 'nav-menus.php' ) ) );
						?>#menu-item-settings-<?php echo $item_id; ?>"><?php _e('Cancel'); ?></a>
				</div>

				<input class="menu-item-data-db-id" type="hidden" name="menu-item-db-id[<?php echo $item_id; ?>]" value="<?php echo $item_id; ?>" />
				<input class="menu-item-data-object-id" type="hidden" name="menu-item-object-id[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->object_id ); ?>" />
				<input class="menu-item-data-object" type="hidden" name="menu-item-object[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->object ); ?>" />
				<input class="menu-item-data-parent-id" type="hidden" name="menu-item-parent-id[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->menu_item_parent ); ?>" />
				<input class="menu-item-data-position" type="hidden" name="menu-item-position[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->menu_order ); ?>" />
				<input class="menu-item-data-type" type="hidden" name="menu-item-type[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->type ); ?>" />
			</div><!-- .menu-item-settings-->
			<ul class="menu-item-transport"></ul>
		<?php
		$output .= ob_get_clean();
	}
}
