<?php
include "db_connect.php";
include "functions.php";

$unameError = "";
$passError = "";
$emailError = "";
$message = "";
$errorMsg = "";

$error = false;

//check if submit isset
if(isset($_POST['submit']) && $_POST['submit'] == 'Registreren'){
	$username = stripslashes(trim($_POST['username']));
	$password = stripslashes(trim($_POST['password']));
	$email = stripslashes(trim($_POST['email']));
	
	//Validate username input
	if(empty($username)){
		$unameError = "*Geen gebruikersnaam ingevuld!";
		$error = true;
	}elseif(strlen($username) < 3){
		$unameError = "*Gebruikersnaam moet minstens 3 karakters zijn!";
		$error = true;
	}else{
		$checkuserQuery = "SELECT userID FROM users WHERE username = '$username'";
		$checkuserResult = mysqli_query($connect, $checkuserQuery) or die("Er is iets mis gegeaan tijdens het ophalen van gegevens.");
		
		//check if user already exist
		if(mysqli_num_rows($checkuserResult) != 0){
			$unameError = "*Gebruiker bestaat al!";
			$error = true;
		}
	}
	
	//Validate password input
	if(empty($password)){
		$passError = "*Geen wachtword ingevuld!";
		$error = true;
	}else{
		if(strlen($password) < 8){
			$passError = "*Wachtwoord moet minstens 8 karakters bevatten!";
			$error = true;
		}
	}
	
	//Validate email input
	if(empty($email)){
		$emailError = "*Geen email ingevuld!";
		$error = true;
	}else{
		$check = test_email($email);
		if($check == "pass"){
			$checkemailQuery = "SELECT userID FROM users WHERE email = '$email' LIMIT 1";
			$checkemailResult = mysqli_query($connect, $checkemailQuery) or die("Er is iets mis gegeaan tijdens het ophalen van gegevens.");
			
			//check if email already exist
			if(mysqli_num_rows($checkemailResult) != 0){
				$emailError = "*Deze e-mail bestaat al";
				$error = true;
			}
			
		}elseif($check == "name"){
			$emailError = "*'naam' gedeelte van uw e-mail moet minimaal 2 letters bevatten ('naam'@'domein'.nl)";
			$error = true;
		}elseif($check == "domain"){
			$emailError = "*'domein' gedeelte van uw e-mail moet minimaal 2 letters bevatten ('naam'@'domein'.nl)";
			$error = true;
		}else{
			$emailError = "*E-mail moet eindigen op '.nl'";
			$error = true;
		}
	}

	//Insert data into database
	if($error == false){
		$usernameEsc = mysqli_real_escape_string($connect, $username);
		$passwordEsc = mysqli_real_escape_string($connect, md5($password));
		$emailEsc = mysqli_real_escape_string($connect, $email);
		
		$insertQuery = "INSERT INTO users (userID, username, password, email) VALUES (NULL, '$usernameEsc', '$passwordEsc', '$emailEsc')";
		$insertResult = mysqli_query($connect, $insertQuery) or die("Er is iets mis gegeaan tijdens het invoeren van gegevens.");
		
		if(mysqli_affected_rows($connect) == 1){
			header('Location: index.php');
			exit();
		}else{
			echo "Er is iets fout gegaan tijdens het registreren.";
			error_log(mysqli_error($connect),3,"error_log.txt");
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
<span class=error><?php echo $errorMsg;?></span>
	<div class="register-form">
		<h2>Registreer</h2>
		<form method="POST" action="register.php">	
			<span class = error><?php echo $unameError;?></span>
			<label for="form-username">Gebruikersnaam:</label>
			<input type="text" id="form-username" name="username" value="<?php if($error){echo htmlentities($_POST['username']);}else{ echo "";}?>">
			
			<span class = error><?php echo $passError;?></span>		
			<label for="form-password">Wachtwoord:</label>
			<input type="password" id="form-password" name="password" value="<?php if($error){echo htmlentities($_POST['password']);}else{ echo "";}?>">
			
			<span class = error><?php echo $emailError;?></span>
			<label for="form-email">E-mail:</label>
			<input type="text" id="form-email" name="email" value="<?php if($error){echo htmlentities($_POST['email']);}else{ echo "";}?>">

			<input type="submit" name="submit" value="Registreren">
		</form>
	</div>
</div><!--end #container -->
</body>
</html>