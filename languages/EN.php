<?php

/**
 *  @module         multiplepage
 *  @version        see info.php of this module
 *  @authors        Robert Joseph and others, cms-lab
 *  @copyright      2009-2010 Robert Joseph and others
 *  @copyright      2010-2017 cms-lab 
 *  @license        GNU General Public License
 *  @license terms  see info.php of this module
 *
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('LEPTON_PATH')) {	
	include(LEPTON_PATH.'/framework/class.secure.php'); 
} else {
	$oneback = "../";
	$root = $oneback;
	$level = 1;
	while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
		$root .= $oneback;
		$level += 1;
	}
	if (file_exists($root.'/framework/class.secure.php')) { 
		include($root.'/framework/class.secure.php'); 
	} else {
		trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}
// end include class.secure.php



//Modul Description
$module_description = 'This module allows you to create multiple blank pages.';

//Variables for the  backend
$MOD_MP['NoName'] = "No files were given names";
$MOD_MP['PagesCreated'] = "Pages created are:";

?>