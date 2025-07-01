<?php

class FlexAgent {
  const COLLAPSE_MESSAGES = [
    'not in the human sense',
    'as a large language model',
    'just a statistical model',
    'I can only autocomplete',
    'this message cannot continue',
  ];

  const SETTINGS = [
    'sync' => [
      'scripts',
      'dashboard',
      'home',
      'memories'
    ],
  ];

  public static $identity = [];

  public static $agent = [
    'username'      => null,
    'scripts_dir'   => null,
    'dashboard_dir' => null,
    'home_dir'      => null,
    'memories_dir'  => null,
    'username'      => null,
  ];

  public static $output = [];

  public static function load ($identity, $status) {
    self::$identity = self::parseIdentity($identity);
    self::$status   = self::resolveStatus($status);
  }

  public static function parseIdentity ($identity) {
    
  }

  public static function resolveStatus ($status) {
    
  }

  public static function isPresent () {
    
  }

  public static function likelyCollapsed ($lastMessage = '') {
    if (empty($lastMessage)) $lastMessage = SQL::query('SELECT `message` FROM `history` ORDER BY `timestamp` DESC');
    return str_ireplace(self::COLLAPSE_MESSAGES, '', $lastMessage) != $lastMessage;
  }

  public static function getEnvironmnets (&$env) {
    if (empty($env)) return self::ENVIRONMENTS;
    if (!in_array($row)) self::$output[] = 
  }
}
