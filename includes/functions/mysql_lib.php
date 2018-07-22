<?php
/*******************************************************************************
	mysql_lib.php				

	This library holds the function that control access to the mysql database.
	
*******************************************************************************/


/**********************************************************************/


// This function connects to the mysql server and selects the default database
// ***NOT USED***
function mysqlConn(){
	($GLOBALS["___mysqli_ston"] = mysqli_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS));	
	((bool)mysqli_query($GLOBALS["___mysqli_ston"], "USE " . constant('MYSQL_DB')));
	checkMySQL();
}



// Function to submit query and return the result object
function mysqlQuery($query, $type, $key = null, $key2 = null){

	if($query == null){
		return false;
	}
	
	$retVal = null;
	$res = mysqli_query($GLOBALS["___mysqli_ston"], $query);
	
	checkMySQL();
	switch($type){
		case 0:	// Return success only
			$retVal = true;
			break;
		case 1:	// Return the new index (For inserts);
			$retVal = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
			break;
		case 2:	// Return the result objet
			$retVal = $res;
			break;
		case 3: // Return the number of rows in the select statement
			$retVal = mysqli_num_rows($res);
			break;
        case 4: // Return an associated array of results
	        $x = 0;
	        $retVal = array();
			while($result = mysqli_fetch_assoc($res)){
			    $retVal[$x++] = $result;
			}
			break;
		case 5: // Return the first value queried
			$result = mysqli_fetch_assoc($res);
			if($key == null){
				$retVal = $result;
			} else {
				$retVal = $result[$key];
			}
			break;
		case 6: // Return an associated array of results indexed by a field
			if($key == null){return false;}
			$retVal = array();
			while($result = mysqli_fetch_assoc($res)){
				$keyValue = $result[$key];
				foreach($result as $field => $data){
					if($field == $key){continue;}
					$retVal[$keyValue][$field] = $data;
				}
			}
			break;
		case 7: // Return an array of single values indexed by a key
			if($key == null || $key2 == null){return false;}
			$retVal = array();
			while($result = mysqli_fetch_assoc($res)){
				$retVal[$result[$key]] = $result[$key2];
			}
			break;
		case 8: // Return an array of single values indexed by their order in the query
			$retVal = array();
			while($result = mysqli_fetch_assoc($res)){
				$retVal[] = array_values($result)[0];
			}
			break;
		default:
			echo("mysqlQuery type paramater is out of bounds on query \n " . $querry);			
			break;
	}
	checkMySQL();
	return $retVal;
}

// This function checks for a mysql error and throws and error to the error handling system should one occur
function checkMySQL(){
	if(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false))){
		echo "<BR>***";
		die('Error: '.((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
	}
}


// Quote variable to make safe
function quote_smart($value)
{
   // Stripslashes
   if (get_magic_quotes_gpc()) {
       $value = stripslashes($value);
   }
   // Quote if not integer
   if (!is_numeric($value)) {
       $value = "'" . ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $value) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : "")) . "'";
   }
   return $value;
}


?>
