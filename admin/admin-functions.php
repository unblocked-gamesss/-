<?php

// Functions for Admin Panel

if(!USER_ADMIN){
	die('Forbidden');
}

function get_setting_group($category){
	// $conn = open_connection();
	// $sql = "SELECT * FROM settings WHERE category = :category";
	// $st = $conn->prepare($sql);
	// $st->bindValue('category', $category, PDO::PARAM_STR);
	// $st->execute();
	// $rows = $st->fetchAll(PDO::FETCH_ASSOC);
	// return $rows;
	$group = [];
	foreach (SETTINGS as $item) {
		if($item['category'] == $category){
			$group[] = $item;
		}
	}
	return $group;
}

function update_setting($name, $value){
	// Migrated, replacing update_settings()
	$this_setting = get_setting($name);
	// Validating data type
	if($this_setting['type'] == 'bool'){
		if($value == 1 || $value == 0){
			//
		} else {
			die('Type not valid');
		}
	} else if($this_setting['type'] == 'number'){
		if(!is_numeric($value)){
			die('Type not valid');
		}
	}
	$conn = open_connection();
	$sql = "UPDATE settings SET value = :value WHERE name = :name LIMIT 1";
	$st = $conn->prepare($sql);
	$st->bindValue(":name", $name, PDO::PARAM_STR);
	$st->bindValue(":value", $value, PDO::PARAM_STR);
	$st->execute();
}

function to_numeric_version($str_version){
	// Used to convert "1.5.0" to int 150
	return (int)str_replace('.', '', $str_version);
}

?>