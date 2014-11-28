<?php

require_once("db/config.php");
require_once("db/DAL.php");
require_once("db/UserDAL.php");

// Handle users in db
session_start();
$userDAL = new UserDAL();
$savedUsers = $userDAL->getUsers();
$userFound = false;
$user = null;
if (isset($_SESSION["accessToken"]) &&
	isset($_POST["accessToken"]) &&
	$_SESSION["accessToken"] == $_POST["accessToken"]) {
		
	$name = $_POST["name"];
	$id = $_POST["id"];
	
	foreach($savedUsers as $savedUser) {
		if ($name == $savedUser["name"] && 
			$id == $savedUser["id"]) {
			
			$userFound = true;
			$user = $savedUser;
		}
	}
	
	if ($_POST["funct"] == "addUser") {
		if ($userFound == false) {
			try {
				$userDAL->addUser($name, $id);
				echo "User saved";
			} catch (Exception $e) {
				echo $e;
			}
		} else {
			echo "User found;".$user["diet"];
		}
	} else if ($_POST["funct"] == "saveDiet") {
		$diet = $_POST["diet"];
		if ($userFound == true) {
			try {
				$userDAL->saveDiet($id, $diet);
				echo "Diet saved";
			} catch (Exception $e) {
				echo $e;  
			}
		} else {
			echo "Användaren hittades inte när kosten skulle sparas i databasen.";
		}
	}
} else {
	echo "Request denied";
}
