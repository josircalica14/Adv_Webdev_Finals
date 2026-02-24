<?php include "includes/header.php"; ?>

<body>
 <div class="wrapper">
     
<?php include "includes/nav.php" ?>
 
 <div class="whitespace"></div>

 <div class="contacto">

<div class="marquee">
  <span>
   contact me / contact me / contact me / contact me / contact me / contact me / contact me / contact me / contact me / contact me / contact me / 
  </span>    
 </div>
     
     <br>
     
 <div class="marquee1">
  <span>
   say hello 🖗 . say hello 🖗 . say hello 🖗 . say hello 🖗 .  say hello 🖗 . say hello 🖗 . say hello 🖗 . say hello 🖗 . say hello 🖗 . say hello 🖗 . say hello 🖗 . say hello 🖗 .  say hello 🖗 . say hello 🖗 . say hello 🖗 . say hello 🖗 .
  </span>    
 </div>
     
 <div class="container">
   <div class="hero-content">
     <br><br>
     <div class="row">
      <div class="col-lg-8">    
    <br>
    
    <p class="wow fadeInUp" data-wow-delay="1.2s">if you like a reading buddy, please fill out the form and send!</p>
  </div>
</div>
</div>
</div>        
    
 <div class="whitespace"></div>  
     
<!-- FORM -->

 <div class="container-fluid">
  <div class="row">
   <div class="col-lg-8">
    <form name="contact-form" id="contact-form" method="post" action="">  
     <ul>
      <li class="wow fadeInUp" data-wow-delay="1.4s">
        <label for="contact-name">name :</label>
        
        <div class="textarea">
          <input type="text" name="contact-name" id="contact-name" value="" required>
        </div>
      </li>
         
     <li class="wow fadeInUp" data-wow-delay="1.6s">
      <label for="contact-email">email :</label>
      <div class="textarea">
        <input type="email" name="contact-email" id="contact-email" value="" required>   
      </div>
     </li>  
         
     <li class="wow fadeInUp" data-wow-delay="1.6s">
      <label for="contact-project">message :</label>
      <div class="textarea">
        <textarea type="email" name="contact-project" id="contact-project" rows="6" value="" required>
        </textarea>   
      </div>
     </li>
    </ul>
    
    <button type="submit" name="contact-submit" id="contact-submit" class="send wow fadeInUp">send</button>    
        
    </form>   
   </div>   
  </div>    
 </div>      
     
<?php include "includes/footer.php"; ?>

</body>
</html>