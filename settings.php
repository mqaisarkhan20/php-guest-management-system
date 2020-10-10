<?php 

require 'includes/config.php';

if (!isset($_SESSION['role'])) {
	header("Location: " . URL . "login.php");
	exit;
} else if ($_SESSION['role'] !== 'admin') {
	header("Location: " . URL . "login.php");
	exit;
}

$current_user_id = $_SESSION['id'];
if (isset($_POST['submit'])) {
	$user_id = clean_input($_POST['user_id']);
	$old_password = clean_input($_POST['old_password']);
	$new_password = clean_input($_POST['new_password']);
	$confirm_password = clean_input($_POST['confirm_password']);

	$user = $db->single_row("SELECT * FROM users WHERE id = '$user_id'");

	if (isset($user['username'])) {
		if (password_verify($old_password, $user['password'])) {
		  $password = password_hash($new_password, PASSWORD_BCRYPT, array('cost' => 14));

		  $data = Array(
		  	'password' => $password
		  );

		  if ($db->update('users', $data, ['id' => $user_id])) {
		  	$_SESSION['message'] = '<div class="alert alert-success alert-dismissible">
				  <button type="button" class="close" data-dismiss="alert">&times;</button>
				  <strong>Error!</strong> Username/password updated successfully.
				</div>';

			  header('Location: ' . URL . 'settings.php');
			  exit;
		  }
		} else {
		  $_SESSION['message'] = '<div class="alert alert-danger alert-dismissible">
			  <button type="button" class="close" data-dismiss="alert">&times;</button>
			  <strong>Error!</strong> Incorrect old password.
			</div>';

		  header('Location: ' . URL . 'settings.php');
		  exit;
		}
	}
}

$user = $db->single_row("SELECT * FROM users WHERE id = '$current_user_id'");
$all_users = $db->multiple_row("SELECT * FROM users WHERE id = 1 OR id = 2");
$nav_active = 'settings';

require 'includes/header.php';
require 'includes/navigation.php';
require 'includes/sidebar.php';

?>

<div class="container pt-4">
	<h3>Settings:</h3>
	<form name="admin_setting" action="" method="POST">
		<div class="row">
			<div class="col-md-4 col-sm-6">
				<?= (isset($_SESSION['message'])) ? $_SESSION['message']: false; ?>
				<?php unset($_SESSION['message']); ?>

				<div class="message-box">
					
				</div>

				<div class="form-group">
					<select name="user_id" id="" class="form-control">
						<option value="">Select user:</option>
						<?php foreach ($all_users as $user): ?>
				      <option value="<?= $user['id'] ?>"><?= $user['username'] ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="form-group">
					<label for="">Old password:</label>
					<input type="password" name="old_password" class="form-control">
				</div>

				<div class="form-group">
					<label for="">New password:</label>
					<input type="password" name="new_password" class="form-control">
				</div>

				<div class="form-group">
					<label for="">Confirm password:</label>
					<input type="password" name="confirm_password" class="form-control">
				</div>

				<div class="form-group">
					<input type="submit" name="submit" value="Submit" class="btn btn-primary btn-sm">
				</div>
			</div>
		</div>
	</form>
	

</div>

<script>
$(document).ready(function() {

	$('form[name="admin_setting"]').submit(function(e) {
		$('.alert').remove();
		

		var user_id = ($('select[name="user_id"]').val()).trim();
		var old_password = ($('input[name="old_password"]').val()).trim();
		var new_password = ($('input[name="new_password"]').val()).trim();
		var confirm_password = ($('input[name="confirm_password"]').val()).trim();

		if (user_id == '') {
			e.preventDefault();
			var message = $(`<div class="alert alert-danger alert-dismissible" style="display: none;">
			  <button type="button" class="close" data-dismiss="alert">&times;</button>
			  <strong>Error!</strong> Select user.
			</div>`);
			$('.message-box').html(message);
			$(message).fadeIn();
		} else if (old_password == '' || new_password == '' || confirm_password == '') {
			e.preventDefault();
			var message = $(`<div class="alert alert-danger alert-dismissible" style="display: none;">
			  <button type="button" class="close" data-dismiss="alert">&times;</button>
			  <strong>Error!</strong> All fields are required.
			</div>`);
			$('.message-box').html(message);
			$(message).fadeIn();
		} else if (old_password == new_password) {
			e.preventDefault();
			var message = $(`<div class="alert alert-danger alert-dismissible" style="display: none;">
			  <button type="button" class="close" data-dismiss="alert">&times;</button>
			  <strong>Error!</strong> New password must be different.
			</div>`);
			$('.message-box').html(message);
			$(message).fadeIn();
		} else if (new_password != confirm_password) {
			e.preventDefault();
			var message = $(`<div class="alert alert-danger alert-dismissible" style="display: none;">
			  <button type="button" class="close" data-dismiss="alert">&times;</button>
			  <strong>Error!</strong> Password not same.
			</div>`);
			$('.message-box').html(message);
			$(message).fadeIn();
		}
	});

});
</script>
<?php require 'includes/footer.php'; ?>