<?php 

require 'includes/config.php';

if (isset($_SESSION['username'])) {
	header('Location: ' . URL);
	exit;
}


if (isset($_POST['sign_in'])) {
	$username = clean_input($_POST['username']);
	$password = clean_input($_POST['password']);

	$user = $db->single_row("SELECT * FROM users WHERE username = '$username'");
	if (count($user) == 0) {
		$_SESSION['message'] = '<div class="alert alert-danger alert-dismissible">
		  <button type="button" class="close" data-dismiss="alert">&times;</button>
		  <strong>Error!</strong> Wrong username or password.
		</div>';

		header('location: ' . URL . 'login.php');
		exit;
	} else if (!password_verify($password, $user['password'])) {
		$_SESSION['message'] = '<div class="alert alert-danger alert-dismissible">
		  <button type="button" class="close" data-dismiss="alert">&times;</button>
		  <strong>Error!</strong> Wrong username or password.
		</div>';

		header('location: ' . URL . 'login.php');
		exit;
	} else {
		$_SESSION['username'] = $user['username'];
		$_SESSION['role'] = $user['role'];
		$_SESSION['id'] = $user['id'];
		
		header('location: ' . URL);
		exit;
	}
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Admin - Login</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- CSS LIBRARIES -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.0/css/all.min.css" />

  <!-- JAVASCRIPT LIBRARIES -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>

	<!-- Custom styles for this template -->
	<style>
		html,
		body {
		  height: 100%;
		}

		body {
		  display: -ms-flexbox;
		  display: -webkit-box;
		  display: flex;
		  -ms-flex-align: center;
		  -ms-flex-pack: center;
		  -webkit-box-align: center;
		  align-items: center;
		  -webkit-box-pack: center;
		  justify-content: center;
		  padding-top: 40px;
		  padding-bottom: 40px;
		  background-color: #f5f5f5;
		}

		.form-signin {
		  width: 100%;
		  max-width: 330px;
		  padding: 15px;
		  margin: 0 auto;
		}
		.form-signin .checkbox {
		  font-weight: 400;
		}
		.form-signin .form-control {
		  position: relative;
		  box-sizing: border-box;
		  height: auto;
		  padding: 10px;
		  font-size: 16px;
		}
		.form-signin .form-control:focus {
		  z-index: 2;
		}
		.form-signin input[type="email"] {
		  margin-bottom: -1px;
		  border-bottom-right-radius: 0;
		  border-bottom-left-radius: 0;
		}
		.form-signin input[type="password"] {
		  margin-bottom: 10px;
		  border-top-left-radius: 0;
		  border-top-right-radius: 0;
		}
	</style>
</head>

<body class="text-center">
  <form class="form-signin" method="POST" action="">
    <!-- <img class="mb-4" src="https://getbootstrap.com/docs/4.0/assets/brand/bootstrap-solid.svg" alt="" width="72" height="72"> -->
    <h1 class="h3 mb-3 font-weight-normal">Sign in</h1>
    <?= (isset($_SESSION['message'])) ? $_SESSION['message']: false; ?>
    <?php unset($_SESSION['message']); ?>
    <label for="username" class="sr-only">Username</label>
    <input type="text" id="username" name="username" class="form-control" placeholder="Email address"  autofocus>
    <label for="inputPassword" class="sr-only">Password</label>
    <input type="password" id="inputPassword" name="password" class="form-control" placeholder="Password" >
    <div class="checkbox mb-3">
      <!-- <label>
        <input type="checkbox" value="remember-me"> Remember me
      </label> -->
    </div>
    <button class="btn btn-lg btn-primary btn-block" name="sign_in" type="submit">Submit</button>
    <!-- <p class="mt-5 mb-3 text-muted">&copy; 2017-2018</p> -->
  </form>

<script>
$(document).ready(function() {
	$('form.form-signin').submit(function(e) {
		$('.alert').remove();

		var username = ($('input[name="username"]').val()).trim();
		var password = ($('input[name="password"]').val()).trim();

		if (username == '' || password == '') {
			e.preventDefault();
			var message = $(`<div class="alert alert-danger alert-dismissible" style="display: none;">
			  <button type="button" class="close" data-dismiss="alert">&times;</button>
			  <strong>Error!</strong> All fields are required.
			</div>`);
			$('form.form-signin > h1').after(message);
			$(message).fadeIn();
		}
	})
});
</script>
</body>
</html>
