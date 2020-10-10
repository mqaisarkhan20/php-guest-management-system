<?php 

require 'includes/config.php';

if (!isset($_SESSION['role'])) {
	header("Location: " . URL . "login.php");
	exit;
} else if ($_SESSION['role'] !== 'admin') {
	header("Location: " . URL . "login.php");
	exit;
}

if (isset($_GET['edit'])) {
	$id = clean_input($_GET['edit']);
	$event = $db->single_row("SELECT * FROM events WHERE id = $id");

	if (count($event) > 0) {

	} else {
		$_SESSION['message'] = '<div class="alert alert-danger alert-dismissible">
		  <button type="button" class="close" data-dismiss="alert">&times;</button>
		  <strong>Error!</strong> No event found with this id.
		</div>';

		header("Location: events.php");
		exit;
	}
} else {
	header("Location: events.php");
	exit;
}

if (isset($_POST['update'])) {
	$event_id = clean_input($_POST['event_id']);
	$name = clean_input($_POST['name']);
	$date = clean_input($_POST['date']);
	$event = $db->single_row("SELECT * FROM events WHERE id = $event_id");
	if (count($event) > 0 ) {
		$new_data = Array(
			'name' => $name,
			'date' => $date
		);
		$condition = Array(
			'id' => $event_id
		);

		if ($db->update('events', $new_data, $condition)) {
			$_SESSION['message'] = '<div class="alert alert-success alert-dismissible">
			  <button type="button" class="close" data-dismiss="alert">&times;</button>
			  <strong>Success!</strong> Event status updated successfully.
			</div>';

			header('location: ' . URL . 'events.php');
			exit;
		}
	} else {
		header('location: ' . URL . 'events.php');
		exit;
	}
}


$nav_active = 'events';
$events = $db->multiple_row("SELECT * FROM events ORDER BY date DESC");

require 'includes/header.php';
require 'includes/navigation.php';
require 'includes/sidebar.php';
?>
<div class="container pt-4">
	<h1>Events</h1>
	<p>Update event</p>
	<div class="row">
		<div class="col-md-5">
			<form action="" method="POST" name="update_event">
				<input type="hidden" name="event_id" value="<?= $event['id'] ?>">
				<div class="form-group">
					<label for="">Event date:</label>
					<input type="date" class="form-control" name="date" value="<?= $event['date'] ?>">
				</div>

				<div class="form-group">
					<label for="">Event name</label>
					<input type="text" class="form-control" name="name" value="<?= $event['name'] ?>">
				</div>

				<div class="form-group">
					<input type="submit" name="update" value="Update" class="btn btn-primary btn-sm">
				</div>
			</form>
		</div>
	</div>
</div>

<script>
$(document).ready(function() {
	$('form[name="update_event"]').submit(function(e) {
		$('.alert').remove();
		
		var form = $(this);
		var date = ($('input[name="date"]').val()).trim();
		var name = ($('input[name="name"]').val()).trim();

		if (date == '' || name == '') {
			e.preventDefault();
			var message = $(`<div class="alert alert-danger alert-dismissible" style="display: none;">
			  <button type="button" class="close" data-dismiss="alert">&times;</button>
			  <strong>Error!</strong> All fields are required.
			</div>`);
			$(form).prepend(message);
			$(message).fadeIn();
		}

	});
});
</script>
<?php require 'includes/footer.php'; ?>