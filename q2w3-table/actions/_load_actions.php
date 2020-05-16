<?php

require_once '_q2w3_table_action.php';

require_once 'q2w3_table_activate_selected.php';

require_once 'q2w3_table_change_status.php';

require_once 'q2w3_table_delete_row.php';

require_once 'q2w3_table_delete_selected.php';

require_once 'q2w3_table_disable_selected.php';

require_once 'q2w3_table_new_row.php';

require_once 'q2w3_table_update_row.php';

require_once 'q2w3_table_search.php';

require_once 'q2w3_table_wp_page_select.php';


add_action( 'wp_ajax_q2w3_table_activate_selected', array( 'q2w3_table_activate_selected', 'ajax' ) );

add_action( 'wp_ajax_q2w3_table_change_status', array( 'q2w3_table_change_status', 'ajax' ) );

add_action( 'wp_ajax_q2w3_table_delete_row', array( 'q2w3_table_delete_row', 'ajax' ) );

add_action( 'wp_ajax_q2w3_table_delete_selected', array( 'q2w3_table_delete_selected', 'ajax' ) );

add_action( 'wp_ajax_q2w3_table_disable_selected', array( 'q2w3_table_disable_selected', 'ajax' ) );

add_action( 'wp_ajax_q2w3_table_new_row', array( 'q2w3_table_disable_selected', 'ajax' ) );

add_action( 'wp_ajax_q2w3_table_update_row', array( 'q2w3_table_update_row', 'ajax' ) );

add_action( 'wp_ajax_q2w3_table_search', array( 'q2w3_table_search', 'ajax' ) );

add_action( 'wp_ajax_q2w3_table_wp_page_select', array( 'q2w3_table_wp_page_select', 'ajax' ) );