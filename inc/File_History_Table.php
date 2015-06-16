<?php

class Files_History_Table extends WP_List_Table{
    function __construct(){
		parent::__construct();
	}
    
    function get_columns(){
		return array(
            'user'      => 'Usuario',
			'download'  => 'Última descarga',
			'file'      => 'Documento descargado',
            'login'     => "Último ingreso"
        );
	}
    
    function get_sortable_columns(){
		return array(
			'user'      => 'user',
			'download'  => 'download',
			'file'      => 'file',
			'login'     => 'login'
        );
	}
    
    function column_default($item, $column_name){
		return $item->$column_name;
	}
    
    function column_user($item){
        $detail = admin_url("admin.php?page=tekafiles_history_detail.php&u=" . $item->id);
        $log = admin_url("admin.php?page=tekafiles_history_log.php&u=" . $item->id);
        $actions = array(
			'detail'    => "<a href='$detail'>Ver descargas</a>",
			'log'       => "<a href='$log'>Ver ingresos</a>"
        );
        $rowactions = $this->row_actions($actions);
		return "$item->user $rowactions";
    }
    
    function prepare_items(){
        //if ($this->current_action()) $this->process_bulk_action();
		global $wpdb;
        
        $query = '  
            SELECT 
                u.id,
                u.display_name AS user,
                f.title AS file,
                CONCAT(d.time, " - ", d.ip) AS download,
                CONCAT(l.date, " - ", l.ip) AS login
            FROM 
                '. $wpdb->prefix .'users u
                LEFT JOIN '. $wpdb->prefix .'tekadownload d ON d.user = u.ID
                LEFT JOIN '. $wpdb->prefix .'tekafile f ON d.tekafile = f.ID
                LEFT JOIN '. $wpdb->prefix .'tekafile_log l ON u.ID = l.user
            WHERE
                ( 
                    d.ID IN (SELECT MAX(td.ID) FROM '. $wpdb->prefix .'tekadownload td GROUP BY td.user)
                    OR
                    d.ID IS NULL
                )
                AND
                (
                    l.ID IN (SELECT MAX(tl.ID) FROM '. $wpdb->prefix .'tekafile_log tl GROUP BY tl.user)
                    OR
                    l.ID IS NULL
                )
            GROUP BY u.ID  
        ';
        
        $query_count = 'SELECT COUNT(*) AS c FROM '. $wpdb->prefix .'users';
        
        $per_page = 100;        
        $columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);
        
        if ($_GET['orderby'] === 'u'){
            $orderby = 'user';
        }
        if ($_GET['orderby'] === 'd'){
            $orderby = 'd.time';
        }
        if ($_GET['orderby'] === 'f'){
            $orderby = 'f.title';
        }
        if ($_GET['orderby'] === 'l'){
            $orderby = 'l.date';
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

