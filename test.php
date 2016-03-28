<?php
include "db_connect.php";
$matches="";

$getdataQuery = "SELECT vtthuis.team, vtuit.team FROM competitieschema AS cs, voetbalteams AS vtthuis, voetbalteams AS vtuit WHERE cs.thuis = vtthuis.teamID AND cs.uit = vtuit.teamID";
// AND cs.uit ON vtuit.teamID ;

$resultQuery = mysqli_query($connect, $getdataQuery);

if ($resultQuery)
{
    $data = mysqli_fetch_array($resultQuery);
    
    print_r($data);
}else{
    echo "Stupid query.";
}


if(isset($_POST['submit'])){
    $inputdate= test($connect,stripslashes(trim($_POST['datum'])));
}

function test($connect, $inputdate){
	$pattern = array(0 => '/(\d{2})(\d{2})(\d{4})/',
					1 => '/(\d{2})\-(\d{2})\-(\d{4})/',
					2 => '/(\d{2})\/(\d{2})\/(\d{4})/');
	
	if(preg_match($pattern[0], $inputdate, $matches)){
		$inputdate = array_slice($matches, 1);
	}elseif(preg_match($pattern[1], $inputdate)){
		$inputdate = explode("-", $inputdate);
	}elseif(preg_match($pattern[2], $inputdate)){
		$inputdate = explode("/", $inputdate);
	}else{
		echo "false";
		exit();
	}
	
	if(checkdate($inputdate[0],$inputdate[1], $inputdate[2])){
		$reorder = array($inputdate[2],$inputdate[0],$inputdate[1]);
		
		$combine = implode('-', $reorder);
		
		mysqli_query($connect, "INSERT INTO competitieschema (matchID, thuis, uit, datum) VALUES (NULL, 2, 4, '$combine');");
		echo $inputdate[0].$inputdate[1].$inputdate[2];
		return $combine;
	}else{
		echo "false";
	}
}
?>

<html>
<head>
<title></title>
</head>
<body>
<?php if(isset($_POST['submit'])){ echo "de datum is " . $inputdate;}?>
<form action="test.php" method="POST">
	<input type="text" name="datum">
	<input type="submit" name="submit" value="Submit">
</form>
</body>
</html>