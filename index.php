<?PHP

$debug = false;

$log_check = 1;
$start_time = time();
include("login.php");
if($debug){
	echo "LOGIN: ".(time()-$start_time)."\n";
}

if($authok != 1){
	echo '<meta http-equiv="refresh" content="0; url=./login.php?required">';
}

if(!isset($_GET["q"])){
	$_GET["q"] = "";
}

ini_set('mssql.charset', 'UTF-8');
mysqli_query($mylink,"SET NAMES 'utf8'");
if($debug){
	echo "CHARSET SET: ".(time()-$start_time)."\n";
}

if(!isset($_SESSION["username"])){
	die('<meta http-equiv="refresh" content="0; url=./login.php?required">');
}

if(isset($_GET["added"])){
	echo '<div class="alert alert-success" role="alert">
			<b>Success!</b> Your data has been uploaded
			</div>';
}

if(isset($_GET["error"])){
	echo '<div class="alert alert-danger" role="alert">
			<b>Error</b> Your upload failed. Please check the file type and try again.
			</div>';
}

if(isset($_GET["permerror"])){
	echo '<div class="alert alert-danger" role="alert">
			<b>Error</b> Insufficient Permission.
			</div>';
}

if(isset($_GET["delete"])){
	if($USER_PERM_LEVEL < 2){
		echo '<meta http-equiv="refresh" content="0; url=./index.php?permerror">';
	}else{
		mysqli_query($mylink, "DELETE FROM data WHERE id='".mysqli_real_escape_string($mylink, $_GET["delete"])."'");
		echo '<div class="alert alert-success" role="alert">
			<b>Success</b> Data was deleted.
			</div>';
	}
}

if(isset($_POST["name"])){
			$base64 = "";
			
			if($USER_PERM_LEVEL < 1){
				echo '<meta http-equiv="refresh" content="0; url=./index.php?permerror">';
			}else{
				if(mysqli_num_rows(mysqli_query($mylink, "SELECT * FROM tags WHERE name = '".mysqli_real_escape_string($mylink, $_POST["tagged"])."' OR id='".mysqli_real_escape_string($mylink, $_POST["tagged"])."'"))<1){
					mysqli_query($mylink, "INSERT INTO `tags`(`name`) VALUES ('".mysqli_real_escape_string($mylink, $_POST["tagged"])."')");
					
					$row = mysqli_fetch_array(mysqli_query($mylink, "SELECT * FROM tags WHERE name = '".mysqli_real_escape_string($mylink, $_POST["tagged"])."' OR id='".mysqli_real_escape_string($mylink, $_POST["tagged"])."'"));
					$_POST["tagged"] = $row["id"];
					
				}
				
				if(isset($_POST["file_name"])){
					$qr = mysqli_query($mylink, "SELECT * FROM data WHERE name='".mysqli_real_escape_string($mylink, $_POST["name"])."' AND file_name LIKE '".mysqli_real_escape_string($mylink, $_POST["file_name"])."'");
					if(mysqli_num_rows($qr)>0){
						$row = mysqli_fetch_array($qr);
						echo $_POST["part"]+1;
						if($_POST["part"]+1 == $_POST["total"]){
							file_put_contents("./".$row["id"].".temp",$_POST["base64"],FILE_APPEND);
							$handle = fopen("./".$row["id"].".temp", 'r');
							
							while(!feof($handle)){
								$builder = fread($handle, 524288);
								mysqli_query($mylink, "UPDATE `data` SET file=CONCAT(file,'".$builder."') WHERE name='".mysqli_real_escape_string($mylink, $_POST["name"])."' AND file_name LIKE '".mysqli_real_escape_string($mylink, $_POST["file_name"])."'");
								
							}
							
							fclose($handle);
							
							unlink("./".$row["id"].".temp");
						}else{
							file_put_contents("./".$row["id"].".temp",$_POST["base64"],FILE_APPEND);
						}
					}else{
						$qr = mysqli_query($mylink, 'INSERT INTO data (`name`, `link`, `description`, `file`,`file_name`, `tagged`) VALUES ("'.mysqli_real_escape_string($mylink, htmlentities($_POST["name"])).'","'.mysqli_real_escape_string($mylink, htmlentities($_POST["url"])).'","'.mysqli_real_escape_string($mylink, htmlentities($_POST["description"])).'","'.mysqli_real_escape_string($mylink, "").'","'.mysqli_real_escape_string($mylink, htmlentities($_POST["file_name"])).'","'.mysqli_real_escape_string($mylink, htmlentities($_POST["tagged"])).'" )');
						$qr = mysqli_query($mylink, "SELECT * FROM data WHERE name='".mysqli_real_escape_string($mylink, $_POST["name"])."' AND file_name LIKE '".mysqli_real_escape_string($mylink, $_POST["file_name"])."'");
						
						$row = mysqli_fetch_array($qr);
						
						file_put_contents("./".$row["id"].".temp",$_POST["base64"]);
						
						if($_POST["part"]+1 == $_POST["total"]){
							$handle = fopen("./".$row["id"].".temp", 'r');
							
							while(!feof($handle)){
								$builder = fread($handle, 524288);
								mysqli_query($mylink, "UPDATE `data` SET file=CONCAT(file,'".$builder."') WHERE name='".mysqli_real_escape_string($mylink, $_POST["name"])."' AND file_name LIKE '".mysqli_real_escape_string($mylink, $_POST["file_name"])."'");
								
							}
							
							fclose($handle);
							
							unlink("./".$row["id"].".temp");
						}
						
						echo "1";
					}
					
				}else{
					echo "1";
				}
				echo mysqli_error($mylink);
			}
			return;
		}
?>


<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">

    <title><?PHP echo HOME_TITLE?></title>
  </head>
  <body>
	<div class="container">
		<h1><?PHP echo HOME_TITLE?></h1>
		<form>
		<div class="input-group mb-3">
		  <div class="input-group-prepend">
			<span class="input-group-text" id="basic-addon1">Search</span>
		  </div>
		  <input type="text" name="q" class="form-control" placeholder="Search for a file" aria-label="Username" aria-describedby="basic-addon1" value="<?PHP echo $_GET["q"];?>">
		  <div class="input-group-append">
			<button class="btn btn-outline-secondary" id="button-addon2">Go</button>
		  </div>
		</div>
		<div class="form-group">
					<label for="exampleFormControlSelect1">Search by Tag</label>
					<select name="class" class="form-control" id="exampleFormControlSelect1">
					  <option value="*">View All</option>
					  <option value="0">General</option>
					  <?PHP
					  $qr = mysqli_query($mylink, "SELECT * FROM tags");
					  for($i = 0; $i < mysqli_num_rows($qr); $i++){
						  $class = mysqli_fetch_array($qr);
						  echo "<option value='".$class["id"]."'>".$class["name"]."</option>";
					  }
					  ?>
					</select>
				  </div>
		</form>
		<hr>
		<div class="row">
		<?PHP
		if($debug){
			echo "BEGIN UI: ".(time()-$start_time)."\n";
		}
		
		if(isset($_GET["q"]) || isset($_GET["class"])){
			if(!isset($_GET["page"])){
				$_GET["page"] = 0;
			}
			
			
			
			if(isset($_GET["class"])){
				if($_GET["class"] == "*"){
					$pages = floor(mysqli_fetch_array(mysqli_query($mylink, "SELECT Count(*) FROM data"))[0]/9);
					$qr = mysqli_query($mylink, "SELECT id,name,link,description,LEFT(file, 1000000) AS tfile,file_name,tagged FROM data WHERE name LIKE '%".mysqli_real_escape_string($mylink, $_GET["q"])."%' ORDER BY id DESC LIMIT 9 OFFSET ".($_GET["page"]*9));
				}else{
					$pages = floor(mysqli_fetch_array(mysqli_query($mylink, "SELECT Count(*) FROM data WHERE tagged = '".mysqli_real_escape_string($mylink, $_GET["class"])."'"))[0]/9);
					$qr = mysqli_query($mylink, "SELECT id,name,link,description,LEFT(file, 1000000) AS tfile,file_name,tagged FROM data WHERE tagged = '".mysqli_real_escape_string($mylink, $_GET["class"])."' ORDER BY id DESC LIMIT 9 OFFSET ".($_GET["page"]*9));
				}
			}else{
				$pages = floor(mysqli_fetch_array(mysqli_query($mylink, "SELECT Count(*) FROM data"))[0]/9);
				$qr = mysqli_query($mylink, "SELECT id,name,link,description,LEFT(file, 1000000) AS tfile,file_name,tagged FROM data WHERE name LIKE '%".mysqli_real_escape_string($mylink, $_GET["q"])."%' ORDER BY id DESC LIMIT 9 OFFSET ".($_GET["page"]*9));
			}
			
			if($debug){
				echo "PAGINATION: ".(time()-$start_time)."\n";
			}
			
			for($i = 0; $i < mysqli_num_rows($qr); $i++){
				$array = mysqli_fetch_array($qr);
				echo '<div class="col-sm-4"><div class="card" style="margin-top: 10px;">
					  <div class="card-body">
						<h5 class="card-title">'.$array["name"].'</h5>';
				$qrr = mysqli_query($mylink, "SELECT * FROM tags WHERE id='".$array["tagged"]."'");
				$tagged_a = mysqli_fetch_array($qrr);
				if($array["link"] != ""){
					if (strpos($array["link"], 'chat.whatsapp') !== false) {
						echo '<p class="card-text">WhatsApp Group<br>'.$array["description"].'</p><span class="badge badge-info">'.$tagged_a["name"].'</span><br><a href="'.$array["link"].'" class="btn btn-primary">Join Group</a>';
					}else{
						echo '<p class="card-text">'.$array["description"].'</p><span class="badge badge-info">'.$tagged_a["name"].'</span><br><a href="'.$array["link"].'" class="btn btn-primary">Open Document</a> ';
					}
				}else{
					echo '<p class="card-text">'.$array["description"].'</p><span class="badge badge-info">'.$tagged_a["name"].'</span> <span class="badge badge-secondary">'.explode(".",$array["file_name"])[sizeof(explode(".",$array["file_name"]))-1].'</span><br>';
					if(mb_strlen($array["tfile"])>100000 && strpos($array["file_name"], '.pdf') == false){
						echo '<a download="'.$array["file_name"].'" href="./get_file.php?id='.$array["id"].'&download" class="btn btn-primary">Download</a> ';
					}else{
						if($array["tfile"] != ""){
						echo '<button type="button" class="btn btn-secondary" data-toggle="modal" data-target=".bd-example-modal-xl-'.$array["id"].'">View Attachment</button>';
						echo '<div class="modal fade bd-example-modal-xl-'.$array["id"].'" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
								  <div class="modal-dialog modal-xl" role="document">
									<div class="modal-content">
									  <div class="modal-header">
										<h5 class="modal-title" id="exampleModalLabel">Attachment</h5>
										<button type="button" class="close" data-dismiss="modal" aria-label="Close">
										  <span aria-hidden="true">&times;</span>
										</button>
									  </div>
									  <iframe src="https://example.org/" onload="if(this.src.trim() == \'https://example.org/\'){this.src = \'./get_file.php?id='.$array["id"].'\';}" height="100%" frameBorder="0" width="100%" style="height: 80vh;"></iframe>
									  <div class="modal-footer">
										<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
									  </div>
									</div>
								  </div>
								</div>';
						}
					}
				}
				
				if($USER_PERM_LEVEL >=2){
					echo '<br><a href="./?delete='.$array["id"].'"<span class="badge badge-danger">Delete</span></a>';
				}
				
				echo ' 
						
					  </div>
					</div></div>';
				
			}
		}
		if($debug){
			echo "DATA OUTPUT: ".(time()-$start_time)."\n";
		}
		
		
		echo '</div><br><center style="text-align: center; margin-top: 10px;"><nav aria-label="Page navigation example">
		  <ul class="pagination">
			<li class="page-item"><a class="page-link" href="./?page='.($_GET["page"]-1).'">Previous</a></li>
			<li class="page-item"><a class="page-link" href="./?page=0">1</a></li>
			<li class="page-item"><a class="page-link">'.($_GET["page"]+1).'</a></li>
			<li class="page-item"><a class="page-link" href="./?page='.($pages).'">'.($pages+1).'</a></li>
			<li class="page-item"><a class="page-link" href="./?page='.($_GET["page"]+1).'">Next</a></li>
		  </ul>
		</nav></center>';
		
		?>
		<hr>
		<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add_document" data-whatever="@getbootstrap">Add Link or Document</button>

		<div class="modal fade bd-example-modal-lg" id="add_document" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
		  <div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
			  <div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Add Document</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				  <span aria-hidden="true">&times;</span>
				</button>
			  </div>
			  <form method="post" id="upload_form" enctype="multipart/form-data">
			  <div class="modal-body">
				  <div class="form-group">
					<label for="exampleInputEmail1">Document Name</label>
					<input type="text" name="name" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" required>
					<small id="emailHelp" class="form-text text-muted">Please keep it as short as possible</small>
				  </div>
				  <div class="form-group">
					<label for="exampleFormControlSelect1">Document Tag</label>
					<select name="tagged" class="form-control" id="classSelect">
					  <option value="0">General</option>
					  <?PHP
					  $qr = mysqli_query($mylink, "SELECT * FROM tags");
					  for($i = 0; $i < mysqli_num_rows($qr); $i++){
						  $class = mysqli_fetch_array($qr);
						  echo "<option value='".$class["id"]."'>".$class["name"]."</option>";
					  }
					  
					  if($debug){
							echo "TAG MODAL: ".(time()-$start_time)."\n";
						}
					  ?>
					  <option value="custom">Custom</option>
					  
					</select>
					<script>
					
					document.getElementById("classSelect").addEventListener("change", function(){
						var e = document.getElementById("classSelect");
						var strUser = e.options[e.selectedIndex].value;
						if(strUser == "custom"){
							document.getElementById("classSelect").outerHTML = '<div class="form-group"><input type="text" name="tagged" class="form-control" id="exampleInputEmail1" required></div>';
						}
						
						
						
					});
						
					  </script>
				  </div>
				  <div class="form-group">
					<label for="exampleInputEmail1">URL</label>
					<input type="text" name="url" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp">
					<small id="emailHelp" class="form-text text-muted">Before adding, please check if the link has already been added by using the search function on the website.</small>
				  </div>
				  <div class="form-group">
					<label for="exampleFormControlTextarea1">Document Description</label>
					<textarea class="form-control" name="description" id="exampleFormControlTextarea1" rows="3"></textarea>
				  </div>
				  <div class="form-group">
					<label for="exampleFormControlFile1">Upload Attachment</label>
					<input type="file" name="docfile" id="docfile" class="form-control-file" id="exampleFormControlFile1">
					<small id="fileHelp" class="form-text text-muted">Accepted files: All | Max Size: 100MB</small>
				  </div>
				  <div class="form-group form-check">
					<input type="checkbox" class="form-check-input" id="exampleCheck1" required>
					<label class="form-check-label" for="exampleCheck1">I hereby accept that the information I submit is valid and correct. I fully understand that failure to abide by these terms will result in my access to this website being revoked.</label>
				  </div>
				  <input type="hidden" name="part" id="i_part" value="0" />
				  <input type="hidden" name="total" id="i_total" value="0" />
				  <input type="hidden" name="file_name" id="file_name" value="0" />
				  <input type="hidden" name="base64" id="base64" value="0" />
				  
				  <div class="progress">
				    <div class="progress-bar" id="upload_progress" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
				  </div>
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="button" id="submit_btn" onclick="makeBase()" class="btn btn-primary">Add Document</button>
				<script>
				
				function makeBase(){
					
					 $('#submit_btn').prop('disabled', true);
					
					if(document.querySelector('#docfile').files.length>0){
					
					var file = document.querySelector('#docfile').files[0];
					var reader = new FileReader();
					   reader.readAsDataURL(file);
					   reader.onload = function () {
						 
						 var parts = reader.result.split(",")[1].match(/.{1,500000}/g);
						 
						 console.log(parts);
						 
						 $('#i_total').val(parts.length);
						 
						 let file = $("#docfile")[0].files[0]; 
						 $('#file_name').val(file.name);
						 
						 
						 i = 0;
						 
						 last_finished_upload = Math.round((new Date()).getTime() / 1000);
						 
						 $('#base64').val(parts[i]);
						 $('#i_part').val(i);
						 
						 var xhttp = new XMLHttpRequest(); 
						 xhttp.onreadystatechange = function() {
							  if (this.readyState == 4 && this.status == 200) {
								  if(i<(parts.length-1)){
								  
									  $('#upload_progress').width(((parseInt(this.responseText.trim())/parts.length)*100).toString()+"%");
									  $('#upload_progress').html(Math.round((parseInt(this.responseText.trim())/parts.length)*100).toString()+"%");
									  
									  
									  console.log(500000/(Math.round((new Date()).getTime() / 1000)-last_finished_upload));
									  last_finished_upload = Math.round((new Date()).getTime() / 1000);
									  
									i++;
									   $('#base64').val(parts[i]);
									   $('#i_part').val(i);
									  xhttp.open("POST", "./", true);
									  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
									  xhttp.send($('#upload_form').serialize()); 
								  }else{
									  $('#upload_progress').width(((parseInt(this.responseText.trim())/parts.length)*100).toString()+"%");
									  $('#upload_progress').html("Done");
								  }
								  
								  
							  }
							};
						  xhttp.open("POST", "./", true);
						  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
						  xhttp.send($('#upload_form').serialize()); 
						
						
						 
					   };
					   reader.onerror = function (error) {
						 console.log('Error: ', error);
					   };
					}else{
						var xhttp = new XMLHttpRequest(); 
						xhttp.open("POST", "./", true);
						  xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
						  xhttp.send($('#upload_form').serialize()); 
						  $('#upload_progress').width("100%");
						  $('#upload_progress').html("Done");
					}
				}
				
				
				
				</script>
			  </div>
			  </form>
			</div>
		  </div>
		</div>
	</div>
	<nav class="navbar navbar-light bg-light footer">
		<span class="navbar-text">
		Made by Patrick Garske (@tuxnull). <a href="https://github.com/tuxnull/OpenDataRepository">Fork this on GitHub</a>
	  </span>
	</nav>
	
	<style>
	/* Sticky footer styles
-------------------------------------------------- */
html {
  position: relative;
  min-height: 100%;
}
body {
  margin-bottom: 60px; /* Margin bottom by footer height */
}
.footer {
  position: absolute;
  bottom: 0;
  width: 100%;
  height: 60px; /* Set the fixed height of the footer here */
  line-height: 60px; /* Vertically center the text there */
  background-color: #f5f5f5;
}


/* Custom page CSS
-------------------------------------------------- */
/* Not required for template or sticky footer method. */

.container {
  width: auto;
  max-width: 680px;
  padding: 0 15px;
}
</style>
<?PHP
echo time()-$start_time;
?>
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
  </body>
</html>

