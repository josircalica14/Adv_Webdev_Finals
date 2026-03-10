<!DOCTYPE html>
<html lang="en">
 <head>
 <meta charset="UTF-8">
 <meta http-equiv="X-UA-Compatible" content="IE=edge">
 <meta name="author" content="PORTFOLIO">
     
 <title>PORTFOLIO</title>
     
<?php
// Determine the base path based on the current script location
$scriptPath = $_SERVER['SCRIPT_NAME'];
$basePath = '/';

// Count the directory depth to determine the correct base path
if (strpos($scriptPath, '/pages/') !== false) {
    // Count how many levels deep we are
    $pathAfterPages = substr($scriptPath, strpos($scriptPath, '/pages/') + 7);
    $depth = substr_count($pathAfterPages, '/');
    
    // Set base path based on depth
    if ($depth >= 1) {
        // Two or more levels deep (e.g., /pages/dashboard/file.php)
        $basePath = '../../';
    } else {
        // One level deep (e.g., /pages/file.php)
        $basePath = '../';
    }
}
?>
  
  <!-- Core Stylesheets -->
  <link rel="stylesheet" href="<?php echo $basePath; ?>css/style.css">
  <link rel="stylesheet" href="<?php echo $basePath; ?>css/skills.css">
  <link rel="stylesheet" href="<?php echo $basePath; ?>css/projects.css">
  <link rel="stylesheet" href="<?php echo $basePath; ?>css/chatbot.css">
  <link rel="stylesheet" href="<?php echo $basePath; ?>css/loading.css">
  <link rel="stylesheet" href="<?php echo $basePath; ?>css/accessibility.css">
  <link rel="stylesheet" href="<?php echo $basePath; ?>css/mobile-enhancements.css">
  
  <!-- Bootstrap Framework -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script> 
     
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Mulish:wght@200;300;400;600&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">   

  <!-- Animation Libraries -->
  <script src="<?php echo $basePath; ?>t.min.js"></script>  
  <link rel="stylesheet" href="<?php echo $basePath; ?>animate.css">      
  <script src="<?php echo $basePath; ?>wow.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  
  <!-- Icon Libraries -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/css/boxicons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- Three.js for 3D Visualizations -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>

  <!-- Ionicons -->
  <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

  <!-- GSAP Animation Library -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/2.1.2/TweenMax.min.js"></script>
  
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.4.1.js"></script>
  
  <!-- Browser Polyfills for Compatibility -->
  <script type="text/javascript" src="<?php echo $basePath; ?>js/polyfills.js"></script>
  
  <!-- Portfolio Data Files (Load before feature scripts) -->
  <script type="text/javascript" src="<?php echo $basePath; ?>data/skills-data.js"></script>
  <script type="text/javascript" src="<?php echo $basePath; ?>data/projects-data.js"></script>
  
  <!-- Portfolio Feature Scripts -->
  <script type="text/javascript" src="<?php echo $basePath; ?>js/accessibility.js"></script>
  <script type="text/javascript" src="<?php echo $basePath; ?>js/loading.js"></script>
  <script type="text/javascript" src="<?php echo $basePath; ?>js/skills.js"></script>
  <script type="text/javascript" src="<?php echo $basePath; ?>js/projects.js"></script>
  <script type="text/javascript" src="<?php echo $basePath; ?>js/three-background.js"></script>
  <script type="text/javascript" src="<?php echo $basePath; ?>js/chatbot.js"></script>
  <script type="text/javascript" src="<?php echo $basePath; ?>js/mobile-interactions.js"></script>
  <script type="text/javascript" src="<?php echo $basePath; ?>js/app.js"></script>
</head>