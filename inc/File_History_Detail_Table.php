<?php

class Files_History_Detail_Table extends WP_List_Table{
    function __construct(){
		parent::__construct();
	}
    
    function get_columns(){
		return array(
            'file'      => 'Documento',
			'category'  => 'Categoría',
			'date'      => 'Cargado',
			'time'      => 'Descargado',
			'ip'        => 'IP'
        );
	}
    
    function get_sortable_columns(){
		return array(
			'file'      => 'file',
			'category'  => 'category',
			'date'      => 'date',
			'time'      => 'time',
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
                f.title AS file, 
                f.category, 
                f.date, 
                d.time, 
                d.ip 
            FROM 
                '. $wpdb->prefix .'tekadownload d 
                JOIN '. $wpdb->prefix .'tekafile f ON d.tekafile = f.ID 
            WHERE user = ' . $user_id . ' 
        ';
        
        $query_count = 'SELECT COUNT(*) AS c FROM '. $wpdb->prefix .'tekadownload WHERE user = ' . $user_id;
        
        $per_page = 100;        
        $columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);
        
        
        switch($_GET['orderby']){
            case "f":
                $orderby = 'file';
                break;
            case "c":
                $orderby = 'category';
                break;
            case "d":
                $orderby = 'date';
                break;
            case "t":
                $orderby = 'time';
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
            $query .= ' ORDER BY d.time DESC';
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

