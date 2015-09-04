<?php
/*
-------------------------------------- EVENT_DASHBOARD.PHP ----------------------------------------
This is a demo dashboard interface for interacting with the sample forms. It is not important in
terms of functionality and simply provides an interface. There's some fun being had with modals
and delete requests, but overall don't worry about what's in here.

NOTE: This file is not thoroughly commented due to it containing no important logic for the forms.

----------------------------------------- FULL STRUCTURE ------------------------------------------
Files marked with *asterisks* are important and worth taking a look at. Others are trivial or only
exist as support for this example and are not otherwise useful.

forms.php
| *process.php*	                  interprets form data and performs DB insert/updates
| custom.css                      custom CSS styling for this example
| include/ 
| - {{event_dashboard.php}}       displays list of events in test DB with edit/delete functions
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

----------------------------------- EXTERNAL FILES/DEPENDENCIES -----------------------------------
This file requires the following external tools/plugins/libraries:
eg. ExampleFile - what it's used for - path/to/file.js (file-its-called-from.php)

Bootstrap 3
 JS - js/bootstrap.min.js
 Core CSS - quick page structure and elements - css/bootstrap.min.css (forms.php)
 Theme CSS - easy form styling - css/bootstrap-theme.min.css (forms.php)

jQuery - reduces JavaScript headaches considerably - https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js (forms.php)

forms.php - contains several JavaScript functions required for this view to function - forms.php (parent)

*/

## Open a new connection to the test database
$database = new mysqli("localhost", "test_user", "test_password", "testing_grounds");
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

## Get all existing events
$results = $database->query("SELECT * FROM web_events");
$events = array(); # initalize $events array
# these arrays are used for the sorting of events on the dashboard
$video_course_ids = array();
$course_ids = array();
$presentation_ids = array();
# build arrays
while($data = $results->fetch_assoc()) { 
	$event_id = $data['event_id'];
	unset($data['event_id']);
	$events[$event_id] = $data;
	if ($data['is_video_course']) {
		$video_course_ids[] = $event_id;
	}
	else if ($data['is_course']) {
		$course_ids[] = $event_id;
	}
	else {
		$presentation_ids[] = $event_id;
	}
}
// Frees the memory associated with a result
$results->free();

// close connection 
$database->close();

## New connection to the actual DB to get listings, course names, and groups
$dbh = new mysqli("162.213.157.205", "roderick", "w1nt3rp3g", "roderick_main");
if (mysqli_connect_errno()) {
  printf("Connect failed: %s\n", mysqli_connect_error());
  exit();
}
$dbh->set_charset("utf8");

# Get listing names for display via UID associated with the event
$results = $dbh->query("SELECT TrainingRecord_fm.*,contact.firstname as firstname,contact.lastname as lastname FROM TrainingRecord_fm,contact WHERE TrainingRecord_fm.UID = contact.UID ORDER BY contact.lastname");
$listings = array();
while ($data = $results->fetch_array()){
  if ($data['webstatus'] == "Listed_on_Website" or $data['UID'] == 21742){
    $listings[$data['UID']] = $data['firstname']." ".$data['lastname'];
  }
}
# I don't like this, but it's necessary right now - as Gordon doesn't come through TrainingRecord_fm, he must be added manually.
$listings[7279] = "Gordon Neufeld";
// Frees the memory associated with a result
$results->free();

// close connection, we're all done with SQL for this file
$dbh->close();

?>

<div id="courses-list">
  <h2 class="sub-header">Courses</h2>
  <div class="table-responsive">
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Event ID</th>
          <th>Title</th>
          <th>Instructor</th>
          <th>Start Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      	<?php 
      	foreach($course_ids as $event_id) {
      		$UID = $events[$event_id]['listing_id'];
		    print '<tr>';
		    print '<td>'.$event_id.'</td>';
		    print '<td>'.$events[$event_id]["title"].'</td>';
		    print '<td>'.$listings[$UID].'</td>';
		    print '<td>'.$events[$event_id]["start_date"].'</td>';
		    print '<td><button data-id="'.$event_id.'" type="button" class="btn btn-primary btn-sm edit">Edit</button> <button data-id="'.$event_id.'" type="button" data-toggle="modal" data-target="#confirm-delete" class="btn btn-default btn-sm">Delete</button></td>';
		    print '</tr>';
		}  
		?>
      </tbody>
    </table>
  </div>

</div>
<div id="video-courses-list">
  <h2 class="sub-header">Video Courses</h2>
  <div class="table-responsive">
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Event ID</th>
          <th>Title</th>
          <th>Instructor</th>
          <th>Start Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      	<?php 
      	foreach($video_course_ids as $event_id) {
		    $UID = $events[$event_id]['listing_id'];
		    print '<tr>';
		    print '<td>'.$event_id.'</td>';
		    print '<td>'.$events[$event_id]["title"].'</td>';
		    print '<td>'.$listings[$UID].'</td>';
		    print '<td>'.$events[$event_id]["start_date"].'</td>';
		    print '<td><button data-id="'.$event_id.'" type="button" class="btn btn-primary btn-sm edit">Edit</button> <button data-id="'.$event_id.'" type="button" data-toggle="modal" data-target="#confirm-delete" class="btn btn-default btn-sm">Delete</button></td>';
		    print '</tr>';
		}  
		?>
      </tbody>
    </table>
  </div>
</div>
<div id="presentations-list">
  <h2 class="sub-header">Presentations</h2>
  <div class="table-responsive">
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Event ID</th>
          <th>Title</th>
          <th>Instructor</th>
          <th>Start Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      	<?php 
      	foreach($presentation_ids as $event_id) {
			$UID = $events[$event_id]['listing_id'];
		    print '<tr>';
		    print '<td>'.$event_id.'</td>';
		    print '<td>'.$events[$event_id]["title"].'</td>';
		    print '<td>'.$listings[$UID].'</td>';
		    print '<td>'.$events[$event_id]["start_date"].'</td>';
		    print '<td><button data-id="'.$event_id.'" type="button" class="btn btn-primary btn-sm edit">Edit</button> <button data-id="'.$event_id.'" type="button" data-toggle="modal" data-target="#confirm-delete" class="btn btn-default btn-sm">Delete</button></td>';
		    print '</tr>';
		}  
		?>
      </tbody>
    </table>
</div>
</div>
<script type="text/javascript">
    $(function(){
    	$('.edit').click(function(e){ //on add input button click
    		console.log('edit function');
        	e.preventDefault();
        	$('.loader-container').show();
        	$( ".main" ).empty();
        	$('.show-list').hide();
	    	$('.visible-edit').show();
        	$('.show-list li.active').removeClass('active');
    		$('#edit-event-li').addClass('active');
        	$( ".main" ).load( "include/event_form.php?id=" + $(this).data("id"), function( response, status, xhr ) {
			  	$('.loader-container').fadeOut('fast');
			});
    	});
    });
</script>