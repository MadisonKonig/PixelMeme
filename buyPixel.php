<?php
/**
 * Created by PhpStorm.
 * User: robkoenig
 * Date: 08/02/18
 * Time: 12:21 PM
 */
/*
 * This is to make sure the uploaded file is an image
 */

$target_file = $_FILES['imageToUpload']['name'];
if($target_file) {
    if(!$_FILES['imageToUpload']['error']){
        $valid_file = true;
        $new_file_name = strtolower($_FILES['imageToUpload']['tmp_name']);
        if($_FILES['imageToUpload']['size'] > 1024000) {
            $valid_file = false;
            $message = 'Error!  Your file\'s size is too large!';
        }
        if($valid_file) {
            move_uploaded_file($_FILES['imageToUpload']['tmp_name'], 'meme_map/'.$_FILES['imageToUpload']['name']);
        }

    }
}

echo '<img src=meme_map/' . $_FILES['imageToUpload']['name'] . ' alt="hello world">';
