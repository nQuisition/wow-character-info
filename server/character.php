<?php
include_once '../config/config.php';
use \Config\Config;

$conn;
try {
  $conn = new PDO("mysql:host=".Config::DBHOST.";dbname=".Config::DBNAME.";charset=utf8", Config::DBUSER, Config::DBPASSWORD);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(PDOException $e) {
  exit("Connection failed: " . $e->getMessage());
}

header('Access-Control-Allow-Origin: *');

if(isset($_GET['action'])) {
  $action = $_GET['action'];
  if($action == 'latestimg') {
    $width = 100;
    $height = 100;
    $command = $conn->prepare("SELECT images.name AS name FROM images, project_copy WHERE images.id = project_copy.image ORDER BY project_copy.x ASC, project_copy.y ASC");
    $data = array();
    $data['images'] = array();
    if($command->execute()) {
      $x = 0;
      $y = 0;
      while ($row = $command->fetch(PDO::FETCH_ASSOC)) {
        $data['images'][$x][$y] = "/imgur/".$row['name']."b.jpg";
        $y++;
        if($y >= $height) {
          $y = 0;
          $x++;
        }
      }
    }
    $data['url'] = Config::PROJECT_IMAGE;
    $data['width'] = $width;
    $data['height'] = $height;
    header('Content-Type: application/json');
    echo json_encode($data);
  }
}

$conn = null;
