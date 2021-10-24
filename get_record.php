<?php
include_once('dbconfig.php');

$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

if ($mysqli->connect_errno)
{
	header('HTTP/1.0 403 Forbidden');
	
	$result = [];

	return json_encode($result);
}

$q = trim($_REQUEST['q']);

if (is_numeric($q))
{
	$query = "SELECT account.*, payment.CardType, payment.CardNumber FROM account LEFT JOIN payment on account.AccountID = payment.AccountID WHERE account.AccountID = ? LIMIT 1";
	 
	$stmt = $mysqli->prepare($query);
			
	$stmt->bind_param("i", $q);

	$stmt->execute();
	
	$record = $stmt->get_result();
	
	$result = [];
	
	if ($record->num_rows > 0)
	{
		$result_array = $record->fetch_array();
		
		$result['AccountID'] = $result_array['AccountID'];
		$result['FirstName'] = $result_array['FirstName'];
		$result['LastName'] = $result_array['LastName'];
		$result['BirthDate'] = $result_array['BirthDate'];
		$result['Address'] = $result_array['Address'];
		$result['ProfilePicture'] = $result_array['ProfilePicture'];
		$result['DateAdded'] = $result_array['DateAdded'];
		$result['CardType'] = trim($result_array['CardType']);
		$result['CardNumber'] = trim($result_array['CardNumber']);
	}
	
	echo json_encode($result);
}

?>
