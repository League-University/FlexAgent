<?php

class URI {
  public static function getPath() {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $path = trim($path, '/');
    return $path;
  }

  public static function getQuery() {
    return $_SERVER['QUERY_STRING'] ?? '';
  }

  public static function getFull() {
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
  }

  public static function getDirectory() {
    $path = self::getPath();
    $lastSlash = strrpos($path, '/');
    if ($lastSlash === false) {
      return '';
    }
    return substr($path, 0, $lastSlash);
  }
}
