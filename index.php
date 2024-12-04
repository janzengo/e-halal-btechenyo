<?php
session_start();
include 'includes/conn.php';

// Check if election_status table is empty
$sql = "SELECT COUNT(*) as count FROM election_status";
$query = $conn->query($sql);
$row = $query->fetch_assoc();
if ($row['count'] == 0) {
    $election_status = 'no_election';
} else {
    // Check election status
    $sql = "SELECT status FROM election_status WHERE id = 1";
    $query = $conn->query($sql);
    $row = $query->fetch_assoc();
    if ($query->num_rows > 0) {
        $election_status = $row['status'];
    } else {
        $election_status = 'paused';
    }
}

if(isset($_SESSION['admin'])){
    header('location: admin/home.php');
}

if(isset($_SESSION['voter'])){
    header('location: home.php');
}
?>
<?php include 'includes/links.php'; ?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition login-page">
    <div class="inner-body">
        <div class="login-box">
            <div class="login-logo-container">
                <img src="images/login.jpg" alt="">
                <h1><span>E-HALAL</span> <br> BTECHenyo</h1>
            </div>
            <p class="text-center text-smaller">A WEB-BASED VOTING SYSTEM FOR<br>DALUBHASAANG POLITEKNIKO NG LUNGSOD NG BALIWAG</p>
            <?php 
            if ($election_status == 'off'): ?>
                <section class="election-message">
                    <div class="election-message-box">
                        <h2>ELECTION PERIOD ENDED</h2>
                        <p>The voting system is currently closed as the election period for Sangguniang Mag-aaral has ended. Stay tuned for future announcements, BTECHenyos!</p>
                    </div>
                    <a href="#">Have some questions?</a>
                </section>
            <?php elseif ($election_status == 'paused') : ?>
                <section class="election-message">
                    <div class="election-message-box">
                        <h2>ELECTION PERIOD ENDED</h2>
                        <p>The voting system for Sangguniang Mag-aaral is currently paused for a moment. Stay tuned, BTECHenyos!</p>
                    </div>
                    <a href="#">Have some questions?</a>
                </section>
            <?php elseif ($election_status == 'no_election') : ?>
                <section class="election-message">
                    <div class="election-message-box">
                        <h2>NO ELECTIONS</h2>
                        <p>There are no elections going on at the moment. Stay tuned, BTECHenyos!</p>
                    </div>
                    <a href="#">Have some questions?</a>
                </section>
            <?php else : ?>
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
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'includes/scripts.php' ?>
</body>
</html>