<?php

function upload_image($file, $path, $max_width) {

	$CFG_FILTER_IMG	= ['png', 'jpg', 'jpeg', 'gif'];

	@mkdir($path, 0755, true);

	$new_file = null;

	$extension = pathinfo( mb_strtolower($file['name']), PATHINFO_EXTENSION);
	$name = pathinfo( mb_strtolower($file['name']), PATHINFO_FILENAME);
	$filename = file_name_format($name);

	if( !in_array($extension, $CFG_FILTER_IMG) ){
		die( "Arquivos com extensão \"$extension\" não são permitidos." );
	}

	$handle = new Upload($file);

	$new_file = "{$path}{$filename}.{$extension}";

	if ($handle->uploaded) {

		$handle->allowed = array('image/*');

		$handle->file_overwrite = true;
		$handle->image_resize = true;
		$handle->image_no_enlarging = true;
		$handle->image_x = $max_width;
		$handle->image_ratio_y = true;

		$handle->file_new_name_body = $filename;

		switch ($extension) {
		case "webp":
			$handle->image_convert      =   "webp";
			$handle->webp_quality 		=   50;
			break;
		case "jpg":
			$handle->image_convert      =   "jpg";
			$handle->jpeg_quality       =   69;
			$handle->image_interlace 	= true;
			break;
		case "png":
			$handle->image_convert      =   "png";
			$handle->png_compression    =   5;
			break;
		}

		$handle->Process($path);

	}

	$handle->Clean();

	return $new_file;

}

function upload_file($file,$path){

	$CFG_FILTER_DOC	= ['doc', 'xls', 'docx', 'xlsx', 'pdf', 'txt', 'csv'];

	$new_file = null;

	@mkdir($path, 0755, true);

	$extension = pathinfo( mb_strtolower($file['name']), PATHINFO_EXTENSION);
	$name = pathinfo( mb_strtolower($file['name']), PATHINFO_FILENAME);
	$filename = file_name_format($name);

	if( in_array($extension, $CFG_FILTER_DOC) ){

		$new_file = "{$path}{$filename}.{$extension}";

		// die($new_file);

		$upload = move_uploaded_file($file['tmp_name'], $new_file);

		if( !$upload ) die( $_FILES["file"]["error"] );

	}

	return $new_file;

}