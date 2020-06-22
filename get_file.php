<?PHP
include("config.php");

if(isset($_GET["id"])){
	$array = mysqli_fetch_array(mysqli_query($mylink, "SELECT * FROM data WHERE id = '".$_GET["id"]."'"));
	
	
	
	if(!isset($_GET["download"])){
		
		$f = finfo_open();

		$mime_type = finfo_buffer($f, base64_decode($array["file"]), FILEINFO_MIME_TYPE);
		
		if($mime_type == "application/pdf"){
			echo '<iframe src="data:application/pdf;base64,'.$array["file"].'" height="100%" frameBorder="0" width="100%"></iframe>';
		}else{
			echo '<h1>File can not be displayed.</h1><a href="./get_file.php?id='.$_GET["id"].'&download" download="'.$array["file_name"].'">Download File</a><br>';	
		}
	}else{
		$f = finfo_open();

		$mime_type = finfo_buffer($f, base64_decode($array["file"]), FILEINFO_MIME_TYPE);
		header('Content-Type: '.$mime_type);
		echo base64_decode($array["file"]);
	}
}


?>
