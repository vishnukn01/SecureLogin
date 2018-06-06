<?php
require_once('load.php');

if($_SERVER['REQUEST_METHOD']=='POST'){
    $status = $login->lost_password($_POST);
}

include('header.php');
?>
<div class="wrapper">
    <form action="" method="post">
        <h1 class="text-center">Lost Password</h1>
        <?php
        if(isset($status)){
            if($status['status']==true){
                $class = 'success';
            }else{
                $class = 'error';
            }
            echo "
                <div class='message ".$class."'>
                    ".$status['message']."
                </div>
                 ";
        }
        ?>
        <input type="email" class="text" name="email" id="email" placeholder="Enter your email" required>
        <input type="submit" class="submit" value="Submit">
    </form>
    <p><a href="index.php">Login here</a></p>
</div>