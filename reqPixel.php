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
$firestore = new FirestoreClient(['projectId' => 'pixelmeme-4e7cf']);
$storage = new StorageClient(['projectId' => 'pixelmeme-4e7cf']);
$userid = $_POST['userid'];
$time = time();
$grab_width = $_POST['photoWidth'];
$grab_height = $_POST['photoHeight'];

$target_file = $_FILES['imageToUpload']['name'];

$canUploadPhoto = false;

$addedCoords = $firestore->collection('coords')->document('-coords');
$coords = $addedCoords->collection('savedcoords')->documents();

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
	echo $pic_info[0].'<br>'.$pic_info[1].'<br>';

	//The link of what I'd need
	//userid    time   x   y   sizeOfSquare    imageExtension   ImgWidth   ImgHeight   (1 or 0)   url to image



//	$mail = new PHPMailer(true);
//	try{
//		$mail -> SMTPDebug = 2;
//		$mail -> isSMTP();
//		$mail -> Host = 'ssl://stmp.gmail.com';
//		$mail -> Port = 587;
//		$mail -> SMTPSecure = 'tls';
//		$mail -> SMTPAuth = true;
//		$mail -> Username = "tallguyking@gmai.com";
//		$mail -> Password = "9Jesus921";
//		$mail -> setFrom('tallguyking@gmail.com');
//		$mail -> addReplyTo('robmkoenig@gmail.com');
//		$mail -> addAddress('tallguyking@gmail.com');
//		$mail -> Subject = 'PHPMailer GMAIL test';
//		$mail -> Body = "test: " . $urlOfImage;
//		$mail -> AltBody = 'This is plain text message body';
//		if(!$mail -> send()){
//			echo "Mailer Error: ". $mail->ErrorInfo;
//		} else {
//			echo 'Message Sent!';
//		}
//	}
//	catch (Exception $e){
//		echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
//	}

//	$to = 'tallguyking@gmail.com';
//	$sub = 'map for you - a meme project';
//	$mess = 'Which will you decide?\r\n
//	Image : '.$urlOfImage;
//	$mess = wordwrap($mess);
//	$headers = [
//		'From' => 'tallguyking@gmail.com',
//		'Reply-To' => 'tallguyking@gmail.com',
//		'X-Mailer' => 'PHP/'.phpversion()
//	];
//	print_r($headers);
//	if(mail($to, $sub, $mess, $headers)){
//		echo 'Mail sent!!';
//	}

} else {
	echo "false, no image uploaded";
}