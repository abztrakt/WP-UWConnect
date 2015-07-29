<?php
/* Functions to scrape the eOutage page and return whether or not there is currently an eOutage */


function check_e_outage() {
   $url = "https://www.washington.edu/cac/outages";
  $options = Array(
            CURLOPT_RETURNTRANSFER => TRUE,  // Setting cURL's option to return the webpage data
            CURLOPT_FOLLOWLOCATION => TRUE,  // Setting cURL to follow 'location' HTTP headers
            CURLOPT_AUTOREFERER => TRUE, // Automatically set the referer where following 'location' HTTP headers
            CURLOPT_CONNECTTIMEOUT => 120,   // Setting the amount of time (in seconds) before the request times out
            CURLOPT_TIMEOUT => 120,  // Setting the maximum amount of time for cURL to execute queries
            CURLOPT_MAXREDIRS => 10, // Setting the maximum number of redirections to follow
            CURLOPT_USERAGENT => "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1a2pre) Gecko/2008073000 Shredder/3.0a2pre ThunderBrowse/3.2.1.8",  // Setting the useragent
            CURLOPT_URL => $url, // Setting cURL's URL option with the $url variable passed into the function
        );

        $ch = curl_init();  // Initialising cURL
        curl_setopt_array($ch, $options);   // Setting cURL's options using the previously assigned array data in $options
        $data = curl_exec($ch); // Executing the cURL request and assigning the returned data to the $data variable
        curl_close($ch);    // Closing cURL

	$status = scrape_between($data, "<div class=\"status\">", "</body>");
	if (strpos($data, "not operating normally")) {
		return $status;
	}
	else { return false; }
}

  function scrape_between($data, $start, $end){
        $data = stristr($data, $start); // Stripping all data from before $start
        $data = substr($data, strlen($start));  // Stripping $start
        $stop = stripos($data, $end);   // Getting the position of the $end of the data to scrape
        $data = substr($data, 0, $stop);    // Stripping all data from after and including the $end of the data to scrape
        return $data;   // Returning the scraped data from the function
    }

  function parse_eoutage($msg) {
	  $data = substr($msg, 31); //strip Updated text
	$data = substr($data, 0, stripos($data, "Information sent to the eOutage mailing list is also posted online"));
	return $data;
  }

  function service_status() {
    $hash = base64_encode( get_option('uwc_SN_USER') . ':' . get_option('uwc_SN_PASS') );
    $args = array(
        'headers' => array(
            'Authorization' => 'Basic ' . $hash,
        ),
        'timeout' => 25,
    );

    echo "<h4>eOutages</h4><p>For more information about eOutages, visit <a href=\"https://www.washington.edu/cac/outages\">eOutage Homepage</a><p>";
    $dom = new DOMDocument();
    $eOutage = check_e_outage();
    if (!$eOutage) {
  		echo "<div class='alert alert-warning' style='margin-top:2em;'>There are no eOutages.</div>";
  		}
    else {
      $dom->loadHTML($eOutage);
      $titleArray = array();
      $dateArray = array();
      $contentArray = array();
      $eOutageTitles = $dom->getElementsByTagName('h4');
      $eOutageDates = $dom->getElementsByTagName('em');
      $eOutageContent = $dom->getElementsByTagName('div');
      foreach ($eOutageTitles as $title) {
          $titleArray[] = $title->nodeValue;
      }
      foreach ($eOutageDates as $date) {
         $dateArray[] = $date->nodeValue;
      }
     foreach ($eOutageContent as $content) {
        $contentArray[] = parse_eoutage($content->textContent);
     }
     for ($i=0; $i < count($titleArray) ; $i++) {
       			echo "<div class='servicecontent row'>";
       			echo "<div class='servicewrap row'>";
                          echo "<span class='glyphicon glyphicon-chevron-right switch' style='display:inline-block;float:left;'></span>";
                          echo "<span class='service_name col-lg-5 col-md-5 col-sm-7 col-xs-7' style='font-weight:bold; display:inline-block;'>".$titleArray[$i]."</span>";
                          echo "<span class='service_time col-lg-4 col-md-4 col-sm-4 col-xs-4' style='color:#aaa; font-size:95%; display:inline-block;'><span class='hidden-sm hidden-xs'></span>".$dateArray[$i]."</span>";
  			echo "</div>";
                          echo "<ul class='relatedincidents'>";
                          echo "<li class='incident-head row'>";
                          echo "<div class='col-lg-9 col-md-9 col-sm-9 col-xs-9'>Description</div>";
                          echo "</li>";
    			echo "<div class='col-lg-9 col-md-9 col-sm-9 col-xs-9 inc_sdesc'>" . $contentArray[$i] . "</div>";
                          echo "</li></a>";
                          echo "</ul>";
                          echo "</div><p>";


  	}

    }
    echo "</div>";
    echo "<br><h4>High Priority Incidents</h4>";


    //$JSON = get_SN('/incident_list.do?JSONv2&sysparm_query=active%3Dtrue%5EstateNOT%20IN6%2C7%5Epriority%3D2%5EORpriority%3D1%5Eu_sectorNOT%20INK20%2CPNWGP%2CPWave%5EORu_sector%3D%5Eparent_incident=NULL&displayvalue=true', $args);
    //$IDJSON = get_SN('/incident_list.do?JSONv2&sysparm_query=active%3Dtrue%5EstateNOT%20IN6%2C7%5Epriority%3D2%5EORpriority%3D1%5Eu_sectorNOT%20INK20%2CPNWGP%2CPWave%5EORu_sector%3D%5Eparent_incident%3DNULL^ORDERBYcmdb_ci', $args);
  	$JSON = get_SN('/incident_list.do?JSONv2&sysparm_query=active%3Dtrue%5EstateNOT%20IN6%2C7%5Epriority%3D2%5EORpriority%3D1%5Eu_sector%3DUW%5EORu_sector%3D%5Eparent_incident=NULL^cmdb_ci.u_organizational_group%3D51af60a86f2a110054aafd16ad3ee4d0&displayvalue=true', $args);
       $IDJSON =  get_SN('/incident_list.do?JSONv2&sysparm_query=active%3Dtrue%5EstateNOT%20IN6%2C7%5Epriority%3D2%5EORpriority%3D1%5Eu_sector%3DUW%5EORu_sector%3D%5Eparent_incident%3DNULL^cmdb_ci.u_organizational_group%3D51af60a86f2a110054aafd16ad3ee4d0^ORDERBYcmdb_ci', $args);
    if(!$JSON) {
            echo "<div class='alert alert-warning' style='margin-top:2em;'>We are currently experiencing problems retrieving the status of our services. Please try again in a few minutes.</div>";
        }
        elseif(empty($JSON->records)) {
            echo "<div class='alert alert-warning' style='margin-top:2em;'>There are no high priority incidents.</div>";
        }
        $sn_data = array();

       foreach ( $IDJSON->records as $record ) {
            if( !isset( $sn_data[$record->cmdb_ci] ) ) {
                    $sn_data[$record->cmdb_ci] = array();
                    unset($first);
                }
  	   else {
                          $first = $sn_data[$record->cmdb_ci][1];  //cannot assume order. load current first for ci
                }
                $create = $record->sys_created_on;
                if( !isset( $first ) ) {
                    $first = $create;
                }
                if($create < $first) {
                    $first = $create;
                }
                $sn_data[$record->cmdb_ci][] = $record;
                $sn_data[$record->cmdb_ci][] = $first;

        }
        $classes = array();
        foreach ($sn_data as $ci) {
          $serviceid = $ci[0]->cmdb_ci;
          $servJSON = get_SN('/cmdb_ci_list.do?JSONv2&sysparm_query=u_active!%3Dfalse%5Esys_id%3D' . $serviceid . '&displayvalue=true', $args);


         $class = $servJSON->records[0]->sys_class_name;
  //	if ($servJSON->records[0]->u_organizational_group !== "UW-IT") { $classes[]="xxx";}
          $classes[] = $class;
  	}

        if ( !empty( $JSON->records ) ) {
            $sn_data = array();
            foreach( $JSON->records as $record ) {
  	     // if ($record->cmdb_ci == "") { continue; }
                if( !isset( $sn_data[$record->cmdb_ci] ) ) {
                    $sn_data[$record->cmdb_ci] = array();
                    unset($first);
                }
  	      else {
  			$first = $sn_data[$record->cmdb_ci][1];  //cannot assume order. load current first for ci
                }
  	      $create = $record->sys_created_on;
                if( !isset( $first ) ) {
                    $first = $create;
                }
                if($create < $first) {
                    $first = $create;
                }

                $sn_data[$record->cmdb_ci][] = $record;
  	      $sn_data[$record->cmdb_ci][] = $first;

            }
  	      ksort($sn_data, SORT_STRING | SORT_FLAG_CASE); //sort alphabetically by ci name

                echo "<h2 class='assistive-text' id='impact_headeing'>Impacted Services</h2>";
                # put the services into a single ordered list
                echo "<div class='row' aria-labelledby='impact_heading'>";
                $i = 0;
                foreach( $sn_data as $ci) {
  		  $update_time = new DateTime();
  		  $current_update_time = new DateTime();
  		  $count = 0;
  		  foreach ($ci as $incident) { //count incidents for ci
  			if (!is_string($incident)) {
  			$current_update_time = DateTime::createFromFormat("m-d-Y H:i:s", $incident->sys_updated_on);



  			      if ($count == 0 || $update_time < $current_update_time) { $update_time = $current_update_time;

  }			$count++;


  			}
  		  }
                    $class = $classes[$i];
                    $service = array_search($ci, $sn_data);
                    // handle the case of blank services and switches who's 'name' is a sequence of 5 or more numbers
                    //if ( $service !== '' && !preg_match('/^\d{5,}$/', $service) ) {
  			if ($service !== '' ) {
  	//print_r( $ci);
  	//		echo $ci[0]['cmdb_ci'];
  			$time = end($ci);

                      echo "<div class='servicecontent row'>";
                        echo "<div class='servicewrap row'>";
                          echo "<span class='glyphicon glyphicon-chevron-right switch' style='display:inline-block;float:left;'></span>";
                          echo "<span class='service_name col-lg-5 col-md-5 col-sm-7 col-xs-7' style='font-weight:bold; display:inline-block;'>".$service." (".$count.")</span>";
                          echo "<span class='service_class hidden-xs hidden-sm col-lg-2 col-md-2' style='display:inline-block; font-size:90%;'>$class</span>";
                          echo "<span class='service_time col-lg-4 col-md-4 col-sm-4 col-xs-4' style='color:#aaa; font-size:95%; display:inline-block;'><span class='hidden-sm hidden-xs'>Reported at </span>$time<br><span class='hidden-sm hiiden-xs'>Updated at ".$update_time->format('m-d-Y H:i:s')."</span></span>";
                        echo "</div>";
                        echo "<ul class='relatedincidents'>";
                        echo "<li class='incident-head row'>";
                            echo "<div class='col-lg-3 col-md-3 col-sm-3 col-xs-3'>Incident Number</div>";
                            echo "<div class='col-lg-9 col-md-9 col-sm-9 col-xs-9'>Description</div>";
                        echo "</li>";
                            foreach( $ci as $incident ) {
                              if (!is_string($incident)) {
                               // echo "<a href='/itconnect/incident/$incident->number'><li class='incident row'>";
  				echo "<li class='incident row'>";
                                    echo "<div class='col-lg-3 col-md-3 col-sm-3 col-xs-3 '>" . $incident->number . "</div>";
                                    echo "<div class='col-lg-4 col-md-4 col-sm-4 col-xs-4 inc_sdesc'>" . $incident->short_description . "</div>";
  				  if ($count > 1) { //if there is only one incident, don't show time twice.
  					echo "<div class='col-lg-4 col-md-4 col-sm-4 col-xs-4' style='color:#aaa; font-size:95%; display:inline-block;'><span class='hidden-sm hidden-xs'>Reported at</span> " . $incident->sys_created_on . "<br>Updated at ".$incident->sys_updated_on."</div>";
  					}
  					echo "</li>";
       //echo "</li></a>";
                              }
                            }
                        echo "</ul>";
                      echo "</div>";
                 }
                 $i++;
              }
  echo "</div>";
  }
    echo "<p class='alert' style='margin-top: 2em;'>Experiencing IT problems not listed on this page? <a href=\"".get_option('uwc_SN_URL')."/uwc.do?sysparm_direct=true#/catalog_order/13234c686f377900328c8bec5d3ee444\">Submit an incident report</a> for any issues you are currently experiencing or call (206) 221-5000. Want to provide feedback about this page? <a href='/itconnect/help'>Get help.</a></p>";
          die();
  }
  add_action( 'wp_ajax_service_status', 'service_status' );
  add_action( 'wp_ajax_nopriv_service_status', 'service_status' );
?>
