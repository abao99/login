<?php
# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"
$hostname_members = "localhost";
$database_members = "members";
$username_members = "admin";
$password_members = "123456";
$members = mysql_pconnect($hostname_members, $username_members, $password_members) or trigger_error(mysql_error(),E_USER_ERROR); 
?>