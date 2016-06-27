<?php
    
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function myecho($string) {
  echo "\n$string\n".(php_sapi_name() !== 'cli'?"<br/>\n":'');
  // Flush to screen
  if (php_sapi_name() !== 'cli') {
      echo "<!--" . str_pad('',4096)." -->";
      ob_flush();
      flush();
  }
}
