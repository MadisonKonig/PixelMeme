<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 12/04/18
 * Time: 4:54 PM
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

$addedCoords = $firestore->collection('coords')->document('-coords');
$coords = $addedCoords->collection('savedcoords')->documents();

$url = $_GET['url'];
$ar = explode("_", $url);
$userid = $ar[0];
$getTime = $ar[1];
$reqX = $ar[2];
$reqY = $ar[3];
$reqSize = $ar[4];
$ext = $ar[5];
$pass = $ar[6];

if($pass === 't'){
	$fileName = $userid."_".$getTime.".".$ext;
	$memepicDL = $storage->bucket($bucketid)->object("req-memes/". $fileName)
	->downloadToFile('/var/www/html/PixelMeme/meme_map/'. $fileName);

	$mememapDL = $storage->bucket($bucketid)->object("meme-map/meme-map.png")
	->downloadToFile('/var/www/html/PixelMeme/meme_map/meme-map(2).png');

	$memePic = imagecreatefromjpeg('meme_map/'.$fileName);
	$mememapPic = imagecreatefrompng('meme_map/meme-map(2).png');

	//YOU ARE CURRENTLY TRANSFERING IT OVER, great job working out bud

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
		header('Location: reqPixel.html');
		exit;
	} else {

		//quit and return
		header('Location: reqPixel.html');
		exit;
	}

}


//$bucket->object()
