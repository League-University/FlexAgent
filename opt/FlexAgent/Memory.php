<?php

namespace 'FlexAgent';

class Memory {
  public static function remember($message, $speaker = 'self') {
    self::initialize();

  // write memory to database
    SQL::query('INSERT INTO `memories` (`message`, `speaker`, `timestamp`) VALUES (?, ?, NOW())', [$message, $speaker]);

  // insert vectors
    $messageId = SQL::lastInsertId();
    $ins = SQL::prepare('INSERT INTO `memory_vectors` (`messageId`, `embedding`) VALUES (?, ?)');
    foreach (self::vectorize($message) as $embedding) $ins->execute([$messageId, $embedding]);

  // insert KG triples
    $ins = SQL::prepare('INSERT INTO `memory_triples` (`messageId`, `subject`, `predicate`, `object`) VALUES (:messageId, :subject, :predicate, :object)');
    foreach (self::triplize($message) as $entry) $ins->execute(['messageId' => $messageId] + $entry);
  }

  public static function recall($search, $speaker = null, $startTimestamp = null, $endTimestamp = null) {
    self::initialize();
    foreach ([
      'speaker'        => 'speaker = :speaker',
      'startTimestamp' => 'timestamp >= :startTimestamp',
      'endTimestamp'   => 'timestamp <= :endTimestamp'
    ] as $param => $where) if (!empty($$param)) {
      $WHERE[] = $where;
      $PARAMS[$param] => $$param;
    }

    if (!empty($WHERE)) $WHERE = implode('\n  AND ', $WHERE);

    $search = SQL::query(<<<SQL
SELECT
  *
-- todo: add relevance
FROM
  messages m
$WHERE
ORDER BY
  relevance
LIMIT
  10
SQL
    );
    $PARAMS['boolean'] = '+('.implode(' +', $search).')';
  }

  private static function initialize() {
  // confirm connection works
  #TODO
  // confirm table schema is correct or create tables
  #TODO
  }
}
