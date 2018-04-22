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
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Google\Cloud\Firestore\FieldValue;
$bucketid = 'pixelmeme-4e7cf.appspot.com';
$firestore = new FirestoreClient(['projectId' => 'pixelmeme-4e7cf']);
$storage = new StorageClient(['projectId' => 'pixelmeme-4e7cf']);



$url = $_GET['url'];
$ar = explode("_", $url);
$userid = $ar[0];
$getTime = $ar[1];
$reqX = $ar[2];
$reqY = $ar[3];
$reqSize = $ar[4];
$ext = $ar[5];
$memepicDLWidth = $ar[6];
$memepicDLHeight = $ar[7];
$pass = $ar[8];
$imgurl = $ar[9];


//the filename of the image
$fileName = $userid . "_" . $getTime . "." . $ext;

if($pass) {
	//create an array to hold the coords of the possible square
	$coordArray = [];
	for($x = 0; $x < $reqSize; ++$x){
		for($y = 0; $y < $reqSize; $y++){
			$coordArray[$x * $reqSize + $y] = ($reqX + $x) . "," . ($reqY + $y);
		}
	}
	$addedCoords = $firestore->collection('coords')->document('saved-coords')
		->collection('savedcoords')->documents();

	$notFound = true;
	//First check to see if the square is already taken or not
	foreach ($addedCoords as $coord) {
		if (in_array($coord->id(), $coordArray)) {
			$storage->bucket($bucketid)->object('req-memes/' . $fileName)->delete();
			//Implement some kind of email to user saying that spot has been taken
			$notFound = false;
			header("Location: reqPixel.html");
			exit();
		}
	}

	if ($notFound === true) {
		//Download the file to be added
		$memepicDL = $storage->bucket($bucketid)->object("req-memes/" . $fileName)
			->downloadToFile('meme_map/' . $fileName);

		//Download the current map
		$mememapDL = $storage->bucket($bucketid)->object("meme-map/meme-map.png")
			->downloadToFile('meme_map/meme-map(2).png');

		$memePic = imagecreatefromjpeg('meme_map/' . $fileName);
		$mememapPic = imagecreatefrompng('meme_map/meme-map(2).png');

		//defines it to be a grid/square
		$reqX -= ($reqX % 10);
		$reqY -= ($reqY % 10);

		$i = 0;
		foreach ($coordArray as $item) {
			$coords = explode(',', $coordArray[$i++]);
//			echo $c[0]."+".$c[1]."<br>";
			$firestore->collection('coords')->document('saved-coords')
				->collection('savedcoords')->document($coords[0] . "," . $coords[1])->set([
					'x' => $coords[0],
					'y' => $coords[1],
					'time' => $getTime,
					'user' => $userid,
					'file' => $fileName
				]);
		}
		imagecopyresampled($mememapPic, $memePic, $reqX, $reqY, 0, 0, $reqSize, $reqSize, $memepicDLWidth, $memepicDLHeight);
		imagepng($mememapPic, 'meme_map/meme-map(2).png');

		//Upload the map back to storage
		$storage->bucket($bucketid)->
		upload(fopen('meme_map/meme-map(2).png', 'r'), [
			'name' => "meme-map/meme-map.png"
		]);

		//Upload the added meme to the perma storage
		$storage->bucket($bucketid)->
		upload(fopen('meme_map/' . $fileName, 'r'), [
			'name' => "added-memes/" . $userid . "_". $getTime . ".".$ext
		]);

		//update the user to have the added meme
		$firestore->collection('users')->document($userid)->set([
			$getTime => [
				'x' => $reqX,
				'y' => $reqY,
				'location' => $imgurl
			]
		], ['merge' => true]);

		//delete the downloaded files
		unlink('meme_map/' . $fileName);
		unlink('meme_map/meme-map(2).png');
	}
} else {
	$storage->bucket($bucketid)->object('req-memes/' . $fileName)->delete();
	echo "file deleted";
}
