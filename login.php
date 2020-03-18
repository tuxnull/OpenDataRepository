<?PHP
include("config.php");

$authok = 0;
if(isset($_SESSION["username"])){
	$qr = mysqli_query($mylink, "SELECT * FROM authorization WHERE username='".mysqli_real_escape_string($mylink, $_SESSION["username"])."'");
			if(mysqli_num_rows($qr)>0){
				$a_c = mysqli_fetch_array($qr);
				if($_SESSION["password"] == $a_c["hashed_password"] && $a_c["permission_level"]>=0){
					$authok = 1;
					$USER_PERM_LEVEL = $a_c["permission_level"];
				}else{
					echo '<div class="alert alert-warning" role="alert">Username or password invalid. Please try again.</div>';
				}
			}else{
				echo '<div class="alert alert-warning" role="alert">Your username wasn\'t found in the database. Please try again.</div>';
			}
}

if(isset($_POST["username"])){
	if(isset($_POST["password"])){
		if(isset($_POST["register"])){
			if($_POST["register"] == "1"){
				$qr = mysqli_query($mylink, "SELECT * FROM authorization WHERE username LIKE '".mysqli_real_escape_string($mylink, $_POST["username"])."'");
				if(mysqli_num_rows($qr)>0){
					echo '<div class="alert alert-warning" role="alert">Username already taken.</div>';
				}else{
					$qr = mysqli_query($mylink, "INSERT INTO `authorization` (`username`, `hashed_password`, `email`, `permission_level`) VALUES ('".mysqli_real_escape_string($mylink, $_POST["username"])."', '".mysqli_real_escape_string($mylink, hash("whirlpool",$_POST["password"]))."', '', -1);");
					echo mysqli_error($mylink);
					echo '<div class="alert alert-warning" role="alert">Your data has been added. Please wait for an administrator to activate your account.</div>';
				}
			}
		}else{
			$qr = mysqli_query($mylink, "SELECT * FROM authorization WHERE username='".mysqli_real_escape_string($mylink, $_POST["username"])."'");
			if(mysqli_num_rows($qr)>0){
				$a_c = mysqli_fetch_array($qr);
				if(hash("whirlpool",$_POST["password"]) == $a_c["hashed_password"] && $a_c["permission_level"]>=0){
					$_SESSION["username"] = $_POST["username"];
					$_SESSION["password"] = hash("whirlpool",$_POST["password"]);
					echo '<meta http-equiv="refresh" content="0; url=./index.php?welcome">';
				}else{
					echo '<div class="alert alert-warning" role="alert">Username or password invalid. Please try again.</div>';
				}
			}else{
				echo '<div class="alert alert-warning" role="alert">Your username wasn\'t found in the database. Please try again.</div>';
			}
		}
	}
}
?>
<?PHP
if(!isset($log_check)){
	echo '<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">

    <title>Login | '.HOME_TITLE.'</title>
  </head>
  <body>
	<div class="container">
		<h1>Please log in to continue</h1>
		<form method="post">
		  <div class="form-group">
			<label for="exampleInputEmail1">Username</label>
			<input name="username" type="text" class="form-control">
		  </div>
		  <div class="form-group">
			<label for="exampleInputPassword1">Password</label>
			<input name="password" type="password" class="form-control">
		  </div>
		  <button type="submit" class="btn btn-primary">Submit</button> or <button name="register" value="1" class="btn btn-primary">Register</button>
		</form>
	</div>
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
  </body>
</html>';
}
?>

