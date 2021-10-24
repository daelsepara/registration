<?php

if (isset($_POST['backButton']))
{
	header("Location: index.php");
	
	exit();
}

include_once('dbconfig.php');

$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

if ($mysqli->connect_errno)
{
	header('HTTP/1.0 403 Forbidden');
	
	die('HTTP/1.0 403 Forbidden');
}

$total = $mysqli->query("SELECT * from account");

$limit = 20;

$offset = 0;

$prev = 0;
		
$next = $offset + $limit;

if (isset($_POST['prevButton']))
{
	$offset = isset($_POST['prev']) ? $_POST['prev'] : 0;
	
	$next = $offset + $limit;
	$prev = $offset - $limit;
	
	if ($prev < 0) $prev = 0;
}

if (isset($_POST['nextButton']))
{
	if ($offset + $limit < count($total))
	{
		$offset = isset($_POST['next']) ? $_POST['next'] : 10;
		
		$next = $offset + $limit;
		$prev = $offset - $limit;
		
		if ($prev < 0) $prev = 0;
	}
}

$records = $mysqli->query("SELECT * from account LIMIT $offset, $limit");

?>
<!DOCTYPE html>
<html lang="en">

<?php $page_title = "View Records"; ?>

<?php include_once("header.php"); ?>

<body>
    <div class="containter">
        <div class="container">
			<br/>
            <div class="row">
				<div class="col-sm-6">
					<h2><p class = "text-primary"><b>Records</b></p></h2>
                </div>
            </div>
            <hr class = "border border-primary"/>
            <a name = "records"/>
            <?php if ($records && $records->num_rows > 0): ?>
            <div class = "row">
				<div class = "col-auto">
					<table class = "table table-striped table-bordered">
						<thead class = "thead-dark">
							<tr>
								<th scope="col">AccountID</th>
								<th scope="col">First Name</th>
								<th scope="col">Last Name</th>
								<th scope="col">Birthdate</th>
								<th scope="col">DateAdded</th>
								<th scope="col"></th>
							</tr>
						</thead>
						<tbody>
						<?php foreach($records as $record): ?>
							<tr>
								<th scope="col"><?php echo $record['AccountID']?></th>
								<th scope="col"><?php echo $record['FirstName']?></th>
								<th scope="col"><?php echo $record['LastName']?></th>
								<th scope="col"><?php echo $record['BirthDate']?></th>
								<th scope="col"><?php echo $record['DateAdded']?></th>
								<th scope="col" class = "text-center"><button type="button" class="btn btn-primary record" data-toggle="modal" data-target="#itemsModal" id = <?php echo $record['AccountID']; ?>>Details</button></th>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
            </div>
            <hr class = "border border-primary"/>
			<form method = "post" action "records.php#records">
			<div class = "row">
				<div class = "col-md-2">
					<button type = "submit" class = <?php echo ($offset <= 0 ? "'btn btn-secondary btn-block'" : "'btn btn-primary btn-block'") ?>  name = "prevButton" id = "prevButton" value = "prevButton" <?php echo ($offset <= 0 ? 'disabled' : '') ?>>Previous</button>
				</div>
				<div class = "col-md-2">
					<button type = "submit" class = <?php echo ($next >= $total ? "'btn btn-secondary btn-block'" : "'btn btn-primary btn-block'") ?>  name = "nextButton" id = "prevButton" value = "nextButton" <?php echo ($next >= $total ? 'disabled' : '') ?>>Next</button>
				</div>
				<div class = "col-md-2">
					<button type = "submit" class = 'btn btn-primary btn-block' id = "backButton" name = "backButton">Back</button>
				</div>
			</div>
			<input type="hidden" name="prev" id="prev" value="<?php echo $prev; ?>">
			<input type="hidden" name="next" id="next" value="<?php echo $next; ?>">
			</form>
			<hr class = "border border-primary"/>
			<br/>
			<?php else: ?>			
			<div class = "row">
				<div class = "col-md-4">
					<p class = "text-secondary">No records found.</p>
				</div>
			</div>
			<hr class = "border border-primary"/>
			<div class = "row">
				<div class = "col-md-3">
					<a href="register.php" class="btn btn-primary">Register an account</a>
                </div>
                <div class = "col-md-3">
					<a href="index.php" class="btn btn-primary">Go back to the main page</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
	<div class="modal fade" id="itemsModal" tabindex="-1" role="dialog" aria-labelledby="itemsModalTitle" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLongTitle">Modal title</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				</div>
				<div class="modal-body">
					<div class = "text-center">
						<div class="spinner-border text-primary" role="status"></div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
	<script>
	$(document).ready(function(){
		
		var spinner = "<div class = 'text-center'><div class='spinner-border text-primary' role='status'></div></div>";
		
		console.log("ready");
		
		$('.record').click(function(){
			
			var record = $(this).attr('id');
			
			$('.modal-body').html(spinner);

			// create request
			$.ajax({
			url: 'get_record.php?q=' + record,
			type: 'get',
			data: {},
			success: function(response){ 
				
				var json = JSON.parse(response);
				
				$('.modal-title').html("<b>Record: <span class = 'text-primary'>" + json['FirstName'] + ' ' + json['LastName'] + "</span></b>");
				
				// add response in modal body
				var html_response = "<table class = 'table table-striped table-bordered'><thead class = 'thead-dark'><tr><th scope='col'>Field</th><th scope='col'>Value</th></tr></thead><tbody>";
				
				html_response += "<tr><th scope = 'col'><p class = 'text-primary'>First Name</p></th><th>" + json['FirstName'] + "</th></tr>";
				
				html_response += "<tr><th scope = 'col'><p class = 'text-primary'>Last Name</p></th><th>" + json['LastName'] + "</th></tr>";
				
				html_response += "<tr><th scope = 'col'><p class = 'text-primary'>Birthdate</p></th><th>" + json['BirthDate'] + "</th></tr>";
				
				html_response += "<tr><th scope = 'col'><p class = 'text-primary'>Address</p></th><th>" + json['Address'] + "</th></tr>";
				
				if (json['ProfilePicture'].length > 0)
				{
					html_response += "<tr><th scope = 'col'><p class = 'text-primary'>Profile Picture</p></th><th><img src = '" + json['ProfilePicture'] + "'/></th></tr>";
				}
				else
				{
					html_response += "<tr><th scope = 'col'><p class = 'text-primary'>Profile Picture</p></th><th></th></tr>";
				}
				
				html_response += "<tr><th scope = 'col'><p class = 'text-primary'>Credit Card</p></th><th>" + json['CardType'] + "</th></tr>";
				
				html_response += "<tr><th scope = 'col'><p class = 'text-primary'>Card Number</p></th><th>" + json['CardNumber'] + "</th></tr>";
				
				html_response += "<tr><th scope = 'col'><p class = 'text-primary'>Date Added</p></th><th>" + json['DateAdded'] + "</th></tr>";
				
				html_response += "</tbody></table>";

				$('.modal-body').html(html_response);

				// display modal
				$('#itemsModal').modal('show');
			}});
		});
	});
	</script>
</body>
</html>
