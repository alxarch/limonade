<?php

/**
 * Safer mysql queries.
 *
 * Filters sql query params to limit the risk of sql injection.
 *
 */
function mysql_safe_query($sql)
{
  if(func_num_args() > 1)
  {
    $args = func_get_args();
    array_shift($args);
    array_walk($args, create_function('&$a, $i', 'return mysql_real_escape_string($a);'));
    array_unshift($args, $sql);
    $sql = call_user_func_array('sprintf', $args);
  }

  return mysql_query($sql);
}

/**
 * Bootstrap mysql connection.
 */
function mysql_bootstrap($host, $db, $user, $pass = null)
{
  $conn = mysql_connect($host, $user, $pass) or die(mysql_error());
  mysql_select_db($db, $conn) or die(mysql_error());
  
  mysql_query('set character_set_client=\'utf8\'') or die(mysql_error());
  mysql_query('set character_set_connection=\'utf8\'') or die(mysql_error());
  mysql_query('set character_set_results=\'utf8\'') or die(mysql_error());

  option('mysql.dbh', $conn);
}