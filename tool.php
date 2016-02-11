<?php

/**
 *  @module         multiplepage
 *  @version        see info.php of this module
 *  @authors        Robert Joseph and others, cms-lab
 *  @copyright      2009-2010 Robert Joseph and others
 *  @copyright      2010-2014 cms-lab 
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

$MOD_MP['NoName'] = "No files were given names";
$MOD_MP['PagesCreated'] = "Pages created are:";

/**
 *	Load Language file
 */
$lang = (dirname(__FILE__))."/languages/". LANGUAGE .".php";
require_once ( !file_exists($lang) ? (dirname(__FILE__))."/languages/EN.php" : $lang );

/**	*******************************
 *	Try to get the template-engine.
 */
global $parser, $loader;
require( dirname(__FILE__)."/register_parser.php" );


if (isset($_POST['button'])) {

	// Include the functions file
	require_once(LEPTON_PATH.'/framework/functions.php');

	$menutitlearr = $_POST['menu_title'];
	$pagetitlearr = $_POST['page_title'];
	$typearr = $_POST['type'];
	$parentarr = $_POST['parent'];
	$visibilityarr = $_POST['visibility'];
	$error= '';

	for($i = 0; $i < count($menutitlearr) ; $i++) {
		$menu_title = trim($menutitlearr[$i]);
		$menu_title = htmlspecialchars($menu_title);
		$page_title = trim($pagetitlearr[$i]);
		$page_title = htmlspecialchars($page_title);
		$module = $typearr[$i];
		$parent = $parentarr[$i];
		$visibility = $visibilityarr[$i];
		$admin_groups = '';
		$viewing_groups = '';
		$admin_groups[0] = '1';
		$viewing_groups[0] = '1';

		if($menu_title != '' and substr($menu_title,0,1)!='.') {
			if ($parent!=0) {
				if (!$admin->get_page_permission($parent,'admin')) {
					$error[] .= ($MESSAGE['PAGES']['INSUFFICIENT_PERMISSIONS']);
					$flag = false;
				} elseif (!$admin->get_permission('pages_add_l0','system')) {
					$error[] .= ($MESSAGE['PAGES']['INSUFFICIENT_PERMISSIONS']);
					$flag = false;
				}	
			}
			// Check to see if page created has needed permissions
			if(!in_array(1, $admin->get_groups_id())) {
				$admin_perm_ok = false;
				foreach ($admin_groups as $adm_group) {
					if (in_array($adm_group, $admin->get_groups_id())) {
						$admin_perm_ok = true;
					}
				}
				if ($admin_perm_ok == false) {
					$error[] .= ($MESSAGE['PAGES']['INSUFFICIENT_PERMISSIONS']);
					$flag = false;
				}
				$admin_perm_ok = false;
				foreach ($viewing_groups as $view_group) {
					if (in_array($view_group, $admin->get_groups_id())) {
						$admin_perm_ok = true;
					}
				}
				if ($admin_perm_ok == false) {
					$error[] .= ($MESSAGE['PAGES']['INSUFFICIENT_PERMISSIONS']);
					$flag = false;
				}
			}
			
			// Work-out what the link and page filename should be
			if($parent == '0') {
				$link = '/'.page_filename($menu_title);
				$filename = LEPTON_PATH.PAGES_DIRECTORY.'/'.page_filename($menu_title).'.php';
			} else {
				$parent_section = '';
				$parent_titles = array_reverse(get_parent_titles($parent));
				foreach($parent_titles AS $parent_title) {
					$parent_section .= page_filename($parent_title).'/';
				}
				if($parent_section == '/') { $parent_section = ''; }
				$link = '/'.$parent_section.page_filename($menu_title);
				$filename = LEPTON_PATH.PAGES_DIRECTORY.'/'.$parent_section.page_filename($menu_title).'.php';
				make_dir(LEPTON_PATH.PAGES_DIRECTORY.'/'.$parent_section);
			}
			
			// Check if a page with same page filename exists
			$flag = true;
			$get_same_page = $database->query("SELECT page_id FROM ".TABLE_PREFIX."pages WHERE link = '$link'");
			if($get_same_page->numRows() > 0 OR file_exists(LEPTON_PATH.PAGES_DIRECTORY.$link.'.php') OR file_exists(LEPTON_PATH.PAGES_DIRECTORY.$link.'/')) {
				$error[] .= "<br /><span class='red'><b>#$i: " ."</b> ". $MESSAGE['PAGES']['PAGE_EXISTS'] ." ($link)<span><br />";

				$flag = false;
			}
			if($flag) {
				$admin_groups = implode(',', $admin_groups);
				$viewing_groups = implode(',', $viewing_groups);

				$run[$i]['msg'] = "$i) Title: $menu_title Module: $module Parent: $parent<br />";
				$language = DEFAULT_LANGUAGE;
				$time = time();
				$uid = $admin->get_user_id();
				$sql = "INSERT INTO ".TABLE_PREFIX."pages 
						SET
							page_title='$page_title',
							menu_title='$menu_title',
							parent='$parent',
							template='',
							target='_top',
							position='2000',
							visibility='$visibility',
							searching='1',
							link='$link',
							menu='1',
							language='$language',
							admin_groups='$admin_groups',
							viewing_groups='$viewing_groups',
							modified_when='$time',
							modified_by='$uid'";
				$run[$i]['sql'] = $sql;
				$run[$i]['num'] = $i;
				$run[$i]['module'] = $module;
				$run[$i]['filename'] = $filename;
				$created[] = "$menu_title";

			}
			$flag = true;
		} else if (substr($menu_title,0,1) =='.') {
			$error[] .= ($MESSAGE['PAGES']['BLANK_PAGE_TITLE'] ." - $menu_title");
		}
	}
	if(!isset($error[0]) or $error[0] == '') {
		if (isset($run)) {
			foreach($run as $row)  {
				require_once (LEPTON_PATH.'/framework/class.order.php');
				$sql = $row['sql'];
				echo("==>$sql<br />");
				$database->query($sql);
				$id = $database->get_one("SELECT LAST_INSERT_ID()");
				

				$query = "SELECT * FROM ".TABLE_PREFIX."pages WHERE page_id = '{$id}'";
				echo($query . "<br />");
				$pagequery = $database->query($query);
				$page = $pagequery->fetchRow();
				$parent = $page['parent'];
				$module = $row['module'];
				$page_id = $page['page_id'];
				$filename = $row['filename'];

				// Get new order
				// Work-out if the page parent (if selected) has a seperate template to the default
				$query = "SELECT template FROM ".TABLE_PREFIX."pages WHERE page_id = '$parent'";
				echo($query . "<br />Module: $module<br />");
				$query_parent = $database->query($query);
				if($query_parent->numRows() > 0) {
					$fetch_parent = $query_parent->fetchRow();
					$template = $fetch_parent['template'];
				} else {
					$template = '';
				}
		
				// Work out level
				$level = level_count($page_id);
				// Work out root parent
				$root_parent = root_parent($page_id);
				// Work out page trail
				$page_trail = get_page_trail($page_id);
				
				// Update page with new level and link
				$query = "UPDATE ".TABLE_PREFIX."pages SET level = '$level', root_parent = '$root_parent', page_trail = '$page_trail', template = '$template' WHERE page_id = '$page_id'";
				echo($query . "<br />");
				$database->query($query);
		
				// Create a new file in the /pages dir
				create_access_file($filename, $page_id, $level);
				
				/* clean up page order */
				$order = new order(TABLE_PREFIX.'pages', 'position', 'page_id', 'parent');
				// First clean order
				$order->clean($parent);

				// Get new order for section
				$order = new order(TABLE_PREFIX.'sections', 'position', 'section_id', 'page_id');
				$position = $order->get_new($parent);
				
				// Add new record into the sections table
				$query = "INSERT INTO ".TABLE_PREFIX."sections (page_id,position,module,block) VALUES ('$page_id','$position', '$module','1')";
				echo($query . "<br />");
				$database->query($query);
				
				// Get the section id
				$section_id = $database->get_one("SELECT LAST_INSERT_ID()");
				
				// Include the selected modules add file if it exists
				if(file_exists(LEPTON_PATH.'/modules/'.$module.'/add.php')) {
					require(LEPTON_PATH.'/modules/'.$module.'/add.php');
				}
			}	
			echo "<br /><strong>". $MESSAGE['PAGES']['ADDED'] .":</strong><ul class='listcreated'>";
			foreach($created as $row) {
				echo ( "<li>" . $row . "</li>");
			}
			echo '</ul>';	
			$menutitlearr = '';
		} else {
			echo $MESSAGE['PAGES']['BLANK_PAGE_TITLE'];
		}
	} else {
		foreach($error as $row) {
			echo $row . "";
		}
	}
}

$num = 15;
$self= $_SERVER['PHP_SELF'] ."?tool=multiplepage";
$typeoptions = array ();

$mp_values = array(
 'self' => $self,
 'TEXT' => $TEXT 
);

$parentoptions = parent_list(0);

// Set module permissions
$module_permissions = $_SESSION['MODULE_PERMISSIONS'];

$result = $database->query("SELECT * FROM ".TABLE_PREFIX."addons WHERE type = 'module' AND function = 'page' order by name");
if($result->numRows() > 0) {
while($module = $result->fetchRow( MYSQL_ASSOC )) {
  // Check if user is allowed to use this module
  if(!is_numeric(array_search($module['directory'], $module_permissions))) {
   $module['selected'] = ($module['directory'] == 'wysiwyg') ? "selected='selected'": "";
   $typeoptions[] = $module;
  }
 }
}

$mp_values['typeoptions'] = $typeoptions;
$mp_values['parentoptions'] = $parentoptions;

$all_menus = array();
for ($i = 0; $i < $num; $i++) {
  $all_menus[] = array(
   'menu_num' => ($i+1),
   'menutitlevalue' => "",
   'pagetitlevalue' => "" 
  );
}
$mp_values['all_menus'] = $all_menus;

$twig_util->resolve_path("modify.lte");

echo $parser->render(
 $twig_modul_namespace.'modify.lte',
 $mp_values
);

// Parent page list
function parent_list($parent) {
 global $admin, $database, $template, $results_array;
 
 $options = array();
 $query = "SELECT * FROM ".TABLE_PREFIX."pages WHERE parent = '$parent' ORDER BY position ASC";
 $get_pages = $database->query($query);
 while($page = $get_pages->fetchRow( MYSQL_ASSOC )) {
  if($admin->page_is_visible($page)==false)
   continue;
  // If the current page cannot be parent, then its children neither
  $list_next_level = true;
  // Stop users from adding pages with a level of more than the set page level limit
  if($page['level']+1 <= PAGE_LEVEL_LIMIT) {
   // Get user perms
   $admin_groups = explode(',', str_replace('_', '', $page['admin_groups']));
   $admin_users = explode(',', str_replace('_', '', $page['admin_users']));

   $in_group = FALSE;
   foreach($admin->get_groups_id() as $cur_gid){
       if (in_array($cur_gid, $admin_groups)) {
           $in_group = TRUE;
       }
   }
   
   if(($in_group) OR is_numeric(array_search($admin->get_user_id(), $admin_users))) {
    $can_modify = true;
   } else {
    $can_modify = false;
   }
   // Title -'s prefix
   $menu_title_prefix = '';
   for($i = 1; $i <= $page['level']; $i++) { $menu_title_prefix .= ' - '; }
   
   $page['menu_title_prefix'] = $menu_title_prefix;
   $options[] = $page;
  }
  if ($list_next_level) {
   $sub_pages = parent_list($page['page_id']);
   if (count($sub_pages)) {
    $options = array_merge($options, $sub_pages);
   }
  }
 }
 return $options;
}
?>
