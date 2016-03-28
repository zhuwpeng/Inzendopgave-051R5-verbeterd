<?php
$host = "localhost";
$username = "user";
$password = "password";

$connect = mysqli_connect($host, $username, $password)
or die ("Could not connect to database.");

//Create table
function make_table($connect, $tabelkolommen, $tabelname){
	$testQuery = "SELECT 1 FROM $tabelname";
	//$testResult = mysqli_query($connect, $testQuery);

	if(!mysqli_query($connect, $testQuery))
	{
		$query = "CREATE TABLE $tabelname ($tabelkolommen);";
		$result = mysqli_query($connect, $query) or die("Kan geen verbinding maken met de database.");

		if(mysqli_query($connect, "SELECT 1 FROM $tabelname"))
		{
			return "Table '$tabelname' has successfully been created in database 'dbloi'.<br>";
		}else{
			return "Please check you query for any mistakes " . mysqli_error($connect);
		}
	}
	else{
		return "The table $tabelname already exists. <br>";
	}
}

//Fill voetbalteams
function fill_voetbaltable($connect){
	$voetbalteamsData = "INSERT INTO voetbalteams
						(teamID, team, plaats, speelsterkte)VALUES
						(NULL, 'Nederland', 'Amsterdam', 1876),
						(NULL, 'Duitsland', 'Munchen', 2046),
						(NULL, 'Engeland', 'Londen', 1934),
						(NULL, 'Frankrijk', 'Parijs', 1931),
						(NULL, 'Spanje', 'Madrid', 1982);";

	$insert_voetbalteamsData = mysqli_query($connect, $voetbalteamsData) or die("Kan geen verbinding maken met de database.");

	if (mysqli_affected_rows($connect) == 5)
	{
		return " Table 'voetbalteams' has been filled with the teams Nederland, Duitsland, Engeland, Frankrijk and Spanje. <br>";
	}else{
		return "Please check you query for any mistakes " . mysqli_error($connect);
	}
}

//table names and query information
$usertabelname = "users";
$usertabelkolommen = "userID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					username VARCHAR(20) NOT NULL,
					password VARCHAR(32) NOT NULL,
					email VARCHAR(50) NULL";

$voetbalteamstabelname = "voetbalteams";
$voetbalteamskolommen = "teamID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
						team VARCHAR(32) NOT NULL,
						plaats VARCHAR(64),
						speelsterkte INT";

$compschematabelname = "competitieschema";
$compschemakolommen= "matchID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					thuis INT NOT NULL,
					uit INT NOT NULL,
					datum date,
					scorethuis INT,
					scoreuit INT";

//check if database exist
if(!mysqli_select_db($connect, 'dbloi'))
{
	mysqli_query($connect, "CREATE DATABASE dbloi;");
	
	echo "Database dbloi has been created. <br>";
}else{
	
	echo "The database dbloi already exists. <br>";
}

if(mysqli_select_db($connect, 'dbloi')){
		//users
		$create_userstabel = make_table($connect, $usertabelkolommen, $usertabelname);

		echo $create_userstabel;

		//voetbalteams
		$create_voetbalteamstabel = make_table($connect, $voetbalteamskolommen, $voetbalteamstabelname);

		echo $create_voetbalteamstabel;
		
		//add teams
		$checkData = "SELECT * FROM voetbalteams";
		$result = mysqli_query($connect, $checkData);

		if(!mysqli_num_rows($result))
		{
			$teams = fill_voetbaltable($connect);
			echo $teams;
		}
		else{
			echo "The default teams have already been added in the database. <br>";
		}

		//competitieschema
		$create_compschematabel = make_table($connect, $compschemakolommen, $compschematabelname);
		echo $create_compschematabel;
}
?>