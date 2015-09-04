<?php
/*
------------------------------------------- FORMS.PHP ---------------------------------------------
Welcome to the main forms file! This file provides a demo interface for interacting with the sample
event form. While this code acts as the control centre, it does not actually contain any of the
form logic itself. The main files of interest are include/event_form.php (the form code and logic)
and process.php (the interpreter that sends the form data to the database). The rest of the files
are just for show/demo purposes.

Bootstrap and jQuery are being used heavily here for ease of prototyping. There may be some
functionality that we would want to use in production, particularly in jQuery, but for the most
part these are extra. The full list of plugins and dependencies can be found below or on each
page individually.

NOTE: This file is not thoroughly commented due to it containing no important logic for the forms.

----------------------------------------- FULL STRUCTURE ------------------------------------------
Files marked with *asterisks* are important and worth taking a look at. Others are trivial or only
exist as support for this example and are not otherwise useful.

{{forms.php}}
| *process.php*	                  interprets form data and performs DB insert/updates
| custom.css                      custom CSS styling for this example
| include/ 
| - event_dashboard.php      	  displays list of events in test DB with edit/delete functions
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
 Core CSS - quick page structure and elements - css/bootstrap.min.css (this)
 Theme CSS - easy form styling - css/bootstrap-theme.min.css (this)

jQuery - reduces JavaScript headaches considerably - https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js (this)
jQuery Plugins
 Chosen - allows for searchable select inputs - used on Instructor, Course, and Group fields
  JS - js/chosen.jquery.min.js (this)
  CSS - css/chosen.min.css (fthis)
 Trumbowyg - Lightweight WYSIWYG Editor for the content fields
  JS - js/trumbowyg.min.js (this) 
  CSS -  - css/trumbowyg.min.css (this )
 Geocomplete - autofill city/province/country locations based on Google Maps API - js/jquery.geocomplete.min.js (this)
 Bootstrap Alerts - allows for jQuery control of alert dialogs (used to show if event has savedsuccessfully) - js/jquery.bsAlerts.min.js (this)

Google Maps API - informs location autocomplete - http://maps.googleapis.com/maps/api/js?sensor=false&amp;libraries=places (this)

*/
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>NI Event Example Forms</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap theme -->
    <link href="css/bootstrap-theme.min.css" rel="stylesheet">

    <!-- Chosen jQuery plugin css -->
    <link href="css/chosen.min.css" rel="stylesheet">

    <!-- WYSIWYG editor styles -->
    <link href="css/trumbowyg.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="custom.css" rel="stylesheet">

  </head>

  <body>
    <div class="loader-container">
	    <div class="loader"></div>
	</div>

  	<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel">Confirm Delete</h4>
                </div>
                <div class="modal-body">
                    <p>You are about to delete an event, this procedure is irreversible.</p>
                    <p>Do you want to proceed?</p>
                    <p class="debug-id"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" id="confirmed-delete-event" class="btn btn-danger btn-ok" data-deleted-text="Event Deleted" data-loading-text="Deleting..." autocomplete="off">Delete</a>
                </div>
            </div>
        </div>
    </div>

    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container-fluid">
        <div class="navbar-header">
          <a class="navbar-brand" href="forms.php">Neufeld Institute Event Forms</a>
        </div>
      </div>
    </nav>

    <div id="container" class="container-fluid">
    	<div class="row">
			<div class="col-sm-3 col-md-2 sidebar">
			  <ul id="dashboard-nav" class="show-list nav nav-sidebar visible-main">
			    <li id="show-all-li" class="active"><a id="show-all" href="#">All</a></li>
			    <li id="show-courses-li"><a id="show-courses" href="#">Courses</a></li>
			    <li id="show-presentations-li"><a id="show-presentations" href="#">Presentations</a></li>
			    <li id="show-video-courses-li"><a id="show-video-courses" href="#">Video Courses</a></li>
			  </ul>
			  <ul id="edit-top-nav" class="show-list nav nav-sidebar visible-new visible-edit">
			    <li><a id="back-to" href="">Back to Events List</a></li>
			  </ul>
			  <ul id="new-event-nav" class="show-list nav nav-sidebar visible-main visible-new">
			    <li id="new-event-li"><a id="new-event" href="#">Add New Event</a></li>
			  </ul>
			  <ul id="edit-event-nav" class="show-list nav nav-sidebar visible-edit">
			    <li id="edit-event-li"><a href="#">Edit Event</a></li>
			  </ul>
			  <ul id="edit-nav" class="show-list nav nav-sidebar visible-edit visible-new">
			    <li><a href="#basic-details">Basic Details</a></li>
			    <li><a href="#date-time">Date and Time</a></li>
			    <li><a href="#location">Location</a></li>
			    <li><a href="#content">Content</a></li>
			    <li><a href="#links-contact">Links and Contact</a></li>
			  </ul>
			</div>
			<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
			</div>
 		</div>
    </div>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/chosen.jquery.min.js"></script>
    <script src="http://maps.googleapis.com/maps/api/js?sensor=false&amp;libraries=places"></script>
    <script src="js/jquery.geocomplete.min.js"></script>
    <script src="js/trumbowyg.min.js"></script>
    <script src="js/jquery.bsAlerts.min.js"></script>
    <script type="text/javascript">
    $(function(){
		$( ".main" ).load( "include/event_dashboard.php", function() {
			console.log('initial load ran');
			$('.show-list').hide();
	    	$('.visible-main').show();
			$('.loader-container').fadeOut('fast');
		});
		$('#new-event').click(function(e){ //on add input button click
        	e.preventDefault();
        	$('.loader-container').show();
        	$( ".main" ).empty();
        	$('.show-list').hide();
	    	$('.visible-new').show();
        	$('.show-list li.active').removeClass('active');
    		$('#new-event-li').addClass('active');
        	$( ".main" ).load( "include/event_form.php", function( response, status, xhr ) {
        		console.log('loaded');
			  	$('.loader-container').fadeOut('fast');
			}) ;
    	});
    	$('#back-to').click(function(e){ //on add input button click
        	e.preventDefault();
			console.log('back to load ran');
        	$('.loader-container').show();
        	$( ".main" ).empty();
        	$('.show-list').hide();
	    	$('.visible-main').show();
        	$('.show-list li.active').removeClass('active');
    		$('#show-all-li').addClass('active');
        	$( ".main" ).load( "include/event_dashboard.php", function() {
			  	$('.loader-container').fadeOut('fast');
			});
    	});

    	$('#show-all').click(function(e){ //on add input button click
        	e.preventDefault();
        	$('#courses-list').show();
        	$('#presentations-list').show();
        	$('#video-courses-list').show();
        	$('.show-list li.active').removeClass('active');
    		$('#show-all-li').addClass('active');
        });
        $('#show-courses').click(function(e){ //on add input button click
        	e.preventDefault();
        	$('#courses-list').show();
        	$('#presentations-list').hide();
        	$('#video-courses-list').hide();
        	$('.show-list li.active').removeClass('active');
    		$('#show-courses-li').addClass('active');
        });
        $('#show-presentations').click(function(e){ //on add input button click
        	e.preventDefault();
        	$('#courses-list').hide();
        	$('#presentations-list').show();
        	$('#video-courses-list').hide();
        	$('.show-list li.active').removeClass('active');
    		$('#show-presentations-li').addClass('active');
        });
        $('#show-video-courses').click(function(e){ //on add input button click
        	e.preventDefault();
        	$('#courses-list').hide();
        	$('#presentations-list').hide();
        	$('#video-courses-list').show();
        	$('.show-list li.active').removeClass('active');
    		$('#show-video-courses-li').addClass('active');
    	});

    	$('#confirm-delete').on('show.bs.modal', function(e) {
            $('#confirmed-delete-event').button('reset');
            $(this).find('.btn-ok').data('id', $(e.relatedTarget).data('id'));
            $('.debug-id').html('Delete ID: <strong>' + $(this).find('.btn-ok').data('id') + '</strong>');
        });
        $('#confirmed-delete-event').click(function(e){ //on add input button click
	    	e.preventDefault();
	    	$(this).button('loading');
	    	url = "include/event_delete.php";
	    	id = $(this).data('id')
			var delete_event = $.post( url, { "event_id": id, "delete" : 1 } );
			delete_event.success(function( data ) {
				if (data) {
					$('#confirm-delete').modal('hide');
					$('.loader-container').show();
					$( ".main" ).load( "include/event_dashboard.php", function() {
						console.log('reload ran');
						$('.loader-container').fadeOut('fast');
					});
				}
				else {
					$('#confirmed-delete-event').html('Failed');
				}
			});
			delete_event.fail(function( data ) {
				$('#confirmed-delete-event').html('Failed');
			});
		});
    });
    </script>
  </body>
</html>
