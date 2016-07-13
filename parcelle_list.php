<?php
/* Copyright (C) 2007-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2016 Bruno Généré      <bgenere@webiseasy.org>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *   	\file       vignoble/parcelle_list.php
 *		\ingroup    vignoble
 *		\brief      This file is an example of a php page
 *					Initialy built by build_class_from_table on 2016-07-13 11:12
 */

//if (! defined('NOREQUIREUSER'))  define('NOREQUIREUSER','1');
//if (! defined('NOREQUIREDB'))    define('NOREQUIREDB','1');
//if (! defined('NOREQUIRESOC'))   define('NOREQUIRESOC','1');
//if (! defined('NOREQUIRETRAN'))  define('NOREQUIRETRAN','1');
//if (! defined('NOCSRFCHECK'))    define('NOCSRFCHECK','1');			// Do not check anti CSRF attack test
//if (! defined('NOSTYLECHECK'))   define('NOSTYLECHECK','1');			// Do not check style html tag into posted data
//if (! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL','1');		// Do not check anti POST attack test
//if (! defined('NOREQUIREMENU'))  define('NOREQUIREMENU','1');			// If there is no need to load and show top and left menu
//if (! defined('NOREQUIREHTML'))  define('NOREQUIREHTML','1');			// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))  define('NOREQUIREAJAX','1');
//if (! defined("NOLOGIN"))        define("NOLOGIN",'1');				// If this page is public (can be called outside logged session)

// Change this following line to use the correct relative path (../, ../../, etc)
$res=0;
if (! $res && file_exists("../main.inc.php")) $res=@include '../main.inc.php';					// to work if your module directory is into dolibarr root htdocs directory
if (! $res && file_exists("../../main.inc.php")) $res=@include '../../main.inc.php';			// to work if your module directory is into a subdir of root htdocs directory
if (! $res && file_exists("../../../dolibarr/htdocs/main.inc.php")) $res=@include '../../../dolibarr/htdocs/main.inc.php';     // Used on dev env only
if (! $res && file_exists("../../../../dolibarr/htdocs/main.inc.php")) $res=@include '../../../../dolibarr/htdocs/main.inc.php';   // Used on dev env only
if (! $res) die("Include of main fails");
// Change this following line to use the correct relative path from htdocs
include_once(DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php');
dol_include_once('/vignoble/class/parcelle.class.php');

// Load traductions files requiredby by page
$langs->load("vignoble");
$langs->load("other");

// Get parameters
$id			= GETPOST('id','int');
$action		= GETPOST('action','alpha');
$backtopage = GETPOST('backtopage');
$myparam	= GETPOST('myparam','alpha');


$search_entity=GETPOST('search_entity','int');
$search_ref=GETPOST('search_ref','alpha');
$search_label=GETPOST('search_label','alpha');
$search_description=GETPOST('search_description','alpha');
$search_surface=GETPOST('search_surface','alpha');
$search_nbpieds=GETPOST('search_nbpieds','int');
$search_ecartement=GETPOST('search_ecartement','alpha');
$search_fk_assolement=GETPOST('search_fk_assolement','int');
$search_fk_cepage=GETPOST('search_fk_cepage','int');
$search_fk_porte_greffe=GETPOST('search_fk_porte_greffe','int');
$search_note_private=GETPOST('search_note_private','alpha');
$search_fk_user_author=GETPOST('search_fk_user_author','int');
$search_fk_user_modif=GETPOST('search_fk_user_modif','int');


$optioncss = GETPOST('optioncss','alpha');

// Load variable for pagination
$limit = GETPOST("limit")?GETPOST("limit","int"):$conf->liste_limit;
$sortfield = GETPOST('sortfield','alpha');
$sortorder = GETPOST('sortorder','alpha');
$page = GETPOST('page','int');
if ($page == -1) { $page = 0; }
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (! $sortfield) $sortfield="t.rowid"; // Set here default search field
if (! $sortorder) $sortorder="ASC";

// Protection if external user
$socid=0;
if ($user->societe_id > 0)
{
    $socid = $user->societe_id;
	//accessforbidden();
}

// Initialize technical object to manage hooks. Note that conf->hooks_modules contains array
$hookmanager->initHooks(array('parcellelist'));
$extrafields = new ExtraFields($db);

// fetch optionals attributes and labels
$extralabels = $extrafields->fetch_name_optionals_label('vignoble');
$search_array_options=$extrafields->getOptionalsFromPost($extralabels,'','search_');

// Load object if id or ref is provided as parameter
$object=new Parcelle($db);
if (($id > 0 || ! empty($ref)) && $action != 'add')
{
	$result=$object->fetch($id,$ref);
	if ($result < 0) dol_print_error($db);
}

// Definition of fields for list
$arrayfields=array(
    
't.entity'=>array('label'=>$langs->trans("Fieldentity"), 'checked'=>1),
't.ref'=>array('label'=>$langs->trans("Fieldref"), 'checked'=>1),
't.label'=>array('label'=>$langs->trans("Fieldlabel"), 'checked'=>1),
't.description'=>array('label'=>$langs->trans("Fielddescription"), 'checked'=>1),
't.surface'=>array('label'=>$langs->trans("Fieldsurface"), 'checked'=>1),
't.nbpieds'=>array('label'=>$langs->trans("Fieldnbpieds"), 'checked'=>1),
't.ecartement'=>array('label'=>$langs->trans("Fieldecartement"), 'checked'=>1),
't.fk_assolement'=>array('label'=>$langs->trans("Fieldfk_assolement"), 'checked'=>1),
't.fk_cepage'=>array('label'=>$langs->trans("Fieldfk_cepage"), 'checked'=>1),
't.fk_porte_greffe'=>array('label'=>$langs->trans("Fieldfk_porte_greffe"), 'checked'=>1),
't.note_private'=>array('label'=>$langs->trans("Fieldnote_private"), 'checked'=>1),
't.fk_user_author'=>array('label'=>$langs->trans("Fieldfk_user_author"), 'checked'=>1),
't.fk_user_modif'=>array('label'=>$langs->trans("Fieldfk_user_modif"), 'checked'=>1),

    
    //'t.entity'=>array('label'=>$langs->trans("Entity"), 'checked'=>1, 'enabled'=>(! empty($conf->multicompany->enabled) && empty($conf->multicompany->transverse_mode))),
    't.datec'=>array('label'=>$langs->trans("DateCreation"), 'checked'=>0, 'position'=>500),
    't.tms'=>array('label'=>$langs->trans("DateModificationShort"), 'checked'=>0, 'position'=>500),
    //'t.statut'=>array('label'=>$langs->trans("Status"), 'checked'=>1, 'position'=>1000),
);
// Extra fields
if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label))
{
   foreach($extrafields->attribute_label as $key => $val) 
   {
       $arrayfields["ef.".$key]=array('label'=>$extrafields->attribute_label[$key], 'checked'=>$extrafields->attribute_list[$key], 'position'=>$extrafields->attribute_pos[$key], 'enabled'=>$extrafields->attribute_perms[$key]);
   }
}




/*******************************************************************
* ACTIONS
*
* Put here all code to do according to value of "action" parameter
********************************************************************/

$parameters=array();
$reshook=$hookmanager->executeHooks('doActions',$parameters,$object,$action);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

include DOL_DOCUMENT_ROOT.'/core/actions_changeselectedfields.inc.php';

if (GETPOST("button_removefilter_x") || GETPOST("button_removefilter.x") ||GETPOST("button_removefilter")) // All test are required to be compatible with all browsers
{
	
$search_entity='';
$search_ref='';
$search_label='';
$search_description='';
$search_surface='';
$search_nbpieds='';
$search_ecartement='';
$search_fk_assolement='';
$search_fk_cepage='';
$search_fk_porte_greffe='';
$search_note_private='';
$search_fk_user_author='';
$search_fk_user_modif='';

	
	$search_date_creation='';
	$search_date_update='';
	$search_array_options=array();
}


if (empty($reshook))
{
	// Action to delete
	if ($action == 'confirm_delete')
	{
		$result=$object->delete($user);
		if ($result > 0)
		{
			// Delete OK
			setEventMessages("RecordDeleted", null, 'mesgs');
			header("Location: ".dol_buildpath('/vignoble/list.php',1));
			exit;
		}
		else
		{
			if (! empty($object->errors)) setEventMessages(null,$object->errors,'errors');
			else setEventMessages($object->error,null,'errors');
		}
	}
}




/***************************************************
* VIEW
*
* Put here all code to build page
****************************************************/

llxHeader('','MyPageName','');

$form=new Form($db);

// Put here content of your page
$title = $langs->trans('MyModuleListTitle');

// Example : Adding jquery code
print '<script type="text/javascript" language="javascript">
jQuery(document).ready(function() {
	function init_myfunc()
	{
		jQuery("#myid").removeAttr(\'disabled\');
		jQuery("#myid").attr(\'disabled\',\'disabled\');
	}
	init_myfunc();
	jQuery("#mybutton").click(function() {
		init_myfunc();
	});
});
</script>';


$sql = "SELECT";
$sql.= " t.rowid,";

		$sql .= " t.entity,";
		$sql .= " t.ref,";
		$sql .= " t.label,";
		$sql .= " t.description,";
		$sql .= " t.surface,";
		$sql .= " t.nbpieds,";
		$sql .= " t.ecartement,";
		$sql .= " t.fk_assolement,";
		$sql .= " t.fk_cepage,";
		$sql .= " t.fk_porte_greffe,";
		$sql .= " t.note_private,";
		$sql .= " t.tms,";
		$sql .= " t.datec,";
		$sql .= " t.fk_user_author,";
		$sql .= " t.fk_user_modif";


// Add fields for extrafields
foreach ($extrafields->attribute_list as $key => $val) $sql.=",ef.".$key.' as options_'.$key;
// Add fields from hooks
$parameters=array();
$reshook=$hookmanager->executeHooks('printFieldListSelect',$parameters);    // Note that $action and $object may have been modified by hook
$sql.=$hookmanager->resPrint;
$sql.= " FROM ".MAIN_DB_PREFIX."parcelle as t";
if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label)) $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."parcelle_extrafields as ef on (u.rowid = ef.fk_object)";
$sql.= " WHERE 1 = 1";
//$sql.= " WHERE u.entity IN (".getEntity('mytable',1).")";

if ($search_entity) $sql.= natural_search("entity",$search_entity);
if ($search_ref) $sql.= natural_search("ref",$search_ref);
if ($search_label) $sql.= natural_search("label",$search_label);
if ($search_description) $sql.= natural_search("description",$search_description);
if ($search_surface) $sql.= natural_search("surface",$search_surface);
if ($search_nbpieds) $sql.= natural_search("nbpieds",$search_nbpieds);
if ($search_ecartement) $sql.= natural_search("ecartement",$search_ecartement);
if ($search_fk_assolement) $sql.= natural_search("fk_assolement",$search_fk_assolement);
if ($search_fk_cepage) $sql.= natural_search("fk_cepage",$search_fk_cepage);
if ($search_fk_porte_greffe) $sql.= natural_search("fk_porte_greffe",$search_fk_porte_greffe);
if ($search_note_private) $sql.= natural_search("note_private",$search_note_private);
if ($search_fk_user_author) $sql.= natural_search("fk_user_author",$search_fk_user_author);
if ($search_fk_user_modif) $sql.= natural_search("fk_user_modif",$search_fk_user_modif);


if ($sall)          $sql.= natural_search(array_keys($fieldstosearchall), $sall);
// Add where from extra fields
foreach ($search_array_options as $key => $val)
{
    $crit=$val;
    $tmpkey=preg_replace('/search_options_/','',$key);
    $typ=$extrafields->attribute_type[$tmpkey];
    $mode=0;
    if (in_array($typ, array('int','double'))) $mode=1;    // Search on a numeric
    if ($val && ( ($crit != '' && ! in_array($typ, array('select'))) || ! empty($crit))) 
    {
        $sql .= natural_search('ef.'.$tmpkey, $crit, $mode);
    }
}
// Add where from hooks
$parameters=array();
$reshook=$hookmanager->executeHooks('printFieldListWhere',$parameters);    // Note that $action and $object may have been modified by hook
$sql.=$hookmanager->resPrint;
$sql.=$db->order($sortfield,$sortorder);
//$sql.= $db->plimit($conf->liste_limit+1, $offset);

// Count total nb of records
$nbtotalofrecords = 0;
if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST))
{
	$result = $db->query($sql);
	$nbtotalofrecords = $db->num_rows($result);
}	

$sql.= $db->plimit($conf->liste_limit+1, $offset);


dol_syslog($script_file, LOG_DEBUG);
$resql=$db->query($sql);
if ($resql)
{
    $num = $db->num_rows($resql);
    
    $params='';
	
if ($search_entity != '') $params.= '&amp;search_entity='.urlencode($search_entity);
if ($search_ref != '') $params.= '&amp;search_ref='.urlencode($search_ref);
if ($search_label != '') $params.= '&amp;search_label='.urlencode($search_label);
if ($search_description != '') $params.= '&amp;search_description='.urlencode($search_description);
if ($search_surface != '') $params.= '&amp;search_surface='.urlencode($search_surface);
if ($search_nbpieds != '') $params.= '&amp;search_nbpieds='.urlencode($search_nbpieds);
if ($search_ecartement != '') $params.= '&amp;search_ecartement='.urlencode($search_ecartement);
if ($search_fk_assolement != '') $params.= '&amp;search_fk_assolement='.urlencode($search_fk_assolement);
if ($search_fk_cepage != '') $params.= '&amp;search_fk_cepage='.urlencode($search_fk_cepage);
if ($search_fk_porte_greffe != '') $params.= '&amp;search_fk_porte_greffe='.urlencode($search_fk_porte_greffe);
if ($search_note_private != '') $params.= '&amp;search_note_private='.urlencode($search_note_private);
if ($search_fk_user_author != '') $params.= '&amp;search_fk_user_author='.urlencode($search_fk_user_author);
if ($search_fk_user_modif != '') $params.= '&amp;search_fk_user_modif='.urlencode($search_fk_user_modif);

	
    if ($optioncss != '') $param.='&optioncss='.$optioncss;
    // Add $param from extra fields
    foreach ($search_array_options as $key => $val)
    {
        $crit=$val;
        $tmpkey=preg_replace('/search_options_/','',$key);
        if ($val != '') $param.='&search_options_'.$tmpkey.'='.urlencode($val);
    } 
    
    print_barre_liste($title, $page, $_SERVER["PHP_SELF"],$params,$sortfield,$sortorder,'',$num,$nbtotalofrecords,'title_companies');
    

	print '<form method="GET" id="searchFormList" action="'.$_SERVER["PHP_SELF"].'">';
    if ($optioncss != '') print '<input type="hidden" name="optioncss" value="'.$optioncss.'">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
	print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
	print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';
	
    if ($sall)
    {
        foreach($fieldstosearchall as $key => $val) $fieldstosearchall[$key]=$langs->trans($val);
        print $langs->trans("FilterOnInto", $all) . join(', ',$fieldstosearchall);
    }
    
	if (! empty($moreforfilter))
	{
		print '<div class="liste_titre liste_titre_bydiv centpercent">';
		print $moreforfilter;
    	$parameters=array();
    	$reshook=$hookmanager->executeHooks('printFieldPreListTitle',$parameters);    // Note that $action and $object may have been modified by hook
	    print $hookmanager->resPrint;
	    print '</div>';
	}

    $varpage=empty($contextpage)?$_SERVER["PHP_SELF"]:$contextpage;
    $selectedfields=$form->multiSelectArrayWithCheckbox('selectedfields', $arrayfields, $varpage);	// This also change content of $arrayfields
	
	print '<table class="liste '.($moreforfilter?"listwithfilterbefore":"").'">';

    // Fields title
    print '<tr class="liste_titre">';
    
if (! empty($arrayfields['t.entity']['checked'])) print_liste_field_titre($arrayfields['t.entity']['label'],$_SERVER['PHP_SELF'],'t.entity','',$param,'',$sortfield,$sortorder);
if (! empty($arrayfields['t.ref']['checked'])) print_liste_field_titre($arrayfields['t.ref']['label'],$_SERVER['PHP_SELF'],'t.ref','',$param,'',$sortfield,$sortorder);
if (! empty($arrayfields['t.label']['checked'])) print_liste_field_titre($arrayfields['t.label']['label'],$_SERVER['PHP_SELF'],'t.label','',$param,'',$sortfield,$sortorder);
if (! empty($arrayfields['t.description']['checked'])) print_liste_field_titre($arrayfields['t.description']['label'],$_SERVER['PHP_SELF'],'t.description','',$param,'',$sortfield,$sortorder);
if (! empty($arrayfields['t.surface']['checked'])) print_liste_field_titre($arrayfields['t.surface']['label'],$_SERVER['PHP_SELF'],'t.surface','',$param,'',$sortfield,$sortorder);
if (! empty($arrayfields['t.nbpieds']['checked'])) print_liste_field_titre($arrayfields['t.nbpieds']['label'],$_SERVER['PHP_SELF'],'t.nbpieds','',$param,'',$sortfield,$sortorder);
if (! empty($arrayfields['t.ecartement']['checked'])) print_liste_field_titre($arrayfields['t.ecartement']['label'],$_SERVER['PHP_SELF'],'t.ecartement','',$param,'',$sortfield,$sortorder);
if (! empty($arrayfields['t.fk_assolement']['checked'])) print_liste_field_titre($arrayfields['t.fk_assolement']['label'],$_SERVER['PHP_SELF'],'t.fk_assolement','',$param,'',$sortfield,$sortorder);
if (! empty($arrayfields['t.fk_cepage']['checked'])) print_liste_field_titre($arrayfields['t.fk_cepage']['label'],$_SERVER['PHP_SELF'],'t.fk_cepage','',$param,'',$sortfield,$sortorder);
if (! empty($arrayfields['t.fk_porte_greffe']['checked'])) print_liste_field_titre($arrayfields['t.fk_porte_greffe']['label'],$_SERVER['PHP_SELF'],'t.fk_porte_greffe','',$param,'',$sortfield,$sortorder);
if (! empty($arrayfields['t.note_private']['checked'])) print_liste_field_titre($arrayfields['t.note_private']['label'],$_SERVER['PHP_SELF'],'t.note_private','',$param,'',$sortfield,$sortorder);
if (! empty($arrayfields['t.fk_user_author']['checked'])) print_liste_field_titre($arrayfields['t.fk_user_author']['label'],$_SERVER['PHP_SELF'],'t.fk_user_author','',$param,'',$sortfield,$sortorder);
if (! empty($arrayfields['t.fk_user_modif']['checked'])) print_liste_field_titre($arrayfields['t.fk_user_modif']['label'],$_SERVER['PHP_SELF'],'t.fk_user_modif','',$param,'',$sortfield,$sortorder);

    
	// Extra fields
	if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label))
	{
	   foreach($extrafields->attribute_label as $key => $val) 
	   {
           if (! empty($arrayfields["ef.".$key]['checked'])) 
           {
				$align=$extrafields->getAlignFlag($key);
				print_liste_field_titre($extralabels[$key],$_SERVER["PHP_SELF"],"ef.".$key,"",$param,($align?'align="'.$align.'"':''),$sortfield,$sortorder);
           }
	   }
	}
    // Hook fields
	$parameters=array('arrayfields'=>$arrayfields);
    $reshook=$hookmanager->executeHooks('printFieldListTitle',$parameters);    // Note that $action and $object may have been modified by hook
    print $hookmanager->resPrint;
	if (! empty($arrayfields['t.datec']['checked']))  print_liste_field_titre($langs->trans("DateCreationShort"),$_SERVER["PHP_SELF"],"t.datec","",$param,'align="center" class="nowrap"',$sortfield,$sortorder);
	if (! empty($arrayfields['t.tms']['checked']))    print_liste_field_titre($langs->trans("DateModificationShort"),$_SERVER["PHP_SELF"],"t.tms","",$param,'align="center" class="nowrap"',$sortfield,$sortorder);
	//if (! empty($arrayfields['t.status']['checked'])) print_liste_field_titre($langs->trans("Status"),$_SERVER["PHP_SELF"],"t.status","",$param,'align="center"',$sortfield,$sortorder);
	print_liste_field_titre($selectedfields, $_SERVER["PHP_SELF"],"",'','','align="right"',$sortfield,$sortorder,'maxwidthsearch ');
    print '</tr>'."\n";

    // Fields title search
	print '<tr class="liste_titre">';
	
if (! empty($arrayfields['t.entity']['checked'])) print '<td class="liste_titre"><input type="text" class="flat" name="search_entity" value="'.$search_entity.'" size="10"></td>';
if (! empty($arrayfields['t.ref']['checked'])) print '<td class="liste_titre"><input type="text" class="flat" name="search_ref" value="'.$search_ref.'" size="10"></td>';
if (! empty($arrayfields['t.label']['checked'])) print '<td class="liste_titre"><input type="text" class="flat" name="search_label" value="'.$search_label.'" size="10"></td>';
if (! empty($arrayfields['t.description']['checked'])) print '<td class="liste_titre"><input type="text" class="flat" name="search_description" value="'.$search_description.'" size="10"></td>';
if (! empty($arrayfields['t.surface']['checked'])) print '<td class="liste_titre"><input type="text" class="flat" name="search_surface" value="'.$search_surface.'" size="10"></td>';
if (! empty($arrayfields['t.nbpieds']['checked'])) print '<td class="liste_titre"><input type="text" class="flat" name="search_nbpieds" value="'.$search_nbpieds.'" size="10"></td>';
if (! empty($arrayfields['t.ecartement']['checked'])) print '<td class="liste_titre"><input type="text" class="flat" name="search_ecartement" value="'.$search_ecartement.'" size="10"></td>';
if (! empty($arrayfields['t.fk_assolement']['checked'])) print '<td class="liste_titre"><input type="text" class="flat" name="search_fk_assolement" value="'.$search_fk_assolement.'" size="10"></td>';
if (! empty($arrayfields['t.fk_cepage']['checked'])) print '<td class="liste_titre"><input type="text" class="flat" name="search_fk_cepage" value="'.$search_fk_cepage.'" size="10"></td>';
if (! empty($arrayfields['t.fk_porte_greffe']['checked'])) print '<td class="liste_titre"><input type="text" class="flat" name="search_fk_porte_greffe" value="'.$search_fk_porte_greffe.'" size="10"></td>';
if (! empty($arrayfields['t.note_private']['checked'])) print '<td class="liste_titre"><input type="text" class="flat" name="search_note_private" value="'.$search_note_private.'" size="10"></td>';
if (! empty($arrayfields['t.fk_user_author']['checked'])) print '<td class="liste_titre"><input type="text" class="flat" name="search_fk_user_author" value="'.$search_fk_user_author.'" size="10"></td>';
if (! empty($arrayfields['t.fk_user_modif']['checked'])) print '<td class="liste_titre"><input type="text" class="flat" name="search_fk_user_modif" value="'.$search_fk_user_modif.'" size="10"></td>';

	
	// Extra fields
	if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label))
	{
        foreach($extrafields->attribute_label as $key => $val) 
        {
            if (! empty($arrayfields["ef.".$key]['checked']))
            {
                $align=$extrafields->getAlignFlag($key);
                $typeofextrafield=$extrafields->attribute_type[$key];
                print '<td class="liste_titre'.($align?' '.$align:'').'">';
            	if (in_array($typeofextrafield, array('varchar', 'int', 'double', 'select')))
				{
				    $crit=$val;
    				$tmpkey=preg_replace('/search_options_/','',$key);
    				$searchclass='';
    				if (in_array($typeofextrafield, array('varchar', 'select'))) $searchclass='searchstring';
    				if (in_array($typeofextrafield, array('int', 'double'))) $searchclass='searchnum';
    				print '<input class="flat'.($searchclass?' '.$searchclass:'').'" size="4" type="text" name="search_options_'.$tmpkey.'" value="'.dol_escape_htmltag($search_array_options['search_options_'.$tmpkey]).'">';
				}
                print '</td>';
            }
        }
	}
    // Fields from hook
	$parameters=array('arrayfields'=>$arrayfields);
    $reshook=$hookmanager->executeHooks('printFieldListOption',$parameters);    // Note that $action and $object may have been modified by hook
    print $hookmanager->resPrint;
    if (! empty($arrayfields['t.datec']['checked']))
    {
        // Date creation
        print '<td class="liste_titre">';
        print '</td>';
    }
    if (! empty($arrayfields['t.tms']['checked']))
    {
        // Date modification
        print '<td class="liste_titre">';
        print '</td>';
    }
    /*if (! empty($arrayfields['u.statut']['checked']))
    {
        // Status
        print '<td class="liste_titre" align="center">';
        print $form->selectarray('search_statut', array('-1'=>'','0'=>$langs->trans('Disabled'),'1'=>$langs->trans('Enabled')),$search_statut);
        print '</td>';
    }*/
    // Action column
	print '<td class="liste_titre" align="right">';
	print '<input type="image" class="liste_titre" name="button_search" src="'.img_picto($langs->trans("Search"),'search.png','','',1).'" value="'.dol_escape_htmltag($langs->trans("Search")).'" title="'.dol_escape_htmltag($langs->trans("Search")).'">';
	print '<input type="image" class="liste_titre" name="button_removefilter" src="'.img_picto($langs->trans("Search"),'searchclear.png','','',1).'" value="'.dol_escape_htmltag($langs->trans("RemoveFilter")).'" title="'.dol_escape_htmltag($langs->trans("RemoveFilter")).'">';
	print '</td>';
	print '</tr>'."\n";
        
    
    $i = 0;
    while ($i < $num)
    {
        $obj = $db->fetch_object($resql);
        if ($obj)
        {
            // You can use here results
            print '<tr>';
            
if (! empty($arrayfields['t.entity']['checked'])) print '<td>'.$obj->entity.'</td>';
if (! empty($arrayfields['t.ref']['checked'])) print '<td>'.$obj->ref.'</td>';
if (! empty($arrayfields['t.label']['checked'])) print '<td>'.$obj->label.'</td>';
if (! empty($arrayfields['t.description']['checked'])) print '<td>'.$obj->description.'</td>';
if (! empty($arrayfields['t.surface']['checked'])) print '<td>'.$obj->surface.'</td>';
if (! empty($arrayfields['t.nbpieds']['checked'])) print '<td>'.$obj->nbpieds.'</td>';
if (! empty($arrayfields['t.ecartement']['checked'])) print '<td>'.$obj->ecartement.'</td>';
if (! empty($arrayfields['t.fk_assolement']['checked'])) print '<td>'.$obj->fk_assolement.'</td>';
if (! empty($arrayfields['t.fk_cepage']['checked'])) print '<td>'.$obj->fk_cepage.'</td>';
if (! empty($arrayfields['t.fk_porte_greffe']['checked'])) print '<td>'.$obj->fk_porte_greffe.'</td>';
if (! empty($arrayfields['t.note_private']['checked'])) print '<td>'.$obj->note_private.'</td>';
if (! empty($arrayfields['t.fk_user_author']['checked'])) print '<td>'.$obj->fk_user_author.'</td>';
if (! empty($arrayfields['t.fk_user_modif']['checked'])) print '<td>'.$obj->fk_user_modif.'</td>';

            
        	// Extra fields
    		if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label))
    		{
    		   foreach($extrafields->attribute_label as $key => $val) 
    		   {
    				if (! empty($arrayfields["ef.".$key]['checked'])) 
    				{
    					print '<td';
    					$align=$extrafields->getAlignFlag($key);
    					if ($align) print ' align="'.$align.'"';
    					print '>';
    					$tmpkey='options_'.$key;
    					print $extrafields->showOutputField($key, $obj->$tmpkey, '', 1);
    					print '</td>';
    				}
    		   }
    		}
            // Fields from hook
    	    $parameters=array('arrayfields'=>$arrayfields, 'obj'=>$obj);
    		$reshook=$hookmanager->executeHooks('printFieldListValue',$parameters);    // Note that $action and $object may have been modified by hook
            print $hookmanager->resPrint;
        	// Date creation
            if (! empty($arrayfields['t.datec']['checked']))
            {
                print '<td align="center">';
                print dol_print_date($db->jdate($obj->date_creation), 'dayhour');
                print '</td>';
            }
            // Date modification
            if (! empty($arrayfields['t.tms']['checked']))
            {
                print '<td align="center">';
                print dol_print_date($db->jdate($obj->date_update), 'dayhour');
                print '</td>';
            }
            // Status
            /*
            if (! empty($arrayfields['u.statut']['checked']))
            {
    		  $userstatic->statut=$obj->statut;
              print '<td align="center">'.$userstatic->getLibStatut(3).'</td>';
            }*/
            // Action column
            print '<td></td>';
    		print '</tr>';
        }
        $i++;
    }
    
    $db->free($resql);

	$parameters=array('sql' => $sql);
	$reshook=$hookmanager->executeHooks('printFieldListFooter',$parameters);    // Note that $action and $object may have been modified by hook
	print $hookmanager->resPrint;

	print "</table>\n";
	print "</form>\n";
	
	$db->free($result);
}
else
{
    $error++;
    dol_print_error($db);
}


// End of page
llxFooter();
$db->close();
