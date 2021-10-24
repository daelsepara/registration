<?php

// tiny credit card validation script from: https://www.braemoor.co.uk/software/creditcard.php
include_once('phpcreditcard.php');

function validateUploadedPicture()
{
	// Adapted with minor modifications from https://www.php.net/manual/en/features.file-upload.php
	
	$error = false;
	
	$message = "";
	
	try {
		
		if (!isset($_FILES['profilePicture']['error']) || is_array($_FILES['profilePicture']['error'])
		)
		{
			throw new RuntimeException('Invalid parameters.');
		}

		if (strlen($_FILES['profilePicture']['tmp_name']) > 0)
		{
			// Check $_FILES['profilePicture']['error'] value.
			switch ($_FILES['profilePicture']['error'])
			{
				case UPLOAD_ERR_OK:
					break;
				case UPLOAD_ERR_NO_FILE:
					break;
				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
					throw new RuntimeException('Exceeded filesize limit.');
				default:
					throw new RuntimeException('Unknown errors.');
			}

			// You should also check filesize here.
			if ($_FILES['profilePicture']['size'] > 1000000)
			{
				throw new RuntimeException('Exceeded filesize limit.');
			}

			// DO NOT TRUST $_FILES['profilePicture']['mime'] VALUE !!
			// Check MIME Type by yourself.
			$finfo = new finfo(FILEINFO_MIME_TYPE);
			
			if ((false === $ext = array_search($finfo->file($_FILES['profilePicture']['tmp_name']),
				array(
					'jpg' => 'image/jpeg',
					'png' => 'image/png',
					'gif' => 'image/gif',),
					true)))
			{
				throw new RuntimeException('Invalid file format.');
			}

			// You should name it uniquely.
			// DO NOT USE $_FILES['profilePicture']['name'] WITHOUT ANY VALIDATION !!
			// On this example, obtain safe unique name from its binary data.
			$newFile = sprintf('./uploads/%s.%s', sha1_file($_FILES['profilePicture']['tmp_name']), $ext);
			
			if (!move_uploaded_file($_FILES['profilePicture']['tmp_name'], $newFile))
			{
				throw new RuntimeException('Failed to upload file.');
			}

			$message = $newFile;
			
			$error = false;
		}
	}
	catch (RuntimeException $e)
	{
		$message = $e->getMessage();
		
		$error = true;
	}
	
	return array('error' => $error, 'message' => $message);
}

include_once('dbconfig.php');

$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

if ($mysqli->connect_errno)
{
	header('HTTP/1.0 403 Forbidden');
	
	die('HTTP/1.0 403 Forbidden');
}

$cardTypes = ['American Express', 'Diners Club Carte Blanche', 'Diners Club', 'Discover', 'Diners Club Enroute','JCB', 'Maestro', 'MasterCard','Solo', 'Switch', 'VISA', 'VISA Electron', 'LaserCard'];

$firstName = isset($_POST["firstName"]) ? trim($_POST["firstName"]) : "";
$lastName = isset($_POST["lastName"]) ? trim($_POST["lastName"]) : "";
$birthDate = isset($_POST["birthDate"]) ? trim($_POST["birthDate"]) : "";
$address = isset($_POST["address"]) ? trim($_POST["address"]) : "";
$creditCardNumber = isset($_POST["creditCardNumber"]) ? trim($_POST["creditCardNumber"]) : "";
$creditCardName = isset($_POST["creditCardName"]) ? trim($_POST["creditCardName"]) : "";

$profilePicture = "";

$uploadError = "";

if (isset($_FILES['profilePicture']['name']))
{
	$result = validateUploadedPicture();
	
	if ($result['error'])
	{
		$uploadError = $result['message'];
	}
	else
	{
		$profilePicture = $result['message'];
	} 
}

$validation_errors = false;

$cardErrorNo = 0;
$cardErrorText = "";

$cardValid = true;

if (strlen($creditCardNumber) > 0 && strlen($creditCardNumber))
{
	$cardValid = checkCreditCard($creditCardNumber, $creditCardName, $cardErrorNo, $cardErrorText);
}
 
$registered = false;

if (isset($_POST['registerButton']))
{
	if (strlen($firstName) == 0)
	{
		$validation_errors = true;
	}
	else if (strlen($lastName) == 0)
	{
		$validation_errors = true;
	}
	else if (strlen($birthDate) == 0)
	{
		$validation_errors = true;
	}
	else if (strlen($address) == 0)
	{
		$validation_errors = true;
	}
	else if (strlen($uploadError) > 0)
	{
		$validation_errors = true;
	}
	else if (!$cardValid)
	{
		$validation_errors = true;
	}
	
	if (!$validation_errors)
	{
		// add record to db
		
		if (strlen($profilePicture) == 0)
		{
			$query = "INSERT INTO account(FirstName, LastName, BirthDate, Address, DateAdded) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)";
			
			$stmt = $mysqli->prepare($query);
			
			$stmt->bind_param("ssss", $firstName, $lastName, $birthDate, $address);

			$stmt->execute();
		}
		else
		{
			$query = "INSERT INTO account(FirstName, LastName, BirthDate, Address, ProfilePicture, DateAdded) VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";
			
			$stmt = $mysqli->prepare($query);
			
			$stmt->bind_param("sssss", $firstName, $lastName, $birthDate, $address, $profilePicture);
			
			$stmt->execute();
		}
		
		// account created
		if ($mysqli->affected_rows > 0)
		{
			$accountID = $mysqli->insert_id;
			
			if ($cardValid && strlen($creditCardNumber) > 0 && strlen($creditCardName) > 0)
			{
				$card_query = "INSERT INTO payment(AccountID, CardNumber, CardType, FirstName, LastName) VALUES (?, ?, ?, ?, ?)";
				
				$stmt = $mysqli->prepare($card_query);
			
				$stmt->bind_param("issss", $accountID, $creditCardNumber, $creditCardName, $firstName, $lastName);
				
				$stmt->execute();
				
				if ($mysqli->affected_rows > 0)
				{
					
				}
			}
			
			$registered = true;
		}
	}
}
?>
<!DOCTYPE html>
<html lang="en">

<?php $page_title = "Register an account"; ?>

<?php include_once("header.php"); ?>

<body class = "bg-dark">
	<style>
	textarea { resize:none; }
	</style>
    <div class = "containter">
        <div class = "container">
			<?php if (!$registered): ?>
			<br/>
            <div class = "row">
				<div class = "col-md-4">
					<h2 class = "text-primary"><b>Registration</b></h2>
                </div>
            </div>
            <hr class = "border-primary border"/>
            <form enctype="multipart/form-data" method = 'post' action = 'register.php'>
            <div class = "row">
				<div class = "col-md-4">
					<div class = "form-group">
						<label for="firstName" class = "text-info">First Name</label>
						<input type = "text" class = "border border-4 form-control<?php echo ($validation_errors && strlen($firstName) == 0) ? " is-invalid" : "" ?>" id = "firstName" name = "firstName" placeholder = "First Name" value = "<?php echo $firstName; ?>">
						<div class="invalid-feedback"><span class = "text-danger">Please enter your first name.</span></div>
					</div>
				</div>
				<div class = "col-md-4">
					<div class = "form-group">
						<label for="lastName" class = "text-info">Last Name</label>
						<input type = "text" class = "form-control<?php echo ($validation_errors && strlen($lastName) == 0) ? " is-invalid" : "" ?>" id = "lastName" name = "lastName" placeholder = "Last Name" value = "<?php echo $lastName; ?>">
						<div class="invalid-feedback"><span class = "text-danger">Please enter your last name.</span></div>
					</div>
				</div>
            </div>
            <div class = "row">
				<div class = "col-md-4">
					<div class = "form-group">
						<label for="birthDate" class = "text-info">Birthdate</label>
						<input type = "text" class = "form-control datepicker<?php echo ($validation_errors && strlen($birthDate) == 0) ? " is-invalid" : "" ?>" name = "birthDate" id = "birthDate" value = "<?php echo $birthDate; ?>" data-provide="datepicker">
						<div class="invalid-feedback"><span class = "text-danger">Please enter your birthdate.</span></div>
					</div>
				</div>
            </div>
            <div class = "row">
				<div class = "col-md-8">
					<div class = "form-group">
						<label for="address" class = "text-info">Complete Address</label>
						<textarea class = "form-control<?php echo ($validation_errors && strlen($address) == 0) ? " is-invalid" : "" ?>" id = "address" name = "address" rows = "4"><?php echo $address; ?></textarea>
						<div class="invalid-feedback"><span class = "text-danger">Please provide your complete address.</span></div>
					</div>
				</div>
            </div>
            <div class = "row">
				<div class = "col-md-8">
					<p class = "text-info">Profile Picture</p>
					<div class = "form-group">
						<div class = "custom-file">
							<label class = "custom-file-label" for="profilePicture">Choose a profile picture</label>
							<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
							<input type = "file" class = "form-control custom-file-input" id = "profilePicture" name = "profilePicture">
						</div>
					</div>
				</div>
            </div>
			<?php if (strlen($uploadError) > 0): ?>
			<div class = "row"><div class = "col-md-4"><span class = "text-danger"><?php echo $uploadError; ?></span></div></div>
			<br/>
			<?php endif; ?>
			<div class = "row">
				<div class = "col-lg-2">
					<p class = "text-info">Credit Card Type</p>
				</div>
				<div class = "col-md-4">
					<select class="form-select form-select-lg mb-3" aria-label="Credit Card Type" name = "creditCardName" id = "creditCardName">
						<option value=""<?php echo strlen($creditCardName) == 0 || strlen($creditCardNumber) == 0 ? "selected" : "" ?>></option>
						<?php foreach($cardTypes as $card): ?>
						<option value = "<?php echo $card; ?>"<?php echo strtolower($creditCardName) == strtolower($card) ? "selected" : "" ?>><?php echo $card; ?></option>
						<?php endforeach; ?>
					</select>
				</div>
            </div>
            <div class = "row">
				<div class = "col-md-4">
					<div class = "form-group">
						<label for="creditCardNumber" class = "text-info">Credit Card Number</label>
						<input type = "text" class = "form-control" id = "creditCardNumber" name = "creditCardNumber" placeholder = "" value = "<?php echo $creditCardNumber; ?>">
					</div>
				</div>
            </div>
            <?php if (!$cardValid): ?>
			<div class = "row"><div class = "col-md-4"><span class = "text-danger"><?php echo $cardErrorText; ?></span></div></div>
			<br/>
			<?php endif; ?>
			<hr class = "border-primary border"/>
            <div class = "row">
				<div class = "col-md-4">
					<button type = "submit" class = "btn btn-primary" name = "registerButton" id = "registerButton">Submit</button>		
				</div>
            </div>
            </form>
            <br/>
            <?php else: ?>
            <br/>
            <div class = "row">
				<div class = "col-md-8">
					<h2 class = "text-success"><b>Registration Successful!</b></h2>
                </div>
            </div>
            <hr class = "border-primary border"/>
            <div class = "row">
				<div class = "col-md-3">
					<a href="register.php" class="btn btn-primary">Register another account</a>
                </div>
                <div class = "col-md-3">
					<a href="index.php" class="btn btn-primary">Go back to the main page</a>
                </div>
            </div>            
            <?php endif; ?>
        </div>
    </div>
    <script>
	$(document).ready(function(){
		
		bsCustomFileInput.init();
		 
		 // selecting dates
		$('.datepicker').datepicker({
			format: 'yyyy-mm-dd',
			startDate: '1900-01-01'
		});
	});
    </script>
</body>

</html>
