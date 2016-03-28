<?php
include "db_connect.php";
include "functions.php";

session_start();

$message="";
$matchtabelCaption = "Competitieschema";
$telteams = 0;
$equalstandings = 0;
$pouletabelmsg = "";

$error = false;

//retrieve matchdata and teamdata
$matchdataquery = "SELECT * FROM competitieschema ORDER BY datum ASC";
$matchdataresult = mysqli_query($connect, $matchdataquery) or die("Er is iets mis gegeaan tijdens het ophalen van tabel gegevens.");

$matchtabel = "";
$matchnumrows = mysqli_num_rows($matchdataresult);

if($matchnumrows > 0){
	while($matchdata = mysqli_fetch_assoc($matchdataresult)){

		//assign data to variable from associative array
		$matchID = $matchdata['matchID'];
		$thuisID = $matchdata['thuis'];
		$uitID = $matchdata['uit'];
		$datumdb = $matchdata['datum'];
		$datum = date("d-m-Y", strtotime($datumdb));
		$scorethuis = $matchdata['scorethuis'];
		$scoreuit = $matchdata['scoreuit'];

		if($scorethuis == NULL){
			$scorethuis = 0;
		}

		if($scoreuit == NULL){
			$scoreuit = 0;
		}

		//retrieve teamname and place from table voetbalteams
		$teamnameQuery = "SELECT teamID, team, plaats FROM voetbalteams WHERE (teamID = '$thuisID' OR teamID = '$uitID');";
		$teamnameResult = mysqli_query($connect, $teamnameQuery) or die("Er is iets mis gegeaan tijdens het ophalen van team gegevens.");

		if($teamnameResult){
			for($i= 0; $i< mysqli_num_rows($teamnameResult); $i++){
				$teamnameArray [$i] = mysqli_fetch_row($teamnameResult);
			}

			//assign data to variables
			if($teamnameArray[0][0] == $thuisID){
				$thuisname = $teamnameArray[0][1];
				$plaats = $teamnameArray[0][2];
				$uitname = $teamnameArray[1][1];
			}else{
				$thuisname = $teamnameArray[1][1];
				$plaats = $teamnameArray[1][2];
				$uitname = $teamnameArray[0][1];
			}
			
			//add corresponding images to each team
			$thuisimg = '<img alt="'.$thuisname.'" src="images/'.strtolower($thuisname).'.jpg">';
			$uitimg = '<img alt="'.$uitname.'" src="images/'.strtolower($uitname).'.jpg">';
			
			$datumlink = $datum;
			
			//store data in variable to display
			if(isset($_SESSION['userID'])){				
				if($scorethuis == NULL && $scoreuit == NULL){
					$datumlink = '<a href="score.php?matchID='.$matchID.'">'.$datum .'</a>';
				}
			}
			$matchtabel .= "<tr>
								<td>$thuisimg"." "."$thuisname</td>
								<td>$uitimg"." "."$uitname</td>
								<td>$scorethuis : $scoreuit</td>
								<td>$plaats</td>
								<td>$datumlink</td>
							</tr>";
		}
	}
}else{
	$matchtabelCaption = "Er zijn nog geen wedstrijden toegevoegd. Log in om een wedstrijd toe te voegen of registreer een account.";
	$matchtabel = "";
	
	if(!empty($_SESSION['userID'])){
	$matchtabelCaption = "Er zijn nog geen wedstrijden toegevoegd.";
	}
}


//retrieve data for pouletabel
$pouledataquery = "SELECT teamID, team, speelsterkte FROM voetbalteams;";
$pouledataresult = mysqli_query($connect, $pouledataquery) or die("Er is iets mis gegaan tijdens het ophalen van team gegevens.");

//check if there are any rows with data
$poulenumrows = mysqli_num_rows($pouledataresult);
$pouletabel = "";

//loop through and store data into an array to display on screen
if($poulenumrows > 0){
	while($pouledata = mysqli_fetch_assoc($pouledataresult)){

		//assign data to variable from assoc array
		$teamID = $pouledata['teamID'];
		$team = $pouledata['team'];
		$speelsterkte = $pouledata['speelsterkte'];
		$pouleimg = "<img alt=".$team." src=\"images/".strtolower($team).".jpg\">";

		//reset veriables
		$scoreVoor = 0;
		$scoreTegen = 0;
		$wins = 0;
		$even = 0;
		$loss = 0;
		$stand = 0;
		$nettscore = 0;
		$telmatches = 0;

		//get data from played matches to display match data
		$playedmatchesQuery = "SELECT * FROM competitieschema WHERE (thuis = '$teamID' OR uit = '$teamID');";
		$playedmatchesResult = mysqli_query($connect, $playedmatchesQuery) or die("Er is iets mis gegaan tijdens het ophalen van competitieschema gegevens.");

		if($playedmatchesResult){
			while($playedmatches = mysqli_fetch_assoc($playedmatchesResult)){
				
				$matchthuisID = $playedmatches['thuis'];
				$matchtuitID = $playedmatches['uit'];
				
				if(!is_null($playedmatches['scorethuis']) && !is_null($playedmatches['scoreuit'])){
					if($teamID != $matchthuisID){
						$scoreVoor = $scoreVoor + $playedmatches['scoreuit'];
						$scoreTegen = $scoreTegen + $playedmatches['scorethuis'];
						$matchthuisScore = $playedmatches['scoreuit'];
						$matchuitScore = $playedmatches['scorethuis'];
					}else{					
						$scoreVoor = $scoreVoor + $playedmatches['scorethuis'];
						$scoreTegen = $scoreTegen + $playedmatches['scoreuit'];
						$matchthuisScore = $playedmatches['scorethuis'];
						$matchuitScore = $playedmatches['scoreuit'];
					}
					
					if($matchthuisScore > $matchuitScore){
						$wins++;
						$stand = $stand + 3;
					}elseif($matchthuisScore == $matchuitScore){
						$even++;
						$stand = $stand + 1;
					}else{
						$loss++;
					}
					
					//calculate goalbalance
					$nettscore = $scoreVoor - $scoreTegen;
					$telmatches++;
				}
			}
		}
		
		$telteams++;

		//store data in variable to sort
		$pouletabelArray[$telteams]= array($pouleimg. " " . $team, $speelsterkte, $telmatches, $wins, $even, $loss, $scoreVoor, $scoreTegen, $nettscore, $stand);
	}
}else{
	$pouletabelmsg = "Geen data beschikbaar";
}


//sort array based on poulestanding
foreach($pouletabelArray as $index => $row){
	$columndoelstand[$index] = $row[8];
	$columnpoulestand[$index] = $row[9];
}

//sort table based on doelsaldo 
array_multisort($columnpoulestand, SORT_DESC, $columndoelstand, SORT_DESC, $pouletabelArray);

for($teams = 0; $teams < $poulenumrows; $teams++){
	$pouletabel .= "<tr>";
	for($stats = 0; $stats < count($pouletabelArray[0]); $stats ++){
		$pouletabel .= "<td>".$pouletabelArray[$teams][$stats]."</td>";
	}
	$pouletabel .= "</tr>";
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
	<span class = error><?php echo $message; echo $pouletabelmsg; ?></span>

	<table class="matchtabel">
		<caption><?php echo $matchtabelCaption; ?></caption>
		<tr>
			<?php if(!empty($matchtabel)){echo "<th>Thuis</th>
												<th>Uit</th>
												<th>Stand</th>
												<th>Locatie</th>
												<th>Speeldatum</th>";}
			?>
		</tr>
		<?php echo $matchtabel;?>
	</table>

	<table class="pouletabel">
		<caption>Poulestand</caption>
		<tr>
			<th>Team</th>
			<th>Speelsterkte (elo)</th>
			<th>Gespeeld</th>
			<th>Win</th>
			<th>Gelijkspel</th>
			<th>Verloren</th>
			<th>Goals voor</th>
			<th>Goals tegen</th>
			<th>Doelsaldo</th>
			<th>Poulestand</th>
		</tr>
		<?php echo $pouletabel;?>
	</table>
</div>
</body>
</html>