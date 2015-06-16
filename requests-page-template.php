<?php
/*
 * The template for displaying all requests/incidents from SN
 */

define( 'DONOTCACHEPAGE', True );
//Get the NETID logged in user
if ( isset( $_SERVER['REMOTE_USER'] ) ) {
    $user = $_SERVER['REMOTE_USER'];
} else if ( isset( $_SERVER['REDIRECT_REMOTE_USER'] ) ) {
    $user = $_SERVER['REDIRECT_REMOTE_USER'];
} else if ( isset( $_SERVER['PHP_AUTH_USER'] ) ) {
    $user = $_SERVER['PHP_AUTH_USER'];
}

get_header(); ?>

<?php while ( have_posts() ) : the_post(); ?>

<div id="main-content" class="main-content row">
    <div id="content" class="it_container" role="main">
    <div id="secondary" class="col-lg-3 col-md-3 hidden-sm hidden-xs" role="complementary">
          <div class="" id="sidebar" role="navigation" aria-label="Sidebar Menu">
          <?php dynamic_sidebar('servicenow-sidebar'); ?>
        </div>
    </div>
    <div id="primary" class="col-xs-12 col-sm-12 col-md-9 col-lg-9 itsm-primary">
        <div class="user-logout row">
          <span class="glyphicon glyphicon-user"></span>&nbsp;<?php echo $user; ?> &nbsp;&nbsp;&nbsp;<a href="<?php echo home_url('/user_logout'); ?>" class="buttonesque" style="vertical-align:text-bottom;">LOGOUT</a>
        </div>
      <h2>
        <span class='category'>
        <?php $ancestor_list = array_reverse(get_post_ancestors($post->ID));
          $is_top = false;
        if (sizeof($ancestor_list) > 0) {
          $top_parent = get_page($ancestor_list[0]);
          echo get_the_title($top_parent);
        } else {
          echo get_the_title();
          $is_top = true;
        }?>
        </span>
      </h2>
      <p><?php the_content(); ?></p>


			<?php
        if(isset( $user ) ) {
      ?>
                <?php
                    // Only do this work if we have everything we need to get to ServiceNow.
                    if ( get_option('uwc_SN_URL') && get_option('uwc_SN_PASS') && get_option('uwc_SN_URL') ) {
                        $args = array(
                            'headers' => array(
                                'Authorization' => 'Basic ' . base64_encode( get_option('uwc_SN_USER') . ':' . get_option('uwc_SN_PASS') ),
                            )
                        );

                        //User table entry for user with user_name matching logged in NETID

                        $user_url = '/sys_user_list.do?JSONv2&sysparm_query=user_name%3D' . $user;
                        $user_json = get_SN($user_url, $args);

                        //Service now sys_id for logged in user
                        $user_id = $user_json->records[0]->sys_id;

                        $states = array(
                            "New" => 'label label-success',
                            "Active" => 'label label-success',
                            "Awaiting User Info" => 'label label-warning',
                            "Awaiting Tier 2 Info" => 'label label-success',
                            "Awaiting Vendor Info" => 'label label-success',
                            "Internal Review" => 'label label-success',
                            "Stalled" => 'label label-success',
                            "Delivered" => 'label label-success',
                            "Resolved" => 'label label-default',
                            "Closed" => 'label label-default',
                        );

                        // Requests
                        $req_url = '/u_simple_requests_list.do?JSONv2&displayvalue=true&sysparm_query=state!=14^u_caller.user_name=' . $user . '^ORwatch_listLIKE' . $user_id;
                        $req_json = get_SN($req_url, $args);
                        $has_req = FALSE;
                        if( !empty( $req_json->records ) ) {
                            $has_req = TRUE;
                        }
                        if($has_req) {
                            //Same request as above with sys_id's instead of dispaly values (used to check if user is a watcher)
                            $req_urlwl = '/u_simple_requests_list.do?JSONv2&sysparm_query=state!=14^u_caller.user_name=' . $user . '^ORwatch_listLIKE' . $user_id;
                            $req_jsonwl = get_SN($req_urlwl, $args);
                        }

                        // Incidents
                        $inc_url = '/incident.do?JSONv2&displayvalue=true&sysparm_action=getRecords&sysparm_query=active=true^state!=14^caller_id.user_name=' . $user. '^ORwatch_listLIKE' . $user_id;
                        $inc_json = get_SN($inc_url, $args);
                        $has_inc = FALSE;
                        if( !empty( $inc_json->records ) ) {
                            $has_inc = TRUE;
                        }
                        if($has_inc) {
                            //same request as above with sys_id's instead of dispaly values (used for the watchlist)
                            $inc_urlwl = '/incident.do?JSONv2&sysparm_action=getRecords&sysparm_query=active=true^state!=14^caller_id.user_name=' . $user. '^ORwatch_listLIKE' . $user_id;
                            $inc_jsonwl = get_SN($inc_urlwl, $args);
                        }

                        // Problems
                        $prb_url = '/problem.do?JSONv2&displayvalue=true&sysparm_action=getRecords&sysparm_query=active=true^watch_listLIKE' . $user_id;
                        $prb_json = get_SN($prb_url, $args);
                        $has_prb = FALSE;
                        if ( !empty( $prb_json->records) ) {
                            $has_prb = TRUE;
                        }
                        if ($has_prb) {
                            $prb_urlwl = '/problem.do?JSONv2&sysparm_action=getRecords&sysparm_query=active=true^watch_listLIKE' . $user_id;
                            $prb_jsonwl = get_SN($prb_urlwl, $args);
                        }
                ?>

                    <?php if( $has_req || $has_inc || $has_prb ) { ?>
                    <h2 id="incident_header" class="assistive-text">Incidents</h2>
                    
                    <div class="request-list request-list-header row">
                        <span id="col_head_num" class="col-lg-2 col-md-2 col-sm-2 request-list-number hidden-xs">Number</span>
                        <span id="col_head_ser" class="col-lg-3 col-md-3 request-list-service hidden-sm hidden-xs">Service</span>
                        <span id="col_head_des" class="col-lg-4 col-md-6 col-sm-6 col-xs-4 request-list-description">Description</span>
                        <span id="col_head_sta" class="col-lg-2 col-md-2 col-sm-2 col-xs-2 request-list-status">Status</span>
                    </div>
                    
                    
                    <?php } ?>

                    <?php if ( $has_prb ) { ?>
                      <ol class="request-list" style="padding-left:0px;" aria-labelledby="problem_header">

                    <?php
                    //Display problems
                    usort($prb_json->records, 'sortByNumberDesc'); //order tickets by number descending
                    usort($prb_jsonwl->records, 'sortByNumberDesc'); //match ordering in watch list
                    $prb_count = 0;
                    foreach ( $prb_json->records as $record ) {
                        if ($record->state != "Resolved" && $record->state != "Awaiting User Info") {
                            $record->state = "Active";
                        }
                            $detail_url = site_url() . '/myrequest/' . $record->number;
                            if ($record->state == "Resolved" || $record->state == "Closed") {
                                echo "<li class='row resolved_ticket'><a href='$detail_url'>";
                            } else {
                                echo "<li class='row row_underline inner_row_underline'><a href='$detail_url'>";
                            }
                    ?>
                            <span class="request-list-number hidden-xs whole_row_link col-md-2 col-lg-2 col-sm-2" aria-labelledby="col_head_num">
                                <?php
                                    echo "$record->number";
                                ?>
                            </span>
                            <span class="request-list-service hidden-sm hidden-xs whole_row_link col-lg-3 col-md-3" aria-labelledby="col_head_ser">
                                <?php
                                echo "$record->cmdb_ci";
                                ?>
                            </span>

                            <span class="request-list-description whole_row_link col-lg-4 col-md-6 col-sm-6 col-xs-4" aria-labelledby="col_head_des">
                                <?php
                                echo "$record->short_description";
                                ?>
                            </span>
                            <span class="request-list-status whole_row_link col-lg-2 col-md-2 col-sm-2 col-xs-2" aria-labelledby="col_head_sta">
                                <?php
                                    //get and display the state of the record
                                    if (array_key_exists($record->state, $states)) {
                                        $class = $states[$record->state];
                                        echo "<span class='$class'>$record->state</span>";
                                    }
                                    //check to see if logged in user is in watchlist and is not the caller - if so display watching label
                                    if ( strpos($prb_jsonwl->records[$prb_count]->watch_list, $user_id) !== FALSE && $prb_jsonwl->records[$prb_count]->u_caller != $user_id) {
                                        echo " <span class='label label-warning'>Watching</span>";
                                    }
                                    $prb_count++;
                                ?>
                            </span>
                        </a></li>
                    <?php
                    }
                    ?>
                    </ol>

                    <?php } ?>

                    <?php if( $has_inc ) { ?>
                    <ol class="request-list" style="padding-left:0px;" aria-labelledby="incident_header">
                    
                    <?php
                    //Display incidents
                    usort($inc_json->records, 'sortByNumberDesc'); //order tickets by number descending
                    usort($inc_jsonwl->records, 'sortByNumberDesc'); //match ordering in watch list
                    $inc_count = 0;
                    foreach ( $inc_json->records as $record ) {
                        if ($record->state != "Resolved" && $record->state != "Awaiting User Info") {
                            $record->state = "Active";
                        }
                            $detail_url = site_url() . '/myrequest/' . $record->number . '/';
                            if ($record->state == "Resolved" || $record->state == "Closed") {
                                echo "<li class='row resolved_ticket'><a href='$detail_url'>";
                            } else {
                                echo "<li class='row row_underline inner_row_underline'><a href='$detail_url'>";
                            }
                    ?>
                            <span class="request-list-number hidden-xs whole_row_link col-md-2 col-lg-2 col-sm-2" aria-labelledby="col_head_num">
                                <?php
                                    echo "$record->number";
                                ?>
                            </span>
                            <span class="request-list-service hidden-sm hidden-xs whole_row_link col-lg-3 col-md-3" aria-labelledby="col_head_ser">
                                <?php
                                echo "$record->cmdb_ci";
                                ?>
                            </span>

                            <span class="request-list-description whole_row_link col-lg-4 col-md-6 col-sm-6 col-xs-4" aria-labelledby="col_head_des">
                                <?php
                                echo "$record->short_description";
                                ?>
                            </span>
                            <span class="request-list-status whole_row_link col-lg-2 col-md-2 col-sm-2 col-xs-2" aria-labelledby="col_head_sta">
                                <?php
                                    //get and display the state of the record
                                    if (array_key_exists($record->state, $states)) {
                                        $class = $states[$record->state];
                                        echo "<span class='$class'>$record->state</span>";
                                    }
                                    //check to see if logged in user is in watchlist and is not the caller - if so display watching label
                                    if ( strpos($inc_jsonwl->records[$inc_count]->watch_list, $user_id) !== FALSE && $inc_jsonwl->records[$inc_count]->u_caller != $user_id) {
                                        echo " <span class='label label-warning'>Watching</span>";
                                    }
                                    $inc_count++;
                                ?>
                            </span>
                        </a></li>
                    <?php
                    }
                    ?>
                    </ol>
                    <?php } ?>

                    <?php if( $has_req || $has_inc ) { ?>
                    <h2 id="request_header" class="assistive-text">Requests</h2>
                    <?php } ?>

                    <?php if( $has_req ) { ?>
                    <ol class="request-list" style="list-style-type:none; padding-left:0px;" aria-labelledby="request_header">
                    <?php
                    //Dispaly Requests
                    usort($req_json->records, 'sortByNumberDesc'); //order tickets by number descending
                    usort($req_jsonwl->records, 'sortByNumberDesc'); //match ordering in watch list
                    $req_count = 0;
                    foreach ( $req_json->records as $record ) {
                    
                            if ($record->state != "Resolved" && $record->state != "Awaiting User Info") {
                                $record->state = "Active";
                            }
                            $detail_url = site_url() . '/myrequest/' . $record->number . '/';
                            if ($record->state == "Resolved" || $record->state == "Closed") {
                                echo "<li class='row resolved_ticket'><a href='$detail_url'>";
                            }
                            else {
                                echo "<li class='row'><a href='$detail_url'>";
                            }
                    ?>
                            <span class="request-list-number hidden-xs whole_row_link col-lg-2 col-md-2 col-sm-2" aria-labelledby="col_head_num">
                                <?php
                                echo "$record->number";
                                ?>
                            </span>
                            <span class="request-list-service hidden-sm hidden-xs whole_row_link col-lg-3 col-md-3" aria-labelledby="col_head_ser">
                                <?php
                                echo "$record->cmdb_ci";
                                ?>
                            </span>
                            <span class="request-list-description whole_row_link col-lg-4 col-md-6 col-sm-6 col-xs-4" aria-labelledby="col_head_des">
                                <?php
                                echo "$record->short_description";
                                ?>
                            </span>
                            <span class="request-list-status whole_row_link col-lg-2 col-md-2 col-sm-2 col-xs-2" aria-labelledby="col_head_sta">
                                <?php
                                    //Get and display state of the request
                                    if (array_key_exists($record->state, $states)) {
                                        $class = $states[$record->state];
                                        echo "<span class='$class'>$record->state</span>";
                                    }
                                    //if logged in user is in the watch list and not the caller then display watching tag
                                    if ( strpos($req_jsonwl->records[$req_count]->watch_list, $user_id) !== FALSE && $req_jsonwl->records[$req_count]->u_caller != $user_id) {
                                        echo " <span class='label label-warning'>Watching</span>";
                                    }
                                    $req_count++;

                                ?>
                            </span>
                        </a></li>
                    <?php
                    }
                    ?>
                    </ol>
                    <?php } ?>
                    
                    <?php if( !$has_req && !$has_inc ) { ?>
                        <p>You have no current requests with UW-IT.</p>
                    <?php } ?>

                <?php } else {?>
                    <p>Whoops! Something went wrong, if this persists, please contact the Administrator.</p>
                <?php }
                } else {
                    echo "<h3>Status 403: Unauthorized</h3>";
                    echo "<p>Please log into your UW NETID to view your list of Requests and Incidents</p>";
                }
                ?>
                        <p class="alert alert-info" style="margin-top:10px;">Not seeing your request?  You may need to log in using a different UW NetID. Want to provide feedback? Please email <a href="mailto:help@uw.edu">help@uw.edu</a>.</p>

              <?php endwhile; ?>
                </div>
		</div><!-- #content -->
	</div><!-- #primary -->
</div><!-- #main-content -->

<?php
get_footer();
