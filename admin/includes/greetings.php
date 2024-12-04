<?php
    $adminId = $conn->real_escape_string($_SESSION['admin']);
    $sql = "SELECT * FROM admin WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $adminId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $courtesyTitle = '';
    if($user['gender'] == "Male") {
        $courtesyTitle = 'Mr.';
    } else if($user['gender'] == "Female") {
        $courtesyTitle = 'Ms.';
    } else {
        $courtesyTitle = 'Ms.';
    }
?>

<?php

// Fetch the current election name
$sqlElection = "SELECT election_name FROM election_status ORDER BY id DESC LIMIT 1";
$stmt = $conn->prepare($sqlElection);
$stmt->execute();
$result = $stmt->get_result();
$election = $result->fetch_assoc();
$election_name = $election['election_name'];
?>


<style>    
/* Greetings Banner Styles */
.greetings-banner {
    background: linear-gradient(to right, #EDFFD6 0%, #D8FFBC 48%, #B3FF8F 100%);
    padding: 20px;
    border-radius: 5px;
    margin-bottom: 5px;
    position: relative; /* Ensures the container can hold the absolutely positioned image */
    
}
.greetings-content {
    display: flex;
    align-items: center;
    position: relative;
    z-index: 1; /* Ensures content is above the image */
}
.greetings-text {
    margin-left: 20px;
    max-width: 50%;
    z-index: 2; /* Ensures text is above the image */
}
.greetings-text h2 {
    font-size: 31px;
    font-weight: 600;
    margin: 0;
    color: #229043;
}
.greetings-text p {
    text-align: justify;
    text-justify: inter-character;
    font-size: 21px;
    margin: 5px 0;
    color: #229043;
}
.greetings-icon {
    position: absolute; /* Position the image absolutely */
    top: 0; /* Adjust this value to position the image vertically */
    right: 0;
    bottom: 0; /* Ensures the image stretches to the bottom of the container */
    z-index: 0; /* Ensures the image is behind the text */
    width: 40%; /* Adjust width to fit the design */
    overflow: hidden; /* Ensures any overflow is hidden */
    display: flex;
    align-items: flex-start; /* Aligns the image to the top of the container */
}
.greetings-icon img {
    width: 100%; /* Ensures the image covers the container */
    height: auto; /* Maintains aspect ratio */
    object-fit: cover; /* Ensures the image covers the container */
    margin-top: -7%; /* Adjust this value to move the image up or down */
    margin-right: 15px !important; 
}
</style>

<div class="greetings-banner">
    <div class="greetings-content">
        <div class="greetings-text">
            <h2>Hi, <?php echo $courtesyTitle; ?> <?php echo $user['firstname']; ?>!</h2>
            <p>Check out the summary of voting for <?php echo $election_name; ?> of <b>Dalubhasaang Politekniko ng Lungsod ng Baliwag!</b></p>
        </div>
    </div>
    <div class="greetings-icon">
        <img src="../images/greeting_image.svg" alt="Greeting Image">
    </div>
</div>
