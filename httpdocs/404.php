<?php
if ($_SERVER['REQUEST_URI'][0] == 'a')
  $found = file_exists('animations/' . substr($_SERVER['REQUEST_URI'], 1));
else
  $found = false;
die("found = " . ($found ? 'true' : 'false') . 'animations/' . substr($_SERVER['REQUEST_URI'], 1));
http_response_code($found ? 200 : 404);
readfile('index.html');
?>
