<html>
  
  <body>
    <!--  Comment <img src="aaaaaaa"/> -->
    <a href="desc.php?id=<?php  echo 'id_' . $id;  ?>">Link</a>
  </body>


  //Inline Comment in HTML.
  /*
    Multi Line Comment in HTML
  */

  <!-- PHP code in HTML. They should not be highlighted. -->
  function AAA()
  {
    foreach($a as $key=>$value)
    {  }
  }

<?php

  <!-- HTML Comment in PHP  -->

  echo "String contains \" escape character.";

  //Inline Comment in PHP
  /*
    Multi Line Comment in PHP
    
    HTML Tag in PHP Multi Line Comment
  */

  function AAA()
  {
    
    //Enumerate keys and values.
    foreach((array)$a as $key=>$value)
    {
      printf('%s=>%s', $key, $value);
    }
    
    //this pattern will not match with keyword.
    functiona
    
  }
  
?>
  
</html>