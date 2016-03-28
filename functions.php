<?php
//Check if correct format email
function test_email($email)
{
	$match_pattern = '/^[a-z]*[A-Z]*@[a-z]*[A-Z]*.nl$/';
	$match = preg_match($match_pattern, $email);

	if($match){
		$explode_result = explode("@", $email);
		$explode_result_domain = explode(".", $explode_result[1]);
		$name = $explode_result[0];
		$domain =$explode_result_domain[0];
		
		if(strlen($name) < 2){
			return "name";
		}elseif( strlen($domain) < 2){
			return "domain";
		}else{
			return "pass";
		}
	}else{
		return false;
	}
}

function get_navbar($connect)
{
	if(isset($_SESSION['userID'])){
		
		$userID = $_SESSION['userID'];
		
		//retrieve username
		$nameQuery = "SELECT username FROM users WHERE userID = $userID;";
		$nameResult = mysqli_query($connect, $nameQuery);
		$userData = mysqli_fetch_assoc($nameResult);
		
		$username = $userData['username'];
		
				
		return "<ul>
						<li><a href=\"index.php\">Home</a></li>
						<li><a href=\"logout.php\">Log uit</a></li>
						<li><a href=\"invoerschema.php\">Wedstrijd toevoegen</a></li>
						<ul>
							<li><p>Welkom <b>". $username . "</b></p></li>
						</ul>
				</ul>";
	}else{
		return "<ul>
					<li><a href=\"index.php\">Home</a></li>
					<li><a href=\"login.php\">Login</a></li>
					<li><a href=\"register.php\">Registreer</a></li>
				</ul>";
	}
}

function date_validation($inputdate){
	
	$matches="";
	
	$pattern = '/(\d{2})\-(\d{2})\-(\d{4})/';
	
	if(preg_match($pattern, $inputdate)){
		$inputdate = explode("-", $inputdate);
	}else{
		return false;
	}
	
	if($inputdate[2]> 2030){
		return "toekomst";
	}
	
	if(checkdate($inputdate[1],$inputdate[0], $inputdate[2])){
		$reorder = array($inputdate[2],$inputdate[1],$inputdate[0]);
		$inputdate = implode('-', $reorder);
		return $inputdate;
	}else{
		return false;
	}
}

