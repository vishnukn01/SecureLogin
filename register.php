<?php
require_once('load.php');

if($_SERVER['REQUEST_METHOD'] == 'POST'){

	$registration = $login->register($_POST);
}

include('header.php');
?>

<div class="wrapper">
	
	<form action="" method="POST">
		<h1 class="text-center">Register</h1>
		<?php
			if(isset($registration)){

				if($registration['status'] == 0){
					$class = 'error';
				}
				if($registration['status'] == 1){
					$class = 'success';
				}
				?>
					<div class="message <?php echo $class; ?>">
						<?php
						echo $registration['message'];
						?>
					</div>
				<?php
			}
		?>
		<input type="text" class="text" name="fullname" placeholder="Enter your full name" required>
		<input type="text" class="text" name="username" placeholder="Enter a username" required>
		<input type="email" class="text" name="email" placeholder="Enter email address" required>
		<input type="password" class="text" name="password" placeholder="Enter a password" required>
		<input type="submit" class="submit" value="Register">
	</form>
	<p><a href="index.php">Have an account? Log in</a></p>

</div>

<?php
include('footer.php');
?>