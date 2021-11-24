<?php # This script list available animations (or scenes)
  function error($message) {
    die("{\"error\":\"$message\"}");
  }
  header('Content-Type: application/json');
  $json = file_get_contents('php://input');
  $data = json_decode($json);
  require '../../../php/database.php';
  $mysqli = new mysqli($database_host, $database_username, $database_password, $database_name);
  if ($mysqli->connect_errno)
    error("Can't connect to MySQL database: $mysqli->connect_error");
  $mysqli->set_charset('utf8');
  $offset = isset($data->offset) ? intval($data->offset) : 0;
  $limit = isset($data->limit) ? intval($data->limit) : 10;
  $type = isset($data->type) ? strtoupper($data->type[0]) : 'A';
  require '../../../php/mysql_id_string.php';
  if ($type == 'S')  // scene
    $extra_condition = 'duration=0';
  else  // animation
    $extra_condition = 'duration>0';
  if (isset($data->url)) {  // view request
    $url = $mysqli->escape_string($data->url);
    $uri = substr($url, strrpos($url, '/'));
    $id = string_to_mysql_id(substr($uri, 2));  // skipping '/A'
    $query = "UPDATE animation SET viewed = viewed + 1 WHERE id=$id";
    $mysqli->query($query) or error($mysqli->error);
    $query = "SELECT * FROM animation WHERE id=$id AND $extra_condition";
  } else
    $query = "SELECT * FROM animation WHERE $extra_condition ORDER BY viewed DESC LIMIT $limit OFFSET $offset";
  $result = $mysqli->query($query) or error($mysqli->error);
  $answer = array();
  while($row = $result->fetch_array(MYSQLI_ASSOC)) {
    settype($row['id'], 'integer');
    settype($row['user'], 'integer');
    settype($row['duration'], 'integer');
    settype($row['size'], 'integer');
    settype($row['viewed'], 'integer');
    $uri = '/' . $type . mysql_id_to_string($row['id']);
    $row['url'] = 'https://' . $_SERVER['SERVER_NAME'] . $uri;
    array_push($answer, $row);
  }
  die(json_encode($answer));
 ?>
