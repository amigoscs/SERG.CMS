<?php
$connect = mysql_connect ($DB_replace_array['DB_HOST_NAME'], $DB_replace_array['DB_USER_NAME'], $DB_replace_array['DB_USER_PASSWORD']) or exit("Database error connect: " . mysql_error());

mysql_select_db($DB_replace_array['DB_DATABASE'], $connect);

require_once(__DIR__ . DIRECTORY_SEPARATOR . "sql.php");
//$sql="SELECT * FROM  `teachers` WHERE  `name`='Иванов'" ;
//$sql= (string) $sql;
//$result = mysql_query($sql, $conn)
?>