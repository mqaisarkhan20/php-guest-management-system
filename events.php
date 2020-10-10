<?php 

require 'includes/config.php';

if (!isset($_SESSION['role'])) {
	header("Location: " . URL . "login.php");
	exit;
} else if ($_SESSION['role'] !== 'admin') {
	header("Location: " . URL . "login.php");
	exit;
}

if (isset($_POST['save'])) {
	$date = clean_input($_POST['date']);
	$name = clean_input($_POST['name']);

	$data = Array(
		'date' => $date,
		'name' => $name,
		'code_public' => md5(round(microtime(true) * 1000)),
		'code_security' => md5(round(microtime(true) * 1000) + rand(1,100)),
		'status' => 1
	);

	if ($db->insert('events', $data)) {
		$_SESSION['message'] = '<div class="alert alert-success alert-dismissible">
		  <button type="button" class="close" data-dismiss="alert">&times;</button>
		  <strong>Success!</strong> Event saved successfully.
		</div>';

		header('location: ' . URL . 'events.php');
		exit;
	}
}

if (isset($_GET['deactivate'])) {
	$event_id = clean_input($_GET['deactivate']);
	$event = $db->single_row("SELECT * FROM events WHERE id = $event_id");
	if (count($event) > 0 ) {
		$new_data = Array(
			'status' => 2
		);
		$condition = Array(
			'id' => $event_id
		);

		if ($db->update('events', $new_data, $condition)) {
			$_SESSION['message'] = '<div class="alert alert-success alert-dismissible">
			  <button type="button" class="close" data-dismiss="alert">&times;</button>
			  <strong>Success!</strong> Event status updated successfully.
			</div>';

			// header('location: ' . URL . 'events.php');
			// exit;
		}
	} else {
		// header('location: ' . URL . 'events.php');
		// exit;
	}
}

if (isset($_GET['activate'])) {
	$event_id = clean_input($_GET['activate']);
	$event = $db->single_row("SELECT * FROM events WHERE id = $event_id");
	if (count($event) > 0 ) {
		$new_data = Array(
			'status' => 1
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

if (isset($_GET['delete'])) {
	$event_id = clean_input($_GET['delete']);
	$event = $db->single_row("SELECT * FROM events WHERE id = $event_id");
	if (count($event) > 0 ) {
		$condition = Array(
			'id' => $event_id
		);

		if ($db->delete('events', $condition)) {
			$db->delete('guests', ['event' => $event_id]);
			$_SESSION['message'] = '<div class="alert alert-success alert-dismissible">
			  <button type="button" class="close" data-dismiss="alert">&times;</button>
			  <strong>Success!</strong> Event deleted successfully.
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
	<?php if (isset($_SESSION['message'])) {
		echo $_SESSION['message'];
		unset($_SESSION['message']);
	} ?>
	<h1>Events</h1>
	<p>Save new event.</p>
	<div class="row">
		<div class="col-md-5">
			<form action="" method="POST" name="save_event">
				<div class="form-group">
					<label for="">Event date:</label>
					<input type="date" class="form-control" name="date">
				</div>

				<div class="form-group">
					<label for="">Event name</label>
					<input type="text" class="form-control" name="name">
				</div>

				<div class="form-group">
					<input type="submit" name="save" value="Save" class="btn btn-primary btn-sm">
				</div>
			</form>
		</div>
	</div>

	<hr>

	<div class="row">
		<div class="col-md-12">
			<h3>Events saved:</h3>
			<table class="table table-bordered">
				<thead>
					<tr>
						<th>Date</th>
						<th>Name</th>
						<th>Registration</th>
						<th>Security</th>
						<th>Guestlist</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php if (count($events) > 0): ?>
					<?php foreach($events as $event): ?>
						<tr>
							<td><?= date_format(date_create($event['date']), 'd.m.Y'); ?></td>
							<td><?= $event['name']; ?></td>
							<td><a target="" href="<?= URL ?>register.php?event_code_public=<?= $event['code_public'] ?>">Register</a></td>
							<td><a target="" href="<?= URL ?>guests.php?event_code_security=<?= $event['code_security'] ?>">Security</a></td>
							<td><a target="" href="<?= URL ?>guestlist.php?event_code_security=<?= $event['code_security'] ?>">Guestlist</a></td>
							<td>
								<?php if ($event['status'] == 2): ?>
								<a onclick="return confirm('Activate event?');" href="<?= URL ?>events.php?activate=<?= $event['id'] ?>">
									<i class="far fa-times-circle my-delete-icon"></i>
								</a>
								<?php elseif ($event['status'] == 1): ?>
								<a onclick="return confirm('Deactivate event?');" href="<?= URL ?>events.php?deactivate=<?= $event['id'] ?>">
									<i class="fas fa-check my-check-icon"></i>
								</a>
								<?php endif; ?>
								<a onclick="return confirm('Delete all event\'s data?');" href="<?= URL ?>events.php?delete=<?= $event['id'] ?>">
									<i class="fas fa-trash my-recyclebin-icon"></i>
								</a>

								<a href="<?= URL ?>edit_event.php?edit=<?= $event['id'] ?>">
									<i class="fas fa-edit my-edit-icon"></i>
								</a>

							</td>
						</tr>
					<?php endforeach; ?>
					<?php else: ?>
						<tr>
							<td colspan="3"><i><center>No data saved yet!</center></i></td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<script>
$(document).ready(function() {
	$('form[name="save_event"]').submit(function(e) {
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