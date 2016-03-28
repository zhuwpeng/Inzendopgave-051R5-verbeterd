<?php
E_ALL;

include "db_connect.php";
include "functions.php";
session_start();

$matchID = "";
$matchIDesc = "";
$message = "";
$selectmatch = "";

$errormsg = "";
$thuisError = "";
$uitError = "";

$error = false;

if(!isset($_SESSION['userID'])){
	header("Location: login.php");
	exit();
}else{
	
	if(isset($_GET['matchID'])){
		$matchID = $_GET['matchID'];
	}else{
		$matchID = $_POST['matchID'];
	}
	
	//get the team names
	$matchQuery = "SELECT thuis, uit FROM competitieschema WHERE matchID = $matchID";
	$matchResult = mysqli_query($connect, $matchQuery) or die("Kan match data niet ophalen.");
	
	$matchdata = mysqli_fetch_assoc($matchResult);
	$thuisID = $matchdata['thuis'];
	$uitID = $matchdata['uit'];
	
	$thuisnaamQuery = "SELECT team FROM voetbalteams WHERE teamID = '$thuisID'";
	$uitnaamQuery = "SELECT team FROM voetbalteams WHERE teamID = '$uitID'";
	
	$thuisnaamResult = mysqli_query($connect, $thuisnaamQuery);
	$uitnaamResult = mysqli_query($connect, $uitnaamQuery);
	

	if (!$thuisnaamResult || !$uitnaamResult) {
		die("Er is iets mis gegeaan tijdens het ophalen van team namen.");
	} else {
		$naamThuis = mysqli_fetch_row($thuisnaamResult);
		$naamUit = mysqli_fetch_row($uitnaamResult);
	
		$thuisTeam = $naamThuis[0];
		$uitTeam = $naamUit[0];
	}
	
	if(isset($_POST['submit']) && $_POST['submit']=='Updaten'){
		$matchID = mysqli_real_escape_string($connect, $_POST['matchID']);
		
		$scorethuis = $_POST['scorethuis'];
		$scoreuit = $_POST['scoreuit'];

		if($scorethuis == ""){
			$thuisError = "U heeft geen waarde ingevuld";
			$error = true;
		}

		if($scoreuit == ""){
			$uitError = "U heeft geen waarde ingevuld";
			$error = true;
		}

		//validate input score
		if(preg_match('[^A-Za-z0-9]', $scorethuis)){
			$thuisError = "Scores mogen alleen nummers bevatten";
			$error = true;
		}
		if(preg_match('[^A-Za-z0-9]', $scoreuit)){
			$uitError = "Scores mogen alleen nummers bevatten";
			$error = true;
		}
		
		//update score using matchID
		if($error == false){
			$scorethuis = mysqli_real_escape_string($connect, $_POST['scorethuis']);
			$scoreuit = mysqli_real_escape_string($connect, $_POST['scoreuit']);
			
			$insertQuery = "UPDATE competitieschema SET scorethuis='$scorethuis', scoreuit='$scoreuit' WHERE matchID = '$matchID'";
			$insertResult = mysqli_query($connect, $insertQuery) or die("Er is iets mis gegeaan tijdens het uitvoeren van de update query.");
				
			if(mysqli_affected_rows($connect) == 1){
				header('Location: index.php');
				exit;
			}else{
				$message = "Er is iets mis gegaan tijdens het bijwerken van de scores.";
				error_log(mysqli_error($connect),3, "error_log.txt");
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
<?php echo $matchIDesc; ?>
	<div class="nav">
		<?php echo get_navbar($connect); ?>
	</div>
	<span><?php echo $message;?></span>
	<span class=error><?php echo $errormsg;?></span>
	<div class="score-form">
		<h2>Werk de score bij</h2>
		<form method="POST" action="score.php">
			<span class=error><?php echo $thuisError;?></span>
			<label for="score-thuis">Doelpunten <?php echo $thuisTeam;?></label>
			<input type="number" name="scorethuis" id="score-thuis" min="0" max="20" value="<?php if($error){echo htmlentities($_POST['scorethuis']);}?>">

			<span class=error><?php echo $uitError;?></span>
			<label for="score-uit">Doelpunten <?php echo $uitTeam;?></label>
			<input type="number" name="scoreuit" id="score-uit" min="0" max="20" value="<?php if($error){echo htmlentities($_POST['scoreuit']);}?>">

			<input type="hidden" name="matchID" value="<?php echo $matchID; ?>">
			<input type="submit" name="submit" value="Updaten">
		</form>
	</div>
</div><!-- end #container -->
</body>
</html>