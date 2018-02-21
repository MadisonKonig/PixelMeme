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
        $pic_info = getimagesize($_FILES['imageToUpload']['tmp_name']);
        $file_ext = image_type_to_extension($pic_info[2]);
        if($file_ext == '.jpg' or $file_ext == '.png' or $file_ext == '.jpeg'){
            if($valid_file) {
                move_uploaded_file($_FILES['imageToUpload']['tmp_name'], 'meme_map/'.$_FILES['imageToUpload']['name']);
            }
        }


    }
}



$photo_added = imagecreatefromjpeg('meme_map/'.$_FILES['imageToUpload']['name']);
$meme_map = imagecreatefrompng('meme_map/meme-map(1).png');
$meme_map_check = imagecreatefromstring(file_get_contents('meme_map/meme-map(1).png'));

//takes user's clicked square
$grab_width = $_POST['photoWidth'];
$grab_height = $_POST['photoHeight'];

//defines it to be a grid
$grab_width -= ($grab_width%10);
$grab_height -= ($grab_height%10);

//size of the photo once added
$added_photo_height = $added_photo_width = $_POST['size'];

$rgb = imagecolorat($meme_map_check, $grab_width, $grab_height);
$colours = imagecolorsforindex($meme_map_check, $rgb);
/*if the pixel is blank*/
if($colours['red'] == 0 and $colours['green'] == 0 and $colours['blue'] == 0 and $colours['alpha'] == 0){
    imagecopyresampled($meme_map, $photo_added, $grab_width, $grab_height,0, 0, $added_photo_width, $added_photo_height, $pic_info[0], $pic_info[1]);
    imagepng($meme_map, 'meme_map/meme-map(1).png');
    header('Location: buyPixel.html');
    exit;
} else {
    header('Location: buyPixel.html');
    exit;
}
#echo '<img src=meme_map/' . $_FILES['imageToUpload']['name'] . ' alt="hello world">';
