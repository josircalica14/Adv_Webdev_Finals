<?php 
include "includes/upload.php";
include "includes/header.php"; 
?>

<body>

 <div class="wrapper">
     
<?php include "includes/nav.php"; ?>

<div class="folders-page">
<div class="upload-container">
  <h1>Upload Files</h1>

  <div class="drop-zone" id="dropZone">
    <div class="icon-wrap">
      <!-- cloud -->
      <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 16V10m0 0-3 3m3-3 3 3"/>
        <path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/>
      </svg>
    </div>
    <h2>Drop your files here or <span class="browse-link" id="browseLink">browse</span></h2>
    <p>Max file size up to 1 GB</p>
  </div>

  <input type="file" id="file-input" multiple>

  <div class="file-list" id="fileList"></div>
  <p class="hint" id="hint" style="display:none;">Click a file badge to download it</p>
</div>
</div>

<?php include "includes/footer.php"; ?>

</body>
</html>