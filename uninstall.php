<?php // plugin uninstall

if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) exit(); // if uninstall not called from WordPress exit

$option_name = 'q2w3_inc_manager';

$uninstall = function($option_name) {
    
    global $wpdb;
    
    $wpdb->query( 'DROP TABLE IF EXISTS '. $wpdb->prefix . $option_name ); // delete includes table    

};

if ( !is_multisite() ) { // For Single site
	
    delete_option( $option_name );
    
    $uninstall();

} else { // For Multisite
	
	global $wpdb;
	
	$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
	
	$original_blog_id = get_current_blog_id();
	
	foreach ( $blog_ids as $blog_id ) {
		
		switch_to_blog( $blog_id );
		
        delete_site_option( $option_name );
        
        $uninstall( $option_name );
	
	}
	
	switch_to_blog( $original_blog_id );

}