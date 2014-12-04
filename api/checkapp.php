<?php

include_once "dbconnect.inc";

$data = make_data_safe($_REQUEST);

$sql = "select code
        from apps
        where username = '$data[user]'
        and password = old_password('$data[password]')";
$res = mysql_query($sql, $conn);

if (mysql_num_rows($res))
  echo mysql_result($res, 0, 0);
