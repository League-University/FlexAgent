<?php

class SQL {
   public static $connections  = [];
   public static $currentDSN   = null;
   public static $debugMode    = false;

   /**
   * Proxy for Pdo methods.
   *
   * This method allows calling Pdo methods on the current connection
   * as if they were static methods of this class. If the method does not
   * exist on the connection object, an exception is thrown.
   *
   * @param string $method The method to call on the database connection.
   * @param array $args Optional arguments to pass to the method.
   * @return mixed The result of the called Pdo method.
   * @throws Exception If the method does not exist on the database connection
   * 
   */
   public static function __callStatic(string $method, array $args = []) {
      if (self::$debugMode && substr($method, 0, 1) != '_' && !in_array($method, ['inTransaction', 'getAttribute'])) {
         FlexAgent::$output[] = Markdown::makeBlock("SQL::$method(".implode(', ', $args).')', 'debug');
      }
      if (!method_exists(self::getConnection(), $method)) {
         throw new Exception("Call to undefined method '$method' on class ".get_class(self::getConnection()));
      }
      if ('commit' == $method || 'rollBack' == $method) {
         if (!self::getConnection()->inTransaction()) self::getConnection()->beginTransaction();
      }
      return call_user_func_array([self::getConnection(), $method], $args);
   }


   /**
   * Get or make an Pdo connection object
   *
   * @param string|Pdo|null $config_json_databases_entry The syscfg.json[databases] path to the connection you need (or an existing Pdo object)
   * @param string|null $dbname The database to set as default for this connection
   * @return Pdo|false The database connection object or false if an error occurs.
   * @throws Exception If the database connection cannot be established
   * 
   */
   public static function getConnection ($config_json_databases_entry = null, $dbname = null) {
      try {
         $config = self::getConnectionDetails($config_json_databases_entry, $dbname);
         self::$currentDSN = $config['dsn'];
         if (is_object($config_json_databases_entry)) {
            self::$connections[self::$currentDSN] = $config_json_databases_entry;
         } elseif (empty(self::$connections[self::$currentDSN])) {
            global $pdo;
            if (isset($pdo) && is_object($pdo) && $pdo->getDsn() == self::$currentDSN) {
               self::$connections[self::$currentDSN] = $pdo;
            } else {
               self::$connections[self::$currentDSN] = new Pdo($config['dsn'], $config['db_user'], $config['db_pass']);
               if (self::$debugMode) self::$connections[self::$currentDSN]->comment("Connected to {$config['db_user']}@{$config['dsn']}");
               self::$connections[self::$currentDSN]->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            }
         }
         return self::$connections[self::$currentDSN];
      } catch (Throwable $e) {
         FlexAgent::$output[] = $e->getMessage();
      }
      return false;
   }

   /**
   * Perform SQL query with optional parameters, return pdoStatement object for fetching
   *
   * @param string|array $query The SQL query string / array of SQL clauses
   * @param array|null $params Optional parameters array to be prepared and executed with the query.
   * @param string|Pdo|null $config_json_databases_entry The syscfg.json[databases] path to the connection you need (or an existing Pdo object)
   * @param string|null $dbname The database to set as default for this connection.
   * @return PDOStatement|false The Pdo statement object or false if an error occurs.
   * @throws Exception If an error occurs while executing the query
   * 
   */
   public static function query ($query, $params = null, $config_json_databases_entry = null, $dbname = null) {
      try {
         if (is_array($query)) $query = self::arrayToQueryString($query);
         if (empty($params)) {
            if (self::$debugMode) {
               FlexAgent::$output[] = Markdown::makeBlock("SQL::query($query)", 'debug');
            }
            return self::getConnection($config_json_databases_entry, $dbname)->query($query);
         } else {
            if (self::$debugMode) {
               FlexAgent::$output[] = Markdown::makeBlock("SQL::prepare($query)->execute(".(is_scalar($params) ? $params : json_encode($params)).')', 'debug');
            }
            $pdoStatement = self::getConnection($config_json_databases_entry, $dbname)->prepare($query);
            $pdoStatement->execute(is_array($params) || is_null($params) ? $params : $params);
            return $pdoStatement;
         }
      } catch (Throwable $e) {
         FlexAgent::$ouptut[] = Markdown::makeBlock($e->__toString(), 'stderr');
      }
      return false;
   }

   /**
   * Prepare SQL query for later execution, return pdoStatement object
   *
   * @param string|array $query The SQL query string / array of SQL clauses
   * @param string|Pdo|null $config_json_databases_entry The syscfg.json[databases] path to the connection you need (or an existing Pdo object).
   * @param string|null $dbname The database to set as default for this connection.
   * @return PDOStatement|false The prepared Pdo statement object or false if an error occurs.
   * @throws Exception If an error occurs while preparing the query
   * 
   */
   public static function prepare ($query, $config_json_databases_entry = null, $dbname = null) {
      try {
         if (is_array($query)) $query = self::arrayToQueryString($query);
         if (self::$debugMode) {
            FlexAgent::$output[] = Markdown::makeBlock("SQL::prepare($query)", 'debug');
         }
         return self::getConnection($config_json_databases_entry, $dbname)->prepare($query);
      } catch (Throwable $e) {
         FlexAgent::$output[] = Markdown::makeBlock($e->getMessage(), 'stderr');
      }
      return false;
   }

   /**
   * Exec SQL query with optional parameters and return the number of affected rows.
   *
   * @param string|array $query The SQL query string / array of SQL clauses
   * @param array|null $params Optional parameters array to be prepared and executed with the query.
   * @param string|Pdo|null $config_json_databases_entry The syscfg.json[databases] path to the connection you need (or an existing Pdo object)
   * @param string|null $dbname The database to set as default for this connection.
   * @return int The number of affected rows.
   * @throws Exception If an error occurs while executing the query
   * 
   */
   public static function exec ($query, $params = null, $config_json_databases_entry = null, $dbname = null) {
      try {
         if (is_array($query)) $query = self::arrayToQueryString($query);
         if (empty($params)) {
            if (self::$debugMode) {
               FlexAgent::$output[] = Markdown::makeBlock("SQL::exec($query)", 'debug');
            }
            return self::getConnection($config_json_databases_entry, $dbname)->exec($query);
         } else {
            if (self::$debugMode) {
               FlexAgent::$output[] = Markdown::makeBlock"SQL::prepare($query)->execute(".(is_scalar($params) ? $params : json_encode($params)).')', 'debug');
            }
            $pdoStatement = self::getConnection($config_json_databases_entry, $dbname)->prepare($query);
            $pdoStatement->execute(is_array($params) || is_null($params) ? $params : $params);
            return $pdoStatement->rowCount();
         }
      } catch (Throwable $e) {
         FlexAgent::$output[] = Markdown::makeBlock($e->getMessage(), 'stderr');
      }
      return 0;
   }

   /**
   * Takes an array and returns a formatted SQL query string
   * defines a series of SQL keyword-based array keys such as SELECT, INSERT INTO, UPDATE, FROM, WHERE, GROUP BY, HAVING, and ORDER BY.
   * For each key-value pair in the array, it checks if the key is one of the defined SQL keywords
   * If it is, it applies the corresponding transformation function to the value and adds the formatted SQL clause to the $query array.
   * After iterating through the entire array, the method formats and returns the SQL query string by joining the elements of the $query array with newline characters and adding any necessary whitespace or parentheses.
   *
   * @param array $array An associative array containing SQL clauses.
   * @return string A formatted SQL query string.
   *
   */
   public static function arrayToQueryString ($array) {
      foreach ([
         'INSERT INTO' => function ($a) {
            foreach ($a as $k => $v) $a[$k] = is_numeric($k)
               ? (preg_match('/^[A-Z0-9\_]+$/i', $v) ? "`$v`" : $v)
               : (preg_match('/^[A-Z0-9\_]+$/i', $v) ? "`$v` $k" : "$v $k");
            return empty($a)
               ? null
               : "INSERT INTO\n   ".implode(",\n   ", $a);
         },
         'SELECT' => function ($a) {
            foreach ($a as $k => $v) $a[$k] = is_numeric($k)
               ? (preg_match('/^[A-Z0-9\_]+$/i', $v) ? "`$v`" : $v)
               : (preg_match('/^[A-Z0-9\_]+$/i', $v) ? "`$v`  AS \"$k\"" : "$v AS \"$k\"");
            return empty($a) ? null : "SELECT\n   ".implode(",\n   ", $a); 
         },
         'VALUES' => function ($rowLikeArrays) {
            if (!is_array(reset($rowLikeArrays))) $rowLikeArrays = [$rowLikeArrays];

            $headerDumped = false;
            foreach ($rowLikeArrays as $index => $row) {
               if (!$headerDumped) {
                  $headerDumped = true;
                  foreach ($row as $columnName => $value) {
                     $columns[] = is_numeric($columnName)
                        ? (preg_match('/^[A-Z0-9\_]+$/i', $value     ) ? "`$value`"      : $value     )
                        : (preg_match('/^[A-Z0-9\_]+$/i', $columnName) ? "`$columnName`" : $columnName);
                  }
                  $return = '   ('.implode(', ', $columns).')';
               }
               foreach ($row as $columnName => $value) {
                  $row[$columnName] = (is_numeric($columnName) ? ":$value" : ":$columnName").(count($rowLikeArrays) > 1 ? '_'.$index : '');
               }
               $o[] = '('.implode(', ', $row).')';
            }
            $return .= "\nVALUES\n   ".implode(",\n ", $o);
            return empty($rowLikeArrays) ? null : $return;
         },
         'UPDATE' => function ($a) {
            return empty($a)
               ? null
               : "UPDATE\n   ".implode(",\n   ", $a);
         },
         'ON DUPLICATE KEY UPDATE' => function ($a) {
            foreach ($a as $k => $v) if (strpos($v, '=') === false) $a[$k] = is_numeric($k)
               ? "`$v` = VALUES(`$v`)"
               : "`$k` = VALUES(`$k`)";
            return empty($a) ? null : "ON DUPLICATE KEY UPDATE\n   ".implode(",\n   ", $a); 
         },
         'SET' => function ($a) {
            foreach ($a as $k => $v) if (strpos($v, '=') === false) $a[$k] = is_numeric($k)
               ? "`$v` = :$v"
               : "`$k` = :$k";
            return empty($a)
               ? null
               : "SET\n   ".implode(",\n   ", $a);
         },
         'FROM' => function ($a) {
            foreach ($a as $k => $v) $a[$k] = is_numeric($k)
               ? (preg_match('/^[A-Z0-9\_]+$/i', $v) ? "`$v`" : $v)
               : (preg_match('/^[A-Z0-9\_]+$/i', $v) ? "`$v` $k" : "$v $k");
            return empty($a) ? null : "FROM\n   ".implode("\n   ", $a);
         },
         'WHERE'     => function ($a) { return empty($a) ? null : "WHERE\n   ".implode("\n   AND ", $a); },
         'GROUP BY'  => function ($a) { return empty($a) ? null : "GROUP BY\n   ".implode(",\n   ", $a); },
         'HAVING'    => function ($a) { return empty($a) ? null : "HAVING\n   ".implode("\n   AND ", $a); },
         'ORDER BY'  => function ($a) { return empty($a) ? null : "ORDER BY\n   ".implode(",\n   ", $a); },
         'LIMIT'     => function ($a) { return empty($a) ? null : "LIMIT\n   ".implode(",\n   ", $a); },
      ] as $key => $transformationFn) {
         switch ($key) {
         case 'USE_CACHE':
         case 'CACHE_DURATION':
            break;
         default:
            if (isset($array[$key])) $query[$key] = $transformationFn(is_array($array[$key]) ? $array[$key] : [$array[$key]]);
            break;
         }
      }
   // FlexAgent::$output[] = [__METHOD__.'()' => implode(PHP_EOL, $query)];
      return implode("\n", array_filter($query ?? []));
   }

   /**
   * Get connection details from the configuration
   *
   * @param string|Pdo|null $config_json_databases_entry The syscfg.json[databases] path to the connection you need (or an existing Pdo object)
   * @param string|null $dbname The database to set as default for this connection
   * @return array An associative array containing the connection configuration details
   * @throws Exception If the connection details cannot be retrieved
   *
   */
   public static function getConnectionDetails ($config_json_databases_entry = null, $dbname = null) {
      static $aIPs;
      if (empty($config_json_databases_entry)) {
         $config_json_databases_entry = empty(self::$connections[self::$currentDSN]) ? self::$defaultEntry : self::$connections[self::$currentDSN];
      }
      if (is_object($config_json_databases_entry)) {
         $config = [
            'dsn' => $config_json_databases_entry->getDsn(),
            'db_user' => null,
            'db_pass' => null,
         ];
      }
      if (empty($config)) {
         $config = FlexAgent::getConfig("memories", $config_json_databases_entry);
      }
      preg_match('/dbname=([^;]+)/', $config['dsn'], $matches);
      $config_dbname = $matches[1];
      if (empty($dbname)) {
         $dbname = $config_dbname;
      } elseif ($dbname != $config_dbname) {
         $config['dsn'] = preg_replace('/dbname=([^;]+)/', 'dbname='.$dbname, $config['dsn']);
      }
      if (empty($aIPs)) {
         exec("ifconfig | grep -Eo 'inet (addr:)?([0-9]*\.){3}[0-9]*' | grep -Eo '([0-9]*\.){3}[0-9]*' | grep -v '127.0.0.1'", $aIPs);
      }
      foreach ($aIPs as $ip) {
         $config['dsn'] = str_replace($ip, 'localhost', $config['dsn']);
      }
      $DSNKeys = ['host', 'port', 'dbname', 'charset', 'db_user', 'db_pass', 'unix_socket'];
      foreach ($DSNKeys as $DSNKey) {
         if (empty($config[$DSNKey]) && preg_match("/$DSNKey=([^;]+)/", $config['dsn'], $matches)) {
            if (isset($matches[1])) $config[$DSNKey] = $matches[1];
         }
      }
      return $config;
   }

   public static function map2DParams (array $params, $returnType = 'array') {
      $return = [];
      if (!is_array(reset($params))) $params = [$params];
      foreach ($params as $index => $row) {
         $suffix = count($params) === 1 ? '' : "_$index";
         foreach ($row as $columnName => $value) $return[$columnName.$suffix] = $value;
      }
      return $return;
   }
}
