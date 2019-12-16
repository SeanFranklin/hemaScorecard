<?php
/*******************************************************************************
	General Functions
	
	Functions which perform general functions, not specific to any application
	
*******************************************************************************/

/******************************************************************************/

function show($array){
//shows content of any varriable
	echo "<pre>",var_dump($array),"</pre>";
}

/******************************************************************************/

function refreshPage(){

	$url = strtok($_SERVER['PHP_SELF'], "#");
	$url .= "#" . ($_SESSION['jumpTo'] ?? ''); // could be empty, treated as null
	unset($_SESSION['jumpTo']);
	header('Location: '.$url);
	exit;
}

/******************************************************************************/

function ifSet($bool, $value){
// If true return the value, if false return an empty value.

	if((bool)$bool != false){
		return $value;
	} else {
		return null;
	}
}

/******************************************************************************/

function ifNotSet($bool, $value){
// If false return the value, if true return an empty value.

	if((bool)$bool != false){
		return null;
	} else {
		return $value;
	}
}

/******************************************************************************/

function optionValue($value, $selectValue = null){
// Writes the value line to an option in a select statement
// If the value is equal to the optional second parameter then the option is selected

	echo "value='{$value}'";
	if(($value === 0 || $value === '0') && ($selectValue === '' || $selectValue === null)){
		// Do nothing
	} elseif($value == $selectValue){
		echo " selected";
	}

}

/******************************************************************************/

function chk($value, $compare = null){
// For use in setting checkboxes to true/false
// It is OK to suppress errors on inputs, non-existant values are acceptable inputs.	

	if(isset($value) == false){
		return '';
	} elseif($compare == null && $value != false){
		return 'checked';
	} elseif($compare == $value){
		return 'checked';
	} else {
		return '';
	}
}

/******************************************************************************/

function plrl($num){
// Returns an 's' if the number is not 1.
// Used for writing things like 1 Point vs 2 Points
	
	if(abs($num) != 1){
		return 's';
	}
}

/******************************************************************************/
function redirect($url){
// redirects the page to the given url

	echo "<script type='text/javascript'> window.location = '{$url}'; </script>";
}

/******************************************************************************/

function appendArray($a1, $a2){
	
	foreach((array)$a1 as $index => $data){
		$out[$index] = $data;
	}
	foreach((array)$a2 as $index => $data){
		$out[$index] = $data;
	}
	return $out;
	
}

/******************************************************************************/

function isSelected($val1, $val2='selected', $output='selected'){
// Function to simplify specifying which value in a select is selected
// Comparison mode: Compares the first two parameters using loose type comparison
//					and returns the third parameter if true, null if false
//					Used to enter two values to compare for equality
// Evaluation mode:	If the first parameter is a boolean it returns the second
//					parameter if true and null if false
//					Used to evaluate an expression in the function call

	if($val1 === true){
		return $val2;
	}
	if($val1 === false){
		return '';
	}

	if($val1 == $val2){
		return $output;
	}
	return '';
}

/******************************************************************************/

function isNotSelected($val1, $val2='selected', $output='selected'){
// Function to return a string when not true or not equal
// Comparison mode: Compares the first two parameters using loose type comparison
//					and returns the third parameter if false, null if true
//					Used to enter two values to compare for equality
// Evaluation mode:	If the first parameter is a boolean it returns the second
//					parameter if false and null if true
//					Used to evaluate an expression in the function call

	if($val1 === true){
		return '';
	}
	if($val1 === false){
		return $val2;
	}

	if($val1 == $val2){
		return '';
	}
	return $output;
}


/******************************************************************************/

function getGoogleSpreadsheet($spreadsheet_url,$headers){
	
	if(!ini_set('default_socket_timeout',    15)) {echo "<!-- unable to change socket timeout -->";}

	if (($handle = fopen($spreadsheet_url, "r")) !== false) {
		while (($data = fgetcsv($handle, 1000, ",")) !== false) {
			$spreadsheetData[]=$data;
		}
		fclose($handle);
	} else {
		die("Problem reading csv");
	}
	
	
	
	if($headers == null){
		foreach($spreadsheetData[0] as $i => $columnName){
			$headers[$i] = $columnName;
		}
	}
	
	unset($spreadsheetData[0]);
	$arrayLength = count($spreadsheetData);
	
	for($i=1;$i<=$arrayLength;$i++){
		foreach($headers as $j => $header){
			$spreadsheetData[$i][$header] = $spreadsheetData[$i][$j];
			unset($spreadsheetData[$i][$j]);
		}
	}
	
	return $spreadsheetData;
}

/******************************************************************************/

function intToString($int, $num){
// returns a string consisting of an int with leading zeros added to make it
// $num characters long

	$string = (string)$int;
	
	for($length = strlen($string);$length<$num;$length++){
		$string = "0".$string;
		
	}

	return $string;

}

/******************************************************************************/

function nullBlankInt($input){
// returns the string 'null' if the input is null. Used for sql queries
	
	if($input != null){
		return $input;
	} else {
		return 'null';
	}
}

/******************************************************************************/

function mysqlSetRecordToDefault($tableName, $whereClause, $fieldsToKeep){
	// Sets the values of all fields on $tableName to their defaults
	// Function affects rows identified by $whereClause
	// and ignores the fields named in the array $fieldsToKeep
	
	if($whereClause == null){return;}
	
	if(is_string($fieldsToKeep)){
		$a = $fieldsToKeep;
		unset($fieldsToKeep);
		$fieldsToKeep[] = $a;
	}
	
	$sql = "SHOW COLUMNS FROM {$tableName}";
	$result = mysqlQuery($sql, ASSOC);
	
	foreach($result as $record){
		$name = $record['Field'];
		if($record['Key'] != 'PRI'){
			$fieldNames[$name] = true;
		}
	}
	
	foreach($fieldsToKeep as $field){
		unset($fieldNames[$field]);
	}
	
	$sql = "UPDATE {$tableName}
		SET ";
	foreach($fieldNames as $name => $true){
		$sql .= "{$name}= DEFAULT, ";
		
	}

	$sql = rtrim($sql,', \t\n');
	$sql .= " ".$whereClause;
	
	mysqlQuery($sql, SEND);
}

/******************************************************************************/

function mysqlSetRecordToNull($tableName, $whereClause, $fieldsToKeep){
	// Sets the values of all fields on $tableName to null
	// Function affects rows identified by $whereClause
	// and ignores the fields named in the array $fieldsToKeep
	
	if($whereClause == null){return;}
	
	if(is_string($fieldsToKeep)){
		$a = $fieldsToKeep;
		unset($fieldsToKeep);
		$fieldsToKeep[] = $a;
	}
	
	$sql = "SHOW COLUMNS FROM {$tableName}";
	$result = mysqlQuery($sql, ASSOC);
	
	foreach($result as $record){
		$name = $record['Field'];
		if($record['Key'] != 'PRI'){
			$fieldNames[$name] = true;
		}
	}
	
	foreach($fieldsToKeep as $field){
		unset($fieldNames[$field]);
	}
	
	$sql = "UPDATE {$tableName}
			SET ";
	foreach($fieldNames as $name => $true){
		$sql .= "{$name}= null, ";
		
	}

	$sql = rtrim($sql,', \t\n');
	$sql .= " ".$whereClause;

	mysqlQuery($sql, SEND);
}

/******************************************************************************/

function xorWithZero($in1, $in2){
// XOR function where zero is recognized as a number and not a null	
	
	$num1 = false;
	$num2 = false;

	if($in1){$num1 = true;}
	if($in2){$num2 = true;}
	
	if($in1 === '0'){$num1 = true;}
	if($in2 === '0'){$num2 = true;}
	
	if($num1){
		if($num2){
			return false;
		} else {
			return true;
		}
	} else {
		if(!$num2){
			return false;
		} else {
			return true;
		}
		
	}
}

/******************************************************************************/

function numSuffix($number){
// Return the correct suffix to a number. ie. 1 -> 'st', 2 -> 'nd', 3 -> 'rd'
// Returns as an html formated superscript.
	switch (substr($number, -1)){
		case 1:
			$suf = "st";
			break;
		case 2:
			$suf = "nd";
			break;
		case 3:
			$suf = "rd";
			break;
		case null:
			break;
		default:
			$suf = "th";
			break;
	}

	return $suf;

}

/******************************************************************************/

function sqlDateToString($sqlDate){
// Converts dates read from sql into human readable format. Month and day only.
// Example: '2017-12-15' -> 'Dec 15th'
	
	if(strcmp($sqlDate,"0000-00-00") == 0){
		return null;
	}
	
	// Day
	$day = $sqlDate[8];
	$day .= $sqlDate[9];
	
	$day .= "<sup>".numSuffix($day)."</sup>";

	
	if($day[0] == 0){
		$day = substr($day, 1);
	}

	// Month
	$monthNumber = substr($sqlDate, 5,2);

	switch ($monthNumber) {
		case '01':
			$month = 'Jan';
			break;
		case '02':
			$month = 'Feb';
			break;
		case '03':
			$month = 'Mar';
			break;
		case '04':
			$month = 'Apr';
			break;
		case '05':
			$month = 'May';
			break;
		case '06':
			$month = 'June';
			break;
		case '07':
			$month = 'July';
			break;
		case '08':
			$month = 'Aug';
			break;
		case '09':
			$month = 'Sept';
			break;
		case '10':
			$month = 'Oct';
			break;
		case '11':
			$month = 'Nov';
			break;
		case '12':
			$month = 'Dec';
			break;
	}
	
	$date = $month." ".$day;
	return $date;
		
}

/******************************************************************************

function query($sql){
// submits a query and checks for errors

	$queryOutput = mysqli_query($GLOBALS["___mysqli_ston"], $sql);
	if(!$queryOutput){
		echo "<BR>!!!!!!!!!";
		die('Error: '.((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
	}
	return $queryOutput;
}

/******************************************************************************/

function connectToDB(){
// Connects to database
// Relies on constants DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD, PRIMARY_DATABASE

	//establishes database connection
	$connection = ($GLOBALS["___mysqli_ston"] = mysqli_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD, PRIMARY_DATABASE));
	// Check connection
	if (mysqli_connect_errno()){
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}
	
	((bool)mysqli_query($GLOBALS["___mysqli_ston"], "USE " . constant('PRIMARY_DATABASE')));
	
	return $connection;
}

/******************************************************************************/

function array_filter_recursive($input) {
// unset empty entries from a multi-dimensional array

	foreach ($input as &$value) 
	{ 
	  if (is_array($value)) 
	  { 
		 $value = array_filter_recursive($value); 
	  } 
	}     
	return array_filter($input,'isNotNull'); 
} 

/******************************************************************************/

function isNotNull($val){
// returns true if value is not null

	return !is_null($val);
}

/******************************************************************************/

function in_array_r($needle, $haystack, $strict = false) {
// recursively checks a multi-dimensional array for a value	
	
    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
            return true;
        }
    }

    return false;
}

/******************************************************************************/

function standardDeviation($arr) {
// function to calculate the standard deviation of array elements 

    $num_of_elements = count($arr);
    if($num_of_elements == 0){
    	return null;
    } 
      
    $variance = 0.0; 
      
    // calculating mean using array_sum() method 
    $average = array_sum($arr)/$num_of_elements; 
      
    foreach($arr as $i){ 
        // sum of squares of differences between all numbers and means. 
        $variance += pow(($i - $average), 2); 
    } 
      
    return (float)sqrt($variance/$num_of_elements); 
} 
	
/******************************************************************************/

// END OF FILE /////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
