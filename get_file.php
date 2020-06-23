<?PHP
include("config.php");
include("login.php");
if($authok != 1){
	die('<meta http-equiv="refresh" content="0; url=./login.php?required">');
}

if(isset($_GET["id"])){
	$array = mysqli_fetch_array(mysqli_query($mylink, "SELECT id,name,link,description,LEFT(file, 1000000) AS tfile,file_name,tagged FROM data WHERE id = '".$_GET["id"]."'"));
	
	if(!isset($_GET["download"])){
		
		if(strlen($array["tfile"])<3000000){
		
			$f = finfo_open();

			$mime_type = finfo_buffer($f, base64_decode($array["tfile"]), FILEINFO_MIME_TYPE);
			
			if($mime_type == "application/pdf"){
				echo '<iframe src="data:application/pdf;base64,'.$array["tfile"].'" height="100%" frameBorder="0" width="100%"></iframe>';
			}else{
				echo '<h1>File can not be displayed.</h1><a href="./get_file.php?id='.$_GET["id"].'&download" download="'.$array["file_name"].'">Download File</a><br>';	
			}
		}else{
			echo '<h1>File can not be displayed.</h1><a href="./get_file.php?id='.$_GET["id"].'&download" download="'.$array["file_name"].'">Download File</a><br>';	
		}
	}else{
		$f = finfo_open();
		$mime_type = finfo_buffer($f, base64_decode($array["tfile"]), FILEINFO_MIME_TYPE);
		header('Content-Type: '.$mime_type);
		
		$array = mysqli_fetch_array(mysqli_query($mylink, "SELECT SUBSTR(file, 1, 100) FROM data WHERE id = '".$_GET["id"]."'"));
		$i = 0;
		while($array[0] != ""){
			$array = mysqli_fetch_array(mysqli_query($mylink, "SELECT SUBSTR(file, ".(($i*5000000)+1).", 5000000) AS tfile FROM data WHERE id = '".$_GET["id"]."'"));
			echo base64_decode($array["tfile"]);
			$i++;
		}
		
	}
}


?>
