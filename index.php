<?php
  	session_start();
  	if(isset($_SESSION['admin'])){
    	header('location: admin/home.php');
  	}

    if(isset($_SESSION['voter'])){
      header('location: home.php');
    }
?>
<style>
	.login-page {
		background-color: #F1F1F1 !important;
	}
	.inner-body {
		display: flex;
		background-color: #fff;
		justify-content: center;
		height: 100%;
		width: 100%;
		border-radius: 30px;
		transform: scale(98%);
	}
	.login-logo-container {
		text-align: center;
 	}
	.login-logo-container h1 {
		font-weight: bold;
		color: #7D7D7D;
		letter-spacing: 2px;
 	}

	.login-logo-container img {
		width: 135px;
		height: 134px;
		flex-shrink: 0;
 	}

	.login-box {
		width: 400px !important;
		margin: 0 auto;
	}

	.login-box-body button {
		text-align: center;
		width: 100%;
		border-radius: 50px;
	}

	/* Typography */
	.text-smaller {
		color: #8C8C8C;
		text-align: center;
		font-family: "SF Pro Display";
		font-size: 12px;
		font-style: light;
		font-weight: 300;
		line-height: normal;
		letter-spacing: 0.5px;
	}
	
	.lined {
		overflow: hidden;
		text-align: center;
	}

	.lined::before,
	.lined::after {
		background-color: #D4D4D4;
		content: "";
		display: inline-block;
		height: 1px;
		position: relative;
		vertical-align: middle;
		width: 35%;
	}
	.lined::before {
		right: 0.5em;
		margin-left: -50%;
	}
	.lined::after {
		left: 0.5em;
		margin-right: -50%;
	}

	.forgot-password {
		display: block;
		text-align: right;
		margin-top: 10px;
		text-decoration: underline;
		color: #239746;
	}

	.forgot-password:hover a, .forgot-password:active a, .forgot-password:hover {
		color: #17632e !important;
	}

	/* Textboxes */
	.username, .password {
		border-radius: 10px !important;
		height: 45px !important;
	}

	/* Buttons */
	.custom {
		background-color: #FFF !important;
		color: #239746 !important;
		transition: all ease 0.2s !important;
	}
	.custom:hover {
		background-color: #239746 !important;
		color: #FFF !important;
		border-color: #239746 !important;
	}

	.form-control-feedback.fa {
  		line-height: 45px !important;
		margin-right: 5px !important;
		font-size: 20px !important;
		float: left !important;
	}
	
	.username::placeholder, .password::placeholder {
		font-size: 13px !important;
		color: #7C7C7C !important;
	}
</style>
<?php include 'includes/header.php'; ?>
<body class="hold-transition login-page">
	<div class="inner-body">
		<div class="login-box">
			<div class="login-logo-container">
				<img src="images/login.jpg" alt="">
				<h1><span>E-HALAL</span> <br> BTECHenyo</h1>
			</div>
			<p class="text-center text-smaller">A WEB-BASED VOTING SYSTEM FOR<br>DALUBHASAANG POLITEKNIKO NG LUNGSOD NG BALIWAG</p>

			<div class="login-box-body">
				<p class="text-center text-smaller lined"><span>LOGIN WITH YOUR STUDENT NUMBER</span></p>

				<form action="login.php" method="POST" role="presentation" autocomplete="off">
					<div class="form-group has-feedback">
						<input type="text" autocomplete="off" class="form-control username" name="voter" placeholder="ENTER YOUR STUDENT NUMBER" required>
						<span class="fa fa-fingerprint form-control-feedback"></span>
					</div>
					<div class="form-group has-feedback">
						<input type="password" autocomplete="off" class="form-control password" name="password" placeholder="PASSWORD" required>
						<span class="fa fa-key form-control-feedback"></span>
						<span class="forgot-password"><a href="#">Forgot Password?</a></span>
					</div>
					<button type="submit" class="btn btn-primary btn-block btn-flat custom" name="login">SIGN IN</button>
				</form>
			</div>
			<?php
				if(isset($_SESSION['error'])){
					echo "
						<div class='callout callout-danger text-center mt20'>
							<p>".$_SESSION['error']."</p> 
						</div>
					";
					unset($_SESSION['error']);
				}
			?>
		</div>
	</div>
	
	<?php include 'includes/scripts.php' ?>
</body>
</html>
