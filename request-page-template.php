<?php

define( 'DONOTCACHEPAGE', True );
if(isset( $_SERVER['REMOTE_USER'])) {
    $user = $_SERVER['REMOTE_USER'];
} else if(isset($_SERVER['REDIRECT_REMOTE_USER'])) {
    $user = $_SERVER['REDIRECT_REMOTE_USER'];
} else if(isset($_SERVER['PHP_AUTH_USER'])) {
    $user = $_SERVER['PHP_AUTH_USER'];
}
$error_flag = False;
$sn_num = get_query_var('ticketID');
if( $sn_num == '' ) {
    $new_url = site_url() . '/myrequests/';
    wp_redirect( $new_url );
}
//Handle submitting comments
if( isset( $_POST['submitted'] ) && isset( $_POST['comments'] ) ) {
    $comments = $_POST['comments'];
    $comments_json = array(
        'actor' => $user,
        'record' => $sn_num,
        'comment' => $comments,
    );
    $comments_json = json_encode( $comments_json );
    $comments_url = SN_URL . '/comment.do';
    // If a POST and have comments - create a comment in SN
    if( get_option('uwc_SN_USER') && get_option('uwc_SN_PASS') && get_option('uwc_SN_URL') ) {
        $args = array(
            'headers' => array(
            'Authorization' => 'Basic ' . base64_encode( get_option('uwc_SN_USER') . ':' . get_option('uwc_SN_PASS') ),
            'Content-Type' => 'application/json',
            ),
        'body' => $comments_json,
        );
    }
    $response = wp_remote_post( $comments_url, $args );
        wp_redirect( $_SERVER['REQUEST_URI'] ); exit;
}

get_header(); ?>

<?php while ( have_posts() ) : the_post(); ?>

<div id="main-content" class="row main-content">
		<div id="content" class="site-content it_container" role="main">
    <div id="secondary" class="col-lg-3 col-md-3 hidden-sm hidden-xs" role="complementary">
      <div class="" id="sidebar" role="navigation" aria-label="Sidebar Menu">
        <?php dynamic_sidebar('servicenow-sidebar'); ?>
      </div>
    </div>
	<div id="primary" class="col-xs-12 col-sm-12 col-md-9 col-lg-9 itsm-primary">
    

			<?php
            the_content();
            if(isset($user)) {
                if( isset( $response ) ) {
                    $status = json_decode($response['body'], true);
                    if( $status['Error']['Status'] !== '200' ) {
                        echo '<div class="alert alert-warning" style="margin-top:2em;">';
                        echo 'Attention! Your comment could not be posted: ' . $status['Error']['Text'] . ' (' . $status['Error']['Status'] . ')';
                        echo '</div>';
                        $error_flag = True;
                    }
                }
                ?>
                <div class="user-logout row">
                 <span style="float:right;"><span class="glyphicon glyphicon-user"></span>&nbsp;<?php echo $user; ?> &nbsp;&nbsp;&nbsp;<a href="<?php echo home_url('/user_logout'); ?>" class="buttonesque">LOGOUT</a></span>
                </div>
                <?php
                        $args = array(
                            'headers' => array(
                                'Authorization' => 'Basic ' . base64_encode( get_option('uwc_SN_USER') . ':' . get_option('uwc_SN_PASS') ),
                            )
                        );

                        //Get table record as JSON associated with NETID logged in user
                        $user_url = '/sys_user_list.do?JSONv2&sysparm_query=user_name%3D' . $user;
                        $user_json = get_SN($user_url, $args);
                        //SN sys_id of user
                        $user_id = $user_json->records[0]->sys_id;
                        $firstname = $user_json->records[0]->first_name;
                        $lastname = $user_json->records[0]->last_name;
                        //fullname of user for use in comparing to watchlist
                        $name = $firstname . " " . $lastname;

                        //Set full ticekt type, build display value url, and sys_id url (for use with watch list) based on the type of ticket
                        $sn_type = substr($sn_num, 0, 3);
                        if( $sn_type == 'REQ' ) {
                            $url = '/u_simple_requests_list.do?JSONv2&displayvalue=true&sysparm_query=number=' . $sn_num . '^u_caller.user_name=' . $user . '^ORwatch_listLIKE' . $user_id;
                            $sn_type = 'request (REQ)';
                            $urlwl =  '/u_simple_requests_list.do?JSONv2&sysparm_query=number=' . $sn_num . '^u_caller.user_name=' . $user . '^ORwatch_listLIKE' . $user_id;
                        } else if( $sn_type == 'INC' ) {
                            $url = '/incident.do?JSONv2&displayvalue=true&sysparm_query=number=' . $sn_num . '^caller_id.user_name=' . $user . '^ORwatch_listLIKE' . $user_id;
                            $sn_type = 'incident (INC)';
                            $urlwl = '/incident.do?JSONv2&sysparm_query=number='. $sn_num . '^caller_id.user_name=' . $user . '^ORwatch_listLIKE' . $user_id;
                        } else if( $sn_type == 'PRB' ) {
                            $url = '/problem.do?JSONv2&displayvalue=true&sysparm_action=getRecords&sysparm_query=active=true^watch_listLIKE' . $user_id;
                            $sn_type = 'problem (PRB)';
                            $urlwl = '/problem.do?JSONv2&sysparm_action=getRecords&sysparm_query=active=true^watch_listLIKE' . $user_id;
                        } else {
                            echo "Unrecognized type";
                            $error_flag = True;
                        }
                        $ticket_json = get_SN($url, $args);
                        $record = $ticket_json->records[0];


                        $ticket_jsonwl = get_SN($urlwl, $args);
                        $recordwl = $ticket_jsonwl->records[0];
                        //array of sys_id's of users in watch list
                        $watch_list = explode(',', $recordwl->watch_list);

                        //We already have the logged in user's netid - is the logged in user the caller?
                        if( $record->u_caller == $name || $record->caller_id == $name ) {
                            $caller_nid = $user;
                        } else {
                            if ($sn_type == 'request (REQ)') {
                                $caller_url = '/sys_user_list.do?JSONv2&sysparm_query=name%3D' . urlencode($record->u_caller);
                            } else if ($sn_type == 'incident (INC)') {
                                $caller_url = '/sys_user_list.do?JSONv2&sysparm_query=name%3D' . urlencode($record->caller_id);
                            }
                            $caller_json = get_SN($caller_url, $args);
                            $caller_nid = $caller_json->records[0]->user_name;
                        }

                        // Get the comments
                        $comment_url = '/sys_journal_field.do?displayvalue=true&JSONv2&sysparm_cation=getRecords&sysparm_query=active=true^element=comments^element_id=' . $record->sys_id;
                        $comment_json = get_SN($comment_url, $args);
                        $comments = $comment_json->records;

                        if ($sn_num !== $record->number) {
                            echo "<div class='alert alert-danger'>$sn_num is not one of your current requests.</div>";
                            $error_flag = True;
                        } else  {
                            echo "<h2 style='margin-top:0;'>$record->short_description&nbsp;&nbsp;<span style='color:#999;'>($record->number)</span></h2>";
                            echo "<h3 class='assistive-text'>Details:</h3>";
                            echo "<table class='table'>";
                        if( !empty( $record->caller_id ) ) {
                            $caller = $record->caller_id;
                        } else if( !empty( $record->u_caller ) ) {
                            $caller = $record->u_caller;
                        } else {
                            $caller = 'UNKNOWN';
                        }

                        // Array of record states and their corresponding classes
                        $states = array(
                            "New" => 'class="label label-success"',
                            "Active" => 'class="label label-success"',
                            "Awaiting User Info" => 'class="label label-warning"',
                            "Awaiting Tier 2 Info" => 'class="label label-success"',
                            "Awaiting Vendor Info" => 'class="label label-success"',
                            "Internal Review" => 'class="label label-success"',
                            "Stalled" => 'class="label label-success"',
                            "Delivered" => 'class="label label-success"',
                            "Resolved" => 'class="label label-default"',
                            "Closed" => 'class="label label-default"',
                        );

                        if ($record->state != "Resolved" && $record->state != "Awaiting User Info" && $record->state != "Closed") {
                            $record->state = "Active";
                        }


                        echo "<tr><td>Status:</td><td class='request_status'>";
                                if (array_key_exists($record->state, $states)) {
                                    $class = $states[$record->state];
                                    echo "<span $class>$record->state</span>";
                                }
                                if ( in_array($user_id, $watch_list) && $user_id != $recordwl->u_caller) {
                                    echo " <span class='label label-warning'>Watching</span>";
                                }
                        echo "</td></tr>";
                        echo "<tr><td>Service:</td> <td>$record->cmdb_ci</td></tr>";
                        echo "<tr><td>Opened on:</td> <td>$record->opened_at</td></tr>";
                        echo "<tr><td>Last Updated:</td> <td>$record->sys_updated_on</td></tr>";

                        //Get attachments
                        /*
                        $att_url = '/sys_attachment.do?JSONv2&sysparm_query=table_sys_id=' . $record->sys_id;
                        $attach_json = get_SN($att_url, $args);
                        if (count($attach_json->records) > 0) {
                            echo "<tr><td>Attachments:</td> <td>";
                            echo "<div class='row'>";

                            foreach( $attach_json->records as $attachment ) {
                                $attID = $attachment->sys_id;
                                $attName = $attachment->file_name;
                                $content_type = $attachment->content_type;
                                //attachment download link
                                $url = 'https://uweval.service-now.com/sys_attachment.do?sys_id=' . $attID;

                                echo "<div class='col-lg-6'>";
                                //Check for mimetype and display related icon
                                if (strstr($content_type, "/", true) == "image") {
                                ?>
                                    <a href=<?= $url; ?> title="<?= $attName ?>"><div class="att_wrap"><i class="fa fa-file-image-o fa-2x"></i><p><?= $attName ?></p></div></a>
                                <?php
                                } else if (strstr($content_type, "/") == "/pdf" ) {
                                ?>
                                    <a href=<?= $url; ?> title="<?= $attName ?>"><div class="att_wrap"><i class="fa fa-file-pdf-o fa-2x"></i><p><?= $attName ?></p></div></a>
                                <?php
                                } else if ( strpos( strstr($content_type, "/"), "zip") ) {
                                ?>
                                    <a href=<?= $url; ?> title="<?= $attName ?>"><div class="att_wrap"><i class="fa fa-file-zip-o fa-2x"></i><p><?= $attName ?></p></div></a>
                                <?php
                                } else {
                                ?>
                                    <a href=<?= $url; ?> title="<?= $attName ?>"><div class="att_wrap"><i class="fa fa-file-o fa-2x"></i><p><?= $attName ?></p></div></a>
                                <?php
                                }
                                echo "</div>";
                            }
                        ?>
                        <?php
                        }
                        */
                        echo "</div>";
                        echo "</td></tr>";
                        echo "</table>";
                        echo "<h3 style='margin-top:2em;'>Description:</h3><div><pre>" . stripslashes($record->description) . " </pre></div>";

                        //Set up comment box
                        if(!$error_flag && $record->state != "Closed") {
                            $submit_url = site_url() . '/myrequest/' . $sn_num . '/'; ?>
                            <form role='form' action="<?php $submit_url; ?>" method='post'>
                            <div class='form-group' style='margin-bottom:1em;'>
                                <label for='exampleInputPassword1'>Respond to Support Staff:</label>
                                <textarea name='comments' class='form-control' rows='3' style='resize:vertical;'></textarea>
                            </div>
                            <button type='submit' class='btn btn-primary'>Submit</button>
                            <input type="hidden" name="submitted" id="submitted" value="true" />
                        </form>
                        <?php 
                        } else if ($record->state == "Closed") {
                          echo "<p class='alert alert-error'>This record has been closed. If you wish to revisit this issue, you can reference the issue number above in a new request to <a href='mailto:help@uw.edu'>help@uw.edu</a>.";
                        } else {
                          echo "<h3>Status 403: Unauthorized</h3>";
                          echo "<p>Please log in to your UW NETID in order to view your Requests and Incidents</p>";
                        } 

                        echo "<h3 style='margin-top:2em;'>Additional comments:</h3>";

                        usort( $comments, 'sortByCreatedOnDesc' ); //comments sorted chronologically descending
                        echo "<ol style='margin-left:0;'>";

                        $prevwatch = array();
                        foreach( $comments as $comment ) {
                            $watcher = False;
                            $comment_user = $comment->sys_created_by;
                            //is this user the logged in user or do we know they're in the watch list already? if not get their SN sys_id
                            if ( !in_array($comment_user, $prevwatch) && $comment_user != $user) {
                                $user_url = '/sys_user_list.do?JSONv2&sysparm_query=user_name%3D' . $comment_user;
                                $user_json = get_SN($user_url, $args);
                                $comment_user_id = $user_json->records[0]->sys_id;
                                //Are they a watcher?
                                if ( in_array($comment_user_id, $watch_list) ) {
                                    $watcher = True;
                                    //We've seen them now
                                    array_push($prevwatch, $comment_user);
                                }
                            } else if ( in_array($comment_user, $prevwatch) ) {
                                $watcher = True;
                            } else {

                            }
                            echo "<li class='media'>";
                            //Check who the commenter is, following roles win in order listed if a user is more than one
                            //logged in user?
                            if ($comment->sys_created_by == $user) {
                                echo "<div class='media-body caller-comments'>";
                                $display_user = $user;
                            //caller?
                            } elseif ($comment->sys_created_by == $caller_nid) {
                                echo "<div class='media-body support-comments'>";
                                $display_user = "Caller";
                            //watcher?
                            } elseif ($watcher) {
                                echo "<div class='media-body support-comments'>";
                                $display_user = "Watcher";
                            //support staff
                            } else {
                                echo "<div class='media-body support-comments'>";
                                $display_user = "SUPPORT STAFF";
                            }
                            echo "<div class='comment-timestamp'><strong class='user_name'>$display_user</strong> <span class='create-date'>$comment->sys_created_on</span></div>";
                            echo "<pre style='white-space:pre-wrap'>";
                            echo stripslashes($comment->value);
                            echo "</pre>";
                            echo "</div>";
                            echo "</li>";
                        }
                        echo "</ol>";
                        } //end if else to see if incident/request number doesn't match
                      }


			?>
		</div><!-- #content -->
	</div><!-- #primary -->
</div><!-- #main-content -->

<?php
  endwhile;
get_footer();
