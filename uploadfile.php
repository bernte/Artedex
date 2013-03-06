<?php

if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$folder = "images/";

	move_uploaded_file($_FILES["file"]["tmp_name"] , "$folder".$_FILES["file"]["name"]);
	echo 'Uploaded photo';
}