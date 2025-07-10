<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

spl_autoload_register(function ($class) {
  $currentUser = get_current_user();
  if ($currentUser !== 'www-data') {
    $file = '/home/' . $currentUser . '/.projects/flexagent/opt/' . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    if (file_exists($file)) {
      include $file;
      return;
    }
  }
  $file = __DIR__ . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
  if (file_exists($file)) {
    include $file;
    return;
  }
});

