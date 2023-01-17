<?php # This script list available protos
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
  if (isset($data->url)) {
    $url = $data->url;
    $query = "UPDATE proto SET viewed = viewed + 1 WHERE url LIKE \"$url\"";
    $mysqli->query($query) or error($mysqli->error);
    die('{"status":"updated"}');
  }
  $sortBy = isset($data->sortBy) && $data->sortBy != "default" && $data->sortBy != "undefined" ?
    $mysqli->escape_string($data->sortBy) : "viewed-desc";
  $parameter = explode("-", $sortBy)[0];
  $order = explode("-", $sortBy)[1];
  if ($parameter == "title" || $parameter == "version") {
    if ($order == "asc")
      $order = "desc";
    else
      $order = "asc";
  }
  $branch = basename(dirname(__FILE__, 4));
  $condition = "branch=\"$branch\"";
  if (isset($data->search)) {
    $searchString = $mysqli->escape_string($data->search);
    $condition .= " AND LOWER(title) LIKE LOWER('%$searchString%')";
  }
  $offset = isset($data->offset) ? intval($data->offset) : 0;
  $limit = isset($data->limit) ? intval($data->limit) : 10;
  $query = "SELECT * FROM proto WHERE $condition ORDER BY $parameter $order LIMIT $limit OFFSET $offset";
  $result = $mysqli->query($query) or error($mysqli->error);
  $protos = array();
  while($row = $result->fetch_array(MYSQLI_ASSOC)) {
    settype($row['id'], 'integer');
    settype($row['viewed'], 'integer');
    settype($row['stars'], 'integer');
    $row['title'] = htmlentities($row['title']);
    $row['description'] = htmlentities($row['description']);
    $row['version'] = htmlentities($row['version']);
    array_push($protos, $row);
  }
  $result = $mysqli->query("SELECT COUNT(*) AS count FROM proto WHERE $condition") or error($mysqli->error);
  $count = $result->fetch_array(MYSQLI_ASSOC);
  $answer = new StdClass;
  $answer->protos = $protos;
  $answer->total = intval($count['count']);
  die(json_encode($answer));
 ?>