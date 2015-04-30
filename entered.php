<?php
require('constant.php');
require('database.php');

if (!isset($_GET['keyword'])) {
	die();
}

$keyword = $_GET['keyword'];
$id = $_GET['id'];
insertQuery($keyword, $id);
?>