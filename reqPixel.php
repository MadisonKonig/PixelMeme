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
use Google\Cloud\OsLogin\Common;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
$test = new Common\PosixAccount();
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



$coordArray = [];
for($x = 0; $x < $_POST['size']; ++$x){
	for($y = 0; $y < $_POST['size']; $y++){
		$coordArray[$x * $_POST['size'] + $y] = ($grab_width + $x) . "," . ($grab_height + $y);
	}
}

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


//(col)coords -> (doc)addedCoords -> (col)coord -> (doc)x,y






if($canUploadPhoto === true) {
	//Loop through all the added coord's, and check if they're taken
	foreach ($coords as $coord) {
		if (in_array($coord->id(), $coordArray)) {
			//FOUND COORD IN USE IN DATABASE
			//REDIRECT BACK
			echo "found";
			header("Location: reqPixel.html");
		}
	}
	for($x = 0; $x < $_POST['size']; $x++){
		for($y = 0; $y < $_POST['size']; $y++){
			//COORD NOT FOUND
			$firestore->collection('coords')->document('request-coords')->
			collection($userid."_".$time)->document($coordArray[$x*$_POST['size']+$y])->set([
				'x' => $grab_width + $x,
				'y' => $grab_height + $y,
				'time' => $time
			]);
		}
	}
	$ext = pathinfo($_FILES['imageToUpload']['name'], PATHINFO_EXTENSION);
	$bucket = $storage->bucket($bucketid);
	$imageUploadData = $bucket->upload(fopen($_FILES['imageToUpload']['tmp_name'], 'r'), [
		'name' => "req-memes/" . $userid . "_". $time . ".".$ext
	]);
	$urlOfImage = $imageUploadData->signedUrl(new \Google\Cloud\Core\Timestamp(new \DateTime('tomorrow')));
	echo $urlOfImage.'<br><br>';


	$mail = new PHPMailer(true);
	try{
		$mail -> SMTPDebug = 2;
		$mail -> isSMTP();
		$mail -> Host = 'ssl://stmp.gmail.com';
		$mail -> Port = 587;
		$mail -> SMTPSecure = 'tls';
		$mail -> SMTPAuth = true;
		$mail -> Username = "tallguyking@gmai.com";
		$mail -> Password = "9Jesus921";
		$mail -> setFrom('tallguyking@gmail.com');
		$mail -> addReplyTo('robmkoenig@gmail.com');
		$mail -> addAddress('tallguyking@gmail.com');
		$mail -> Subject = 'PHPMailer GMAIL test';
		$mail -> Body = "test: " . $urlOfImage;
		$mail -> AltBody = 'This is plain text message body';
		if(!$mail -> send()){
			echo "Mailer Error: ". $mail->ErrorInfo;
		} else {
			echo 'Message Sent!';
		}
	}
	catch (Exception $e){
		echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
	}

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


//
////passing the uploaded image to a new file
//$photo_added = imagecreatefromjpeg('meme_map/'.$_FILES['imageToUpload']['name']);
//$meme_map = imagecreatefrompng('meme_map/meme-map(1).png');
//$meme_map_check = imagecreatefromstring(file_get_contents('meme_map/meme-map(1).png'));
//

//

//

//
//
//$whitePixel = true;
////need to look through each pixel, and see if the area is in use or not.
////loop through the width
//for($checkPixelWidth = $grab_width; $checkPixelWidth < $grab_width + $added_photo_width; ++$checkPixelWidth){
//
//    //loop through the height
//    for($checkPixelHeight = $grab_height; $checkPixelHeight < $grab_height + $added_photo_height; ++$checkPixelHeight){
//
//        $rgb = imagecolorat($meme_map_check, $checkPixelWidth, $checkPixelHeight);
//        $colours = imagecolorsforindex($meme_map_check, $rgb);
//        if(!($colours['red'] == 255 and $colours['green'] == 255 and $colours['blue'] == 255 and $colours['alpha'] == 0)){
//            $whitePixel = false;
//            break;
//        }
//    }
//    //if any of the pixels are white, then we can just leave the loop checker completely.
//    if($whitePixel === false) {
//        break;
//    }
//}
//
////make sure we can open the file
//$link_grab = $_POST['hyperLink'];
//$arraylink = "./meme_map/links.txt";
//$file = fopen($arraylink, "a") or die("Unable to open file");

/*if the pixel is blank*/
//if($whitePixel === true) {
//    //Order for input of txt
//    //image width, image height, image size, link
//    fwrite($file, $grab_width. "," .$grab_height. "," .$added_photo_height. "," .$link_grab. "\n");
//    fclose($file);
//
//    //check to make sure that the file is added, and can be opened, then add it to the map
//    imagecopyresampled($meme_map, $photo_added, $grab_width, $grab_height,0, 0, $added_photo_width, $added_photo_height, $pic_info[0], $pic_info[1]);
//    imagepng($meme_map, 'meme_map/meme-map(1).png');
//
//    //quit and return
//    header('Location: reqPixel.html');
//    exit;
//} else {
//
//    //quit and return
//    header('Location: reqPixel.html');
//    exit;
//}
#echo '<img src=meme_map/' . $_FILES['imageToUpload']['name'] . ' alt="hello world">';
