<?php

$userType = isset($_SESSION['userType']) ? $_SESSION['userType'] : null;
?>

<div class="navbar">
    <div class="logo">PF</div>

    <div class="nav-links">
        <li><a href="index.php">HOME</a></li>
        <li><a href="folders.php">FOLDERS</a></li>
        <li><a href="about.php">ABOUT</a></li>
        <li><a href="contact.php">CONTACT</a></li>
        <li><a href="more.php">MORE</a></li>
    </div>

    <a id="me" href="account.php" class="account-btn">ME</a>
</div>
