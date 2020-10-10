<?php 

require 'includes/config.php';

if (!isset($_SESSION['role'])) {
	header("Location: " . URL . "login.php");
	exit;
} else if ($_SESSION['role'] !== 'admin') { //  && $_SESSION['role'] !== 'security'
	header("Location: " . URL . "login.php");
	exit;
}

$guests = [];$event = [];
if (isset($_GET['event_code_security'])) {
	$event_code_security = clean_input($_GET['event_code_security']);
	$event = $db->single_row("SELECT * FROM events WHERE code_security = '$event_code_security'");

	if (count($event) == 0) {
		$_SESSION['message'] = '<div class="alert alert-danger alert-dismissible">
		  <button type="button" class="close" data-dismiss="alert">&times;</button>
		  <strong>Error!</strong> No event found!
		</div>';

		header("Location: events.php");
		exit;
	} else {
		$guests = $db->multiple_row("SELECT * FROM guests WHERE event = $event[id]");
	}
} else {
	header("Location: events.php");
	exit;
}

$nav_active = 'guests';

require 'includes/header.php';
require 'includes/navigation.php';
require 'includes/sidebar.php';
?>
<div class="container pt-4">
	<?php if (isset($_SESSION['message'])) {
		echo $_SESSION['message'];
		unset($_SESSION['message']);
	} ?>

	<div class="row">
		<div class="col-md-12">
			<p>
				<h3 class="d-inline-block">Event:&nbsp;&nbsp;</h3>
				<h4 class="d-inline-block text-primary"><?= $event['name'] ?></h4>
			</p>
			<p>
				<h3 class="d-inline-block">Date:&nbsp;&nbsp;</h3>
				<h4 class="d-inline-block text-success"><?= date_format(date_create($event['date']), 'd.m.Y') ?></h4>
			</p>
		</div>
		<hr>
		<h4>Guests:</h4>
		<table class="table table-bordered">
			<thead>
				<tr>
					<th>Name</th>
					<th>Surename</th>
					<th>Street nr</th>
					<th>Plz</th>
					<th>City</th>
					<th>Phone</th>
					<th>Checkin time</th>
					<th>Checkout time</th>
					<th>Status</th>
				</tr>
			</thead>
			<tbody>
				<?php if (count($guests) > 0): ?>
				<?php foreach ($guests as $guest): ?>
				<?php
        $guests_status = [
					'ZERO INDEX',
					'Registered',
					'Checked in',
					'Checked out',
					'Rejected',
					'Rejected and infected',
					'Staff'
				];
				?>
        <tr>
					<td><?= ucfirst($guest['name']); ?></td>
					<td><?= ucfirst($guest['surename']); ?></td>
					<td><?= ucfirst($guest['street_nr']); ?></td>
					<td><?= $guest['plz']; ?></td>
					<td><?= ucfirst($guest['city']); ?></td>
					<td><?= $guest['phone']; ?></td>
					<td><?= ($guest['checkin_time'] !== null)?date_format(date_create($guest['checkin_time']), 'd.m.Y H:i:s'): '-'; ?></td>
					<td><?= ($guest['checkout_time'] !== null)?date_format(date_create($guest['checkout_time']), 'd.m.Y H:i:s'): '-'; ?></td>
					<td><?= $guests_status[$guest['status']]; ?></td>
				</tr>
				<?php endforeach; ?>
				<?php else: ?>
	      <tr>
					<td colspan="9"><i><center>No record saved yet!</center></i></td>
				</tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>

</div>


<script>
$(document).ready(function() {
});
</script>
<?php require 'includes/footer.php'; ?>