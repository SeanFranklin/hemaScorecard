<?php
/*******************************************************************************
	Deployment flag constants.

	Extracted from config.php so the API bootstrap can reuse database.php
	(which references DEPLOYMENT_UNKNOWN on line 11) without pulling in
	config.php's session/permission side effects.

*******************************************************************************/

	if(!defined('DEPLOYMENT_UNKNOWN')){
		define("DEPLOYMENT_UNKNOWN",0);
		define("DEPLOYMENT_PRODUCTION",1);
		define("DEPLOYMENT_TEST",2);
		define("DEPLOYMENT_LOCAL",3);
	}

// END OF FILE /////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
