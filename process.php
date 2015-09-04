<?php
/*
------------------------------------------ PROCESS.PHP --------------------------------------------
Welcome to the event form processor! This takes the POST data from the event form and either adds
or updates an event in the database accordingly. 

This is pure PHP and fairly straightforward, but the things to focus on would likely be the methods 
of encoding the data that comes through as arrays and the generation of the queries.

This code lacks significant security and should not be used in production without improvements in
that regard. It also doesn't do error handling very well. 

----------------------------------------- FILE STRUCTURE ------------------------------------------
The code initially identifies if an event ID has been supplied and determines if this is an update
or a new event. It then builds an key=>value pair $event array with the POST data.

After this, it connects to the database and builds the appropriate query out of the $event array
information. It then inserts or updates the event. If it is a new event using an ID registration
form, it will then attempt to create the registration URL with the new ID and update the record.

----------------------------------------- FULL STRUCTURE ------------------------------------------
Files marked with *asterisks* are important and worth taking a look at. Others are trivial or only
exist as support for this example and are not otherwise useful.

forms.php
| {{*process.php*}}               interprets form data and performs DB insert/updates
| custom.css                      custom CSS styling for this example
| include/ 
| - event_dashboard.php           displays list of events in test DB with edit/delete functions
| - *include/event_form.php*	  the heart of this whole thing - the sample events form
| - include/event_delete.php      deletes events when requested from dashboard (trivial)
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

**CSS and JS files should be concanated/minified together in production

----------------------------------- EXTERNAL FILES/DEPENDENCIES -----------------------------------
This file requires no external tools/plugins/libraries, but would appreciate a database connection.
*/

## initalize variables
$event = array();
$success_id = 0;

## get $event_id from POST and check if its nonzero
$event_id = $_POST["event_id"];
if ($event_id) {
	$update = True; # if its nonzero, we're updating
	$event['event_id'] = $event_id; # first addition to the array
}
else {
	$update = False; # if its zero, this is a new event
}

## Now let's build the array.
# we assume title exists, as it was required
$event['title'] = $_POST["title"];
$event['extended_title'] = empty($_POST["extended_title"]) ? "NULL" : (int) $_POST["extended_title"];
# why empty()? POST data is global so ISSET cannot be reliable used
# why "NULL" and not NULL? It must be a string to be inserted in the MySQL query correctly, as NULL prints as '' in the query

# type integers as they come in
$event['course_id'] = empty($_POST["course_id"]) ? "NULL" : (int) $_POST["course_id"];
$event['listing_id'] = empty($_POST["listing_id"]) ? "NULL" : (int) $_POST["listing_id"];
$event['group_id'] = empty($_POST["group_id"]) ? "NULL" : (int) $_POST["group_id"];

$event['start_date'] = empty($_POST["start_date"]) ? "NULL" : $_POST["start_date"];
$event['end_date'] = empty($_POST["end_date"]) ? "NULL" : $_POST["end_date"];

$event['meeting_day'] = empty($_POST["meeting_day"]) ? "NULL" : $_POST["meeting_day"];
$event['start_time'] = empty($_POST["start_time"]) ? "NULL" : $_POST["start_time"];
$event['end_time'] = empty($_POST["end_time"]) ? "NULL" : $_POST["end_time"];
$event['time_zone'] = empty($_POST["time_zone"]) ? "NULL" : $_POST["time_zone"];
$event['runs_for'] = empty($_POST["runs_for"]) ? "NULL" : $_POST["runs_for"];
$event['runs_for_unit'] = empty($_POST["runs_for_unit"]) ? "NULL" : $_POST["runs_for_unit"];

$event['city'] = empty($_POST["city"]) ? "NULL" : $_POST["city"];
$event['province'] = empty($_POST["province"]) ? "NULL" : $_POST["province"];
$event['country'] = empty($_POST["country"]) ? "NULL" : $_POST["country"];
$event['place'] = empty($_POST["place"]) ? "NULL" : $_POST["place"];

$event['details'] = empty($_POST["details"]) ? "NULL" : $_POST["details"];
$event['description'] = empty($_POST["description"]) ? "NULL" : $_POST["description"];
$event['suitability'] = empty($_POST["suitability"]) ? "NULL" : $_POST["suitability"];
$event['time_place'] = empty($_POST["time_place"]) ? "NULL" : $_POST["time_place"];
$event['fees_registration'] = empty($_POST["fees_registration"]) ? "NULL" : $_POST["fees_registration"];
$event['host_sponsor'] = empty($_POST["host_sponsor"]) ? "NULL" : $_POST["host_sponsor"];
$event['accreditation'] = empty($_POST["accreditation"]) ? "NULL" : $_POST["accreditation"];
$event['cancellation'] = empty($_POST["cancellation"]) ? "NULL" : $_POST["cancellation"];
$event['further_information'] = empty($_POST["further_information"]) ? "NULL" : $_POST["further_information"];

# assemble the custom fields array
$custom_fields = array();
/*
Technically we should only have to check if one is empty as there should always be the same number.
However, strange behaviour could occur if the user doesn't fill everything out on the form. The 
best way to address this is likely back in form, and if a proper validator is used then this could
be reduced in compexity. This applies to the contacts and links arrays too.
*/
$custom_fields_titles = empty($_POST["custom_field_titles"]) ? array() : $_POST["custom_field_titles"];
$custom_fields_array = empty($_POST["custom_fields"]) ? array() : $_POST["custom_fields"];
foreach ($custom_fields_titles as $key => $custom_fields_title) {
	$custom_fields[$custom_fields_title] = $custom_fields_array[$key];
}
/*
serialization is necessary but can create some strange characters that can mess up our SQL query
so we base64 encode the serialized array, sacrificing space for safety
*/
$event['custom_fields'] = base64_encode(serialize($custom_fields));

$event['website_url'] = empty($_POST["website_url"]) ? "NULL" : $_POST["website_url"];
$event['flyer_url'] = empty($_POST["flyer_url"]) ? "NULL" : $_POST["flyer_url"];
$event['registration_form'] = empty($_POST["registration_form"]) ? "NULL" : $_POST["registration_form"];
$event['registration_url'] = empty($_POST["registration_url"]) ? "NULL" : $_POST["registration_url"];
if (($event['registration_form'] == 'event_id') and (!$update)) {
	 # destroy registration_url value if an event_id form has been chosen and its a new event
	$event['registration_url'] = NULL;
}

# assemble the contacts array
$contacts = array();
$contact_titles = empty($_POST["contact_titles"]) ? array() : $_POST["contact_titles"];
$contact_types = empty($_POST["contact_types"]) ? array() : $_POST["contact_types"];
$contacts_array = empty($_POST["contacts"]) ? array() : $_POST["contacts"];
foreach ($contact_titles as $key => $contact_title) {
	$contacts[$contact_title] = array($contact_types[$key], $contacts_array[$key]);
}
$event['contacts'] = base64_encode(serialize($contacts));

# assemble the links array
$links = array();
$link_titles = empty($_POST["link_titles"]) ? array() : $_POST["link_titles"];
$links_array = empty($_POST["links"]) ? array() : $_POST["links"];
foreach ($link_titles as $key => $link_title) {
	$links[$link_title] = $links_array[$key];
}
$event['links'] = base64_encode(serialize($links));

# for checkboxes, if it exists in POST it means its true
$event['is_course'] = !empty($_POST["is_course"]) ? 1 : 0;
$event['is_video_course'] = !empty($_POST["is_video_course"]) ? 1 : 0;
$event['is_posted'] = !empty($_POST["is_posted"]) ? 1 : 0;
$event['is_gordon'] = !empty($_POST["is_gordon"]) ? 1 : 0;
$event['is_intensive'] = !empty($_POST["is_intensive"]) ? 1 : 0;
$event['is_highlight'] = !empty($_POST["is_highlight"]) ? 1 : 0;
$event['is_online'] = !empty($_POST["is_online"]) ? 1 : 0;
$event['english_or_french'] = !empty($_POST["english_or_french"]) ? 1 : 0;

## Database connection
$database = new mysqli("localhost", "test_user", "test_password", "testing_grounds");
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}
$database->set_charset("utf8");

## Sanitize our strings
foreach($event as $key => $value) 
{ 
    if (is_string($event[$key]) and !($event[$key] === "NULL")) {
    	# we skip null values as they're already fine and we don't want them wrapped in another set of quotes
    	# every other string is escaped. Integers should be fine, and we shouldn't see any other data types.
    	$event[$key] = "'".$database->real_escape_string($value)."'";
    }
} 

## Build the query and execute it
if ($update) { # updating existing event
	$query = "UPDATE web_events SET ";
	$seperator = ''; # no leading comma
	foreach($event as $key => $value) {
    	$query .= $seperator.$key.' = '.$value; # create key-value pair for SQL query
    	$seperator = ','; # add commas after the first one
	} 
	$query .= " WHERE event_id=$event_id"; # finish up query
	$update_row = $database->query($query); # and execute!
	if($update_row){
		$success_id = $event_id;
	    #print '<br />Success!<br />'; 
	}else{
	    die('Error in update: ('.$$query.')'.$database->error).' Query: '.$query;
	}
}
else { # new event
	# query syntax is different here - the keys and values are split up into their own arrays
	$columns = array_keys($event);
	$columns = implode(",", $columns);
	$values = array_values($event);
	$values = implode(",",$values);

	# they are then added to the query
	$query = "INSERT INTO web_events ($columns) VALUES ($values)";
	$insert_row = $database->query($query);  # and execute!
	$row_id = $database->insert_id;
	if($insert_row){
		$success_id = $row_id; # set row ID as success ID
	}else{
	    die('Error in insert: ('. $database->errno .') '. $database->error);
	}
	# if this new event was supposed to have an event_id related registration form, we can now create it with the ID
	if ($event['registration_form'] == "'event_id'") {
		$registration_url = "'".$database->real_escape_string("http://neufeldinstitute.com/main/cms/event_form.php?type=".$row_id)."'"; # create URL
		$query = "UPDATE web_events SET registration_url = $registration_url WHERE event_id=$row_id";  # build query
		$add_registration = $database->query($query); # and execute!
		if($add_registration){ 
		    # do nothing, let it pass to the echoing of the id at the end
		}else{
		    die('Error in creating event form by ID (event has been posted) : ('. $database->errno .') '. $database->error);
		}
	}	
}
echo $success_id; # return this value to the form's AJAX call if we made it this far - hopefully the event ID
?>