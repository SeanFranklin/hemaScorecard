<?php
/*******************************************************************************
	DB return-type constants for mysqlQuery().

	Extracted from config.php so the API bootstrap can reuse mysqlQuery()
	without pulling in config.php's session/permission side effects.

*******************************************************************************/

	if(!defined('SEND')){
		define("SEND",0);
		define("INDEX",1);
		define("RAW",2);
		define("NUM_ROWS",3);
		define("ASSOC",4);
		define("SINGLE",5);
		define("KEY",6);
		define("KEY_SINGLES",7);
		define("SINGLES",8);
	}

// END OF FILE /////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
