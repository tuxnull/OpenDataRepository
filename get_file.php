<?PHP
include("config.php");

if(isset($_GET["id"])){
	$array = mysqli_fetch_array(mysqli_query($mylink, "SELECT * FROM data WHERE id = '".$_GET["id"]."'"));
	echo '<iframe src="data:application/pdf;base64,'.$array["file"].'" height="100%" frameBorder="0" width="100%"></iframe>';
}


?>