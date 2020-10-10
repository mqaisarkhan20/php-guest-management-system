<?php 

require 'includes/config.php';

// if (!isset($_SESSION['role'])) {
// 	header("Location: " . URL . "login.php");
// 	exit;
// } else if ($_SESSION['role'] !== 'admin') {
// 	header("Location: " . URL . "login.php");
// 	exit;
// }

$event = [];
if (isset($_GET['event_code_public'])) {
	$event_code_public = clean_input($_GET['event_code_public']);
	$event = $db->single_row("SELECT * FROM events WHERE code_public = '$event_code_public'");

	if (count($event) > 0) {
		if ($event['status'] == 2) {
			$_SESSION['message'] = '<div class="alert alert-danger alert-dismissible">
			  <button type="button" class="close" data-dismiss="alert">&times;</button>
			  <strong>Error!</strong> The registration for this event is not available!
			</div>';

			header("Location: index.php");
			exit;
		}
	} else {
		$_SESSION['message'] = '<div class="alert alert-danger alert-dismissible">
		  <button type="button" class="close" data-dismiss="alert">&times;</button>
		  <strong>Error!</strong> No event found!
		</div>';

		header("Location: index.php");
		exit;
	}
}

if (isset($_POST['register'])) {
	$event_id = clean_input($_POST['event']);
	$name = clean_input($_POST['name']);
	$surename = clean_input($_POST['surename']);
	$street_nr = clean_input($_POST['street_nr']);
	$plz = clean_input($_POST['plz']);
	$city = clean_input($_POST['city']);
	$phone = clean_input($_POST['phone']);
	$pptu = (isset($_POST['pptu'])) ? "accepted" : "rejected";

	$data = Array(
		'event' => $event_id,
		'name' => $name,
		'surename' => $surename,
		'street_nr' => $street_nr,
		'plz' => $plz,
		'city' => $city,
		'phone' => $phone,
		'privacy_check' => $pptu,
		'status' => 1
	);

	if ($db->insert('guests', $data)) {
		$_SESSION['message'] = '<div class="alert alert-success alert-dismissible">
		  <button type="button" class="close" data-dismiss="alert">&times;</button>
		  <strong>Success!</strong> Guest registered successfully!
		</div>';

		if (isset($_GET['event_code_public'])) {
			header("Location: register.php?event_code_public=".$_GET['event_code_public']);
			exit;
		} else {
			header("Location: register.php");
			exit;
		}
	}
}

// $nav_active = 'events';
$guests_query = "SELECT guests.*, events.name as event_name
								FROM guests 
								LEFT JOIN events
								ON events.id = guests.event
								ORDER BY guests.id DESC";

$nav_active = "register";
$guests = $db->multiple_row($guests_query);

require 'includes/header.php';
require 'includes/navigation.php';
require 'includes/sidebar.php';
?>
<div class="container pt-4">
	<?php if (isset($_SESSION['message'])) {
		echo $_SESSION['message'];
		unset($_SESSION['message']);
	} ?>
	<?php if (count($event) > 0): ?>
	<p>
		<h3 class="d-inline-block">Event:&nbsp;&nbsp;</h3>
		<h4 class="d-inline-block text-primary"><?= $event['name'] ?></h4>
	</p>
	<p>
		<h3 class="d-inline-block">Date:&nbsp;&nbsp;</h3>
		<h4 class="d-inline-block text-success"><?= date_format(date_create($event['date']), 'd.m.Y') ?></h4>
	</p>
	<h1>Register Guest</h1>
	<p>Register guest here:</p>

	<form action="" method="POST" name="register_form">
	<input type="hidden" name="event" value="<?= $event['id'] ?>">
	<div class="row">
		<div class="col-md-4">
			<div class="form-group">
				<label for="">Guest name:</label>
				<input type="text" name="name" class="form-control">
			</div>
		</div>
		<div class="col-md-4">
			<div class="form-group">
				<label for="">Guest surename:</label>
				<input type="text" name="surename" class="form-control">
			</div>
		</div>
		<div class="col-md-4">
			<div class="form-group">
				<label for="">Guest Street + Nr:</label>
				<input type="text" name="street_nr" class="form-control">
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-4">
			<div class="form-group">
				<label for="">PLZ:</label>
				<input type="text" name="plz" class="form-control">
			</div>
		</div>
		<div class="col-md-4">
			<div class="form-group">
				<label for="">City:</label>
				<input type="text" name="city" class="form-control">
			</div>
		</div>
		<div class="col-md-4">
			<div class="form-group">
				<label for="">Phone:</label>
				<input type="text" name="phone" class="form-control">
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-8">
			<div class="form-group">
				<div class="custom-control custom-checkbox">
				  <input type="checkbox" name="pptu" class="custom-control-input" id="pptu">
				  <label class="custom-control-label" for="pptu">Privacy Policy & Terms of Use</label>
				</div>
			</div>
		</div>
	</div>

	<!-- <div class="row"> -->
		<div class="form-group">
			<input type="submit" name="register" value="Register" class="btn btn-primary btn-sm">
		</div>
	<!-- </div> -->
	</form>

	<hr>
	<?php endif; ?><!-- IF EVENT NOT EXIST THEN DON'T SHOW REGISTER FORM -->

<?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
	<div class="row">
		<div class="col-md-12">
			<table class="table table-bordered">
				<thead>
					<tr>
						<th>Event</th>
						<th>Name</th>
						<th>Surename</th>
						<th>Street Nr</th>
						<th>Plz</th>
						<th>City</th>
						<th>Phone</th>
						<th>Checkin</th>
						<th>Checkout</th>
					</tr>
				</thead>
				<tbody>
					<?php if (count($guests) > 0): ?>
					<?php foreach($guests as $guest): ?>
					<tr>
						<td><?= $guest['event_name'] ?></td>
						<td><?= $guest['name'] ?></td>
						<td><?= $guest['surename'] ?></td>
						<td><?= $guest['street_nr'] ?></td>
						<td><?= $guest['plz'] ?></td>
						<td><?= $guest['city'] ?></td>
						<td><?= $guest['phone'] ?></td>
						<td><?= ($guest['checkin_time']) ? $guest['checkin_time'] : '-'; ?></td>
						<td><?= ($guest['checkout_time']) ? $guest['checkout_time'] : '-'; ?></td>
					</tr>
					<?php endforeach; ?>
					<?php else: ?>
						<tr>
							<td colspan="9"><i><center>No data saved yet!</center></i></td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>
<?php endif; ?><!-- CHECK IF ADMIN IS LOGGED IN -->
</div>

<script>
$(document).ready(function() {
	$('form[name="register_form"]').submit(function(e) {
		$('.alert').remove();
		
		var form = $(this);
		var name = ($('input[name="name"]').val()).trim();
		var surename = ($('input[name="surename"]').val()).trim();
		var street_nr = ($('input[name="street_nr"]').val()).trim();
		var plz = ($('input[name="plz"]').val()).trim();
		var city = ($('input[name="city"]').val()).trim();
		var phone = ($('input[name="phone"]').val()).trim();
		var pptu = $('input[name="pptu"]').is(":checked");

		if (name == '' || surename == ''|| street_nr == ''|| plz == ''|| city == ''|| phone == '') {
			e.preventDefault();
			var message = $(`<div class="alert alert-danger alert-dismissible" style="display: none;">
			  <button type="button" class="close" data-dismiss="alert">&times;</button>
			  <strong>Error!</strong> All fields are required.
			</div>`);
			$(form).prepend(message);
			$(message).fadeIn();
		} else if (!/^\d+$/.test(plz)) {
			e.preventDefault();
			var message = $(`<div class="alert alert-danger alert-dismissible" style="display: none;">
			  <button type="button" class="close" data-dismiss="alert">&times;</button>
			  <strong>Error!</strong> Enter correct zip code.
			</div>`);
			$(form).prepend(message);
			$(message).fadeIn();
		} else if (!pptu) {
			e.preventDefault();
			var message = $(`<div class="alert alert-danger alert-dismissible" style="display: none;">
			  <button type="button" class="close" data-dismiss="alert">&times;</button>
			  <strong>Error!</strong> Check Privacy Policy & Terms of Use.
			</div>`);
			$(form).prepend(message);
			$(message).fadeIn();
		}

	});
});
</script>
<?php require 'includes/footer.php'; ?>