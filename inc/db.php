<?php

$connection = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD)
        OR die('Cannot connect to database!');

mysql_select_db(DB_NAME);

?>
