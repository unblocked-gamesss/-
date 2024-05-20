<?php

if(!file_exists('cloudarcade.zip')){
	die('"cloudarcade.zip" is missing!');
}

if(!class_exists('ZipArchive')){
	die('"ZipArchive" extension is missing or disabled.');
}

$warning_list = [];

if (!is_writable('cloudarcade.zip')) {
	$warning_list[] = 'Can\'t write a file';
}

if (!file_exists('test')) {
    if (mkdir('test', 0755)) {
        rmdir('test');
    } else {
        $warning_list[] = 'Can\'t create folder';
    }
}

if (version_compare(phpversion(), '7.0.0', '>=')) {
    //
} else {
    $warning_list[] = 'You need PHP 7+, currently your\'re using PHP '.phpversion();
}

if (!function_exists('curl_init')) {
    $warning_list[] = 'CURL is disabled, please activate it.';
}

if(!empty($warning_list)){
	echo '<ul>';
	foreach ($warning_list as $item) {
		echo '<li>'.$item.'</li>';
	}
	echo '</ul>';
	return;
}

$tmp_folder = 'tmp_cloudarcade';

mkdir($tmp_folder, 0777);

$zip = new ZipArchive;
$res = $zip->open('cloudarcade.zip');
if ($res === TRUE) {
	$zip->extractTo($tmp_folder);
	$zip->close();
	//
	if(file_exists('read-me.txt')){
		unlink('read-me.txt');
	}
	echo 'OK';
	recurse_copy($tmp_folder, __DIR__);
	delete_files($tmp_folder);
	unlink($tmp_folder.'/.htaccess');
	rmdir( $tmp_folder );
	unlink('cloudarcade.zip');
	header('Location: index.php');
	unlink('unpack.php');
} else {
  die('Failed to extract!');
}

function recurse_copy($src,$dst) { 
	$dir = opendir($src); 
	@mkdir($dst); 
	while(false !== ( $file = readdir($dir)) ) { 
		if (( $file != '.' ) && ( $file != '..' )) { 
			if ( is_dir($src . '/' . $file) ) { 
				recurse_copy($src . '/' . $file,$dst . '/' . $file); 
			} 
			else { 
				copy($src . '/' . $file,$dst . '/' . $file);
			} 
		} 
	} 
	closedir($dir); 
}

function delete_files($target) {
	if(is_dir($target)){
		$files = glob( $target . '*', GLOB_MARK );

		foreach( $files as $file ){
			delete_files( $file );      
		}

		rmdir( $target );
	} elseif(is_file($target)) {
		unlink( $target );  
	}
}

?>