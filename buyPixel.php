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
/*
 * don't store the photo's on your server !!TO DO!!
 *
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

//passing the uploaded image to a new file
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


$whitePixel = true;
//need to look through each pixel, and see if the area is in use or not.
//loop through the width
for($checkPixelWidth = $grab_width; $checkPixelWidth < $grab_width + $added_photo_width; ++$checkPixelWidth){

    //loop through the height
    for($checkPixelHeight = $grab_height; $checkPixelHeight < $grab_height + $added_photo_height; ++$checkPixelHeight){

        $rgb = imagecolorat($meme_map_check, $checkPixelWidth, $checkPixelHeight);
        $colours = imagecolorsforindex($meme_map_check, $rgb);
        if(!($colours['red'] == 255 and $colours['green'] == 255 and $colours['blue'] == 255 and $colours['alpha'] == 0)){
            $whitePixel = false;
            break;
        }
    }
    //if any of the pixels are white, then we can just leave the loop checker completely.
    if($whitePixel === false) {
        break;
    }

}

//make sure we can open the file
$link_grab = $_POST['hyperLink'];
$arraylink = "./meme_map/links.txt";
$file = fopen($arraylink, "a") or die("Unable to open file");

/*if the pixel is blank*/
if($whitePixel === true) {
    //Order for input of txt
    //image width, image height, image size, link
    fwrite($file, $grab_width. "," .$grab_height. "," .$added_photo_height. "," .$link_grab. "\n");
    fclose($file);

    //check to make sure that the file is added, and can be opened, then add it to the map
    imagecopyresampled($meme_map, $photo_added, $grab_width, $grab_height,0, 0, $added_photo_width, $added_photo_height, $pic_info[0], $pic_info[1]);
    imagepng($meme_map, 'meme_map/meme-map(1).png');

    //quit and return
    header('Location: buyPixel.html');
    exit;
} else {

    //quit and return
    header('Location: buyPixel.html');
    exit;
}
#echo '<img src=meme_map/' . $_FILES['imageToUpload']['name'] . ' alt="hello world">';
