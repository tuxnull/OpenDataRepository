<?PHP
include("config.php");

if(isset($_GET["id"])){
	$array = mysqli_fetch_array(mysqli_query($mylink, "SELECT * FROM data WHERE id = '".$_GET["id"]."'"));
	if(mb_strlen($string, '8bit')<200000){
		echo '<iframe src="data:application/pdf;base64,'.$array["file"].'" height="100%" frameBorder="0" width="100%"></iframe>';
	}else{
		echo '<h1>File is too large to be displayed.</h1>';	
	}
}


?>
