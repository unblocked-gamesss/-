<?php

require_once( TEMPLATE_PATH . '/functions.php' );

if ( !isset($_GET['slug']) || !$_GET['slug'] ) {
	require( ABSPATH . 'includes/page-homepage.php' );
	return;
}
if(count($url_params) > 2){
	// Have additional unofficial parameters
	require( ABSPATH . 'includes/page-404.php' );
	return;
}

$_GET['slug'] = htmlspecialchars($_GET['slug']);

Game::update_views( $_GET['slug'] );
$game = Game::getBySlug( $_GET['slug'] );

if(!$game->published){
	// This game is drafted
	if(is_login() && USER_ADMIN){
		// Show message for admin user
		echo '<div class="alert alert-warning alert-draft" style="z-index: 1000;">The game has not been published yet and is currently in draft mode.</div>';
	} else {
		$game = null;
		// Show 404 page for visitor
	}
}

if($game){
	$page_title = $game->title . ' | '.SITE_DESCRIPTION;
	$meta_description = str_replace(array('"', "'"), "", strip_tags($game->description));

	require( TEMPLATE_PATH . '/game.php' );
} else {
	require( ABSPATH . 'includes/page-404.php' );
}

?>