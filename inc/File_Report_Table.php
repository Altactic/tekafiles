<?php

class File_Report_table extends WP_List_Table {

	function __construct () {
		parent::__construct();
	}

	function get_columns () {
		return array(
			'cb' => "<input type='checkbox' />",
			'user' => 'Usuario',
			'locked' => 'Bloqueado');
	}

	function get_sortable_columns () {
		return array(
			'user' => 'user');
	}

	function column_default ($item, $column_name) {
		return $item->$column_name;
	}

	function column_cb ($item) {
		return "<input type='checkbox' name='user[]' value='$item->ID' />";
	}

	function column_locked ($item) {
		if ($item->locked) return 'Si';
		else return 'No';
	}

	function get_bulk_actions () {
		return array(
			'lock' => 'Bloquear',
			'unlock' => 'Desbloquear');
	}

	function process_bulk_action () {
		if ( isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) ) {
            $nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
            $action = 'bulk-' . $this->_args['plural'];
            if ( ! wp_verify_nonce( $nonce, $action ) )
                wp_die( 'Nope! Security check failed!' );
        }
        if (isset($_POST['user'])) {
        	global $wpdb;
        	$action = $this->current_action();
        	$items = $_POST['user'];
        	$items = join(',', $items);
        	switch ($action) {
        		case 'lock':
        			$locked = 1;
        			break;
        		case 'unlock':
        			$locked = 0;
        			break;
        	}
        	$query = "UPDATE {$wpdb->prefix}tekafile_user
        		SET locked=$locked
        		WHERE ID IN ($items)";
        	$wpdb->query($query);
        }
	}

	function prepare_items () {
		if ($this->current_action()) $this->process_bulk_action();
		global $wpdb;

		$file_id = $_GET['t'];
		$tu = $wpdb->prefix . 'tekafile_user';
		$u = $wpdb->prefix . 'users';
		$t = $wpdb->prefix . 'tekafile';
		$query = "SELECT tu.ID as ID, u.display_name as user, tu.locked as locked
			FROM $tu as tu
			JOIN $u as u ON tu.user=u.ID
			JOIN $t as t ON tu.tekafile=t.ID
			WHERE tu.tekafile=$file_id";
		$columns = $this->get_columns();
		$hidden = array();
		$per_page = 40;
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);

		if ($_GET['orderby'] === 'u') $orderby = 'u.display_name';
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
            'total_pages' => ceil($total_items/$per_page)
        ) );
	}

}