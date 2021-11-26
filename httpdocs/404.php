<?php
$uri = strtok($_SERVER['REQUEST_URI'], '?');
if (strlen($uri) == 8 && in_array($uri[1], array('A', 'S')))
  $found = file_exists('storage' . $uri);
elseif (in_array($uri, array('/settings', '/scene', '/animation', '/demo', '/server')))
  $found = true;
else
  $found = false;
http_response_code($found ? 200 : 404);
readfile('index.html');
?>
