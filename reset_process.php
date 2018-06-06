<?php
require_once('load.php');
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $reset = $login->reset_password($_POST);
}else{
    $reset = array('status'=>0, 'message'=>'There was an error in resetting your password.');
}
include('header.php');
?>
<div class="wrapper">
    <h1 class="text-center"></h1>
    <?php
    if(isset($reset)){
        if($reset['status']==1){
            $class = 'success';
        }else{
            $class = 'error';
        }
        ?>
        <div class='message <?php echo $class; ?>'>
            <?php echo $reset['message']; ?>
        </div>
        <?php
    }
    ?>
</div>
<?php include('footer.php'); ?>