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
$photo_added = imagecreatefromstring(file_get_contents('meme_map/'.$_FILES['imageToUpload']['name']));
$meme_map = imagecreatefromstring(file_get_contents("meme_map/meme-map.png"));
$rgb = imagecolorat($meme_map, 0, 0);
$colours = imagecolorsforindex($meme_map, $rgb);

/*if the pixel is blank*/
if($colours['red'] == 255 and $colours['green'] == 255 and $colours['blue'] == 255 and @$colours['alpha'] == 0){
    /*
     *
     *
     * THIS DOESN'T WORK
     *
     * 
    */
    /*imagecopyresampled($dst_img, $src_img, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h,$src_w, $src_h)*/
    imagecopyresampled($meme_map, $photo_added, 100, 100,0, 0, $pic_info[0], $pic_info[1],$pic_info[0], $pic_info[1]);
    /*header('Location: mememain.html');
    exit;*/
    echo '<img src=meme_map/meme-map.png alt="hello world">';
}


echo '<img src=meme_map/' . $_FILES['imageToUpload']['name'] . ' alt="hello world">';
