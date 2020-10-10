<?php 

require 'includes/config.php';

$nav_active = "success";
// $guests = $db->multiple_row($guests_query);

require 'includes/header.php';
// require 'includes/navigation.php';
require 'includes/sidebar.php';
?>
<div class="container pt-4">
	<div class="row">
		<div class="col-md-12">
		<?php if (isset($_SESSION['message'])) {
			echo $_SESSION['message'];
			unset($_SESSION['message']);
		} ?>

			<p>You html here ...</p>
		</div>
	</div>
</div>


<script>
$(document).ready(function() {
});
</script>
<?php require 'includes/footer.php'; ?>