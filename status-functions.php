<?php
/* Functions to scrape the eOutage page and return whether or not there is currently an eOutage */


function check_e_outage() {
  $e_Outage_url = get_option('uwc_EOutage_URL');
  $data = wp_remote_retrieve_body( wp_remote_get($e_Outage_url) );

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


?>
