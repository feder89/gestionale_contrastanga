<?php

if(isset($_POST['tot_serata']) && $_POST['tot_serata']!="" &&
    isset($_POST['da_incassare']) && $_POST['da_incassare']!="" &&
    isset($_POST['non_incassato']) && $_POST['non_incassato']!="" &&
    isset($_POST['tot_ricevute']) && $_POST['tot_ricevute']!=""){



$html='<html>
  <head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <meta name="generator" content="PSPad editor, www.pspad.com">
  <style type="text/css">
    @page {margin: 0;}
    @font-face {
      font-family: \'HelveticaNeue\';
      src: url(\'../fonts/Helvetica/HelveticaNeueLt.ttf\') format(\'truetype\');
      font-weight: normal;
      font-style: normal;
    }
    html, body{
      width: 170mm;
      height: 220mm;
      font-family: HelveticaNeue;
    }
   
   </style>
  </head>
  <body>';

  $table='
    <p style="margin-top: 3cm;">
    Totale incassato: &#8364; '.$_POST['tot_serata'].
    '</p>
    <p>
    Totale da incassare: &#8364; '.$_POST['da_incassare'].
    '</p>
    <p>
    Totale non pagato: &#8364; '.$_POST['non_incassato'].
    '</p>
    <p>
    Totale incassato con Ricevute Fiscali: &#8364; '.$_POST['tot_ricevute'].
    '</p>
  ';

  $html.=$table.'</body>
</html>
';


    echo $html;


    }else {
      echo "#error#Errore Nella stampa degli incassi";
    }
    function startsWith($haystack, $needle) {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }
?>