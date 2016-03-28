<?php
include "db_connect.php";
include "functions.php";
session_start();

$message = "";
$selectField = "";
$thuisselectField = "";
$uitselectField = "";

$selectError = "";
$dateError = "";

$error = false;

//check if session contains a user id and redirect if not set
if(!isset($_SESSION['userID'])){
	header("Location: login.php");
	exit();
}else {
	
	//get teams for selectfield
	$selectQuery = "SELECT teamID, team FROM voetbalteams";
	$selectResult = mysqli_query($connect, $selectQuery) or die("Er is iets mis gegeaan tijdens het ophalen van teamdata.");
	
	//assign data to selectfield
	for($i=0; mysqli_num_rows($selectResult) > $i; $i++){
		$teamData[$i]= mysqli_fetch_row($selectResult);
		$thuisselectField .= "<option value=" . $teamData[$i][0] . ">" . $teamData[$i][1] . "</option>";
		$uitselectField .= "<option value=" . $teamData[$i][0] . ">" . $teamData[$i][1] . "</option>";
	}
			
	//check if submit has been pressed and validate userinput
	if (isset($_POST['submit']) && $_POST['submit'] == 'Aanmaken') {
		$thuisploeg = stripslashes(trim($_POST['thuis']));
		$uitploeg = stripslashes(trim($_POST['uit']));
		$datum = stripslashes(trim($_POST['datum']));
		
		if ($_POST['thuis'] == $_POST['uit']) {
			$selectError = "Thuis en uit spelende ploegen mogen niet hetzelfde zijn!";
			$error = true;
		} 
		
		if (empty($datum)){
			$dateError = "U heeft geen geldig datum ingevuld!";
			$error = true;
		}else{
			$valDate = date_validation($datum);
		
			if($valDate == false){
				$dateError = "Datum format moet dd-mm-yyyy (vb. 23-01-2000) zijn.";
				$error = true;
			}
			
			if($valDate == "toekomst"){
				$dateError = "Jaar is te ver in de toekomst";
				$error = true;
			}
		}
		
		//insert into database if no error
		if ($error == false) {
			$thuisploeg = mysqli_real_escape_string($connect, $_POST['thuis']);
			$uitploeg = mysqli_real_escape_string($connect, $_POST['uit']);
			$validdatum = mysqli_real_escape_string($connect, $valDate);
			
			//insert data into competitieschema
			$insertquery = "INSERT INTO competitieschema (matchID, thuis, uit, datum) VALUES (NULL, '$thuisploeg', '$uitploeg', '$validdatum')";
			$insertresult = mysqli_query($connect, $insertquery) or die("Er is iets mis gegeaan tijdens het invoeren van gegevens.");
			
			//check if data has been inserted and redirect or log error if error happens
			if (mysqli_affected_rows($connect) == 1) {
				header('Location: index.php');
				exit();
			} else {
				$message = "Er is iets mis gegaan tijdens het toevoegen van een nieuwe wedstrijd.";
				error_log(mysqli_error($connect), 3, "error_log.txt");
			}
			
		}else{
			//create the selectfield with the previously selected field
			$thuisselectField = "";
			$uitselectField = "";
			for($i=0; mysqli_num_rows($selectResult) > $i; $i++){
				$thuisselectField .= "<option " . ($_POST['thuis'] == $teamData[$i][0] ? "Selected" : '') . " value=" . $teamData[$i][0] . ">" . $teamData[$i][1] . "</option>";
				$uitselectField .= "<option " . ($_POST['uit'] == $teamData[$i][0] ? "Selected" : '') .  " value=" . $teamData[$i][0] . ">" . $teamData[$i][1] . "</option>";
			}
		}
	}
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Inleveropgave 051R5</title>
<link rel="stylesheet" type="text/css" href="css/style.css">
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
	<div class="matchinsert-form">
		<h2>Wedstrijd aanmaken</h2>
		<form method="POST" action="invoerschema.php">
			<label for="select-thuis">Thuis spelende ploeg</label>
			<select name="thuis" id="select-thuis">
			<?php echo $thuisselectField;?>
			</select>

			<span class=error><?php echo $selectError;?></span>
			<label for="select-uit">Uit spelende ploeg</label>
			<select name="uit" id="select-uit">
			<?php echo $uitselectField;?>
			</select>

			<span class=error><?php echo $dateError;?></span>
			<label for="form-datum">Speel datum</label>
			<input type="text" name="datum" value="<?php if($error){echo htmlentities($_POST['datum']);}else{ echo"";}?>" id="form-datum">
			<input type="submit" name="submit" value="Aanmaken">
		</form>
	</div>
</div><!-- end #container -->
</body>
</html>