<?php
/*
----------------------------------------- EVENT_FORM.PHP ------------------------------------------
Welcome to the event form! This contains the example code for the kinds of inputs, form elements,
and added features that would be good to use on the Campus forms or Filemaker webwindow.

The things to focus on in this file are the input names, input types, and some of the Javascript
used for form functionality. Prominent examples will be highlighted in comments.

----------------------------------------- FILE STRUCTURE ------------------------------------------
This file contains PHP logic for a database connection to retrieve data for populating the events
form if an event ID is specified over GET. If not, the creation of a new event is assumed.
After the database operations, the HTML5 form with inputs is generated, using the data stored in
the $events array from the first step to populate if applicable.
The last bit of the file is the JavaScript required for this form to work. All necessary Javascript
can be found within the <script> tags at the end of file.

----------------------------------------- FULL STRUCTURE ------------------------------------------
Files marked with *asterisks* are important and worth taking a look at. Others are trivial or only
exist as support for this example and are not otherwise useful.

forms.php
| *process.php*                   interprets form data and performs DB insert/updates
| custom.css                      custom CSS styling for this example
| include/ 
| - event_dashboard.php           displays list of events in test DB with edit/delete functions
| - {{*include/event_form.php*}}  the heart of this whole thing - the sample events form
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
jQuery Plugins
 Chosen - allows for searchable select inputs - used on Instructor, Course, and Group fields
  JS - js/chosen.jquery.min.js (forms.php)
  CSS - css/chosen.min.css (forms.php)
 Trumbowyg - Lightweight WYSIWYG Editor for the content fields
  JS - js/trumbowyg.min.js (forms.php) 
  CSS -  - css/trumbowyg.min.css (forms.php) 
 Geocomplete - autofill city/province/country locations based on Google Maps API - js/jquery.geocomplete.min.js (forms.php)
 Bootstrap Alerts - allows for jQuery control of alert dialogs (used to show if event has savedsuccessfully) - js/jquery.bsAlerts.min.js (forms.php)

Google Maps API - informs location autocomplete - http://maps.googleapis.com/maps/api/js?sensor=false&amp;libraries=places (forms.php)

*/

## Check for an event ID to see if a new event is being created or an existing one updated
# for a new event, $event_id is set to 0 (passing 0 as the event ID is the same as passing nothing)
# however, this behaviour is likely to change when real forms are implemented with Filemaker
$event_id = isset($_GET['id']) ? $_GET['id'] : 0;

## initialize the $event array and $values array.
$event = array(); # holds existing event information from database, used to create $values
$values = array(); # holds formatted values for use in the form

## If we are updating, retrieve the event information.
$update = False; # assumed to be false
if ($event_id) {
  $update = True; # set true if $event_id is not zero/null
  $database = new mysqli("localhost", "test_user", "test_password", "testing_grounds");
  if (mysqli_connect_errno()) {
      printf("Connect failed: %s\n", mysqli_connect_error());
      exit();
  }
  // Query for retrieving the event
  $results = $database->query("SELECT * FROM web_events WHERE event_id=$event_id");
  $event = $results->fetch_assoc();
  // Frees the memory associated with a result (but PHP is pretty good at GC on its own)
  $results->free();
  // Close connection 
  $database->close();
}

## Let's build the values array!
# $values array holds values that are either used within form logic or  directly outputted as HTML.

/* 
FUNCTION: get_value
This function is basically a translator from $events to $values.
Some fields, like most <input> types, hold their value within the <input> tag as value="something".
Others, like <textarea>, put their values between their opening and closing tags. Therefore, we
include a $format_value option to wrap the value appropriately - if True, it puts in the format
<input>, otherwise it simply copies the value directly for echoing between tags.
*/
function get_value($key, $event, $format_value=False) {
  $value = False; # default value - note that False prints as the empty string ''
  # technically 'if ($update)'' would work here too, this is a bit more robust in case of unexpected SQL behaviour
  if (array_key_exists($key, $event)) {
    if ($event[$key] != NULL) {
      $value = $format_value ? 'value="'.$event[$key].'"' : $event[$key];
    }
  }
  return $value;
}

# now we start building
$values['title'] = get_value('title', $event, True);
$values['extended_title'] = get_value('extended_title', $event, True);

$values['course_id'] = get_value('course_id', $event);
$values['listing_id'] = get_value('listing_id', $event);
$values['group_id'] = get_value('group_id', $event);

$values['start_date'] = get_value('start_date', $event, True);
$values['end_date'] = get_value('end_date', $event, True);

$values['meeting_day'] = get_value('meeting_day', $event);
$values['start_time'] = get_value('start_time', $event, True);
$values['end_time'] = get_value('end_time', $event, True);
$values['time_zone'] = get_value('time_zone', $event, True);
$values['runs_for'] = get_value('runs_for', $event, True);
$values['runs_for_unit'] = get_value('runs_for_unit', $event, True);

# BOOLEAN VALUES
/* 
These behave a little differently - if true, then we need a 'checked' value for the corresponding 
checkbox input.
Note that there is also default behaviour for the checkboxes is $update = False in which they will 
ignore the value declared here - these happens within the form, find them below.
*/
$values['is_course'] = get_value('is_course', $event) ? 'checked' : '';
$values['is_video_course'] = get_value('is_video_course', $event) ? 'checked' : '';
$values['is_posted'] = get_value('is_posted', $event) ? 'checked' : '';
$values['is_gordon'] = get_value('is_gordon', $event) ? 'checked' : '';
$values['is_intensive'] = get_value('is_intensive', $event) ? 'checked' : '';
$values['is_highlight'] = get_value('is_highlight', $event) ? 'checked' : '';
$values['is_online'] = get_value('is_online', $event) ? 'checked' : '';
$values['english_or_french'] = get_value('english_or_french', $event) ? 'checked' : '';

$values['city'] = get_value('city', $event, True);
$values['province'] = get_value('province', $event, True);
$values['country'] = get_value('country', $event, True);
$values['place'] = get_value('place', $event, True);

$values['details'] = get_value('details', $event);
$values['description'] = get_value('description', $event);
$values['suitability'] = get_value('suitability', $event);
$values['time_place'] = get_value('time_place', $event);
$values['fees_registration'] = get_value('fees_registration', $event);
$values['host_sponsor'] = get_value('host_sponsor', $event);
$values['accreditation'] = get_value('accreditation', $event);
$values['cancellation'] = get_value('cancellation', $event);
$values['further_information'] = get_value('further_information', $event);

$values['website_url'] = get_value('website_url', $event, True);
$values['flyer_url'] = get_value('flyer_url', $event, True);
$values['registration_form'] = get_value('registration_form', $event);
$values['registration_url'] = get_value('registration_url', $event, True);

# SERIALIZED/ENCODED ARRAYS
/* 
Another special case - for this, get_value is basically just confirming that we have data waiting
in the array to use - if $events[key] is not NULL or '', it will evaluate to True here. We then
must FIRST decode the field, THEN unserialize it into a PHP array.
Reasons for using base64 encoding are elaborated on in process.php, but essentially its more
sanitary for SQL to deal with - especially as we automate the assembly of the query.
*/
$values['custom_fields'] =  get_value('custom_fields', $event) ? unserialize(base64_decode($event['custom_fields'])) : False;
$values['links'] =  get_value('links', $event) ? unserialize(base64_decode($event['links'])) : False;
$values['contacts'] =  get_value('contacts', $event) ? unserialize(base64_decode($event['contacts'])) : False;

## Now we need to get some extra accompanying information.
# Let's connect to the real deal.
$dbh = new mysqli("162.213.157.205", "roderick", "w1nt3rp3g", "roderick_main");
if (mysqli_connect_errno()) {
  printf("Connect failed: %s\n", mysqli_connect_error());
  exit();
}
$dbh->set_charset("utf8");

# Our first query is getting the $listings array built for the Instructor select field.
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

# Next query - get the courses for the $courses array and Courses select field.
$results = $dbh->query("SELECT * FROM institute_event_courses WHERE post_to_website='Yes' ORDER BY name") or die('Error: '.$db->error." ".__LINE__);
$courses = array();
while ($data = $results->fetch_array()){
  $courses[$data['id']] = $data['name'];
}
// Frees the memory associated with a result
$results->free();

# Finally, let's query the groups to build $groups and the Group select field.
$results = $dbh->query("SELECT * FROM DistEdGroups WHERE Approved=1 ORDER BY DistEdGroupID DESC") or die('Error: '.$db->error." ".__LINE__);
$groups = array();
while ($data = $results->fetch_array()){
  $groups[$data['DistEdGroupID']] = $data['GroupName'];
}
// Frees the memory associated with a result
$results->free();
// close connection, we're all done with SQL for this file
$dbh->close();

/*
-------------------------------------------- THE FORM ---------------------------------------------
Now we start to construct the form. I used Bootstrap for easy pre-fab design and structuring, but
its by no means an essential part of the form. The important stuff is mostly within the <input>,
<select>/<option>, and <textarea> tags.
*/

?>
<form id="event-form" class="form-bridge" action="process.php" method="POST">
  <? # hidden event_id identifier ?>
  <input type="hidden" name="event_id" value="<?=$event_id?>">
  <a class="event-anchor" id="basic-details" /><? # these anchors are just for navigation of the example site and have no other purpose ?>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="form-group">
<?php
/*
---------- TYPE OPTIONS ----------
These fields generally apply to the type or "Category" of the event on the website.
The online and course field also control other parts of the form. 
Elements of the class 'course-option' are only visible "is_course" is checked - eg. is_video_course
Elements of the class 'online-option' are only visible "is_online" is checked
Additionally, elements of the class 'location-option' are NOT visible when is_online is checked

By default on a new form, is_online and is_course are checked - this is done by the ternary
operator checking to see if $update is true or false.
*/
?>
        <h5 class="form-h5">Type Options:</h5>
        <div class="row">
          <div class="checkbox col-xs-2">
            <label>
              <input type="checkbox" id="is_online" name="is_online" <?php echo ($update) ? $values['is_online'] : "checked"; ?>>
              <span>Online</span>
            </label>
          </div>
          <div class="checkbox col-xs-2">
            <label>
              <input type="checkbox" id="is_course" name="is_course" <?php echo ($update) ? $values['is_course'] : "checked"; ?>>
              <span>Course</span>
            </label>
          </div>
          <div class="checkbox col-xs-2 course-option">
            <label>
              <input name="is_video_course" type="checkbox" <?php echo ($update) ? $values['is_video_course'] : ""; ?>>
              <span>Video Course</span>
            </label>
          </div>
          <div class="checkbox col-xs-2 course-option">
            <label>
              <input name="is_intensive" type="checkbox" <?php echo ($update) ? $values['is_intensive'] : ""; ?>>
              <span>Intensive</span>
            </label>
          </div>
        </div>
      </div>
<?php
/*
---------- INSTRUCTOR/COURSE/GROUP SELECTS ----------
These fields allow for the selection of instructor, group, and course. The actual value is the
associated ID.

The jQuery plugin Chosen is being used here as it allows for searching the select lists, which is
helpful for the particularly long ones (Instructor and Group). These are given the class
'chosen-select' which is the identifier the plugin is asked to use in the JavaScript. They are also
given a data-placeholder value for the plugin to use. Other than that, there's too much to worry
about - it does its job well.
*/
?>
      <div class="form-group">
        <label for="listing_id">Instructor:</label>
        <select name='listing_id' data-placeholder="Select Instructor" class="form-control chosen-select" id="listing_id">
          <option value=""></option>
          <?php 
          foreach ($listings as $listing_id => $listing){
            if ($listing_id == $values['listing_id']) {
              echo "<option selected value=".$listing_id.">".$listing."</option>
              ";
            }
            else {
              echo "<option value=".$listing_id.">".$listing."</option>
              ";
            }
          }
          ?>
        </select>
      </div>

      <div class="form-group course-option">
        <label for="course_id">Course:</label>
        <select name='course_id' data-placeholder="Select Course" class="form-control chosen-select" id="course_id">
          <option value=""></option>
          <?php 
          foreach ($courses as $course_id => $course){
            if ($course_id == $values['course_id']) {
              echo "<option selected value=".$course_id.">".$course."</option>
              ";
            }
            else {
              echo "<option value=".$course_id.">".$course."</option>
              ";
            }
          }
          ?>
        </select>
      </div>
      <div class="form-group">
        <label for="group_id">Group:</label>
        <select name='group_id' data-placeholder="Select Group" class="form-control chosen-select" id="group_id">
          <option value=""></option>
          <?php 
          foreach ($groups as $group_id => $group){
            if ($group_id == $values['group_id']) {
              echo "<option selected value=".$group_id.">".$group_id." - ".$group."</option>
              ";
            }
            else {
              echo "<option value=".$group_id.">".$group_id." - ".$group."</option>
              ";
            }
          }
          ?>
        </select>
      </div>
<?php
/*
---------- TITLES ----------
Pretty self explanatory. On this sample form I have made title the only required element, but we
will likely want to change this (and possibly add a validator script to the whole thing).

Note if you are unfamiliar: ?=XXX? is shorthand for ?php echo XXX; ?
*/
?>
      <div class="form-group">
        <label for="title">Title</label>
        <input name="title" type="text" class="form-control" id="title" placeholder="eg. Heart Matters - The Science of Emotion" required autofocus <?=$values['title']?>>
        <span class="help-block">For internal lookup, sidebars, search functions, etc.</span>
      </div>
      <div class="form-group">
        <label for="extended_title">Extended Title</label>
        <input name="extended_title" type="text" class="form-control" id="extended_title" placeholder="eg. A five day course on Heart Matters - The Science of Emotion" <?=$values['extended_title']?>>
        <span class="help-block">For display on main event listings and wherever there's sufficient room.</span>
      </div>
<?php
/*
---------- POSTING OPTIONS ----------
These fields generally apply to the display logic for views on the website. Pretty much the same
deal as the Type Options, but these don't control any form elements.

By default on a new form, english_or_french is checked.
*/
?>
      <div class="form-group">
        <h5 class="form-h5">Posting Options:<h5>
        <div class="row ">
          <div class="checkbox col-xs-2">
            <label>
              <input name="is_posted" type="checkbox" <?php echo ($update) ? $values['is_posted'] : ""; ?>>
              <span>Posted to Website</span>
            </label>
          </div>
          <div class="checkbox col-xs-2">
            <label>
              <input name="is_highlight" type="checkbox" <?php echo ($update) ? $values['is_highlight'] : ""; ?>>
              <span>Highlighted</span>
            </label>
          </div>
          <div class="checkbox col-xs-2">
            <label>
              <input name="english_or_french" type="checkbox" <?php echo ($update) ? $values['english_or_french'] : "checked"; ?>>
              <span>English/French</span>
            </label>
          </div>
          <div class="checkbox col-xs-2">
            <label>
              <input name="is_gordon" type="checkbox" <?php echo ($update) ? $values['is_gordon'] : ""; ?>>
              <span>Gordon Event</span>
            </label>
          </div>
        </div>
      </div>
      
      
    </div>
  </div>
  <a class="event-anchor" id="date-time" /><?php # these anchors are just for navigation of the example site and have no other purpose ?>
  <div class="panel panel-default">
    <div class="panel-heading">Date and Time</div>
    <div class="panel-body">
      <div class="form-group">
<?php
/*
---------- START/END DATE ----------
These are given the HTML field type "date" which automatically supplies them with date pickers and
constricts the valid input to only dates of the form YYYY-MM-DD, conveniently also how MySQL likes
to store dates. Very little has to be done here because of that.

It may be worth considering using a different date picker and/or automatically setting end_date to
match start_date on first selection.
*/
?>

        <label for="start_date">Start Date</label>
        <input type="date" class="form-control" id="start_date" name="start_date" <?=$values['start_date']?>>
        <span class="help-block"></span>
      </div>
      <div class="form-group">
        <label for="end_date">End Date</label>
        <input type="date" class="form-control" id="end_date" name="end_date" <?=$values['end_date']?>>
        <span class="help-block"></span>
      </div>
<?php
/*
---------- MEETING TIMES ----------
These fields are specific to online courses, meant to build a string for website display like:
"COURSENAME meets on MEETING_DAYs from START_TIME to END_TIME TIME_ZONE and runs for RUNS_FOR RUNS_FOR_UNIT"

They are only visible if both is_course and is_online are checked - this is specified via the
course-option and online-option classes.
*/
?>
      <div class="form-group course-option online-option">
        <label for="meeting_day">Meeting Day</label>
        <select name="meeting_day" id="meeting_day">
          <option value=""></option>
          <option <?php echo ($values['meeting_day'] == 'Monday') ? 'selected' : ''; ?> value="Monday">Monday</option>
          <option <?php echo ($values['meeting_day'] == 'Tuesday') ? 'selected' : ''; ?> value="Tuesday">Tuesday</option>
          <option <?php echo ($values['meeting_day'] == 'Wednesday') ? 'selected' : ''; ?> value="Wednesday">Wednesday</option>
          <option <?php echo ($values['meeting_day'] == 'Thursday') ? 'selected' : ''; ?> value="Thursday">Thursday</option>
          <option <?php echo ($values['meeting_day'] == 'Friday') ? 'selected' : ''; ?> value="Friday">Friday</option>
          <option <?php echo ($values['meeting_day'] == 'Saturday') ? 'selected' : ''; ?> value="Saturday">Saturday</option>
          <option <?php echo ($values['meeting_day'] == 'Sunday') ? 'selected' : ''; ?> value="Sunday">Sunday</option>
        </select>
        <span class="help-block"></span>
      </div>
<?php
/*
---------- START/END TIME ----------
The input type for Start Time and End Time is 'time', which again gets along nicely with the MySQL
field type. 
*/
?>
      <div class="form-group course-option online-option">
        <label for="meeting_time">Start Time</label>
        <input type="time" class="form-control" id="start_time" name="start_time" <?=$values['start_time']?>>
        <span class="help-block"></span>
      </div>
      <div class="form-group course-option online-option">
        <label for="meeting_time">End Time</label>
        <input type="time" class="form-control" id="end_time" name="end_time" <?=$values['end_time']?>>
        <span class="help-block"></span>
      </div>
<?php
/*
---------- TIME ZONE ----------
Time Zone selects are crazy, so this has just been left as a text input.
*/
?>
      <div class="form-group course-option online-option">
        <label for="runs_for">Time Zone</label>
        <input type="text" class="form-control" id="time_zone" name="time_zone" maxlength="4" placeholder="eg. PST, PDT, MST, CST, EST, CET, CEST, etc." <?=$values['time_zone']?>>
        <span class="help-block">Use 3-4 character time zone code.</span>
      </div>
<?php
/*
---------- RUNS FOR ----------
Runs for has been left as a text input, but could potentially be restricted to numbers.
*/
?>
      <div class="form-group course-option online-option">
        <label for="runs_for">Runs for:</label>
        <input type="text" class="form-control" id="runs_for" name="runs_for" placeholder="eg. 5" <?=$values['runs_for']?>>
        <span class="help-block"></span>
      </div>
<?php
/*
---------- RUNS FOR UNIT ----------
Runs for unit has been left as a text input, but could potentially be a select.
*/
?>
      <div class="form-group course-option online-option">
        <label for="runs_for_unit">Runs for unit:</label>
        <input type="text" class="form-control" id="runs_for_unit" name="runs_for_unit" placeholder="eg. weeks, days, sessions" <?=$values['runs_for_unit']?>>
        <span class="help-block"></span>
      </div>
    </div>
  </div>

<?php
/*
---------- LOCATION ----------
These are the location-specific fields. If is_online is checked, none of these show up and a
placeholder is used instead.
*/
?>
  <a class="event-anchor" id="location" /><? # these anchors are just for navigation of the example site and have no other purpose ?>
  <div class="panel panel-default">
    <div class="panel-heading">Location</div>
    <div class="panel-body">
      <div class="online-option"><h5 class="form-h5">Online</h5></div>
<?php
/*
Otherwise, the location lookup field uses a jQuery plugin to hook into the Google Maps API to
create a searchable list of all the cities in the world. On selection, it auto-populates the
other fields. It uses the data-geo tags for the populating.
The hope is to create more uniform spelling/formatting across the board with this, but it is 
ultimately an experiment and not absolutely necessary.
*/
?>
      <div class="form-group location-input">
        <label for="location-lookup">City Lookup:</label>
        <input type="text" class="form-control" id="location-lookup" placeholder="Enter city name and select from list">
        <span class="help-block"></span>
      </div>
      <div class="form-group location-input location-details">
          <label for="city">City:</label>
          <input type="text" class="form-control" id="city" name="city" data-geo="locality" <?=$values['city']?>>
          <span class="help-block"></span>
      </div>
      <div class="form-group location-input location-details">
          <label for="province">Province/State:</label>
          <input type="text" class="form-control" id="province" name="province" data-geo="administrative_area_level_1" <?=$values['province']?>>
          <span class="help-block"></span>
      </div>
      <div class="form-group location-input location-details">
          <label for="country">Country:</label>
          <input type="text" class="form-control" id="country" name="country" data-geo="country" <?=$values['country']?>>
          <span class="help-block"></span>
      </div>
<?php
/*
---------- PLACE ----------
There is also an optional "place" input for venue names.
*/
?>
      <div class="form-group location-input">
          <label for="place">Place (optional):</label>
          <input type="text" class="form-control" id="place" name="place" placeholder="eg. Vancouver Museum" <?=$values['place']?>>
          <span class="help-block"></span>
      </div>
    </div>
  </div>

<?php
/*
---------- CONTENT FIELDS  ----------
These are textarea fields for the main content. The jQuery WYSIWYG plugin Trumbowyg is used to
create the editors. Nothing too complicated here, until you get the custom section...
*/
?>
  <a class="event-anchor" id="content" /><? # these anchors are just for navigation of the example site and have no other purpose ?>
  <div class="panel panel-default">
    <div class="panel-heading">Content</div>
    <div class="panel-body">
      <div class="form-group">
          <label for="details">Details:</label>
          <textarea class="form-control wysiwyg-editor" name="details"><?=$values['details']?></textarea>
          <span class="help-block"></span>
      </div>
      <div class="form-group">
          <label for="description">Description:</label>
          <textarea class="form-control wysiwyg-editor" name="description"><?=$values['description']?></textarea>
          <span class="help-block"></span>
      </div>
      <div class="form-group">
          <label for="suitability">Suitability:</label>
          <textarea class="form-control wysiwyg-editor" name="suitability"><?=$values['suitability']?></textarea>
          <span class="help-block"></span>
      </div>
      <div class="form-group">
          <label for="time_place">Time and Place:</label>
          <textarea class="form-control wysiwyg-editor" name="time_place"><?=$values['time_place']?></textarea>
          <span class="help-block"></span>
      </div>
      <div class="form-group">
          <label for="fees_registration">Fees and Registration:</label>
          <textarea class="form-control wysiwyg-editor" name="fees_registration"><?=$values['fees_registration']?></textarea>
          <span class="help-block"></span>
      </div>
      <div class="form-group">
          <label for="host_sponsor">Host/Sponsor:</label>
          <textarea class="form-control wysiwyg-editor" name="host_sponsor"><?=$values['host_sponsor']?></textarea>
          <span class="help-block"></span>
      </div>
      <div class="form-group">
          <label for="further_information">Accreditation:</label>
          <textarea class="form-control wysiwyg-editor" name="accreditation"><?=$values['accreditation']?></textarea>
          <span class="help-block"></span>
      </div>
      <div class="form-group">
          <label for="further_information">Cancellation:</label>
          <textarea class="form-control wysiwyg-editor" name="cancellation"><?=$values['cancellation']?></textarea>
          <span class="help-block"></span>
      </div>
      <div class="form-group">
          <label for="further_information">Further Information:</label>
          <textarea class="form-control wysiwyg-editor" name="further_information"><?=$values['further_information']?></textarea>
          <span class="help-block"></span>
      </div>
<?php
/*
As many additional WYSIWYG fields as desired can be added dynamically in the custom-field-wrapper.
If fields already exist, the PHP code below adds another pair of inputs for each one.
The first input is the field's title and is a simple text input, the other is the same editor as
the rest of the fields have. The counter variable $x is used to keep the form IDs seperate, but
this is entirely for the sake of JavaScript interactions - it has no bearing on how information
is stored upon submission. The only important features there are the names, custom_field_titles[]
and custom_fields[] - these create arrays out of the data and do not care how many times they
are used.

As a nice added feature, the label for the editor mirrors the title field, both upon inialization
and live with JavaScript.

New fields are added with JavaScript functions that will be explained more below.  
*/
?>
      <div id="custom-field-wrapper">
        <?php 
        $x = 1;
        if ($values['custom_fields']) {
          foreach ($values['custom_fields'] as $custom_field_title => $custom_field) {
            echo '<div class="form-group">
                    <label for="custom-field-title-'.$x.'">Custom Field Title:</label>
                    <input id="custom-field-title-'.$x.'" type="text" class="form-control" name="custom_field_titles[]" value="'.$custom_field_title.'"><br />
                    <label id="custom-field-label-'.$x.'" for="custom-field-'.$x.'">'.$custom_field_title.':</label>
                    <textarea id="custom-field-'.$x.'" class="form-control wysiwyg-editor" name="custom_fields[]">'.$custom_field.'</textarea><br />
                    <a href="#" class="remove_field">Remove</a>
                  </div>';
            $x++;
          }
        }
        ?>
      </div>
      <button id="add-custom-field" type="button" class="btn btn-default">Add Custom Details</button>
    </div>
  </div>
<?php
/*
---------- LINKS/CONTACT ----------
For the Website and Flyer fields, the input type URL is being used. This is not a very robust
validator and essentially just looks for "http://" at the beginning (the part nobody likes to type),
so we may want to consider changing them to text inputs with different validation.
*/
?>
  <a class="event-anchor" id="links-contact" /><? # these anchors are just for navigation of the example site and have no other purpose ?>
  <div class="panel panel-default">
    <div class="panel-heading">Links and Contacts</div>
    <div class="panel-body">
      <div class="form-group">
        <label for="website_url">Website URL:</label>
        <input type="url" class="form-control" id="website_url" name="website_url" <?=$values['website_url']?>>
        <span class="help-block"></span>
      </div>
      <div class="form-group">
        <label for="flyer_url">Flyer URL:</label>
        <input type="url" class="form-control" id="flyer_url" name="flyer_url" <?=$values['flyer_url']?>>
        <span class="help-block"></span>
      </div>
<?php
/*
The registration URL can generated from the Event ID, generated from the Group ID, or manually
entered. The registration_form select input controls this - the registration_url field is
readonly unless 'URL' is selected for registration_form, and it automatically filled otherwise.
Most of the magic here happens in JavaScript - it will even update if Group ID is changed after it
has already been selected here.

If this is a new event and Event ID has been selected then registration_url will be ignored, as an
event ID has not yet been generated - this will happen automatically in process.php.
*/
?>
      <div class="form-group">
        <label for="registration_form">Registration Form: </label>
        <select name="registration_form" id="registration_form">
          <option value=""></option>
          <option <?php echo ($values['registration_form'] == 'event_id') ? 'selected' : ''; ?> value="event_id">Event ID</option>
          <option <?php echo ($values['registration_form'] == 'group_id') ? 'selected' : ''; ?> value="group_id">Distance Ed Group ID</option>
          <option <?php echo ($values['registration_form'] == 'url') ? 'selected' : ''; ?> value="url">URL</option>
        </select>
        <span class="help-block"></span>
      </div>
      <div class="form-group">
        <label for="registration_url">Registration URL:</label>
        <input type="text" class="form-control" id="registration_url" name="registration_url" readonly <?=$values['registration_url']?>>
        <span class="help-block"></span>
      </div>
<?php
/*
---------- CUSTOM LINKS ----------
This is similar to how custom fields work. A pair of inputs is generated for each link, a name and
a URL. These are stored with the link_titles[] and links[] names and the array is constructed in
process.php with the structure of links[link_title] => link. The same type of array is returned to
build any pre-existing links with the PHP below.
*/
?>
      <div class="well well-sm">
        <h5 class="form-h5">Other Links</h5>
        <div id="custom-links-wrapper">
        <?php 
        $y = 1;
        if ($values['links']) {
          foreach ($values['links'] as $link_title => $link) {
            echo '<div class="row">
                    <div class="form-group col-xs-4">
                      <div class="row">
                        <label class="col-xs-4" for="custom-link-title-'.$y.'">Display Text</label>
                        <input id="custom-link-title-'.$y.'" type="text" class="form-control-nowidth col-xs-8" name="link_titles[]" placeholder="eg. Sponsor Website" value="'.$link_title.'">
                      </div>
                    </div>
                    <div class="form-group col-xs-6">
                      <div class="row">
                        <label class="col-xs-2" id="custom-link-label-'.$y.'" for="custom-link-'.$y.'">URL</label>
                        <input id="custom-link-'.$y.'" type="text" class="form-control-nowidth col-xs-10" name="links[]" placeholder="eg. http://www.sponsorwebsite.com/" value="'.$link.'">
                      </div>
                    </div>
                    <div class="col-xs-2">
                      <a href="#" class="remove_field">Remove</a>
                    </div>
                  </div>';
            $y++;
          }
        }
        ?>
        </div>
        <button id="add-custom-link" type="button" class="btn btn-default">Add Link</button>
      </div>
<?php
/*
---------- CUSTOM CONTACTS ----------
Also similar to how custom fields work, but this actuall collects three inputs per contact -
a name, whether or not its an email address or a URL, and the address field. These are turned into 
a nested array of the structure: contacts[title] = array(type, address). The same type of array is 
returned to build any pre-existing contacts with the PHP below.

The custom-contact-type field is actually hidden and controlled by a dropdown menu. This was done
to cooperate with some Bootstrap oddities but is not an important feature of the form - a normal
select input would work just well.
*/
?>
      <div class="well well-sm">
        <h5 class="form-h5">Other Contacts</h5>
        <div id="custom-contacts-wrapper">
        <?php 
        $z = 1;
        if ($values['contacts']) {
          foreach ($values['contacts'] as $contact_title => $contact_array) {
            echo '<div class="row">
                    <div class="form-group col-xs-4">
                      <div class="row">
                        <label class="col-xs-4" for="custom-contact-title-'.$z.'">Display Text</label>
                        <input id="custom-contact-title-'.$z.'" type="text" class="form-control-nowidth col-xs-8" name="contact_titles[]" placeholder="eg. Event Organizer" value="'.$contact_title.'">
                      </div>
                    </div>
                    <div class="form-group col-xs-6">
                      <div class="input-group">
                        <div class="input-group-btn">
                          <button id="custom-contact-type-button-'.$z.'" type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.$contact_array[0].' <span class="caret"></span></button>
                          <ul class="dropdown-menu">
                            <li><a class="contact-type-menu-'.$z.'" href="#">Email</a></li>
                            <li><a class="contact-type-menu-'.$z.'" href="#">URL</a></li>
                          </ul>
                        <input type="hidden" name="contact_types[]" id="custom-contact-type-'.$z.'" value="'.$contact_array[0].'">
                        </div><!-- /btn-group -->
                        <input name="contacts[]" type="text" class="form-control" value="'.$contact_array[1].'">
                      </div><!-- /input-group -->
                    </div>
                    <div class="col-xs-2">
                      <a href="#" class="remove_field">Remove</a>
                    </div>
                  </div>';
            $z++;
          }
        }
        ?>
        </div>
        <button id="add-custom-contact" type="button" class="btn btn-default">Add Contact</button>
      </div>
    </div>
  </div>
  <div id="save-alert" data-alerts="alerts"></div> <?php # This is a alert dialog for indicating save success/failure ?>
<?php
/*
---------- SUBMIT ----------
Note that the submit button behaviour is overridden by a JavaScript function below (there are no
obvious signs that this is the case from the form itself, so its worth mentioning). It makes an
AJAX call instead of leaving the page and displays success/failure with the button text and
alert dialog above. 

The extra data- on the submit button is for Bootstrap's benefit and are called in JavaScript submit function
*/
?>
  <button id="submit-form" data-error-text="Not saved, all is lost!" data-loading-text="Saving..." class="btn btn-lg btn-primary btn-block" type="submit">Save</button>
</form>
<?php
/*
------------------------------------------- JAVASCRIPT --------------------------------------------
Here's where a lot of the magic is happening. Most of this is running on jQuery - I'll give a brief
overview of what everything does.

Note: $(function(){}) is shorthand for $(document).ready(function(){})
*/
?>
<script type="text/javascript">
  $(function(){
    var event_id = <?=$event_id?>;

    // sets the visibility of course options depending on checkbox input state
    $.fn.togglecourseoptions = function () {
      if ($('#is_course').is(":checked")) {
        $('.course-option').show();
        if ($('#is_online').is(":checked")) {
          $('.online-option').show();
          $('.location-input').hide();
        }
        else {
          $('.online-option').hide();
          $('.location-input').show();
        }
      }
      else {
        $('.course-option').hide();
      }
    };
    // sets the visibility of online options depending on checkbox input state
    $.fn.toggleonlineoptions = function () {
      if ($('#is_online').is(":checked")) {
        $('.location-input').hide();
        if ($('#is_course').is(":checked")) {
          $('.online-option').show();
        }
        else {
          $('.online-option').show();
          $('.course-option').hide();
        }
      }
      else {
        $('.location-input').show();
        $('.online-option').hide();
      }
    };

    // for both of the above, run once on page load and with every change of the relevant checkbox
    $.fn.togglecourseoptions();
    $('#is_course').change(function(){$.fn.togglecourseoptions()});
    $.fn.toggleonlineoptions();
    $('#is_online').change(function(){$.fn.toggleonlineoptions()});

    // handles the registration_form and registration_url interactions
    $.fn.registrationoptions = function () {
      if ( $("#registration_form").val() == "event_id" ) {
        if (event_id == 0) { // if its new, we have no event ID. This value will be ignored.
          $("#registration_url").val('<no event ID exists yet - URL will be created on event save>');
        }
        else { // otherwise, the URL is generated
          $("#registration_url").val('http://neufeldinstitute.com/main/cms/event_form.php?type=' + event_id);
        }
        $("#registration_url").prop('readonly', true);
      }
      else if ( $("#registration_form").val() == "group_id" ) { 
        var groupID = $("#group_id").chosen().val();
        $("#registration_url").val('http://campus.neufeldinstitute.com/custom_scripts/forms/disted-reg-form.php?formid=' + groupID);
        $("#registration_url").prop('readonly', true);
      }
      else if ( $("#registration_form").val() == "url" ) { 
        $("#registration_url").prop('readonly', false);
      }
      else { // for no selection
        $("#registration_url").prop('readonly', true);
      }
    };
    
    // run the above function once on page load, then every time registration_form changes
    $.fn.registrationoptions();
    $("#registration_form").change(function(){$.fn.registrationoptions()});

    // initialize the alert plugin for the save dialog
    $( "#save-alert" ).bsAlerts();

    // initalize the Chosen plugin on all elements of class .chosen-select
    $(".chosen-select").chosen({
      no_results_text: "Oops, nothing found!"
    }); 
    // watch the group_id Chosen select box for change in case the registration URL is linked to it
    $("#group_id").chosen().change(function(){ 
      if ( $("#registration_form").val() == "group_id" ) {
        var groupID = $(this).chosen().val();
        $("#registration_url").val('http://campus.neufeldinstitute.com/custom_scripts/forms/disted-reg-form.php?formid=' + groupID);
      }
    });

    // initalize the location lookup plugin
    $("#location-lookup").geocomplete({
      details: ".location-details",
      detailsAttribute: "data-geo",
      types: ['(regions)']
    });

    // initialize the WYSIWYG editors
    $('.wysiwyg-editor').trumbowyg();

    // CUSTOM FIELD FUNCTIONS
    var wrapper         = $("#custom-field-wrapper"); // Fields wrapper
    var add_button      = $("#add-custom-field"); // Add button ID
    var x = <?php echo json_encode($x); ?>; // counter so that delete buttons don't get confused.
    // This is initalized with the current PHP value, as some fields may have already been added by PHP.
    // json_encode is used above as a recommended safe way to convert to string, but is probably overkill
    $(add_button).click(function(e){ //on add input button click
        e.preventDefault(); // prevent default button click behavior
        $(wrapper).append('<div class="form-group">\
          <label for="custom-field-title-' + x + '">Custom Field Title:</label>\
          <input id="custom-field-title-' + x + '" type="text" class="form-control" name="custom_field_titles[]" placeholder="eg. Accreditation, Lodging, Cancellation, Prerequisites"><br />\
          <label id="custom-field-label-' + x + '" for="custom-field-' + x + '">Custom Field:</label>\
          <textarea id="custom-field-' + x + '" class="form-control wysiwyg-editor" name="custom_fields[]"></textarea><br />\
          <a href="#" class="remove_field">Remove</a>\
          </div>'); // add a new group of inputs
        $('.wysiwyg-editor').trumbowyg(); // initialize the WYSIWYG editor
        var title = '#custom-field-title-' + x;
        var label = '#custom-field-label-' + x;
        $(title).keyup(function() {
            $(label).text( this.value + ':'); // mirrors the label of the textarea with the title input
        });
        x++;
    });
    $(wrapper).on("click",".remove_field", function(e){ //user click on remove text
        e.preventDefault(); $(this).parent('div').remove(); // remove input group
    });

    // CUSTOM LINK FUNCTIONS
    var link_wrapper         = $("#custom-links-wrapper"); //Fields wrapper
    var link_add_button      = $("#add-custom-link"); //Add button ID
    var y = <?php echo json_encode($y); ?>; // counter so that delete buttons don't get confused
    // This is initalized with the current PHP value, as some fields may have already been added by PHP.
    // json_encode is used above as a recommended safe way to convert to string, but is probably overkill
    $(link_add_button).click(function(e){ //on add input button click
        e.preventDefault(); // prevent default button click behavior
        $(link_wrapper).append('<div class="row">\
            <div class="form-group col-xs-4">\
              <div class="row">\
                <label class="col-xs-4" for="custom-link-title-' + y + '">Display Text</label>\
                <input id="custom-link-title-' + y + '" type="text" class="form-control-nowidth col-xs-8" name="link_titles[]" placeholder="eg. Sponsor Website">\
              </div>\
            </div>\
            <div class="form-group col-xs-6">\
              <div class="row">\
                <label class="col-xs-2" id="custom-link-label-' + y + '" for="custom-link-' + y + '">URL</label>\
                <input id="custom-link-' + y + '" type="text" class="form-control-nowidth col-xs-10" name="links[]" placeholder="eg. http://www.sponsorwebsite.com/">\
              </div>\
            </div>\
            <div class="col-xs-2">\
              <a href="#" class="remove_field">Remove</a>\
            </div>\
          </div>'); // add a new group of inputs
        y++;
    });
    $(link_wrapper).on("click",".remove_field", function(e){ //user click on remove text
        e.preventDefault(); $(this).parent('div').parent('div').remove(); // remove input group
    });

    // CUSTOM LINK FUNCTIONS
    var contact_wrapper         = $("#custom-contacts-wrapper"); //Fields wrapper
    var contact_add_button      = $("#add-custom-contact"); //Add button ID
    var z = <?php echo json_encode($z); ?>; // counter so that delete buttons don't get confused
    // This is initalized with the current PHP value, as some fields may have already been added by PHP.
    // json_encode is used above as a recommended safe way to convert to string, but is probably overkill
    if (z > 1) {
      for (var i = 1; i < z; i++) {
        var selector = '.contact-type-menu-' + i;
        var hidden_input = '#custom-contact-type-' + i;
        var button = '#custom-contact-type-button-' + i;
        $(selector).click(function(e){
          var z = $(this).data('z');
          e.preventDefault();
          var selected = $(this).text();
          $(hidden_input).val(selected);
          $(button).html(selected + ' <span class="caret"></span>');
        });
      }
    }
    $(contact_add_button).click(function(e){ //on add input button click
        e.preventDefault(); // prevent default button click behavior
        $(contact_wrapper).append('<div class="row">\
            <div class="form-group col-xs-4">\
              <div class="row">\
                <label class="col-xs-4" for="custom-contact-title-' + z + '">Display Text</label>\
                <input id="custom-contact-title-' + z + '" type="text" class="form-control-nowidth col-xs-8" name="contact_titles[]" placeholder="eg. Event Organizer">\
              </div>\
            </div>\
            <div class="form-group col-xs-6">\
              <div class="input-group">\
                <div class="input-group-btn">\
                  <button id="custom-contact-type-button-' + z + '" type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Email <span class="caret"></span></button>\
                  <ul class="dropdown-menu">\
                    <li><a class="contact-type-menu-' + z + '" href="#">Email</a></li>\
                    <li><a class="contact-type-menu-' + z + '" href="#">URL</a></li>\
                  </ul>\
                <input type="hidden" name="contact_types[]" id="custom-contact-type-' + z + '" value="Email">\
                </div><!-- /btn-group -->\
                <input name="contacts[]" type="text" class="form-control">\
              </div><!-- /input-group -->\
            </div>\
            <div class="col-xs-2">\
              <a href="#" class="remove_field">Remove</a>\
            </div>\
          </div>'); // add a new group of inputs
          // this sets the hidden select input used for the contact type
          // this setup is more done to cooperate with Bootstrap styles and is not a necessary part of the form
          var selector = '.contact-type-menu-' + z;
          var hidden_input = '#custom-contact-type-' + z;
          var button = '#custom-contact-type-button-' + z;
          // when the dropdown menu is clicked, change the value of the hidden input accordingly
          $(selector).click(function(e){
            var z = $(this).data('z');
            e.preventDefault();
            var selected = $(this).text();
            $(hidden_input).val(selected);
            $(button).html(selected + ' <span class="caret"></span>');
          });
          z++;
    });
    $(contact_wrapper).on("click",".remove_field", function(e){ //user click on remove text
        e.preventDefault(); $(this).parent('div').parent('div').remove(); // remove input group
    });
    
    
    // FORM SUBMIT FUNCTION (REALLY IMPORTANT!)
    $( "#event-form" ).submit(function( event ) {
      event.preventDefault(); // prevent default submit behavior
      $( "#submit-form").button('loading');
      var url = $(this).attr('action'); // URL is taken from form's action value (process.php)
      var data = $(this).serialize(); // serializes all the input data in the form for POST
      $.ajax({ //make the AJAX call 
        type: "POST",
        url: url,
        data: data,
        success: function(data) { // returned data should be the event ID if successfull
          $( "#submit-form").button('reset');
          if ($.isNumeric( data ) && data) { // we assume if only a number is returned then its probably the ID, but 0 is a fail and will evaluate to False
            $(document).trigger("add-alerts", [{
              'message': "The event has been updated successfully. Event ID is " + data + ".",
              'priority': 'success'
            }]);
          }
          else { // anything else could be a error string and should be outputted in the alert
            $(document).trigger("add-alerts", [{
              'message': "Something went wrong: " + data + ".",
              'priority': 'error'
            }]);
          }
        },
        error: function(data) { // only occurs if the AJAX call fails altogether
          $( "#submit-form").button('error');
          $(document).trigger("add-alerts", [{
              'message': "Something went wrong, the event was not updated.",
              'priority': 'error'
          }]);
        }
      });
    });
    
  });
</script>