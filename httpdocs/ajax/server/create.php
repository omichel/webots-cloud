<?php # This script initializes a new project
  function error($message) {
    die("{\"error\":\"$message\"}");
  }
  function remove($message) {
    global $mysqli, $url;
    $mysqli->query("DELETE FROM server WHERE url=\"$url\"") or error($mysqli->error);
    error($message);
  }
  header('Content-Type: application/json');
  $json = file_get_contents('php://input');
  $data = json_decode($json);
  require '../../../php/database.php';
  $mysqli = new mysqli($database_host, $database_username, $database_password, $database_name);
  if ($mysqli->connect_errno)
    error("Can't connect to MySQL database: $mysqli->connect_error");
  $mysqli->set_charset('utf8');
  $url = $mysqli->escape_string($data->url);
  if (substr($url, 0, 8) !== 'https://')
    remove("Malformed URL: $url");
  $load_content = @file_get_contents("$url/load");
  if ($load_content === false)
    remove("Cannot reach session server at $url");
  if (!is_numeric($load_content))
    remove("Bad answer from session server: $load_content");
  $load = intval($load_content);
  $query = "SELECT id FROM server WHERE url=\"$url\"";
  $result = $mysqli->query($query) or error($mysqli->error);
  $server = $result->fetch_array(MYSQLI_ASSOC);
  if ($server) {
    $id = $server['id'];
    $query = "UPDATE server SET load=$load WHERE id=$id";
    $mysqli->query($query) or error($mysqli->error);
  } else {
    $query = "INSERT INTO server(url, load) VALUES(\"$url\", $load)";
    $mysqli->query($query) or error($mysqli->error);
    $id = $mysqli->insert_id;
  }

  $answer = array();
  $answer['id'] = $id;
  $answer['url'] = $url;
  $answer['load'] = $load;
  $answer['updated'] = date("Y-m-d H:i:s");
  die(json_encode($answer));
 ?>
