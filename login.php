<?php 
include('header.php');

?>
<div class="wrapper">
    <form action="index.php" method="post">
        <h1 class="text-center">Login</h1>
        <?php if ( isset( $login_status ) && false == $login_status ) : ?>
        <div class="error">
            <p>Your username and password are incorrect. Try again.</p>
        </div>
        <?php endif; ?>
        <input type="text" class="text" name="username" placeholder="Enter username" required>
        <input type="password" class="text" name="password" placeholder="Enter password" required>
        <input type="submit" class="submit" value="Log in">
        <p><input type="checkbox" name="rememberme" value="1">Remember me</p>
    </form>
    <p><a href="register.php">Register here</a></p>
    <p><a href="lostpassword.php">Reset password</a></p>
</div>
<?php include('footer.php'); ?>