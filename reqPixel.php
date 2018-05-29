<?php
/**
 * Created by PhpStorm.
 * User: robkoenig
 * Date: 08/02/18
 * Time: 12:21 PM
 */


//Variables needed
$file1 = "./PixelMeme-2643998ac4ab.json";
putenv("GOOGLE_APPLICATION_CREDENTIALS=$file1");

require 'vendor/autoload.php';

use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Storage\StorageClient;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
$bucketid = 'pixelmeme-4e7cf.appspot.com';
//$firestore = new FirestoreClient(['projectId' => 'pixelmeme-4e7cf']);
$storage = new StorageClient(['projectId' => 'pixelmeme-4e7cf']);
$userid = $_POST['userid'];
$time = time();
$grab_width = $_POST['photoWidth'];
$grab_height = $_POST['photoHeight'];

$target_file = $_FILES['imageToUpload']['name'];

$canUploadPhoto = false;



//defines it to be a "square"
//$grab_width -= ($grab_width%10);
//$grab_height -= ($grab_height%10);

////size of the photo once added
//$grab_height = $added_photo_width = $_POST['size'];





echo '<br><br><br>';

////determine if the file is a image

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
            	$canUploadPhoto = true;
            }
        }
    }
}

if($canUploadPhoto === true) {
	$ext = pathinfo($_FILES['imageToUpload']['name'], PATHINFO_EXTENSION);
	$bucket = $storage->bucket($bucketid);
	$imageUploadData = $bucket->upload(fopen($_FILES['imageToUpload']['tmp_name'], 'r'), [
		'name' => "req-memes/" . $userid . "_". $time . ".".$ext
	]);

	$urlOfImage = $imageUploadData->signedUrl(new \Google\Cloud\Core\Timestamp(new \DateTime('tomorrow')));
	echo $urlOfImage.'<br><br>';

	//Approve
	echo '<a href=http://localhost/PixelMeme/checkPixel.php?url='.
		$userid.'_'.
		$time.'_'.
		$grab_width.'_'.
		$grab_height.'_'.
		$_POST['size'].'_'.
		$ext.'_'.
		$pic_info[0].'_'.
		$pic_info[1].'_1_'.
		$urlOfImage.
		'>Approve</a><br><br>';
	//Nope
	echo '<a href=http://localhost/PixelMeme/checkPixel.php?url='.
		$userid.'_'.
		$time.'_'.
		$grab_width.'_'.
		$grab_height.'_'.
		$_POST['size'].'_'.
		$ext.'_'.
		$pic_info[0].'_'.
		$pic_info[1].'_0_'.
		$urlOfImage.
		'>Nope</a>';

} else {
	echo "false, no image uploaded<br>
			<a href='index.html'>Home</a> ";

}