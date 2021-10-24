<!DOCTYPE html>
<html lang="en">

<?php $page_title = "Online Registration and Records"; ?>

<?php include_once("header.php"); ?>

<body>
    <div class="containter">
        <div class="container">
			<br/>
            <div class="row">
				<div class="col-sm-6">
					<h2><p class = "text-primary"><b>Registration</b></p></h2>
                </div>
            </div>
            <hr class = "border-primary border"/>
            <div class="row">
				<div class="col-sm-6">
					<div class="card border-info">
					  <div class="card-body">
						<h5 class="card-title">Registration</h5>
						<p class="card-text">Register for an account online</p>
						<a href="register.php" class="btn btn-primary">Go to registration page</a>
					  </div>
					</div>
				</div>
				<div class="col-sm-6">
					<div class="card border-info">
					  <div class="card-body">
						<h5 class="card-title">Records</h5>
						<p class="card-text">View all registrations.</p>
						<a href="records.php" class="btn btn-primary">See Records</a>
					  </div>
					</div>
				</div>
			</div>
        </div>
    </div>
</body>

</html>
