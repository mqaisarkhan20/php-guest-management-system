<?php 

require 'includes/config.php';

if (!isset($_SESSION['role'])) {
	header("Location: " . URL . "login.php");
	exit;
} else if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'security') {
	header("Location: " . URL . "login.php");
	exit;
}

if (isset($_GET['guest_status']) && isset($_GET['event_code_security']) && isset($_GET['guest_id'])) {
	$allowed_statuses = [2, 3, 4, 5, 6];
	$guest_status = clean_input($_GET['guest_status']);
	$guest_id = clean_input($_GET['guest_id']);
	$event_code_security = clean_input($_GET['event_code_security']);

	$acceptable = is_numeric($guest_status) && (in_array($guest_status, $allowed_statuses));
	
	if (!$acceptable) {
		header('Location: guests.php?event_code_security=' . $event_code_security);
		exit;
	}

	$guest = $db->single_row("SELECT * FROM guests WHERE id = $guest_id");
	if (count($guest) == 0) {
		header('Location: guests.php?event_code_security=' . $event_code_security);
		exit;
	}

	$update = Array(
		'status' => $guest_status
	);

	$condition = Array(
		'id' => $guest_id
	);

	$time_stamp = date("Y-m-d H:i:s", time());
	if ($guest_status == 2) {
		$update['checkin_time'] = $time_stamp;
	} else if ($guest_status == 3) {
		$update['checkout_time'] = $time_stamp;
	} else if ($guest_status == 4) {
		$update['checkin_time'] = $time_stamp;
		$update['checkout_time'] = $time_stamp;
	} else if ($guest_status == 5) {
		$update['checkin_time'] = $time_stamp;
		$update['checkout_time'] = $time_stamp;
	} else if ($guest_status == 6) {
		$update['checkin_time'] = $time_stamp;
	}

	if ($db->update('guests', $update, $condition)) {
		$_SESSION['message'] = '<div class="alert alert-success alert-dismissible">
		  <button type="button" class="close" data-dismiss="alert">&times;</button>
		  <strong>Success!</strong> The guest status updated successfully.
		</div>';

		header("Location: guests.php?event_code_security=" . $event_code_security);
		exit;
	}
}

$event = [];
if (isset($_GET['event_code_security'])) {
	$code_security = clean_input($_GET['event_code_security']);
	$event = $db->single_row("SELECT * FROM events WHERE code_security = '$code_security'");

	if (count($event) > 0) {
		if ($event['status'] == 2) {
			$_SESSION['message'] = '<div class="alert alert-danger alert-dismissible">
			  <button type="button" class="close" data-dismiss="alert">&times;</button>
			  <strong>Error!</strong> The guestlist is not valid for today.
			</div>';

			header("Location: events.php");
			exit;
		}
	} else {
		$_SESSION['message'] = '<div class="alert alert-danger alert-dismissible">
		  <button type="button" class="close" data-dismiss="alert">&times;</button>
		  <strong>Error!</strong> No event found!
		</div>';

		header("Location: events.php");
		exit;
	}
}

$nav_active = 'guests';
$guests_checked_in = $db->multiple_row("SELECT * FROM guests WHERE event = $event[id] AND (status = 2 AND status != 6 AND checkin_time != '')"); //  AND (status = 6 AND checkin_time != '')
$all_guests = $db->multiple_row("SELECT guests.*, events.id as event_id, 
																events.code_public as eventcode_public,
																events.code_security as eventcode_security,
																events.date as event_date, events.name as event_name
																FROM guests 
																LEFT JOIN events
																ON events.id = guests.event
																WHERE guests.event = $event[id]
																ORDER BY guests.name ASC");
$guests_status_values = [
	'',
	'Registered',
	'Checked in',
	'Checked out',
	'Guest rejected',
	'Guest rejected and infected',
	'Staff'
];

require 'includes/header.php';
require 'includes/navigation.php';
require 'includes/sidebar.php';
?>
<div class="container pt-4">
	<?php if (isset($_SESSION['message'])) {
		echo $_SESSION['message'];
		unset($_SESSION['message']);
	} ?>
	<?php if (is_array($event)): ?>
	<p>
		<h3 class="d-inline-block">Event:&nbsp;&nbsp;</h3>
		<h4 class="d-inline-block text-primary"><?= $event['name'] ?></h4>
	</p>
	<p>
		<h3 class="d-inline-block">Date:&nbsp;&nbsp;</h3>
		<h4 class="d-inline-block text-success"><?= (date_format(date_create($event['date']), 'd.m.Y')) ?></h4>
	</p>
	<?php endif; ?>
	<a href=""><button class="btn btn-success btn-sm float-right">RELOAD</button></a>
	<h2>Guests</h2>
	<p class="pt-2">Number of guests checked in: <strong><?= count($guests_checked_in) ?></strong></p>
	<table class="table table-bordered">
		<thead>
			<tr>
				<!-- <th>Event name</th> -->
				<th>Guest</th>
				<!-- <th>Street</th>
				<th>Plz</th>
				<th>City</th>
				<th>Phone</th>
				<th>Status</th>
				<th>Checkin time</th>
				<th>Checkout time</th> -->
				<th>Status</th>
			</tr>
		</thead>
		<tbody>
			<?php if (count($all_guests) > 0): ?>
			<?php foreach ($all_guests as $guest): ?>
			<tr>
				<!-- <td><?= $guest['event_name']; ?></td> -->
				<td>
					<a data-guest-id="<?= $guest['id'] ?>" href="" class="getUserInfo" >
						<?= ucfirst($guest['name']) . ' ' . ucfirst($guest['surename'][0]) . '.' ?>
					</a>
				</td>
				<!-- <td>
					<a href="<?= URL ?>guests_details.php?guest_id=<?= $guest['id'] ?>&?eventcode_security=<?= $guest['eventcode_security'] ?>">
						<?= ucfirst($guest['name']) . ' ' . ucfirst($guest['surename'][0]) . '.' ?>
					</a>
				</td> -->
				<!-- <td><?= ucfirst($guest['street_nr']); ?></td>
				<td><?= $guest['plz']; ?></td>
				<td><?= ucfirst($guest['city']); ?></td>
				<td><?= $guest['phone']; ?></td>
				<td><?= $guests_status_values[$guest['status']]; ?></td>
				<td><?= ($guest['checkin_time']) ? $guest['checkin_time']: '-'; ?></td>
				<td><?= ($guest['checkout_time']) ? $guest['checkout_time']: '-'; ?></td> -->
				<td>
					<?php // $guest['status'] = 6; // TESTING PURPOSE ?>
					<?php if ($guest['status'] == 1): ?>

						<a href="#">
							<i class="fas fa-check my-check-icon text-secondary"></i>
						</a>&nbsp;&nbsp;
						<a href="#">
							<i class="far fa-times-circle text-secondary"></i>
						</a>&nbsp;&nbsp;
						<a href="#">
							<i class="fas fa-door-open text-secondary"></i>
						</a>&nbsp;&nbsp;
						<a href="#">
							<i class="fas fa-exclamation text-secondary"></i>
						</a>&nbsp;&nbsp;
						<a href="#">
							<i class="fas fa-user-tie text-secondary"></i>
						</a>

					<?php elseif ($guest['status'] == 2): ?>

						<a href="#">
							<i class="fas fa-check text-success"></i>
						</a>&nbsp;&nbsp;
						<a href="#">
							<i class="far fa-times-circle text-secondary"></i>
						</a>&nbsp;&nbsp;
						<a href="#">
							<i class="fas fa-door-open text-secondary"></i>
						</a>&nbsp;&nbsp;
						<a href="#">
							<i class="fas fa-exclamation text-secondary"></i>
						</a>&nbsp;&nbsp;
						<a href="#">
							<i class="fas fa-user-tie text-secondary"></i>
						</a>

					<?php elseif ($guest['status'] == 3): ?>

						<a href="#">
							<i class="fas fa-check text-secondary"></i>
						</a>&nbsp;&nbsp;
						<a href="#">
							<i class="far fa-times-circle text-secondary"></i>
						</a>&nbsp;&nbsp;
						<a href="#">
							<i class="fas fa-door-open text-warning"></i>
						</a>&nbsp;&nbsp;
						<a href="#">
							<i class="fas fa-exclamation text-secondary"></i>
						</a>&nbsp;&nbsp;
						<a href="#">
							<i class="fas fa-user-tie text-secondary"></i>
						</a>

					<?php elseif ($guest['status'] == 4): ?>

						<a href="#">
							<i class="fas fa-check my-check-icon text-secondary"></i>
						</a>&nbsp;&nbsp;
						<a href="#">
							<i class="far fa-times-circle my-delete-icon text-danger"></i>
						</a>&nbsp;&nbsp;
						<a href="#">
							<i class="fas fa-door-open text-secondary"></i>
						</a>&nbsp;&nbsp;
						<a href="#">
							<i class="fas fa-exclamation text-secondary"></i>
						</a>&nbsp;&nbsp;
						<a href="#">
							<i class="fas fa-user-tie text-secondary"></i>
						</a>

					<?php elseif ($guest['status'] == 5): ?>

						<a href="#">
							<i class="fas fa-check my-check-icon text-secondary"></i>
						</a>&nbsp;&nbsp;
						<a href="#">
							<i class="far fa-times-circle my-delete-icon text-secondary"></i>
						</a>&nbsp;&nbsp;
						<a href="#">
							<i class="fas fa-door-open text-secondary"></i>
						</a>&nbsp;&nbsp;
						<a href="#">
							<i class="fas fa-exclamation text-danger"></i>
						</a>&nbsp;&nbsp;
						<a href="#">
							<i class="fas fa-user-tie text-secondary"></i>
						</a>

					<?php elseif ($guest['status'] == 6): ?>

						<a href="#">
							<i class="fas fa-check my-check-icon text-secondary"></i>
						</a>&nbsp;&nbsp;
						<a href="#">
							<i class="far fa-times-circle my-delete-icon text-secondary"></i>
						</a>&nbsp;&nbsp;
						<a href="#">
							<i class="fas fa-door-open text-secondary"></i>
						</a>&nbsp;&nbsp;
						<a href="#">
							<i class="fas fa-exclamation text-secondary"></i>
						</a>&nbsp;&nbsp;
						<a href="#">
							<i class="fas fa-user-tie text-primary"></i>
						</a>

					<?php endif; ?>
				</td>
			</tr>
			<?php endforeach; ?>
			<?php else: ?>
			<tr>
				<td colspan="2"><i><center>No record saved yet!</center></i></td>
			</tr>
			<?php endif; ?>
		</tbody>
	</table>

<!-- The Modal -->
<div class="modal" id="userInfoModal">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Guest Information:</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <!-- Modal body -->
      <div class="modal-body">
      </div>

      <!-- Modal footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>


</div>

<script>
$(document).ready(function() {

	/*
	  HELPER FUNCTIONS
	*/

	function getURL(string) {
		return window.location.href.replace('#', '');
	}

	function capitalizeFirstLetter(string) {
		if (string != null) {
			return string.charAt(0).toUpperCase() + string.slice(1);
		} else {
			return '';
		}
	}

	function guestStatus(number) {
		var guests = [
			'ZERO INDEX',
			'Registered',
			'Checked in',
			'Checked out',
			'Rejected',
			'Rejected and infected',
			'Staff'
		];

		return (typeof guests[number] != 'undefined') ? guests[number]: `<span class="text-danger">WRONG STATUS NUMBER</span>`;
	}

	function getFourIcons(guest) {
		if (guest.status == 1) {

			return `<a onclick="return confirm('Guest check in?')" href="`+getURL()+`&guest_status=2&guest_id=`+guest.id+`">
					<i class="fas fa-check my-check-icon text-secondary"></i>
				</a>&nbsp;&nbsp;
				<a onclick="return confirm('Reject guest?')" href="`+getURL()+`&guest_status=4&guest_id=`+guest.id+`">
					<i class="far fa-times-circle text-secondary"></i>
				</a>&nbsp;&nbsp;
				<!-- <a onclick="return confirm('Guest check out?')" href="`+getURL()+`&guest_status=3&guest_id=`+guest.id+`">
					<i class="fas fa-door-open text-secondary"></i>
				</a>&nbsp;&nbsp; -->
				<a onclick="return confirm('Report guest as infected?')" href="`+getURL()+`&guest_status=5&guest_id=`+guest.id+`">
					<i class="fas fa-exclamation text-secondary"></i>
				</a>&nbsp;&nbsp;
				<a onclick="return confirm('Checkin as staff?')" href="`+getURL()+`&guest_status=6&guest_id=`+guest.id+`">
					<i class="fas fa-user-tie text-secondary"></i>
				</a>`;

		} else if (guest.status == 2) {

			return `<!-- <a onclick="return confirm('Guest check in?')" href="`+getURL()+`&guest_status=2&guest_id=`+guest.id+`">
				<i class="fas fa-check text-success"></i>
			</a>&nbsp;&nbsp;
			<a onclick="return confirm('Reject guest?')" href="`+getURL()+`&guest_status=4&guest_id=`+guest.id+`">
				<i class="far fa-times-circle text-secondary"></i>
			</a>&nbsp;&nbsp; -->
			<a onclick="return confirm('Guest check out?')" href="`+getURL()+`&guest_status=3&guest_id=`+guest.id+`">
				<i class="fas fa-door-open text-secondary"></i>
			</a>&nbsp;&nbsp;
			<!-- <a onclick="return confirm('Report guest as infected?')" href="`+getURL()+`&guest_status=5&guest_id=`+guest.id+`">
				<i class="fas fa-exclamation text-secondary"></i>
			</a>&nbsp;&nbsp;
			<a onclick="return confirm('Checkin as staff?')" href="`+getURL()+`&guest_status=6&guest_id=`+guest.id+`">
				<i class="fas fa-user-tie text-secondary"></i>
			</a> -->`;

		} else if (guest.status == 3) {

			return `<!-- <a onclick="return confirm('Guest check in?')" href="`+getURL()+`&guest_status=2&guest_id=`+guest.id+`">
				<i class="fas fa-check text-secondary"></i>
			</a>&nbsp;&nbsp;
			<a onclick="return confirm('Reject guest?')" href="`+getURL()+`&guest_status=4&guest_id=`+guest.id+`">
				<i class="far fa-times-circle text-secondary"></i>
			</a>&nbsp;&nbsp;
			<a onclick="return confirm('Guest check out?')" href="`+getURL()+`&guest_status=3&guest_id=`+guest.id+`">
				<i class="fas fa-door-open text-warning"></i>
			</a>&nbsp;&nbsp;
			<a onclick="return confirm('Report guest as infected?')" href="`+getURL()+`&guest_status=5&guest_id=`+guest.id+`">
				<i class="fas fa-exclamation text-secondary"></i>
			</a>&nbsp;&nbsp;
			<a onclick="return confirm('Checkin as staff?')" href="`+getURL()+`&guest_status=6&guest_id=`+guest.id+`">
				<i class="fas fa-user-tie text-secondary"></i>
			</a> -->`;

		} else if (guest.status == 4) {

			return `<!-- <a onclick="return confirm('Guest check in?')" href="`+getURL()+`&guest_status=2&guest_id=`+guest.id+`">
				<i class="fas fa-check my-check-icon text-secondary"></i>
			</a>&nbsp;&nbsp;
			<a onclick="return confirm('Reject guest?')" href="`+getURL()+`&guest_status=4&guest_id=`+guest.id+`">
				<i class="far fa-times-circle my-delete-icon text-danger"></i>
			</a>&nbsp;&nbsp;
			<a onclick="return confirm('Guest check out?')" href="`+getURL()+`&guest_status=3&guest_id=`+guest.id+`">
				<i class="fas fa-door-open text-secondary"></i>
			</a>&nbsp;&nbsp;
			<a onclick="return confirm('Report guest as infected?')" href="`+getURL()+`&guest_status=5&guest_id=`+guest.id+`">
				<i class="fas fa-exclamation text-secondary"></i>
			</a>&nbsp;&nbsp;
			<a onclick="return confirm('Checkin as staff?')" href="`+getURL()+`&guest_status=6&guest_id=`+guest.id+`">
				<i class="fas fa-user-tie text-secondary"></i>
			</a> -->`;

		} else if (guest.status == 5) {

			return `<!-- <a onclick="return confirm('Guest check in?')" href="`+getURL()+`&guest_status=2&guest_id=`+guest.id+`">
				<i class="fas fa-check my-check-icon text-secondary"></i>
			</a>&nbsp;&nbsp;
			<a onclick="return confirm('Reject guest?')" href="`+getURL()+`&guest_status=4&guest_id=`+guest.id+`">
				<i class="far fa-times-circle my-delete-icon text-secondary"></i>
			</a>&nbsp;&nbsp;
			<a onclick="return confirm('Guest check out?')" href="`+getURL()+`&guest_status=3&guest_id=`+guest.id+`">
				<i class="fas fa-door-open text-secondary"></i>
			</a>&nbsp;&nbsp;
			<a onclick="return confirm('Report guest as infected?')" href="`+getURL()+`&guest_status=5&guest_id=`+guest.id+`">
				<i class="fas fa-exclamation text-danger"></i>
			</a>&nbsp;&nbsp;
			<a onclick="return confirm('Checkin as staff?')" href="`+getURL()+`&guest_status=6&guest_id=`+guest.id+`">
				<i class="fas fa-user-tie text-secondary"></i>
			</a> -->`;
		} else if (guest.status == 6) {

			return `<!-- <a onclick="return confirm('Guest check in?')" href="`+getURL()+`&guest_status=2&guest_id=`+guest.id+`">
				<i class="fas fa-check my-check-icon text-secondary"></i>
			</a>&nbsp;&nbsp;
			<a onclick="return confirm('Reject guest?')" href="`+getURL()+`&guest_status=4&guest_id=`+guest.id+`">
				<i class="far fa-times-circle my-delete-icon text-secondary"></i>
			</a>&nbsp;&nbsp; -->
			<a onclick="return confirm('Guest check out?')" href="`+getURL()+`&guest_status=3&guest_id=`+guest.id+`">
				<i class="fas fa-door-open text-secondary"></i>
			</a>&nbsp;&nbsp;
			<!-- <a onclick="return confirm('Report guest as infected?')" href="`+getURL()+`&guest_status=5&guest_id=`+guest.id+`">
				<i class="fas fa-exclamation text-secondary"></i>
			</a>&nbsp;&nbsp;
			<a onclick="return confirm('Checkin as staff?')" href="`+getURL()+`&guest_status=6&guest_id=`+guest.id+`">
				<i class="fas fa-user-tie text-primary"></i>
			</a> -->`;
		} else {
			return `<span class="text-danger">WRONG STATUS</span>`;
		}
	}


	$('.getUserInfo').click(function(e) {
		e.preventDefault();

		var guest_id = $(this).attr('data-guest-id');

		$.ajax({
			url: 'ajax.php',
			method: 'GET',
			data: {
				'get_guest_info': true,
				'id': guest_id
			},
			success: function(data) {
				var user = data;
				var modalBody = $('#userInfoModal').find('.modal-body');

				if (user.length == 0) {
					$(modalBody).html($(`<div class="alert alert-danger alert-dismissible">
					  <button type="button" class="close" data-dismiss="alert">&times;</button>
					  <strong>Error!</strong> Guest information not available!
					</div>`));

					$('#userInfoModal').modal();
				} else {
					var modalBodyHtml = 
					`<div class="row">
						<div class="col-md-5">
							<ul class="list-group">
							  <li class="list-group-item d-flex justify-content-between align-items-center">
							    <strong>Event:</strong>
							    <span class="">${capitalizeFirstLetter(user.event_name)}</span>
							  </li>
							  <li class="list-group-item d-flex justify-content-between align-items-center">
							    <strong>Guest name:</strong>
							    <span class="">${capitalizeFirstLetter(user.name)}</span>
							  </li>
							  <li class="list-group-item d-flex justify-content-between align-items-center">
							    <strong>Guest surename:</strong>
							    <span class="">${capitalizeFirstLetter(user.surename)}</span>
							  </li>
							  <li class="list-group-item d-flex justify-content-between align-items-center">
							    <strong>Street nr:</strong>
							    <span class="">${capitalizeFirstLetter(user.street_nr)}</span>
							  </li>
							  <li class="list-group-item d-flex justify-content-between align-items-center">
							    <strong>Plz:</strong>
							    <span class="">${user.plz}</span>
							  </li>
							</ul>
						</div>
						<div class="col-md-5">
							<ul class="list-group">
								<li class="list-group-item d-flex justify-content-between align-items-center">
							    <strong>City:</strong>
							    <span class="">${user.city}</span>
							  </li>
							  <li class="list-group-item d-flex justify-content-between align-items-center">
							    <strong>Phone:</strong>
							    <span class="">${user.phone}</span>
							  </li>
							  <li class="list-group-item d-flex justify-content-between align-items-center">
							    <strong>Status:</strong>
							    <span class="">${guestStatus(user.status)}</span>
							  </li>
							  <!-- <li class="list-group-item d-flex justify-content-between align-items-center">
							    <strong>Checkin time:</strong>
							    <span class="">${ user.checkin_time == null ? '-': user.checkin_time }</span>
							  </li>
							  <li class="list-group-item d-flex justify-content-between align-items-center">
							    <strong>Checkout time:</strong>
							    <span class="">${ user.checkout_time == null ? '-': user.checkout_time }</span>
							  </li> -->
							</ul>
						</div>
					</div>

					<hr>

					<div class="row">
						<div class="col-md-5">
							<h4 class="d-inline-block">Action:</h4>
							<span class="float-right">
								`+getFourIcons(user)+`
							</span>
						</div>
					</div>
					`;

					$(modalBody).html($(modalBodyHtml));
					$('#userInfoModal').modal();
				}
			},
			error: function(error) {
				console.log(error);
			}
		});
		
	});
});
</script>
<?php require 'includes/footer.php'; ?>