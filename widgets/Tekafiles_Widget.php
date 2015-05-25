<?php

class Tekafiles_Widget extends WP_Widget {

  public function __construct() {
    parent :: __construct(false, 'Librería TEKA');
  }

  public function widget($args, $instance) {
    if (!current_user_can('read')) {
      return;
    }
    wp_enqueue_script('tekafiles_widget', TEKAFILES_URL . '/js/widget.js', array('jquery'));
    wp_enqueue_style('tekafiles_widget', TEKAFILES_URL . '/css/widget.css');
    extract($args);
    global $wpdb;
    $user_id = get_current_user_id();
    $categories = $wpdb->get_col("SELECT t.category
			FROM {$wpdb->prefix}tekafile_user as tu
			JOIN {$wpdb->prefix}tekafile as t ON tu.tekafile=t.ID
			WHERE tu.user=$user_id AND t.enabled>0
			GROUP BY t.category");
    $files = $wpdb->get_results("SELECT t.title as title, t.category as category, t.file as file, t.ID as ID, t.description as description, tu.locked as locked
			FROM {$wpdb->prefix}tekafile_user as tu
			JOIN {$wpdb->prefix}tekafile as t ON tu.tekafile=t.ID
			WHERE tu.user=$user_id AND t.enabled>0");
    $title = apply_filters('widget_title', $instance['title']);
    echo $before_widget;
    ?>
    <?php if ($title): ?>
      <?php echo $before_title . $title . $after_title; ?>
    <?php endif; ?>

    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
    
    <div id="accordion">
    <?php foreach ($categories as $category): ?>
        <h3><?php echo $category; ?></h3>
        <div>
          <ul class='bullet_arrow2 imglist'>
      <?php foreach ($files as $file): ?>
        <?php if ($file->category === $category): ?>
                <li>
                  <?php if ($file->locked): ?>
                    <a href="#" class='lnk-download locked'><?php echo $file->title; ?></a>
                    <?php else: ?>
                    <a class="lnk-download" href="<?php echo admin_url("admin-post.php?action=download&t=$file->ID"); ?>"><?php echo $file->title; ?></a>
                  <?php endif; ?>
                  <?php echo $file->description; ?>
                </li>
                <?php endif; ?>
              <?php endforeach; ?>
          </ul>
        </div>
          <?php endforeach; ?>
    </div>

      <?php
      echo $after_widget;
    }

    public function update($new, $old) {
      $instance = $old;
      $instance['title'] = strip_tags($new['title']);
      return $instance;
    }

    public function form($instance) {
      if ($instance) {
        $title = esc_attr($instance['title']);
      }
      else {
        $title = '';
      }
      ?>
    <p>
      <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title', 'wp_widget_plugin'); ?></label>
      <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
    </p>
    <?php
  }

}
