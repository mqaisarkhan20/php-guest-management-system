<?php 

require 'includes/config.php';

if (!isset($_SESSION['role'])) {
	$error = new stdClass();
	$error->msg = 'You are not allowed!';
	header('Content-Type: application/json');
	echo json_encode($error);
	exit;
}

if (isset($_GET['get_guest_info']) && isset($_GET['id'])) {
	$id = clean_input($_GET['id']);

	$guest_query = "SELECT guests.*, events.id as event_id, events.date as event_date, events.name as event_name
									FROM guests 
									LEFT JOIN events
									ON guests.event = events.id
									WHERE guests.id = $id";

	$guest = $db->single_row($guest_query);
	// $guest['status'] = 4; // FOR TESTING
	header('Content-Type: application/json');
	echo json_encode($guest);
	exit;
}