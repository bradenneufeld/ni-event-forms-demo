<?php
/*
--------------------------------------- EVENT_DELETE.PHP ------------------------------------------
Pretty straightforward and unimportant file. Only used for dashboard demo.
----------------------------------------- FULL STRUCTURE ------------------------------------------
Files marked with *asterisks* are important and worth taking a look at. Others are trivial or only
exist as support for this example and are not otherwise useful.

forms.php
| *process.php*                   interprets form data and performs DB insert/updates
| custom.css                      custom CSS styling for this example
| include/ 
| - event_dashboard.php           displays list of events in test DB with edit/delete functions
| - *include/event_form.php*  	  the heart of this whole thing - the sample events form
| - {{include/event_delete.php}}  deletes events when requested from dashboard (trivial)
| css                             see explanations below for 3rd party dependencies**
| - bootstrap-theme.min.css
| - bootstrap.min.css
| - chosen.min.css
| - trumbowyg.min.css
| - images
| - | - icons-black-2x.png
| - | - icons-black.png
| - | - icons-white-2x.png
| - | - icons-white.png
| jss                             see explanations below for 3rd party dependencies**
| - bootstrap.min.js
| - chosen.jquery.min.js
| - jquery.bsAlerts.min.js
| - jquery.geocomplete.min.js
| - trumbowyg.min.js

*/
$event_id = (isset($_POST["event_id"]) and !empty($_POST["event_id"])) ? $_POST["event_id"] : False; // get the event ID
$delete = (isset($_POST["delete"]) and !empty($_POST["delete"])) ? $_POST["delete"] : False; // get the Delete request
$success = 0; // initalize success for AJAX return
if ($event_id and $delete) { // if we have an ID and delete has been POSTed
	$database = new mysqli("localhost", "test_user", "test_password", "testing_grounds");
	if (mysqli_connect_errno()) {
    	exit(0);
    }
    $query = "DELETE FROM web_events WHERE event_id=$event_id"; // delete the event
    $delete_row = $database->query($query);
    if($delete_row){
		$success = $event_id; // if sucessful, return the ID
	}
}
echo $success; // AJAX return value
?>