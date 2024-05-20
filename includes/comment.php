<?php

require('../config.php');
require('../init.php');

if(get_setting_value('comments')){
	if(isset($_POST['send']) && $login_user){
		$conn = open_connection();
		if(isset($_POST['source']) && $_POST['source'] == 'jquery-comments'){
			if(!$_POST['parent']){
				$_POST['parent'] = null;
			}
			$_POST['content'] = comment_filtering($_POST['content']);
			$approved = 1;
			if(get_setting_value('moderate_comment') && $login_user->role != 'admin'){
				// Moderate comment is activated
				$approved = 0;
			}
			$sql = 'INSERT INTO comments (parent_id, game_id, comment, sender_id, sender_username, created_date, approved) VALUES (:parent_id, :game_id, :comment, :sender_id, :sender_username, :created_date, :approved)';
			$st = $conn->prepare($sql);
			$st->bindValue(":parent_id", $_POST['parent'], PDO::PARAM_INT);
			$st->bindValue(":game_id", $_POST['game_id'], PDO::PARAM_INT);
			$st->bindValue(":comment", $_POST['content'], PDO::PARAM_STR);
			$st->bindValue(":sender_id", $login_user->id, PDO::PARAM_INT);
			$st->bindValue(":sender_username", $login_user->username, PDO::PARAM_STR);
			$st->bindValue(":created_date", date('Y-m-d H:m:s'), PDO::PARAM_STR);
			$st->bindValue(":approved", $approved, PDO::PARAM_INT);
			$st->execute();

			$login_user->add_xp(20);

			echo('success');
		}
	}

	if(isset($_POST['load']) && isset($_POST['game_id'])){
		$conn = open_connection();
		$sql = 'SELECT * FROM comments WHERE game_id = :game_id AND approved = 1 ORDER BY parent_id asc, id asc LIMIT 50';
		$st = $conn->prepare($sql);
		$st->bindValue(":game_id", $_POST['game_id'], PDO::PARAM_INT);
		$st->execute();
		$row = $st->fetchAll(PDO::FETCH_ASSOC);
		$list = array();
		foreach ($row as $item) {
			$item['avatar'] = get_user_avatar($item['sender_username']);
			$list[] = $item;
		}
		echo json_encode((array)$list);
	}
}

if(isset($_POST['delete']) && $login_user){
	$conn = open_connection();
	if( USER_ADMIN && !ADMIN_DEMO){
		$sql = 'DELETE FROM comments WHERE id = :id LIMIT 1';
		$st = $conn->prepare($sql);
		$st->bindValue(":id", $_POST['id'], PDO::PARAM_INT);
		$st->execute();
	} else {
		$sql = 'DELETE FROM comments WHERE sender_id = :sender_id AND id = :id LIMIT 1';
		$st = $conn->prepare($sql);
		$st->bindValue(":sender_id", $login_user->id, PDO::PARAM_INT);
		$st->bindValue(":id", $_POST['id'], PDO::PARAM_INT);
		$st->execute();
	}
	echo 'deleted';
}

if(isset($_POST['approve']) && $login_user && USER_ADMIN){
	$conn = open_connection();
	$sql = 'UPDATE comments SET approved = 1 WHERE id = :id LIMIT 1';
	$st = $conn->prepare($sql);
	$st->bindValue(":id", $_POST['id'], PDO::PARAM_INT);
	$st->execute();
	echo 'ok';
}

function comment_filtering($comment){
	if(file_exists(ABSPATH.'includes/banned-words-comment.json')){
		$words = json_decode(file_get_contents(ABSPATH.'includes/banned-words-comment.json'), true);
		$comment = str_ireplace($words, '***', $comment);
	}
	return $comment;
}

?>