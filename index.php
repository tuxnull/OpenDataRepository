<?PHP
$log_check = 1;
include("login.php");
if($authok != 1){
	echo '<meta http-equiv="refresh" content="0; url=./login.php?required">';
}

if(!isset($_GET["q"])){
	$_GET["q"] = "";
}
ini_set('mssql.charset', 'UTF-8');
mysqli_query($mylink,"SET NAMES 'utf8'");

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

if(isset($_POST["name"])){
			$base64 = "";
			if(is_uploaded_file($_FILES["docfile"]["name"])){
				$path = $_FILES['docfile']['name'];
				$ext = pathinfo($path, PATHINFO_EXTENSION);
				if($ext != "pdf"){
					echo '<meta http-equiv="refresh" content="0; url=./index.php?error">';
				}
				$base64 = base64_encode(file_get_contents($_FILES["docfile"]["tmp_name"]));
			}
			if($USER_PERM_LEVEL < 1){
				echo '<meta http-equiv="refresh" content="0; url=./index.php?permerror">';
			}else{
				$qr = mysqli_query($mylink, 'INSERT INTO data (`name`, `link`, `description`, `file`, `tagged`) VALUES ("'.mysqli_real_escape_string($mylink, $_POST["name"]).'","'.mysqli_real_escape_string($mylink, $_POST["url"]).'","'.mysqli_real_escape_string($mylink, $_POST["description"]).'","'.mysqli_real_escape_string($mylink, $base64).'","'.mysqli_real_escape_string($mylink, $_POST["tagged"]).'" )');
				echo mysqli_error($mylink);
				echo '<meta http-equiv="refresh" content="0; url=./index.php?added">';
			}
		}
?>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">

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
		  <input type="text" name="q" class="form-control" placeholder="Search for a class or file" aria-label="Username" aria-describedby="basic-addon1" value="<?PHP echo $_GET["q"];?>">
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
		
		if(isset($_GET["q"]) || isset($_GET["class"])){
			if(!isset($_GET["page"])){
				$_GET["page"] = 0;
			}
			
			
			
			if(isset($_GET["class"])){
				if($_GET["class"] == "*"){
					$pages = round(mysqli_num_rows(mysqli_query($mylink, "SELECT id FROM data"))/9);
					$qr = mysqli_query($mylink, "SELECT * FROM data WHERE name LIKE '%".mysqli_real_escape_string($mylink, $_GET["q"])."%' ORDER BY id DESC LIMIT 9 OFFSET ".($_GET["page"]*9));
				}else{
					$pages = round(mysqli_num_rows(mysqli_query($mylink, "SELECT id FROM data WHERE tagged = '".mysqli_real_escape_string($mylink, $_GET["class"])."'"))/9);
					$qr = mysqli_query($mylink, "SELECT * FROM data WHERE tagged = '".mysqli_real_escape_string($mylink, $_GET["class"])."' ORDER BY id DESC LIMIT 9 OFFSET ".($_GET["page"]*9));
				}
			}else{
				$pages = round(mysqli_num_rows(mysqli_query($mylink, "SELECT id FROM data"))/9);
				$qr = mysqli_query($mylink, "SELECT * FROM data WHERE name LIKE '%".mysqli_real_escape_string($mylink, $_GET["q"])."%' ORDER BY id DESC LIMIT 9 OFFSET ".($_GET["page"]*9));
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
						echo '<p class="card-text">WhatsApp Group<br>'.$array["description"].'</p><span class="badge badge-info">'.$tagged_a["name"].'</span><a href="'.$array["link"].'" class="btn btn-primary">Join Group</a>';
					}else{
						echo '<p class="card-text">'.$array["description"].'</p><span class="badge badge-info">'.$tagged_a["name"].'</span><a href="'.$array["link"].'" class="btn btn-primary">Open Document</a> ';
					}
				}else{
					echo '<p class="card-text">'.$array["description"].'</p><span class="badge badge-info">'.$tagged_a["name"].'</span>';
				}
				
				if($array["file"] != ""){
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
								  <iframe src="./get_file.php?id='.$array["id"].'" height="100%" frameBorder="0" width="100%" style="height: 80vh;"></iframe>
								  <div class="modal-footer">
									<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
								  </div>
								</div>
							  </div>
							</div>';
				}
				echo ' 
						
					  </div>
					</div></div>';
				
			}
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
			  <form method="post" enctype="multipart/form-data">
			  <div class="modal-body">
				  <div class="form-group">
					<label for="exampleInputEmail1">Document Name</label>
					<input type="text" name="name" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" required>
					<small id="emailHelp" class="form-text text-muted">Please keep it as short as possible</small>
				  </div>
				  <div class="form-group">
					<label for="exampleFormControlSelect1">Tag a Class</label>
					<select name="tagged" class="form-control" id="exampleFormControlSelect1">
					  <option>General</option>
					  <?PHP
					  $qr = mysqli_query($mylink, "SELECT * FROM tags");
					  for($i = 0; $i < mysqli_num_rows($qr); $i++){
						  $class = mysqli_fetch_array($qr);
						  echo "<option value='".$class["id"]."'>".$class["name"]."</option>";
					  }
					  ?>
					</select>
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
					<input type="file" name="docfile" class="form-control-file" id="exampleFormControlFile1">
					<small id="fileHelp" class="form-text text-muted">Accepted files: PDF | Max Size: 100MB</small>
				  </div>
				  <div class="form-group form-check">
					<input type="checkbox" class="form-check-input" id="exampleCheck1" required>
					<label class="form-check-label" for="exampleCheck1">I hereby accept that the information I submit is valid and correct. I fully understand that failure to abide by these terms will result in my access to this website being revoked.</label>
				  </div>
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button class="btn btn-primary">Add Document</button>
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

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
  </body>
</html>