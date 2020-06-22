<?PHP
include("config.php");

if(isset($_GET["id"])){
	$array = mysqli_fetch_array(mysqli_query($mylink, "SELECT * FROM data WHERE id = '".$_GET["id"]."'"));
	if(!isset($_GET["download"])){
		if(mb_strlen($array["file"])<100000){
			echo '<iframe src="data:application/pdf;base64,'.$array["file"].'" height="100%" frameBorder="0" width="100%"></iframe>';
		}else{
			echo '<h1>File is too large to be displayed.</h1><br>';	
		}
	}else{
		$f = finfo_open();

		$mime_type = finfo_buffer($f, base64_decode($array["file"]), FILEINFO_MIME_TYPE);
		header('Content-Type: '.$mime_type);
		echo base64_decode($array["file"]);
	}
}


?>
