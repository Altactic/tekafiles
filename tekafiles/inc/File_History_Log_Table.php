<?php

class Files_History_Log_Table extends WP_List_Table{
    function __construct(){
		parent::__construct();
	}
    
    function get_columns(){
		return array(
            'date'      => 'Fecha de ingreso',
			'ip'        => 'IP'
        );
	}
    
    function get_sortable_columns(){
		return array(
			'date'      => 'date',
			'ip'        => 'ip'
        );
	}
    
    function column_default($item, $column_name){
		return $item->$column_name;
	}
    
    function prepare_items(){
        global $wpdb;
        
        $user_id = $_GET["u"];
        
        $query = '   
            SELECT 
                l.date,
                l.ip
            FROM '. $wpdb->prefix .'tekafile_log l 
            WHERE user = '.$user_id.' 
        ';
        
        $query_count = 'SELECT COUNT(*) AS c FROM '. $wpdb->prefix .'tekafile_log WHERE user = ' . $user_id;
        
        $per_page = 100;        
        $columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);
        
        switch($_GET['orderby']){
            case "d":
                $orderby = 'date';
                break;
            case "i":
                $orderby = 'ip';
                break;
            default:
                $orderby = null;
                break;
        }
        if (isset($_GET['order'])){
            $order = mysql_real_escape_string($_GET["order"]);
        }
        if (isset($orderby) && isset($order)){
            $query .= ' ORDER BY '.$orderby.' '.$order;
        }
        else{
            $query .= ' ORDER BY l.date DESC';
        }
        
        $count_result = $wpdb->get_results($query_count);
		$total_items = $count_result[0]->c;
        
        $paged = !empty($_GET["paged"]) ? mysql_real_escape_string($_GET["paged"]) : '';
        if(empty($paged) || !is_numeric($paged) || $paged <= 0 ){ 
            $paged = 1; 
        }
        
        if(!empty($paged) && !empty($per_page)){
        	$offset = ($paged - 1) * $per_page;
         	$query .= ' LIMIT ' . (int)$offset . ',' . (int)$per_page;
       	}
        
        $this->items = $wpdb->get_results($query);
        
        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ));
    }
}