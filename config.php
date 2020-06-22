<?PHP
session_start();
define("MYSQL_HOST", "HOSTNAME");
define("MYSQL_DATABASE", "DATABASE");
define("MYSQL_USER", "USERNAME");
define("MYSQL_PASSWORD", "PASSWORD");

define("HOME_TITLE", "OpenDataRepository");

$mylink = mysqli_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE);

if(mysqli_connect_error()!=""){
	echo mysqli_connect_errno()."|";
	die(mysqli_connect_error());
}

$qr = mysqli_query($mylink, "SHOW TABLES");
if(!mysqli_num_rows($qr)>0){
	mysqli_query($mylink,'CREATE TABLE data(id int NOT NULL AUTO_INCREMENT,name tinytext,link text,description text,file mediumtext,file_name mediumtext, tagged int, PRIMARY KEY (id));');
	mysqli_query($mylink,'CREATE TABLE tags(id int NOT NULL AUTO_INCREMENT,name tinytext, PRIMARY KEY (id));');
	mysqli_query($mylink,'CREATE TABLE authorization(id int NOT NULL AUTO_INCREMENT,username tinytext,hashed_password text,email text,permission_level int, PRIMARY KEY (id));');
}
?>
