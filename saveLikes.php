<?php
require('constant.php');
require('database.php');

$likes = $_GET['likes'];
$id = $_GET['id'];
saveLikes($likes, $id);
?>