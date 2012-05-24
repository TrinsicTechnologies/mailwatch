<?php

require_once("./functions.php");

// function for escaping and quoting mysql strings
function escape($values)
{
  if( is_array( $values ) )
    $values = array_map( array( &$this, 'escape' ), $values );
  else
  {    
    // Quote if not integer
    if ( !is_numeric( $values ) || $values{0} == '0' )
    {
       if ( $str = mysql_real_escape_string( $values ) ) {

       }
       else if ( $str = mysql_escape_string( $values ) ) {

       }

       $values = "'".$str."'";
    }
  }

  return $values;
}

// primary message query function
function get_message_list($options)
{
  global $domain;
  
  $start = 0;
  $msg_list = array();
  $sql = "SELECT id AS msg_id, from_address, from_domain, to_address, to_domain, clientip, subject, date, time, IF ( isspam = 0 AND ishighspam = 0, 'no', 'yes' ) AS isspam, IF ( virusinfected = 0 AND otherinfected = 0, 'no', 'yes' ) AS isvirus, nameinfected AS virusname, IF ( spamblacklisted = 0, 'no', 'yes' ) AS isblacklisted, IF ( spamwhitelisted = 0, 'no', 'yes' ) AS iswhitelisted, size, headers FROM maillog";

  // only display data to or from their domain
  $sql .= " WHERE ( to_address LIKE ".escape("%@$domain")." OR from_address LIKE ".escape("%@$domain")." OR to_domain = ".escape($domain)." OR from_domain = ".escape($domain)." )";

  // possible MySQL conditions
  $possible_options = array("msg_id", "search_by", "search_operator", "search", "date_start", "date_end", "time_start", "time_end", "limitstart", "limitnum", "isspam", "isvirus", "isblacklisted", "iswhitelisted", "isquarantined");
  $found = FALSE;
  foreach( $options as $key => $value )
    if ( in_array( $key, $possible_options ) )
      $found = TRUE;

  if ( $found )
  {
    // search filters
    if ( isset($options['search']) && isset($options['search_by']) )
    {
      // make sure valid search_by
      $possible_filters = array("", "all", "to_address", "to_domain", "from_address", "from_domain", "subject");
      if ( !in_array( $options['search_by'], $possible_filters ) )
      {
        $msg_list['trinsic']['result'] = $options['search_by']." is an invalid search filter.";
        return $msg_list;
      }

      // and valid search
      if ( $options['search'] == "" )
      {
        $msg_list['trinsic']['result'] = "Search filter is empty.";
        return $msg_list;
      }
      else
        $search = $options['search'];

      // and valid operator
      $possible_operators = array( "matches", "contains" );
      if ( !isset( $options['search_operator'] ) || $options['search_operator'] == "matches" )
      {
        if ( $options['search_by'] == "" || $options['search_by'] == "all" )
          $sql .= " AND ( to_address = ".escape($search)." OR to_domain = ".escape($search)." OR from_address = ".escape($search)." OR from_domain = ".escape($search)." OR subject = ".escape($search)." )";
        else
          $sql .= " AND ".$options['search_by']." = ".escape($search);
      }
      else if ( $options['search_operator'] == "contains" )
      {
        if ( $options['search_by'] == "" || $options['search_by'] == "all" )
          $sql .= " AND ( to_address LIKE ".escape("%$search%")." OR to_domain LIKE ".escape("%$search%")." OR from_address LIKE ".escape("%$search%")." OR from_domain LIKE ".escape("%$search%")." OR subject LIKE ".escape("%$search%")." )";
        else
          $sql .= " AND ".$options['search_by']." LIKE ".escape("%$search%");
      }
      else
      {
        $msg_list['trinsic']['result'] = $options['search_operator']." is an invalid search operator.";
        return $msg_list;
      }
    }

    // date ranges
    if ( isset( $options['date_start'] ) || isset( $options['date_end'] ) )
    {
      if ( isset( $options['date_start'] ) )
      {
        // valid date format?
        $date_start = explode( '-', $options['date_start'] );
        if ( !checkdate( $date_start[1], $date_start[2], $date_start[0] ) )
        {
          $msg_list['trinsic']['result'] = $options['date_start']." is an invalid date format(YYYY-MM-DD).";
          return $msg_list;
        }
      }
   
      if ( isset( $options['date_end'] ) )
      {
        // valid date format?
        $date_end = explode( '-', $options['date_end'] );
        if ( !checkdate( $date_end[1], $date_end[2], $date_end[0] ) )
        {
          $msg_list['trinsic']['result'] = $options['date_end']." is an invalid date format(YYYY-MM-DD).";
          return $msg_list;
        }
      }

      // generate SQL
      if ( isset( $options['date_start'] ) && isset( $options['date_end'] ) )
        $sql .= " AND date BETWEEN ( ".escape($options['date_start'])." AND ".escape($options['date_end'])." )";
      else if ( isset( $options['date_start'] ) )
        $sql .= " AND date >= ".escape($options['date_start']);
      else if ( isset( $options['date_end'] ) )
        $sql .= " AND date <= ".escape($options['date_end']);
    }

    // time ranges
    if ( isset( $options['time_start'] ) || isset( $options['time_end'] ) )
    {
      if ( isset( $options['time_start'] ) )
      {
        // valid time format?
        $time_start = explode( ':', $options['time_start'] );
        if ( !is_numeric( $time_start[0] ) || !is_numeric( $time_start[1] ) || !is_numeric( $time_start[2] ) 
          || $time_start[0] < 0 || $time_start[0] > 24 || $time_start[1] < 0 
          || $time_start[1] > 59 || $time_start[2] < 0 || $time_start[2] > 59 )
        {
          $msg_list['trinsic']['result'] = $options['time_start']." is an invalid time format(HH:MM:SS).";
          return $msg_list;
        }
      }
   
      if ( isset( $options['time_end'] ) )
      {
        // valid time format?
        $time_end = explode( ':', $options['time_end'] );
        if ( !is_numeric( $time_end[0] ) || !is_numeric( $time_end[1] ) || !is_numeric( $time_end[2] ) 
          || $time_end[0] < 0 || $time_end[0] > 24 || $time_end[1] < 0 
          || $time_end[1] > 59 || $time_end[2] < 0 || $time_end[2] > 59 )
        {
          $msg_list['trinsic']['result'] = $options['time_end']." is an invalid time format(HH:MM:SS).";
          return $msg_list;
        }
      }

      // generate SQL
      if ( isset( $options['time_start'] ) && isset( $options['time_end'] ) )
        $sql .= " AND time BETWEEN ( ".escape($options['time_start'])." AND ".escape($options['time_end'])." )";
      else if ( isset( $options['time_start'] ) )
        $sql .= " AND time >= ".escape($options['time_start']);
      else if ( isset( $options['time_end'] ) )
        $sql .= " AND time <= ".escape($options['time_end']);
    }

    // filter for spam messages
    if ( isset( $options['isspam'] ) )
    {
      if ( $options['isspam'] == "yes" )
        $sql .= " AND isspam > 0";
      else if ( $options['isspam'] == "no" )
        $sql .= " AND isspam < 1";
      else
      {
        $msg_list['trinsic']['result'] = "Invalid option for isspam.  isspam valid options are yes or no.";
        return $msg_list;
      }
    }

    // filter for infected messages
    if ( isset( $options['isvirus'] ) )
    {
      if ( $options['isvirus'] == "yes" )
        $sql .= " AND ( virusinfected > 0 OR otherinfected > 0 )";
      else if ( $options['isvirus'] == "no" )
        $sql .= " AND ( virusinfected < 1 AND otherinfected < 1 )";
      else
      {
        $msg_list['trinsic']['result'] = "Invalid option for isvirus.  isvirus valid options are yes or no.";
        return $msg_list;
      }
    }

    // filter for blacklisted messages
    if ( isset( $options['isblacklisted'] ) )
    {
      if ( $options['isblacklisted'] == "yes" )
        $sql .= " AND spamblacklisted > 0";
      else if ( $options['isblacklisted'] == "no" )
        $sql .= " AND spamblacklisted < 1";
      else
      {
        $msg_list['trinsic']['result'] = "Invalid option for isblacklisted.  isblacklisted valid options are yes or no.";
        return $msg_list;
      }
    }

    // filter for whitelisted messages
    if ( isset( $options['iswhitelisted'] ) )
    {
      if ( $options['iswhitelisted'] == "yes" )
        $sql .= " AND spamwhitelisted > 0";
      else if ( $options['iswhitelisted'] == "no" )
        $sql .= " AND spamwhitelisted < 1";
      else
      {
        $msg_list['trinsic']['result'] = "Invalid option for iswhitelisted.  iswhitelisted valid options are yes or no.";
        return $msg_list;
      }
    }

    // filter for quarantined messages
    if ( isset( $options['isquarantined'] ) )
    {
      if ( $options['isquarantined'] == "yes" )
        $sql .= " AND quarantined = 0";
      else if ( $options['isquarantined'] == "no" )
        $sql .= " AND quarantined = 1";
      else
      {
        $msg_list['trinsic']['result'] = "Invalid option for isquarantined.  isquarantined valid options are yes or no.";
        return $msg_list;
      }
    }

    // filter for specific messages
    if ( isset( $options['msg_id'] ) )
      $sql .= " AND id = ".escape($options['msg_id']);

    // filter start and stop
    if ( isset( $options['limitstart'] ) || isset( $options['limitnum'] ) )
    {
      if ( ( isset( $options['limitstart'] ) && !is_numeric( $options['limitstart'] ) ) || ( isset( $options['limitnum'] ) && !is_numeric( $options['limitnum'] ) ) )
      {
        $msg_list['trinsic']['result'] = "Either limitstart or limitnum are invalid.  They must be numeric.";
        return $msg_list;
      }

      if ( isset( $options['limitstart'] ) && $options['limitstart'] >= 0 )
        $start = $options['limitstart'];
      else
        $start = "0";

      if ( isset( $options['limitnum'] ) && $options['limitnum'] > 0 )
        $limit = $options['limitnum'] > LIMITMAX ? LIMITMAX : $options['limitnum'];
      else
        $limit = 25;

      $sql .= " LIMIT $start, $limit";
    }
    else
      $sql .= " LIMIT 0, ".LIMITMAX;
  }
  else
    $sql .= " LIMIT 0, ".LIMITMAX;

  $results = dbquery($sql);
  $msg_list['trinsic']['result'] = "success";
  $numrows = mysql_num_rows($results);
  $msg_list['trinsic']['totalresults'] = $numrows;
  $msg_list['trinsic']['startnumber'] = $start;
  $msg_list['trinsic']['numreturned'] = $numrows;
  while ( $row = mysql_fetch_array( $results, MYSQL_ASSOC ) )
    $msg_list['trinsic']['messages'][] = $row;

  return $msg_list;
}

// blacklist retrieval
function get_blacklist($options)
{
  global $domain;
  
  $start = 0;
  $msg_list = array();
  $sql = "SELECT from_address, to_address FROM blacklist WHERE to_address LIKE ".escape("%@$domain");

  // possible MySQL conditions
  $possible_options = array("search_by", "search_operator", "search", "limitstart", "limitnum" );
  $found = FALSE;
  foreach( $options as $key => $value )
    if ( in_array( $key, $possible_options ) )
      $found = TRUE;

  if ( $found )
  {
    // search filters
    if ( isset($options['search']) && isset($options['search_by']) )
    {
      // make sure valid search_by
      $possible_filters = array( "", "all", "to_address", "from_address" );
      if ( !in_array( $options['search_by'], $possible_filters ) )
      {
        $msg_list['trinsic']['result'] = $options['search_by']." is an invalid search filter.";
        return $msg_list;
      }

      // and valid search
      if ( $options['search'] == "" )
      {
        $msg_list['trinsic']['result'] = "Search filter is empty.";
        return $msg_list;
      }
      else
        $search = $options['search'];

      // and valid operator
      $possible_operators = array( "matches", "contains" );
      if ( !isset( $options['search_operator'] ) || $options['search_operator'] == "matches" )
      {
        if ( $options['search_by'] == "" || $options['search_by'] == "all" )
          $sql .= " AND ( to_address = ".escape($search)." OR from_address = ".escape($search)." )";
        else
          $sql .= " AND ".$options['search_by']." = ".escape($search);
      }
      else if ( $options['search_operator'] == "contains" )
      {
        if ( $options['search_by'] == "" || $options['search_by'] == "all" )
          $sql .= " AND ( to_address LIKE ".escape("%$search%")." OR from_address LIKE ".escape("%$search%")." )";
        else
          $sql .= " AND ".$options['search_by']." LIKE ".escape("%$search%");
      }
      else
      {
        $msg_list['trinsic']['result'] = $options['search_operator']." is an invalid search operator.";
        return $msg_list;
      }
    }

    // filter start and stop
    if ( isset( $options['limitstart'] ) || isset( $options['limitnum'] ) )
    {
      if ( ( isset( $options['limitstart'] ) && !is_numeric( $options['limitstart'] ) ) || ( isset( $options['limitnum'] ) && !is_numeric( $options['limitnum'] ) ) )
      {
        $msg_list['trinsic']['result'] = "Either limitstart or limitnum are invalid.  They must be numeric.";
        return $msg_list;
      }

      if ( isset( $options['limitstart'] ) && $options['limitstart'] >= 0 )
        $start = $options['limitstart'];
      else
        $start = "0";

      if ( isset( $options['limitnum'] ) && $options['limitnum'] > 0 )
        $limit = $options['limitnum'] > LIMITMAX ? LIMITMAX : $options['limitnum'];
      else
        $limit = 25;

      $sql .= " LIMIT $start, $limit";
    }
    else
      $sql .= " LIMIT 0, ".LIMITMAX;
  }
  else
    $sql .= " LIMIT 0, ".LIMITMAX;

  $results = dbquery($sql);
  $msg_list['trinsic']['result'] = "success";
  $numrows = mysql_num_rows($results);
  $msg_list['trinsic']['totalresults'] = $numrows;
  $msg_list['trinsic']['startnumber'] = $start;
  $msg_list['trinsic']['numreturned'] = $numrows;
  while ( $row = mysql_fetch_array( $results, MYSQL_ASSOC ) )
    $msg_list['trinsic']['blacklist'][] = $row;

  return $msg_list;
}

// whiteliste retrieval
function get_whitelist($options)
{
  global $domain;
  
  $start = 0;
  $msg_list = array();
  $sql = "SELECT from_address, to_address, DATE_FORMAT( timestamp, '".DATE_FORMAT."' ) AS date, added_by FROM whitelist WHERE to_address LIKE ".escape("%@$domain");

  // possible MySQL conditions
  $possible_options = array("search_by", "search_operator", "search", "limitstart", "limitnum" );
  $found = FALSE;
  foreach( $options as $key => $value )
    if ( in_array( $key, $possible_options ) )
      $found = TRUE;

  if ( $found )
  {
    // search filters
    if ( isset($options['search']) && isset($options['search_by']) )
    {
      // make sure valid search_by
      $possible_filters = array( "", "all", "to_address", "from_address" );
      if ( !in_array( $options['search_by'], $possible_filters ) )
      {
        $msg_list['trinsic']['result'] = $options['search_by']." is an invalid search filter.";
        return $msg_list;
      }

      // and valid search
      if ( $options['search'] == "" )
      {
        $msg_list['trinsic']['result'] = "Search filter is empty.";
        return $msg_list;
      }
      else
        $search = $options['search'];

      // and valid operator
      $possible_operators = array( "matches", "contains" );
      if ( !isset( $options['search_operator'] ) || $options['search_operator'] == "matches" )
      {
        if ( $options['search_by'] == "" || $options['search_by'] == "all" )
          $sql .= " AND ( to_address = ".escape($search)." OR from_address = ".escape($search)." )";
        else
          $sql .= " AND ".$options['search_by']." = ".escape($search);
      }
      else if ( $options['search_operator'] == "contains" )
      {
        if ( $options['search_by'] == "" || $options['search_by'] == "all" )
          $sql .= " AND ( to_address LIKE ".escape("%$search%")." OR from_address LIKE ".escape("%$search%")." )";
        else
          $sql .= " AND ".$options['search_by']." LIKE ".escape("%$search%");
      }
      else
      {
        $msg_list['trinsic']['result'] = $options['search_operator']." is an invalid search operator.";
        return $msg_list;
      }
    }

    // filter start and stop
    if ( isset( $options['limitstart'] ) || isset( $options['limitnum'] ) )
    {
      if ( ( isset( $options['limitstart'] ) && !is_numeric( $options['limitstart'] ) ) || ( isset( $options['limitnum'] ) && !is_numeric( $options['limitnum'] ) ) )
      {
        $msg_list['trinsic']['result'] = "Either limitstart or limitnum are invalid.  They must be numeric.";
        return $msg_list;
      }

      if ( isset( $options['limitstart'] ) && $options['limitstart'] >= 0 )
        $start = $options['limitstart'];
      else
        $start = "0";

      if ( isset( $options['limitnum'] ) && $options['limitnum'] > 0 )
        $limit = $options['limitnum'] > LIMITMAX ? LIMITMAX : $options['limitnum'];
      else
        $limit = 25;

      $sql .= " LIMIT $start, $limit";
    }
    else
      $sql .= " LIMIT 0, ".LIMITMAX;
  }
  else
    $sql .= " LIMIT 0, ".LIMITMAX;

  $results = dbquery($sql);
  $msg_list['trinsic']['result'] = "success";
  $numrows = mysql_num_rows($results);
  $msg_list['trinsic']['totalresults'] = $numrows;
  $msg_list['trinsic']['startnumber'] = $start;
  $msg_list['trinsic']['numreturned'] = $numrows;
  while ( $row = mysql_fetch_array( $results, MYSQL_ASSOC ) )
    $msg_list['trinsic']['whitelist'][] = $row;

  return $msg_list;
}

// for API authentication validation
function validate_account( $username, $password, $api_id )
{
  //  using a unique secure API_ID is optional
  if ( REQUIRE_API_ID )
    $result = dbquery( "SELECT * FROM users WHERE username=".escape($username)." AND password=".escape($password)." AND api_id=".escape($api_id) );
  else
    $result = dbquery( "SELECT * FROM users WHERE username=".escape($username)." AND password=".escape($password) );

  if ( mysql_num_rows( $result ) > 0 )
    return "passed";
   
  $response = array();
  $response['trinsic']['result'] = "API authentication failed.";
  return $response;
}

$api_id = isset($_POST['api_id']) ? $_POST['api_id'] : "";
$validated = validate_account( $_POST["username"], $_POST["password"], $api_id );
if ( $validated == "passed" )
{
  // extract domain from username
  $ar = explode( '@', $_POST["username"] );
  $domain = $ar[1];

  // possible API actions check
  $possible_actions = array("get_messages", "get_blacklist", "get_whitelist");
  if ( isset( $_POST["action"] ) && in_array( $_POST["action"], $possible_actions ) )
  {
    switch ( $_POST["action"] )
    {
      case "get_messages":
        $value = get_message_list($_POST);
        break;
      case "get_blacklist":
        $value = get_blacklist($_POST);
        break;
      case "get_whitelist":
        $value = get_whitelist($_POST);
        break;
      default:
        $value = "Invalid action specified.";
        break;
    }
  }
  else
  {
    $value = array();
    if ( !isset( $_POST["action"] ) || $_POST["action"] == "" )
      $value['trinsic']['result'] = "Action not specified";
    else
      $value['trinsic']['result'] = $_POST["action"]." is an invalid action";
  }

  exit( json_encode( $value ) );
}
else
  exit( json_encode( $validated ) );
?>
