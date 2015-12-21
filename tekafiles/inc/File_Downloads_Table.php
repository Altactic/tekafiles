<?php

class File_Downloads_table extends WP_List_Table {

  function __construct () {
    parent::__construct();
  }

  function get_columns () {
    return array(
      'time' => 'Fecha');
  }

  function get_sortable_columns () {
    return array(
      'time' => 'time');
  }

  function column_default ($item, $column_name) {
    return $item->$column_name;
  }

  function column_date ($item) {
    return $item->time;
  }

  function prepare_items () {
    global $wpdb;
    $file_id = $_GET['t'];
    $user_id = $_GET['u'];
    $query = "SELECT time
      FROM {$wpdb->prefix}tekadownload
      WHERE tekafile=$file_id AND user=$user_id";
    $columns = $this->get_columns();
    $hidden = array();
    $per_page = 20;
    $sortable = $this->get_sortable_columns();
    $this->_column_headers = array($columns, $hidden, $sortable);

    if ($_GET['orderby'] === 't') $orderby = 'time';
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
