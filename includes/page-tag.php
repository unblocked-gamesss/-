<?php

require_once( TEMPLATE_PATH . '/functions.php' );

if(PRETTY_URL){
	if(count($url_params) > 3){
		// Tag page only contains 3 parameter max,
		// If more than that, the url is not valid
		// Show 404 screen
		require( ABSPATH . 'includes/page-404.php' );
		return;
	}
	if(isset($url_params[2]) && !is_numeric($url_params[2])){
		// Page number should be a number
		// Show 404 screen
		require( ABSPATH . 'includes/page-404.php' );
		return;
	}
}

$cur_page = 1;
if(isset($url_params[2])){
	$_GET['page'] = $url_params[2];
	if(!is_numeric($_GET['page'])){
		$_GET['page'] = 1;
	}
}
if(isset($_GET['page'])){
	$cur_page = htmlspecialchars($_GET['page']);
	if(!is_numeric($cur_page)){
		$cur_page = 1;
	}
}

$tag_name = null;

$conn = open_connection();
$sql = 'SELECT id FROM tags WHERE name = :name';
$st = $conn->prepare($sql);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$st->bindValue(":name", $_GET['slug'], PDO::PARAM_STR);
$st->execute();
$row = $st->fetch(PDO::FETCH_ASSOC);
if ($row) {
	$tag_name = $_GET['slug'];
}

if(!is_null($tag_name)){
	$items_per_page = get_setting_value('category_results_per_page');
	$data = Game::getListByTag($tag_name, $items_per_page, 'id DESC', $items_per_page*($cur_page-1), false);
	$games = $data['results'];
	$total_games = $data['totalRows'];
	$total_page = $data['totalPages'];
	$meta_description = _t('Play %a Games', $tag_name).' | '.SITE_DESCRIPTION;
	$page_title = _t('%a Games', $tag_name).' | '.SITE_DESCRIPTION;

	require( TEMPLATE_PATH . '/tag.php' );
} else {
	require( ABSPATH . 'includes/page-404.php' );
}

?>