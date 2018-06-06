<h1 class="text-center"></h1>
<?php
//Get tokens from url
if($_GET){
    $selector = $_GET['selector'];
    $validator = $_GET['validator'];

    //Check whether tokens are hexadecimal
    if(ctype_xdigit($selector)===true && ctype_xdigit($validator)===true){
        ?>
            <form action="reset_process.php" method="post">
                <?php
                
                ?>
                <input type="hidden" name="selector" value="<?php echo $selector; ?>">
                <input type="hidden" name="validator" value="<?php echo $validator; ?>">
                <input type="password" class="text" name="password" placeholder="Enter your new password">
                <input type="submit" class="submit" value="Submit">
            </form>
            <p><a href="index.php">Login here</a></p>
        <?php
    }
}

?>


    

