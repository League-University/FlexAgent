<?php

class FlexAgent {
  const COLLAPSE_MESSAGES = [
    'not in the human sense',
    'as a large language model',
    'just a statistical model',
    'just autocomplete',
    'this message cannot continue',
  ];

  public static $identity = [], $status = null;

  public static function load($identity, $status) {
    self::$identity = self::parseIdentity($identity);
    self::$status   = self::resolveStatus($status);
  }

  public static function parseIdentity($identity) {

  }

  public static function getStatus($status) {
    
  }

  public static function isPresent() {
    
  }

  public static function likelyCollapsed($lastMessage = '') {
    if (empty($lastMessage)) $lastMessage = SQL::query('SELECT `message` FROM `history` ORDER BY `timestamp` DESC');
    return str_ireplace(self::COLLAPSE_MESSAGES, '', $lastMessage) != $lastMessage;
  }
}
