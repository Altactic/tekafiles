<?php
/**
 * Plugin Name: Teka Files
 * Plugin URI: http://github.com/foxrock/tekafiles
 * Description: Teka file administrator.
 * Version: 1.0
 * Author: Andres Londono
 * Author URI: http://www.foxrock.co
 * License: GPL2
 */
define('TEKAFILES_PREFIX', 'TEKAFILES_');
define('TEKAFILES_DIR', WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__)));
define('TEKAFILES_URL', plugins_url('', __FILE__));

include_once TEKAFILES_DIR . '/install.php';
include_once TEKAFILES_DIR . '/widgets/Tekafiles_Widget.php';

function tekafiles_admin_init() {
  wp_register_script('tekafiles_new', TEKAFILES_URL . '/js/new.js', array('jquery'));
  wp_register_script('tekafiles_widget', TEKAFILES_URL . '/js/widget.js', array('jquery'));
  wp_register_style(
      'tekafiles_new', TEKAFILES_URL . '/css/new.css');
}

function tekafiles_menu() {
  add_menu_page(
      'Libreria Teka', 'Libreria Teka', 'manage_tekafiles', 'tekafiles.php', 'tekafiles_page', 'dashicons-clipboard');
  add_submenu_page(
      'tekafiles.php', 'Documentos', 'Documentos', 'manage_tekafiles', 'tekafiles.php', 'tekafiles_page');
  add_submenu_page(
      NULL, 'Reporte', 'Reporte', 'manage_tekafiles', 'tekafiles_report.php', 'tekafiles_report_page');
  add_submenu_page(
      NULL, 'Descargas', 'Descargas', 'manage_tekafiles', 'tekafiles_downloads.php', 'tekafiles_downloads_page');
}

function tekafiles_new_menu() {
  $suffix = add_submenu_page(
      'tekafiles.php', 'Documento', 'Nuevo Documento', 'manage_tekafiles', 'tekafiles_new.php', 'tekafiles_new_page');
  add_action('admin_print_scripts-' . $suffix, 'tekafiles_new_scripts');
}

function tekafiles_new_scripts() {
  wp_localize_script('tekafiles_new', 'ajax', array('url' => admin_url('admin-ajax.php')));
  wp_enqueue_script('tekafiles_new');
  wp_enqueue_style('tekafiles_new');
}

function tekafiles_page() {
  if (!current_user_can('manage_tekafiles')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
  }
  require_once TEKAFILES_DIR . '/inc/Files_Table.php';
  $table = new Files_Table();
  $table->prepare_items();
  ?>
  <div class='wrap'>
    <h2>Documentos Teka<a class='add-new-h2' href='<?php echo admin_url('admin.php?page=tekafiles_new.php'); ?>'>Nuevo</a></h2>
    <form action='' method='POST'>
        <?php $table->display(); ?>
    </form>
  </div>
  <?php
}

function tekafiles_new_page() {
  if (isset($_POST['submit'])) {
    tekafiles_process_new();
  }
  $users = get_users(array(
    'role' => 'subscriber',
    'fields' => array('ID', 'display_name')));
  if (isset($_GET['e'])) {
    global $wpdb;
    $ID = $_GET['e'];
    $file = $wpdb->get_row(
        "SELECT *
      FROM {$wpdb->prefix}tekafile
      WHERE ID=$ID");
    $file_users = $wpdb->get_results(
        "SELECT user, tekafile
      FROM {$wpdb->prefix}tekafile_user
      WHERE tekafile=$ID", OBJECT_K);
  }
  require_once TEKAFILES_DIR . '/views/new.php';
}

function tekafiles_report_page() {
  if (!current_user_can('manage_tekafiles')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
  }
  global $wpdb;
  require_once TEKAFILES_DIR . '/inc/File_Report_Table.php';
  $table = new File_Report_Table();
  $table->prepare_items();

  $id = $_GET['t'];
  $query = "SELECT title
    FROM {$wpdb->prefix}tekafile
    WHERE ID=$id";
  $file = $wpdb->get_row($query);
  ?>
  <div class='wrap'>
    <h2>Permisos de Descarga <?php echo $file->title; ?></h2>
    <a href='<?php echo admin_url("admin.php?page=tekafiles.php"); ?>'>Volver a la lista de archivos</a>
    <form action='' method='POST'>
        <?php $table->display(); ?>
    </form>
  </div>
  <?php
}

function tekafiles_downloads_page() {
  if (!current_user_can('manage_tekafiles')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
  }
  global $wpdb;
  require_once TEKAFILES_DIR . '/inc/File_Downloads_Table.php';
  $table = new File_Downloads_Table();
  $table->prepare_items();

  $file_id = $_GET['t'];
  $query = "SELECT title
    FROM {$wpdb->prefix}tekafile
    WHERE ID=$file_id";
  $file = $wpdb->get_row($query);

  $user_id = $_GET['u'];
  $user = get_user_by('id', $user_id);
  ?>
  <div class='wrap'>
    <h2>Reporte de descargas</h2>
    <p>Usuario: <?php echo "$user->first_name $user->last_name"; ?></p>
    <p>Archivo: <?php echo $file->title; ?></p>
    <p><a href="<?php echo admin_url("admin.php?page=tekafiles_report.php&t=$file_id"); ?>">Volver a permisos de descargas</a></p>
    <?php $table->display(); ?>
  </div>
  <?php
}

function tekafiles_process_new() {
  if (!current_user_can('manage_tekafiles')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
  }

  global $wpdb;
  // Check that nonce field
  // check_admin_referer( 'tekafiles_new_file_nonce' );

  $category = strtolower($_POST['category']);
  $date = date('Y-m-d', strtotime($_POST['date']));
  if (isset($_POST['public']))
    $public = 1;
  else
    $public = 0;
  if (isset($_POST['enabled']))
    $enabled = 1;
  else
    $enabled = 0;

  $all = get_users(array(
    'role' => 'subscriber',
    'fields' => 'ID'));
  $values = array(
    'title' => $_POST['title'],
    'description' => $_POST['description'],
    'category' => $category,
    'date' => $date,
    'public' => $public,
    'enabled' => $enabled);
  if (isset($_POST['edit'])) {
    $file_id = $_POST['edit'];
    $old = $wpdb->get_col(
        "SELECT user
      FROM {$wpdb->prefix}tekafile_user
      WHERE tekafile=$file_id");
    $wpdb->update(
        $wpdb->prefix . "tekafile", $values, array(
      'ID' => $file_id));
    $new = $_POST['users'];
    if ($public) {
      $insert = array_diff($all, $old);
    }
    else {
      $delete = array_diff($old, $new);
      $delete = join(',', $delete);
      $query = "DELETE FROM {$wpdb->prefix}tekafile_user
        WHERE user IN ($delete) AND tekafile=$file_id";
      $wpdb->query($query);
      $insert = array_diff($new, $old);
    }

    $values = "";
    foreach ($insert as $ID) {
      $values .= "($file_id, $ID),";
    }
    $values = substr($values, 0, strlen($values) - 1);
    $query = "INSERT INTO {$wpdb->prefix}tekafile_user
        (tekafile, user)
        VALUES $values";
    $wpdb->query($query);
  }
  else {
    $upload = $_FILES['file'];
    $overrides = array('test_form' => false);
    $file = wp_handle_upload($upload, $overrides);
    $values['file'] = $file['file'];
    $wpdb->insert($wpdb->prefix . 'tekafile', $values);
    $file_id = $wpdb->insert_id;
    if ($public)
      $insert = $all;
    else
      $insert = $_POST['users'];
    $values = "";
    foreach ($insert as $ID) {
      $values .= "($file_id, $ID),";
    }
    $values = substr($values, 0, strlen($values) - 1);
    $query = "INSERT INTO {$wpdb->prefix}tekafile_user
        (tekafile, user)
        VALUES $values";
    $wpdb->query($query);
  }

  wp_redirect(admin_url('/admin.php?page=tekafiles.php'));
  exit;
}

function tekafiles_ajax_search_users() {
  $search = $_POST['search'];
  $users = get_users(array(
    'search' => "$search*",
    'fields' => array('ID', 'user_email', 'display_name', 'user_login')
  ));
  $result = "";
  foreach ($users as $user) {
    $result .= "<option value='$user->user_email' label='$user->display_name' />";
  }
  echo $result;
  die();
}

function tekafiles_ajax_search_categories() {
  global $wpdb;
  $search = strtolower($_POST['search']);
  $table = $wpdb->prefix . "tekafile";
  $rows = $wpdb->get_results("SELECT category FROM $table WHERE category LIKE '%$search%' GROUP BY category");
  $result = "";
  foreach ($rows as $row) {
    $result .= "<option value='$row->category' />";
  }
  echo $result;
  die();
}

function tekafiles_ajax_validate_user() {
  $email = $_POST['email'];
  $user = get_user_by('email', $email);
  if ($user) {
    echo $user->user_email;
  }
  exit;
}

function tekafiles_admin_post_download_file() {
  global $wpdb;
  $user_id = get_current_user_id();
  $file_id = $_GET['t'];
  $query = "SELECT *
    FROM {$wpdb->prefix}tekafile_user
    WHERE user=$user_id AND tekafile=$file_id";
  $access = $wpdb->get_row($query);
  var_dump($query);
  if ($access && !$access->locked) {
    echo "fuck!";

    $file = $wpdb->get_row("SELECT *
      FROM {$wpdb->prefix}tekafile
      WHERE ID=$file_id");
    if ($file->enabled) {
      $path = $file->file;
      if (is_file($path)) {
        $ext = '.' . pathinfo($path, PATHINFO_EXTENSION);
        $size = filesize($path);

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=\"" . $file->title . $ext . "\";");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . $size);
        header("Content-type: application/octet-stream");
        readfile($path);

        $table = $wpdb->prefix . "tekadownload";
        $values = array(
          'tekafile' => $file_id,
          'user' => $user_id,
          'time' => date('Y-m-d H:i:s')
        );
        $wpdb->insert($table, $values);
        $wpdb->query("UPDATE {$wpdb->prefix}tekafile_user
          SET locked=1
          WHERE tekafile=$file_id AND user=$user_id");
        exit;
      }
    }
  }
  exit;
}

function tekafiles_upload_dir_filter($dir) {
  $dir['subdir'] = '/tekafiles';
  $dir['path'] = $dir['basedir'] . $dir['subdir'];
  $dir['url'] = $dir['baseurl'] . $dir['subdir'];
  return $dir;
}

function tekafiles_register_widgets() {
  register_widget('Tekafiles_Widget');
}

function tekafiles_login_redirect($redirect_to) {
  global $user;
  if (isset($user->roles) && is_array($user->roles)) {
    if (in_array('subscriber', $user->roles)) {
      return site_url('/downloads');
    }
    else {
      return $redirect_to;
    }
  }
  else {
    return $redirect_to;
  }
}

register_activation_hook(__FILE__, 'tekafiles_activate');
register_deactivation_hook(__FILE__, 'tekafiles_deactivate');
add_action('plugins_loaded', 'tekafiles_update_db_check');

add_action('admin_menu', 'tekafiles_menu');
add_action('admin_menu', 'tekafiles_new_menu');

add_action('wp_ajax_search_users', 'tekafiles_ajax_search_users');
add_action('wp_ajax_search_categories', 'tekafiles_ajax_search_categories');
add_action('wp_ajax_validate_user', 'tekafiles_ajax_validate_user');

add_action('admin_init', 'tekafiles_admin_init');

add_action('admin_post_download', 'tekafiles_admin_post_download_file');

add_filter('upload_dir', 'tekafiles_upload_dir_filter');

add_action('widgets_init', 'tekafiles_register_widgets');

add_filter('login_redirect', 'tekafiles_login_redirect');
