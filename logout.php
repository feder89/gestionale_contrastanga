<?php
	require_once 'include/core.inc.php';

  	//elimina sessione
  	session_destroy();

  	//elimina coockie
  	unset($_COOKIE['username']);
  	unset($_COOKIE['password']);

  	$res = setcookie('username', '', time() - 3600);
  	$res = setcookie('password', '', time() - 3600);

  	//redirect
  	header("Location: login.php");
  	die();
?>