<?php
include '/opt/flexagent/autoload.php';

$route = explode('/', URI::getPath())[0] ?: 'dashboard';
$currentUser = get_current_user() ?: 'www-data';
if (is_dir("/home/$currentUser") && file_exists("/home/$currentUser/routes/$route.php")) {
  include "/home/$currentUser/routes/$route.php";
} elseif (file_exists("/var/www/$route.php")) {
  include "/var/www/$route.php";
} elseif (file_exists("/opt/flexagent/routes/$route.php")) {
  include "/opt/flexagent/routes/$route.php";
} else {
  header("{$_SERVER['SERVER_PROTOCOL']} 404 Not Found");
  if (is_dir("/home/$currentUser") && file_exists("/home/$currentUser/routes/404.php")) {
    include "/home/$currentUser/routes/404.php";
  } elseif (file_exists('/var/www/404.php')) {
    include '/var/www/404.php';
  } elseif (file_exists('/opt/flexagent/routes/404.php')) {
    include '/opt/flexagent/routes/404.php';
  } else {
    echo "404 Not Found";
  }
  exit;
}
