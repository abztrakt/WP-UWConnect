<?php
/**
 * Plugin Name: UW Connect for Wordpress
 * Description: A plugin for interfacing WP sites with a Service Now installation.
 * Version: 1.0.0
 * Author: UW IT ACA
 * Author URI: https://github.com/uw-it-aca
 * License: GPL2
 */
/*  Copyright 2015  UW IT ACA  (email : cstimmel@uw.edu)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
error_reporting(E_ERROR | E_PARSE);
include('services.php');
include('status-functions.php');
function startsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
}

function uw_connect_script_setup() {
    wp_register_style( 'uwconnect_font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css' );
    wp_register_style( 'uwconnect_bootstrap', plugin_dir_url(__FILE__) . 'styles/bootstrap-3.1.1/css/bootstrap-3.1.1.min.css' );
    wp_register_style( 'uwconnect_style', plugin_dir_url(__FILE__) . 'styles/style.css');
    wp_enqueue_style( 'uwconnect_font-awesome' );
    wp_enqueue_style( 'uwconnect_bootstrap' );
    wp_register_script( 'jQuery','//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js', array(), '1.7.2' );
    wp_enqueue_script( 'jQuery' );
    wp_enqueue_style( 'uwconnect_style' );
}
add_action( 'wp_enqueue_scripts', 'uw_connect_script_setup');


function uw_connect_menu() {
  add_options_page( 'UW Connect Options', 'WP-UWConnect', 'manage_options', 'uw-connect-options', 'uw_connect_options' );
}
add_action( 'admin_menu', 'uw_connect_menu' );

function get_page_by_name($pagename) {
  $pages = get_pages();
  foreach ($pages as $page) {
    if ($page->post_name == $pagename) {
      return $page;
    }
  }
  return false;
}

function uw_connect_options() {
  $hidden_field_name = 'uwc_submit_hidden';
  // variables for the field and option names
  $url = 'uwc_SN_URL';
  $data_url = 'uwc_SN_URL';
  $user = 'uwc_SN_USER';
  $data_user = 'uwc_SN_USER';
  $pass = 'uwc_SN_PASS';
  $data_pass = 'uwc_SN_PASS';
  $myreq = 'uwc_MYREQ';
  $data_myreq = 'uwc_MYREQ';
  $servstat = 'uwc_SERVSTAT';
  $data_servstat = 'uwc_SERVSTAT';
  $servcat = 'uwc_SERVCAT';
  $data_servcat = 'uwc_SERVCAT';

  // Read in existing option value from database
  $url_val = get_option( $url );
  $user_val = get_option( $user );
  $pass_val = get_option( $pass );
  $myreq_val = get_option( $myreq );
  $servstat_val = get_option( $servstat );
  $servcat_val = get_option( $servcat );
  if ($myreq_val == '') {
      update_option( $myreq, 'off' );
  }
  if ($servstat_val == '') {
      update_option( $servstat, 'off' );
  }
  if ($servcat_val == '' ) {
      update_option( $servcat, 'off' );
  }

  // See if the user has posted us some information
  // If they did, this hidden field will be set to 'Y'
  if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {
      // Read their posted value
      $url_val = $_POST[ $data_url ];
      $user_val = $_POST[ $data_user ];
      $pass_val = $_POST[ $data_pass ];
      $myreq_val = $_POST[ $data_myreq ];
      $servstat_val = $_POST[ $data_servstat ];
      $servcat_val = $_POST[ $data_servcat ];

      $prevmyreq = get_option( $myreq );
      $prevservstat = get_option( $servstat );
      $prevservcat = get_option( $servcat );

      // Save the posted value in the database
      update_option( $url, $url_val );
      update_option( $user, $user_val );
      update_option( $pass, $pass_val );
      update_option( $myreq, $myreq_val );
      update_option( $servstat, $servstat_val );
      update_option( $servcat, $servcat_val );

      if ( $myreq_val == 'on' ) {
          if (!get_page_by_name('myrequest')) {
              create_request_page();
          }
          if (!get_page_by_name('myrequests')) {
              create_requests_page();
          }
      } else if ($myreq_val == 'off' && $prevmyreq == 'on' ) {
          $myreqpage = get_page_by_name('myrequest');
          $myreqspage = get_page_by_name('myrequests');
          wp_delete_post( $myreqpage->ID, true );
          wp_delete_post( $myreqspage->ID, true );
      } else {
      }

      if ( $servstat_val == 'on' ) {
          if (!get_page_by_name('incident')) {
              create_incident_page();
          }
          if (!get_page_by_name('servicestatus')) {
              create_servicestatus_page();
          }
      } else if ( $servstat_val == 'off' && $prevservstat == 'on' ) {
          $sspage = get_page_by_name('servicestatus');
          $incpage = get_page_by_name('incident');
          wp_delete_post( $sspage->ID, true );
          wp_delete_post( $incpage->ID, true );
      } else {
      }

      if ( $servcat_val == 'on' ) {
          create_service_home_page();
          create_servicecategories_page();
          create_serviceAZ_page();
      } else if ( $servcat_val == 'off' && $prevservcat == 'on' ) {
          $shpage = get_page_by_name('services');
          $scpage = get_page_by_name('servicecategories');
          $servspage = get_page_by_name('servicesaz');
          wp_delete_post( $shpage->ID, true );
          wp_delete_post( $scpage->ID, true );
          wp_delete_post( $servspage->ID, true );
      } else {
      }

      flush_rewrite_rules();

?>
<div class="updated"><p><strong><?php _e('settings saved.', 'menu' ); ?></strong></p></div>
<?php
  }
  // Now display the settings editing screen
  echo '<div class="wrap">';
  // header
  echo "<h2>" . __( 'UW Connect Plugin Settings', 'menu' ) . "</h2>";
  // settings form
  ?>

<form name="form1" method="post" action="">
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

<p><?php _e("ServiceNow URL:", 'menu' ); ?>
<input type="text" name="<?php echo $data_url; ?>" value="<?php echo $url_val; ?>" size="20">
</p><hr />

<p><?php _e("ServiceNow User:", 'menu' ); ?>
<input type="text" name="<?php echo $data_user; ?>" value="<?php echo $user_val; ?>" size="20">
</p><hr />

<p><?php _e("ServiceNow Pass:", 'menu' ); ?>
<input type="password" name="<?php echo $data_pass; ?>" value="<?php echo $pass_val; ?>" size="20">
</p><hr />

<h2>Enable Portals</h2>

<p><?php _e("ServiceNow My Requests Portal: ", 'menu' );

?>
<input type="radio" name="<?php echo $data_myreq; ?>" value="on" <?php echo ($myreq_val=='on')?'checked':'' ?>>ON
<input type="radio" name="<?php echo $data_myreq; ?>" value="off" <?php echo ($myreq_val=='off')?'checked':''?>>OFF
</p><hr />

<p><?php _e("ServiceNow Service Status Portal: ", 'menu' ); ?>
<input type="radio" name="<?php echo $data_servstat; ?>" value="on" <?php echo ($servstat_val=='on')?'checked':'' ?>>ON
<input type="radio" name="<?php echo $data_servstat; ?>" value="off" <?php echo ($servstat_val=='off')?'checked':'' ?>>OFF
</p><hr />

<p><?php _e("Service Catalog: ", 'menu' ); ?>
<input type="radio" name="<?php echo $data_servcat; ?>" value="on" <?php echo ($servcat_val=='on')?'checked':'' ?>>ON
<input type="radio" name="<?php echo $data_servcat; ?>" value="off" <?php echo ($servcat_val=='off')?'checked':'' ?>>OFF

</p><hr /><p class="submit">
<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
</p>

</form>
</div>
<?php
}

function add_query_vars($qvars) {
    $qvars[] = "ticketID";
    return $qvars;
}
add_filter('query_vars', 'add_query_vars');

function add_rewrite_rules($aRules) {
    $aNewRules = array('incident/([^/]+)/?$' => 'index.php?pagename=incident&ticketID=$matches[1]');
    $bNewRules = array('myrequest/([^/]+)/?$' => 'index.php?pagename=myrequest&ticketID=$matches[1]');
    $aRules = $bNewRules + $aNewRules + $aRules;
    return $aRules;
}
add_filter('rewrite_rules_array', 'add_rewrite_rules');

function options_setup() {
    if (!get_option('uwc_MYREQ')) {
      update_option('uwc_MYREQ', 'off');
    }
    if (!get_option('uwc_SERVSTAT')) {
      update_option('uwc_SERVSTAT', 'off');
    }
    if (!get_option('uwc_SERVCAT')) {
      update_option('uwc_SERVCAT', 'off');
    }
}
register_activation_hook(__FILE__, 'options_setup');

function create_service_home_page() {
    if (!get_page_by_name('services') && get_option('uwc_SERVCAT') == 'on') {
      $post = array(
            'comment_status' => 'open',
            'ping_status' =>  'closed',
            'post_name' => 'services',
            'post_status' => 'publish',
            'post_title' => 'Service Catalog',
            'post_type' => 'page',
      );
      $newvalue = wp_insert_post( $post, false );
    }
}
register_activation_hook(__FILE__, 'create_service_home_page');

function create_incident_page() {
    if (!get_page_by_name('incident') && get_option('uwc_SERVSTAT') == 'on') {
      $post = array(
            'comment_status' => 'open',
            'ping_status' =>  'closed',
            'post_name' => 'incident',
            'post_status' => 'publish',
            'post_title' => 'Incident',
            'post_type' => 'page',
      );
      $newvalue = wp_insert_post( $post, false );
      update_option( 'incpage', $newvalue );
    }
}
register_activation_hook(__FILE__, 'create_incident_page');

function create_request_page() {
    if (!get_page_by_name('myrequest') && get_option('uwc_MYREQ') == 'on') {
      $post = array(
            'comment_status' => 'open',
            'ping_status' =>  'closed',
            'post_name' => 'myrequest',
            'post_status' => 'publish',
            'post_title' => 'My Request',
            'post_type' => 'page',
      );
      $newvalue = wp_insert_post( $post, false );
      update_option( 'mrpage', $newvalue );
    }
}
register_activation_hook(__FILE__, 'create_request_page');

function create_requests_page() {
    if (!get_page_by_name('myrequests') && get_option('uwc_MYREQ') == 'on') {
      $post = array(
            'comment_status' => 'open',
            'ping_status' =>  'closed',
            'post_name' => 'myrequests',
            'post_status' => 'publish',
            'post_title' => 'My Requests',
            'post_type' => 'page',
      );
      $newvalue = wp_insert_post( $post, false );
      update_option( 'mrspage', $newvalue );
    }
}
register_activation_hook(__FILE__, 'create_requests_page');


function create_servicestatus_page() {
    if (!get_page_by_name('servicestatus') && get_option('uwc_SERVSTAT') == 'on') {
      $post = array(
            'comment_status' => 'open',
            'ping_status' =>  'closed',
            'post_name' => 'servicestatus',
            'post_status' => 'publish',
            'post_title' => 'ServiceStatus',
            'post_type' => 'page',
      );
      $newvalue = wp_insert_post( $post, false );
      update_option( 'sspage', $newvalue );
    }
}
register_activation_hook(__FILE__, 'create_servicestatus_page');

function create_servicecategories_page() {
    if (!get_page_by_name('servicecategories') && get_option('uwc_SERVCAT') == 'on') {
      $post = array(
            'comment_status' => 'open',
            'ping_status' =>  'closed',
            'post_name' => 'servicecategories',
            'post_status' => 'publish',
            'post_title' => 'Service Categories',
            'post_type' => 'page',
            'post_content' => '[taxtermlist tax="servicecategory"]',
      );
      $newvalue = wp_insert_post( $post, false );
      update_option( 'sspage', $newvalue );
    }
}
register_activation_hook(__FILE__, 'create_servicecategories_page');

function create_serviceAZ_page() {
    if (!get_page_by_name('servicesaz') && get_option('uwc_SERVCAT') == 'on') {
      $post = array(
            'comment_status' => 'open',
            'ping_status' =>  'closed',
            'post_name' => 'servicesaz',
            'post_status' => 'publish',
            'post_title' => 'Services A-Z',
            'post_type' => 'page',
      );
      $newvalue = wp_insert_post( $post, false );
      update_option( 'servspage', $newvalue );
    }
}
register_activation_hook(__FILE__, 'create_serviceAZ_page');

function flush_rewrite() {
  flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'flush_rewrite');

function request_page_template( $template ) {

  if ( is_page( 'myrequest' ) ) {
    if ( basename( get_page_template() ) == "page.php" ) {
      $new_template = dirname(__FILE__) . '/request-page-template.php';
      if ( '' != $new_template ) {
        return $new_template ;
      }
    }
  }
  if ( is_page( 'myrequests' ) ) {
    if ( basename( get_page_template() ) == "page.php" ) {
      $new_template = dirname(__FILE__) . '/requests-page-template.php';
      if ( '' != $new_template ) {
        return $new_template ;
      }
    }
  }
  if ( is_page( 'incident' ) ) {
    if ( basename( get_page_template() ) == "page.php" ) {
      $new_template = dirname(__FILE__) . '/incident-page-template.php';
      if ( '' != $new_template ) {
        return $new_template ;
      }
    }
  }
  if ( is_page( 'servicestatus' ) ) {
    if ( basename( get_page_template() ) == "page.php" ) {
      $new_template = dirname(__FILE__) . '/servicestatus-page-template.php';
      if ( '' != $new_template ) {
        return $new_template ;
      }
    }
  }
  if ( is_page( 'services' ) ) {
    if ( basename( get_page_template() ) == "page.php" ) {
      $new_template = dirname(__FILE__) . '/service-home.php';
      if ( '' != $new_template ) {
        return $new_template ;
      }
    }
  }
  if ( is_page( 'servicecategories' ) ) {
    if ( basename( get_page_template() ) == "page.php" ) {
      $new_template = dirname(__FILE__) . '/service-categories.php';
      if ( '' != $new_template ) {
        return $new_template ;
      }
    }
  }
  if ( is_page( 'servicesaz' ) ) {
    if ( basename( get_page_template() ) == "page.php" ) {
      $new_template = dirname(__FILE__) . '/serviceAZ.php';
      if ( '' != $new_template ) {
        return $new_template ;
      }
    }
  }
  return $template;
}
add_filter( 'template_include', 'request_page_template');

function enable_ajax() {
  wp_enqueue_script( 'services', plugin_dir_url( __FILE__ ) . 'service.js', 'jquery', true);
  wp_localize_script( 'services', 'service_ajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
}
add_action('template_redirect', 'enable_ajax');

//Builds a request to Service Now and returns results as a JSON object.
function get_SN($url, $args) {
    $url = get_option('uwc_SN_URL') . $url;
    $response = wp_remote_get( $url, $args );
    $body = wp_remote_retrieve_body( $response );
    $json = json_decode( $body );
    return $json;
}

// Takes two datetime objects and sorts descending by sys_updated_on
function sortByUpdatedOnDesc($a, $b) {
    $dt_a = DateTime::createFromFormat('m-d-Y H:i:s', $a->sys_updated_on);
    $dt_b = DateTime::createFromFormat('m-d-Y H:i:s', $b->sys_updated_on);
    return $dt_a < $dt_b;
}

// Takes two datetime objects and sorts descending by sys_created_on
function sortByCreatedOnDesc($a, $b) {
    $dt_a = DateTime::createFromFormat('m-d-Y H:i:s', $a->sys_created_on);
    $dt_b = DateTime::createFromFormat('m-d-Y H:i:s', $b->sys_created_on);
    return $dt_a < $dt_b;
}

// Takes two strings and sorts descending by number
function sortByNumberDesc($a, $b) {
    return $a->number < $b->number;
}
?>
