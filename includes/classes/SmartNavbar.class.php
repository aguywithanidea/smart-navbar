<?php
// Thanks to https://wordpress.org/plugins/custom-headers-and-footers/


if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('Smart-Navbar: Illegal Page Call!'); }

/**
* SmartNavbar class for turning POST titles into intuitive nav-bar
*
*/
class SmartNavbar {

  var $debug = true;
  var $help = false;
  var $i18n = 'smart-navbar';             // key for internationalization stubs
  var $opt_key = '_snb_plugin_options';   // options key
  var $options = array();
  var $plugin_file = 'smart-navbar/smart-navbar.php';  // this helps us with the plugin_links
  var $slug = 'smart-navbar';
  var $cookie_name = '_snb_user';
  var $cookie_val = null;
  
  public function __construct() {
	  // initialize the dates
	  $this->options = get_option($this->opt_key);
  }   

  public function __destruct() {

  }
  public function activate_plugin() {
    // no logging here -it barfs on activation
    $this->log("in the activate_plugin()");
    // $this->check_plugin_version();
  }
  public function deactivate_plugin() {
    $this->options = false;
    delete_option($this->opt_key);  // remove the options from db
	  return;
  }
  public function ajax_handler() {
    global $wpdb; // this is how you get access to the database
    $this->log("in ajax handler");
    $what = $_POST;
    $this->log(sprintf("POST params = %s",print_r($what,1)));
    echo 'OK';
    wp_die(); // this is required to terminate immediately and return a proper response    
    // wp_die('Error','Foo title',400); // for testing errors
  }
  public function configuration_screen() {
    if (is_user_logged_in() && is_admin() ){
      // $message = $this->update_options($_POST);
      // $opts = get_option(SGW_PLUGIN_OPTTIONS);
      // $posts = $this->get_post_meta();
      // $existing = array();
      
      if ($message) {
        printf('<div id="message" class="updated fade"><p>%s</p></div>',$message);
      } elseif ($this->error) { // reload the form post in this form
        // stuff here if we have an error
      }
      // the rest of the admin screen here...
      print("<h2>This is where the form goes</h2>");
    }
  }
	/* Contextual Help for the Plugin Configuration Screen */
  public function configuration_screen_help($contextual_help, $screen_id, $screen) {
    if ($screen_id == $this->help) {
      $contextual_help = <<<EOF
<h2>Smart Navbar</h2>      
<p>Here's where we put the help.</p>
EOF;
    }
  	return $contextual_help;
  }
  public function header_bar( &$wp_query) {
    global $wp_the_query,$post;
    if ( ( $wp_query === $wp_the_query ) && !is_admin() && !is_feed() && !is_robots() && !is_trackback() && !is_home()) {
      // $this->log(sprintf("post => %s",print_r($post,1)));
      $author = get_the_author_meta('display_name',$post->post_author);
      $author_link = sprintf("<a href='%s'>%s</a>",get_author_posts_url($post->post_author),$author );
      $category = get_the_category_list(',', '', $post->ID);
      $img = SNB_BASE_URL . 'includes/images/';
      $is_admin = '';
      if (is_admin_bar_showing()) { $is_admin = 'class="with-admin"';}
      
      // TODO: Get navigation into bar
      $text = <<<EOF
        <div id="smart-navbar" {$is_admin}>
          <div id="smart-navbar-left">
            <!--<i id='snb-arrow-circle' class='fa fa-arrow-circle-left fa-lg' title='Previous'></i>-->
            <i id='snb-heart' class='fa fa-heart-o fa-lg' title='Add to Favorites'></i>
            <i id='snb-bookmark' class='fa fa-bookmark-o fa-lg' title='Add to Bookmarks'></i>
            <!--<i id='snb-share-square' class='fa fa-share-square-o fa-lg' title='Share with Friends'></i>-->
            <i id='snb-share-square' class='fa fa-question-circle fa-lg' title='What is This?'></i>
          </div>
          
          <div id="smart-navbar-right">
            <!--<i class='fa fa-arrow-circle-right fa-lg' title='Next'></i>-->
          </div>
          <div id="smart-navbar-center">
            <h3 class='entry-title'>{$post->post_title}</h3>
            <div class='author'>by {$author_link}</div>
            <div class='categories-links'>posted in {$category}</div>
          </div>
        </div>
EOF;
      echo $text;
    }
  }
  
  public function plugin_links($links) {
    $url = $this->settings_url();
    $settings = '<a href="'. $url . '">'.__("Settings", $this->i18n).'</a>';
    array_unshift($links, $settings);  // push to left side
    return $links;
  }
  // load custom style and js
  public function plugin_init() {
    wp_enqueue_style( 'snb_font_css', '//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css', array(), SNB_PLUGIN_VERSION );
    wp_enqueue_style( 'snb_core_css', SNB_BASE_URL . 'includes/css/smart_nav.css', array(), SNB_PLUGIN_VERSION );
    wp_enqueue_script('snb_core_js', SNB_BASE_URL . 'includes/js/smart-nav.js', array('jquery'), SNB_PLUGIN_VERSION );
    wp_localize_script('snb_core_js', 'ajax_object',array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));
  }
  public function read_cookies() {
    $cookie = isset( $_COOKIE[$this->cookie_name] ) ? $_COOKIE[$this->cookie_name] : null;
    if ($cookie) { $this->cookie_val = $cookie; }
    // $this->log("cookie = $this->cookie_val");
    return $this->cookie_val;
  }
  public function write_cookies() {
    $cookie = $this->read_cookies();
    // $this->log("existing cookie in write_cookies : $cookie");
    if (!$cookie) { $cookie = $this->uuid(); }
    setcookie($this->cookie_name, $cookie, time()+(365 * 24 * 60 * 60), COOKIEPATH, COOKIE_DOMAIN, false); // 1 year cookie
  }
  /*
    PRIVATE FUNCTIONS
  */
  private function settings_url() {
    $url = 'options-general.php?page='.$this->slug;
    return $url;
  }
  // Private functions
  private function log($msg) {
    if ($this->debug) {
      error_log(sprintf("%s\n",$msg),3,dirname(__FILE__) . '/../../error.log');
    }
  }
  private function uuid() {
   return uniqid('_snb',true);
  }

}
?>