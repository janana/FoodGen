<?php

class DAL {
	/**
	 * @var Mysqli connection
	 */
	protected $connection;
	/**
	 * connects to database
	 */
	public function __construct() {
		$this->connection = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		if (mysqli_connect_errno()) {
		    echo "Connect failed: %s\n" . mysqli_connect_error();
	 	   exit();
		}
	}
}
