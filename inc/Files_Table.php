<?php

class Files_Table extends WP_List_Table {

	function __construct() {
		parent::__construct( array(
			'singular' => 'Library',
			'plural' => 'Libraries',
			'ajax' => false));
	}

	function get_columns () {
		return array(
			'cb' => "<input type='checkbox' />",
			'title' => 'Título',
			'description' => 'Descripción',
			'category' => 'Categoría',
			'enabled' => 'Habilitado');
	}

	function get_sortable_columns () {
		return array(
			'title' => 'title',
			'category' => 'category');
	}

	function column_default ($item, $column_name) {
		return $item->$column_name;
	}

	function column_cb($item) {
		return "<input type='checkbox' name='file[$item->file]' value='$item->ID' />";
	}

	function column_title($item) {
		$edit = admin_url("admin.php?page=tekafiles_new.php&e=$item->ID");
		$report = admin_url("admin.php?page=tekafiles_report.php&t=$item->ID");
		$actions = array(
			'edit' => "<a href='$edit'>Editar</a>",
			'report' => "<a href='$report'>Permisos</a>");
		$rowactions = $this->row_actions($actions);
		return "$item->title $rowactions";
	}

	function column_enabled($item) {
		if ($item->enabled) return 'Si';
		else return 'No';
	}

	function get_bulk_actions() {
		return array(
			'delete' => 'Eliminar',
			'enable' => 'Habilitar',
			'disable' => 'Deshabilitar'
		);
	}

	function process_bulk_action() {
        if ( isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) ) {
            $nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
            $action = 'bulk-' . $this->_args['plural'];
            if ( ! wp_verify_nonce( $nonce, $action ) )
                wp_die( 'Nope! Security check failed!' );
        }
        if (isset($_POST['file'])) {
	        global $wpdb;
	        $action = $this->current_action();
	        $items = $_POST['file'];
	        $files = array_keys($items);
	        $items = join(',', $items);
	        switch ( $action ) {
	            case 'delete':
	            	foreach ($files as $file) {
	            		if (is_file($file)) unlink($file);
	            	}
	            	$query = "DELETE FROM {$wpdb->prefix}tekafile_user
	            		WHERE tekafile IN ($items)";
	            	$wpdb->query($query);
	            	$query = "DELETE FROM {$wpdb->prefix}tekadownload
	            		WHERE tekafile IN ($items)";
	            	$wpdb->query($query);
	            	$query = "DELETE FROM {$wpdb->prefix}tekafile
	            		WHERE ID IN ($items)";
	            	$wpdb->query($query);
	                break;
	            case 'enable':
	            	$query = "UPDATE {$wpdb->prefix}tekafile
	            	SET enabled=1
	            	WHERE ID IN ($items)";
	            	$wpdb->query($query);
	            	break;
	            case 'disable':
	            	$query = "UPDATE {$wpdb->prefix}tekafile
	            	SET enabled=0
	            	WHERE ID IN ($items)";
	            	$wpdb->query($query);
	            	break;
	        }
	    }
        return;
	}

	function prepare_items () {
		if ($this->current_action()) $this->process_bulk_action();
		global $wpdb;

		$table = $wpdb->prefix . 'tekafile';
		$query = "SELECT * FROM $table";
		$per_page = 10000;
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);

		if ($_GET['orderby'] === 'c') $orderby = 'category';
		if ($_GET['orderby'] === 't') $orderby = 'title';
		if (isset($_GET['order'])) $order = mysql_real_escape_string($_GET["order"]);
       	if (isset($orderby) && isset($order)) $query .= ' ORDER BY '.$orderby.' '.$order;

       	$data = $wpdb->get_results($query);
		$current_page = $this->get_pagenum();
		$total_items = count($data);

		$paged = !empty($_GET["paged"]) ? mysql_real_escape_string($_GET["paged"]) : '';
        if(empty($paged) || !is_numeric($paged) || $paged<=0 ){ $paged=1; }
        $totalpages = ceil($totalitems/$per_page);
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
