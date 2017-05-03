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


$module_directory 	= 'multiplepage';
$module_name 		= 'Multiple Page Creator';
$module_function 	= 'tool';
$module_version 	= '1.1.1';
$module_platform 	= '2.3';
$module_author 		= 'Robert Joseph and others, cms-lab';
$module_license = 	'GNU General Public License';
$module_license_terms = '-';
$module_description = 'This module allows you to create 15 new pages all at one time';
$module_home		= 'http://cms-lab.com';
$module_guid		= '79B9C2C2-2101-498F-9693-3982C84C02EC';

/**
 *	
 *	changelog:
 *	07/14/2009: rlj - added more permissions for page editing 
 *				  more checks for errouneous titles
 *				  fixed ordering problem
 *				  allow only submenu from pages that are already created
 *	03/06/2009: Stefek - added style
 *	03/04/2009: rlj - added variable definition to get rid of errors and warnings
 *	03/16-2010: Argos - changed tool.php so that you can add separate menu title and page title
 *	07/10/2014: cms-lab - recode with twig
 *
 */ 
?>
