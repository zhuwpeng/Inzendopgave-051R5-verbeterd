<?php
include "db_connect.php";
include "functions.php";
session_start();

$message="";

$error_msg = "";
$usernameError = "";
$passwordError = "";
$username = "";

$error = false;

if(isset($_POST['submit']) && $_POST['submit'] == "Login"){

	$username = stripslashes(trim($_POST['username']));
	$password = stripslashes(trim($_POST['password']));

	//retrieve data to compare to verify user
	if(!empty($username) && !empty($password)){
		$userverifyQuery = "SELECT userID, username, password FROM users WHERE username = '$username'";
		$verifyResult = mysqli_query($connect, $userverifyQuery) or die("Er is iets mis gegeaan tijdens het ophalen van gegevens.");
		$checkNumRow = mysqli_num_rows($verifyResult);
		
		if($checkNumRow > 1){
			$message = "Er zijn meerdere dezelfde gebruikersnamen gevonden. Controleer de database en probeer het opnieuw.";
		}else{
			$userdata = mysqli_fetch_assoc($verifyResult);
			$dbpassword = $userdata['password'];
			$dbusername = $userdata['username'];
			
			//password verify
			if(md5($password) == $dbpassword && $username==$dbusername){
				$_SESSION['userID'] = $userdata['userID'];
				header("Location: index.php");
				exit();
			}else{
				$error_msg = "Uw wachtwoord of gebruikersnaam klopt niet";
				$error = true;
			}
		}
	}else{
		if(empty($username)){
			$usernameError = "U heeft geen gebruikersnaam opgegeven!";
			$error = true;
		}
		
		if(empty($password)){
			$passwordError = "U heeft geen wachtwoord opgegeven!";
			$error = true;
		}
	}
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Inleveropgave 051R5</title>
	<link rel="stylesheet" href="css/style.css" type="text/css">
</head>
<body>
<header>
<h1>Scoreboard</h1>
</header>

<div id="container">
	<div class="nav">
		<?php echo get_navbar($connect);?>
	</div>
	<span><?php echo $message;?></span>
	<div class="user-form">
		<form method="POST" action="login.php">
			<h2>Login</h2>
			<span class = error><?php echo $error_msg;?></span>
			<span class = error><?php echo $usernameError;?></span>
			<label for="form-username">Gebruikersnaam</label>
			<input type="text" name="username" id="form-username" value="<?php echo htmlentities($username);?>">
			
			<span class = error><?php echo $passwordError;?></span>
			<label for="form-password">Wachtwoord</label>
			<input type="password" name="password" id="form-password" value="<?php if($error){ echo htmlentities($_POST['password']); }else{ echo ""; }?>">
			
			<input type="submit" name="submit" value="Login">
		</form>
	</div>
</div>
</body>
</html>