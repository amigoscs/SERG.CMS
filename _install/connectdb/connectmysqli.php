<?php
$connect = new mysqli(
		$DB_replace_array['DB_HOST_NAME'],
		$DB_replace_array['DB_USER_NAME'],
		$DB_replace_array['DB_USER_PASSWORD'],
		$DB_replace_array['DB_DATABASE']
) or exit("Database error connect");
$connect->set_charset("utf8");

if($connect->connect_error) {
	exit("Database error connect: " . $connect->connect_error);
}

require_once(__DIR__ . DIRECTORY_SEPARATOR . "sql.php");

foreach($tablesArray as $tableName => $queryesTable)
{
	if($queryesTable['create']) {
		$queryString = str_replace('__TABLE__', $tableName, $queryesTable['create']);
		$connect->query($queryString);
	}
	if($queryesTable['insert']) {
		$queryString = str_replace('__TABLE__', $tableName, $queryesTable['insert']);
		$connect->query($queryString);
	}
}



?>
