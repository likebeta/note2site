<?php
// parse response of getUserInfo
function parseUserInfo($response){
	$userinfo = json_decode($response);
	if (is_object($userinfo) && property_exists($userinfo,'id')){
		return $userinfo;
	}
	else{
		return false;
	}
}
// print UserInfo
function printUserInfo($userinfo){
	foreach($userinfo as $key => $value) {
		echo "$key: $value<br />";
	}
}

// parse response of listNotebooks
function parseNotebooks($response){
	$notebooks = json_decode($response);
	if (is_array($notebooks)){
		return $notebooks;
	}
	else{
		return false;
	}
}
// print notebooks
function printNotebooks($notebooks){
	foreach($notebooks as $obj){
		foreach($obj as $key => $value){
			echo "$key: $value<br />";
		}
	}
}

// parse response of listNotes
function parseNotes($response){
	$notes = json_decode($response);
	if (is_array($notes)){
		return $notes;
	}
	else{
		return false;
	}
}
// print notes
function printNotes($notes){
	foreach($notes as $value){
		echo "$value<br />";
	}
}

// parse response of getNote
function parseNote($response){
	$note = json_decode($response);
	if (is_object($note)){
		return $note;
	}
	else{
		return false;
	}
}
// print note
function printNote($note){
	foreach($note as $key => $value){
		echo "$key: $value<br />";
	}
}

// normalize content of note
function normalizeContent($content,$client){
	
}
?>
