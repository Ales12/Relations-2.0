<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.");
}
//error_reporting ( -1 );
//ini_set ( 'display_errors', true );

$plugins->add_hook('member_profile_end', 'profile_relation');
$plugins->add_hook('usercp_start', 'usercp_relation');
$plugins->add_hook('global_intermediate', 'global_relation_alert');
if(class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
    $plugins->add_hook("global_start", "relationen_alerts");
}


function relationen_info()
{
    return array(
        "name"            => "RPG Relationen im Profil",
        "description"    => "Hier können Relationen im Profil selbst verwaltet werden.",
        "website"        => "",
        "author"        => "Alex",
        "authorsite"    => "",
        "version"        => "2.0",
        "guid"             => "",
        "codename"        => "",
        "compatibility" => "*"
    );
}

function relationen_install()
{
    global $db;
    if($db->engine=='mysql'||$db->engine=='mysqli')
    {
        $db->query("CREATE TABLE `".TABLE_PREFIX."relationen` (
          `rid` int(10) NOT NULL auto_increment,
          `username` varchar(255) NOT NULL,
           `anfrager` int(10) NOT NULL,
          `angefragte` int(10) NOT NULL,
          `kat` varchar(255) NOT NULL,
          `art` varchar(255) NOT NULL,
          `shortfacts` varchar(255) NOT NULL,
          `npc_wanted` varchar(500) NOT NULL,
          `description_wanted` text NOT NULL,
          `ok` int(11) NOT NULL default '0',
          PRIMARY KEY (`rid`)
        ) ENGINE=MyISAM".$db->build_create_table_collation());

    }


    $setting_group = array(
        'name' => 'relation',
        'title' => 'Einstellungen der Relationen',
        'description' => 'Einstellung für die Relationen',
        'disporder' => 1, // The order your setting group will display
        'isdefault' => 0
    );

    $gid = $db->insert_query("settinggroups", $setting_group);

    $setting_array = array(
        // A text setting
        'relation_category' => array(
            'title' => 'Kategorien',
            'description' => 'Welche Kategorien soll es geben?',
            'optionscode' => 'text',
            'value' => 'Familie, Freunde, Bekannte, Feinde, Vergangenheit', // Default
            'disporder' => 1
        ),
    );


    foreach($setting_array as $name => $setting)
    {
        $setting['name'] = $name;
        $setting['gid'] = $gid;

        $db->insert_query('settings', $setting);
    }

// Don't forget this!
    rebuild_settings();

    //Templates
    $insert_array = array(
        'title' => 'relationen',
        'template' => $db->escape_string('<table width="100%"><tr><td class=\'tcat\'><strong>{$lang->relation_profile}</strong></td></tr>
    <tr><td class="trow1"><details>
        <summary>{$lang->relation_form}</summary>
{$relationen_formular}
        </details>
</td></tr>
<tr><td><div class="profil_flex">
    {$relationen_bit_profil}
    </div>    </td></tr>
</table>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'relationen_alert_other',
        'template' => $db->escape_string('<div class="pm_alert">
  Du hast aktuell  <strong>{$count} {$anfrage}</strong> bei <a id="switch_{$alert2[\'uid\']}" href="#switch" class="switchlink">{$user}</a> offen.
</div>
<br />'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'relationen_alert',
        'template' => $db->escape_string('<div class="pm_alert">
  Du hast aktuell  <strong>{$count} {$anfrage}</strong> offen. <b><a href="usercp.php?action=relationen">Hier</a></b> kannst du sie bearbeiten.
</div>
<br />'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'relationen_anfragen',
        'template' => $db->escape_string('<html>
<head>
<title>{$mybb->settings[\'bbname\']} - {$lang->relation_page}</title>
{$headerinclude}
</head>
<body>
{$header}
<table width="100%" border="0" align="center">  <tr>{$usercpnav}
  

<td valign="top">
    <table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
        <tr><td class="thead"><h1>{$lang->relation_openrelas}}</h1></td>
        </tr>    <tr><td align="center">
        <table width="90%" style="margin: auto;">
            <tr><td class="thead" colspan="4"><strong>{$lang->relation_yourrequest}</strong></td></tr>
    <tr class="tcat">
    <td width="10%"><strong>{$lang->relation_requester}</strong></td>
    <td width="20%"><strong>{$lang->relation_entry}</strong></td>
    <td width="50%"><strong>{$lang->relation_desc}</strong></td>
    <td width="10%"><strong>{$lang->relation_options}</strong></td>
    </tr>
    {$anfragen_bit}
                <tr><td class="thead" colspan="4"><strong>{$lang->relation_ownrequest}</strong></td></tr>
    <tr class="tcat">
    <td width="10%"><strong>{$lang->relation_yourrequsts}</strong></td>
    <td width="20%"><strong>{$lang->relation_entry}</strong></td>
    <td width="50%"><strong>{$lang->relation_desc}</strong></td>
    <td width="10%"><strong>{$lang->relation_options}</strong></td>
    </tr>
 {$deine_anfragen}
        </table><br />
        </td>
        </tr>
        <tr><td class="thead"><h1>{$lang->relation_relas}</h1></td></tr>
            <tr><td><table width="90%" style="margin: auto;">    
        
        <tr><td class="thead" colspan="4"><strong>{$lang->relation_relas_other}</strong></td></tr>
        <tr class="tcat">
        <td width="10%"><strong>{$lang->relation_relas_reg}</strong></td>
    <td width="20%"><strong>{$lang->relation_entry}</strong></td>
    <td width="50%"><strong>{$lang->relation_desc}</strong></td>
    <td width="10%"><strong>{$lang->relation_options}</strong></td>
        </tr>
    {$all_relas}
                <tr><td class="thead" colspan="4"><strong>Deine Relationen</strong></td></tr>
        <tr class="tcat">
        <td width="10%"><strong>{$lang->relation_relas_own}</strong></td>
    <td width="20%"><strong>{$lang->relation_entry}</strong></td>
    <td width="50%"><strong>{$lang->relation_desc}</strong></td>
    <td width="10%"><strong>{$lang->relation_options}</strong></td>
        </tr>
    {$all_own_relas}
        </table>
        </td></tr>
    </table>
    </td>
</tr>
</table>
{$footer}
</body>
</html>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'relationen_anfragen_bit',
        'template' => $db->escape_string('<tr>
    <td class=\'trow1\' align="center">&raquo; {$user}</td>
        <td class=\'trow1\'>&raquo; {$lang->relation_relations}: <b>{$row[\'art\']}</b><br />
    &raquo; Kategorie: <b>{$row[\'kat\']}</b></td>
    <td class=\'trow1\'>{$row[\'description_wanted\']}</td>
    <td class=\'trow1\'>{$optionen}<div class="modal" id="double_{$row[\'rid\']}" style="display: none;">{$rela_back}</div></td>
    </tr>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'relationen_anfragen_back',
        'template' => $db->escape_string('<form method="post" action=""><input type=\'hidden\' value=\'{$row[\'rid\']}\' name=\'getrid\'><input type=\'hidden\' value=\'{$row[\'angefragte\']}\' name=\'anfrager\'><input type=\'hidden\' value=\'{$row[\'anfrager\']}\' name=\'angefragte\'>
<table border="0" cellspacing="5" cellpadding="{$theme[\'tablespace\']}" class="tborder" style="width: 400px; margin:auto;">
    <tr><td class=\'trow1\' align=\'center\' colspan=\'2\'><h3>{$lang->relation_rela_back} <b>{$row[\'username\']}</b></h3></td></tr>
    <tr>    <td class=\'trow1\'><strong>{$lang->relation_relations}</strong></td>    <td class=\'trow1\'><select name="kat">
  {$rela_select_edit}
            </select></td></tr>
        <tr><td class=\'trow1\'><strong>{$lang->relation_kind}</strong></td><td class=\'trow1\'><input type="text" name="art" id="art" value="{$row[\'art\']}" class="textbox" /></td></tr>
        <tr>    <td class=\'trow1\' ><strong>{$lang->relation_desc}</strong></td><td class=\'trow1\'><textarea class="textarea" name="description_wanted" id="description_wanted" rows="5" cols="15" style="width: 80%">{$row[\'description_wanted\']}</textarea></td>    </tr>
        <tr>
<td align="center" colspan=\'2\'><input type="submit" name="double" value="ebenfalls Eintragen" id="submit" class="button"></td></tr></form></table>
      </form>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'relationen_bit_profil',
        'template' => $db->escape_string('<div class="relas"><div class="relakat">{$cat}</div>
    <div class="relas_innerbox">
<table width=\'100%\'>
    {$characters}
        </table></div>
</div>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'relationen_bit_profil_edit',
        'template' => $db->escape_string('<form method="post" action=""><input type=\'hidden\' value=\'{$row[\'rid\']}\' name=\'getrid\'><input type=\'hidden\' value=\'{$row[\'anfrager\']}\' name=\'anfrager\'> <input type=\'hidden\' value=\'{$row[\'angefragte\']}\' name=\'angefragte\'>
<table border="0" cellspacing="5" cellpadding="{$theme[\'tablespace\']}" class="tborder" style="width: 50%; margin:auto;">
    <tr><td class=\'trow1\' align=\'center\' colspan=\'2\'><strong>{$lang->relation_profile_edit} <b>{$row[\'username\']}</b></strong></td></tr>
    <tr>    <td class=\'trow1\'><strong>{$lang->relation_profile_relakind}</strong></td>    <td class=\'trow1\'><select name="kat">
        <option value="{$rela_type}" selected>{$rela_type}</option>
  {$rela_select_edit}
            </select></td></tr>
        <tr><td class=\'trow1\'><strong>{$lang->relation_profile_rela}</strong></td><td class=\'trow1\'><input type="text" name="art" id="art" value="{$row[\'art\']}" class="textbox" /></td></tr>
        <tr>    <td class=\'trow1\' ><strong>{$lang->relation_profile_desc}</strong></td><td class=\'trow1\'><textarea class="textarea" name="description_wanted" id="description_wanted" rows="5" cols="15" style="width: 80%">{$row[\'description_wanted\']}</textarea></td>    </tr>
        <tr>
<td align="center" colspan=\'2\'><input type="submit" name="rela_edit" value="editieren" id="submit" class="button"></td></tr></form></table>
      </form>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);


    $insert_array = array(
        'title' => 'relationen_bit_profil_edit_npc',
        'template' => $db->escape_string('<form method="post" action=""  enctype="multipart/form-data"><input type=\'hidden\' value=\'{$row[\'rid\']}\' name=\'getrid\'><input type=\'hidden\' value=\'{$row[\'anfrager\']}\' name=\'anfrager\'> 
<table border="0" cellspacing="5" cellpadding="{$theme[\'tablespace\']}" class="tborder" style="width: 50%; margin:auto;">
    <tr><td class=\'trow1\' align=\'center\' colspan=\'2\'><strong>{$lang->relation_profile_edit} <b>{$npc_name[\'username\']}</b></strong></td></tr>
    <tr>        <td class=\'trow1\'><strong>{$lang->relation_profile_npcname}</strong></td>
      <td class=\'trow1\'>
        <input type="text" name="chara_name" id="chara_name" value="{$npc_name[\'username\']}" class="textbox" /></td></tr>
<tr>        <td class=\'trow1\'><strong>{$lang->relation_profile_npczero}</strong></td>
      <td class=\'trow1\'>
<input type=\'text\' value=\'{$row[\'angefragte\']}\' name=\'angefragte\' class="textbox"></td></tr>
    <tr><td class=\'trow1\'><strong>{$lang->relation_profile_relakind}</strong></td>
            <td class=\'trow1\'><select name="kat">
  {$rela_select_edit}
            </select></td></tr>
        <tr><td class=\'trow1\'><strong>{$lang->relation_profile_rela}</strong></td><td class=\'trow1\'><input type="text" name="art" id="art" value="{$row[\'art\']}" class="textbox" /></td></tr>
              <tr><td class=\'trow1\'><strong>{$lang->relation_profile_shortfacts}</strong></td><td class=\'trow1\'><input type="text" name="shortfacts" id="shortfacts" value="{$row[\'shortfacts\']}" class="textbox" /></td></tr>
              <tr><td class=\'trow1\'><strong>{$lang->relation_profile_wantedlink}</strong></td><td class=\'trow1\'><input type="text" name="npc_wanted" id="npc_wanted" value="{$row[\'npc_wanted\']}" class="textbox" /></td></tr>
    <tr>    <td class=\'trow1\' ><strong>{$lang->relation_profile_desc}</strong></td><td class=\'trow1\'><textarea class="textarea" name="description_wanted" id="description_wanted" rows="5" cols="15" style="width: 80%">{$row[\'description_wanted\']}</textarea></td>    </tr>
      <tr>
<td align="center" colspan=\'2\'><input type="submit" name="npc_edit" value="editieren" id="submit" class="button"></td></tr></form></table>
      </form>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);
    $insert_array = array(
        'title' => 'relationen_formular',
        'template' => $db->escape_string('<table border="0" cellspacing="5" cellpadding="{$theme[\'tablespace\']}" class="tborder" style="width: 50%; margin:auto;">
    <form id="relationen" method="post" action="member.php?action=profile&uid={$memprofile[\'uid\']}">
    <tr><td class=\'trow1\'><strong>{$lang->relation_profile_relakind}</strong></td><td class=\'trow1\'><strong>{$lang->relation_profile_rela}</strong></td>
    </tr>
        <tr><td class=\'trow1\'><select name="kat">
{$rela_select}
    </select></td><td class=\'trow1\'><input type="text" name="art" id="art" value="" class="textbox" /></td></tr>
        <tr>    <td class=\'trow1\' colspan="2"><strong>{$lang->relation_profile_desc}</strong></td></tr>
        <tr><td class=\'trow1\' align="center" colspan="2"><textarea class="textarea" name="description_wanted" id="description_wanted" rows="5" cols="15" style="width: 80%">Beschreibe hier kurz die Beziehung zwischen {$memprofile[\'username\']} und dir.</textarea></td>    </tr>
<tr><td colspan="2" align="center"><input type="submit" name="add" value="eintragen" id="submit" class="button"></td></tr></form></table>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'relationen_formular_npc',
        'template' => $db->escape_string('<table border="0" cellspacing="5" cellpadding="{$theme[\'tablespace\']}" class="tborder" style="width: 80%; margin:auto;">
    <form id="relationen" method="post" action="member.php?action=profile&uid={$memprofile[\'uid\']}"  enctype="multipart/form-data">
        <tr><td class=\'trow1\'><strong>{$lang->relation_profile_npcname}</strong></td><td class=\'trow1\'><strong>{$lang->relation_profile_relakind}</strong></td>
        </tr>
        <tr>
                        <td class=\'trow1\'><input type="text" name="chara_name" id="chara_name" placeholder="Vorname Nachname" class="textbox"  style="width: 200px;" /></td>
    <td class=\'trow1\'><select name="kat">
{$rela_select}
    </select></td></tr>
        <tr>
    <td class=\'trow1\'><strong>{$lang->relation_profile_rela}</strong></td><td class=\'trow1\'><strong>{$lang->relation_profile_shortfacts}</strong></td>
            </tr>
    <tr>    <td class=\'trow1\'><input type="text" name="art" id="art" placeholder="Mutter, Vater, beste Freunde, Feinde etc." class="textbox" style="width: 200px;"  /></td>
    <td class=\'trow1\'><input type="text" name="age" id="age" placeholder="xx Jahre" class="textbox" style="width: 100px;" />
		<input type="text" name="work" id="work" placeholder="Beruf/Schule" class="textbox" style="width: 100px;" />
		<input type="text" name="relation" id="relation" placeholder="Beziehungsstatus (Single, Verlobt, in einer Beziehung etc.)" class="textbox" style="width: 100px;" /></td>    </tr>
                <tr>    <td class=\'trow1\' ><strong>{$lang->relation_profile_wantedlink}</strong></td><td class=\'trow1\'><strong>Ein Gesuch vorhanden?</strong></td></tr>
        <td class=\'trow1\' align="center"><textarea class="textarea" name="description_wanted" id="description_wanted" rows="5" cols="15" style="width: 80%">Beschreibe hier kurz die Beziehung zwischen {$memprofile[\'username\']} und den NPC.</textarea></td>    <td class=\'trow1\' align="center"><input type="text" name="npc_wanted" id="npc_wanted" placeholder="https://" class="textbox" style="width: 80%;" /></td>        </tr>
<tr>
<td align="center" colspan="2" class="trow2"><input type="submit" name="npc_add" value="eintragen" id="submit" class="button"></td></tr></form></table>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'relationen_profil_bit',
        'template' => $db->escape_string('<tr><td class="tcat" colspan="2"><div class=\'relaname\'>{$user} &raquo; {$row[\'art\']}</div></td></tr>
<tr><td class="trow" colspan="2" align="center"><div class="rela_facts">{$shortfacts}</div></td></tr>
<tr class=\'relas_td\'><td width=\'15%\' align=\'center\'>{$rel_avatar}</td>
<td align=\'center\' width=\'75%\'>
    <div class="smalltext" style="height: 65px; overflow: auto; padding: 0 3px; text-align: justify;">{$rela_desc}</div>
    <div class="rela_facts">{$npc_wanted} {$delete} {$edit}<div class="modal" id="edit_{$row[\'rid\']}" style="display: none;">{$edit_rela}</div></div>    </td>
</tr>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    //CSS einfügen
    $css = array(
        'name' => 'relationen.css',
        'tid' => 1,
        'attachedto' => '',
        "stylesheet" =>    '.profil_flex{
display: flex;    
flex-wrap: wrap;
}
.relaname {
    font-size: 13px;
    text-transform: uppercase;
    color: #fff;
    text-align: center;
}

.relaname a{
    text-transform: uppercase;
    text-align: center;
}
.relas{
width: 48%;
height: 330px;
margin: 5px;
}

.relas_td img{
width: 100px;
    height: auto;
}

.relas_innerbox{
height: 300px; 
    overflow: auto    
}
.relation{
height: 200px; 
overflow: auto;     
}

.relakat{
    background: #0066a2 url(images/thead.png) top left repeat-x;
    color: #ffffff;
    border-bottom: 1px solid #263c30;
    padding: 8px;
    font-weight: bold;
}

.rela_facts{

color: #333;
font-size: 11px;
text-decoration: none;
display: inline;
text-transform: uppercase;
font-weight: 600;
}  ',
        'cachefile' => $db->escape_string(str_replace('/', '', 'relationen.css')),
        'lastmodified' => time()
    );

    require_once MYBB_ADMIN_DIR . "inc/functions_themes.php";

    $sid = $db->insert_query("themestylesheets", $css);
    $db->update_query("themestylesheets", array("cachefile" => "css.php?stylesheet=" . $sid), "sid = '" . $sid . "'", 1);

    $tids = $db->simple_select("themes", "tid");
    while ($theme = $db->fetch_array($tids)) {
        update_theme_stylesheet_list($theme['tid']);
    }
// Don't forget this!
    rebuild_settings();
}

function relationen_is_installed()
{
    global $db;
    if($db->table_exists("relationen"))
    {
        return true;
    }
    return false;
}

function relationen_uninstall()
{
    global $db;
    if($db->table_exists("relationen"))
    {
        $db->drop_table("relationen");
    }

    $db->delete_query('settings', "name IN ('relation_category')");
    $db->delete_query('settinggroups', "name = 'relation'");
// Don't forget this
    $db->delete_query("templates", "title LIKE '%relationen%'");
    require_once MYBB_ADMIN_DIR."inc/functions_themes.php";
    $db->delete_query("themestylesheets", "name = 'relationen.css'");
    $query = $db->simple_select("themes", "tid");
    while($theme = $db->fetch_array($query)) {
        update_theme_stylesheet_list($theme['tid']);
    }


    rebuild_settings();
}

function relationen_activate()
{
    global $db, $cache;
    //Alertseinstellungen
    if(class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
        $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

        if (!$alertTypeManager) {
            $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
        }

        $alertType = new MybbStuff_MyAlerts_Entity_AlertType();
        $alertType->setCode('relation_accepted'); // The codename for your alert type. Can be any unique string.
        $alertType->setEnabled(true);
        $alertType->setCanBeUserDisabled(true);

        $alertTypeManager->add($alertType);

        $alertType = new MybbStuff_MyAlerts_Entity_AlertType();
        $alertType->setCode('relation_refused'); // The codename for your alert type. Can be any unique string.
        $alertType->setEnabled(true);
        $alertType->setCanBeUserDisabled(true);

        $alertTypeManager->add($alertType);

        $alertType = new MybbStuff_MyAlerts_Entity_AlertType();
        $alertType->setCode('relation_change'); // The codename for your alert type. Can be any unique string.
        $alertType->setEnabled(true);
        $alertType->setCanBeUserDisabled(true);

        $alertTypeManager->add($alertType);
    }

    //Variabeln im Template einfügen
    require MYBB_ROOT."/inc/adminfunctions_templates.php";
    find_replace_templatesets("header", "#".preg_quote('{$pm_notice}')."#i", '{$relationen_alert} {$pm_notice}');
    find_replace_templatesets("member_profile", "#".preg_quote('{$signature}')."#i", '{$signature} {$relationen}');



}

function relationen_deactivate()
{
    global $db, $cache;

    //Alertseinstellungen
    if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
        $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

        if (!$alertTypeManager) {
            $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
        }

        $alertTypeManager->deleteByCode('relation_accepted');
        $alertTypeManager->deleteByCode('relation_refused');
        $alertTypeManager->deleteByCode('relation_change');
    }

    require MYBB_ROOT."/inc/adminfunctions_templates.php";
    find_replace_templatesets("header", "#".preg_quote('{$relationen_alert}')."#i", '', 0);
    find_replace_templatesets("member_profile", "#".preg_quote('{$relationen}')."#i", '', 0);
}

function profile_relation(){
    global $db, $lang, $mybb, $memprofile, $templates, $relationen, $edit, $relationen_formular,  $relationen_profil_bit, $avatar, $theme, $rela_select, $rela_type, $rela_select_edit, $rela_desc, $desc_popup ;

    require_once MYBB_ROOT."inc/datahandlers/pm.php";
    $pmhandler = new PMDataHandler();
    require_once MYBB_ROOT."inc/class_parser.php";
    $parser = new postParser;
    $lang->load('relationen');

    $options = array(
        "allow_html" => 1,
        "allow_mycode" => 1,
        "allow_smilies" => 1,
        "allow_imgcode" => 1,
        "filter_badwords" => 0,
        "nl2br" => 1,
        "allow_videocode" => 0
    );

    //Anfrager
    $anfrager = $mybb->user['uid'];

    //Bei dem Angefragt wird
    $angefragte = $memprofile['uid'];

    $username = $mybb->memprofile['username'];

    $rela_cat = $mybb->settings['relation_category'];

    $rela_cat = explode(", ", $rela_cat);
    foreach ($rela_cat as $cat){
        $rela_select .= "<option value='{$cat}'>{$cat}</option>";
    }


    if($mybb->user['uid'] != '0'){
        if($memprofile['uid'] != $mybb->user['uid']){
            eval("\$relationen_formular = \"" . $templates->get ("relationen_formular") . "\";");
        } else {
            eval("\$relationen_formular = \"" . $templates->get ("relationen_formular_npc") . "\";");
        }
    }


//Eintragen von existierenden User
    if($mybb->user['uid'] != '0'){
        if(isset($_POST['add'])) {
            $anfrager = $anfrager;
            $angefragte = $angefragte;
            $username= $username;
            $kat = $_POST['kat'];
            $art = $_POST['art'];
            $desc = $_POST['description_wanted'];
            $wanted = "";
            $shortfacts = "";

            $new_record = array(
                "username" => $db->escape_string($username),
                "anfrager" => $db->escape_string($anfrager),
                "angefragte" => $db->escape_string($angefragte),
                "kat" => $db->escape_string($kat),
                "art" => $db->escape_string($art),
                "npc_wanted" => $db->escape_string($wanted),
                "description_wanted" => $db->escape_string($desc),
                "shortfacts" => $db->escape_string($shortfacts)
            );

            $db->insert_query("relationen", $new_record);
            redirect("member.php?action=profile&uid={$memprofile['uid']}");



        }

//NPC eintragen

        if (isset($_POST['npc_add'])) {
            $angefragte = 0;
            $anfrager = $mybb->user['uid'];
            $username= $_POST['chara_name'];
            $kat = $_POST['kat'];
            $art = $_POST['art'];
            $shortfacts = $_POST['age']." # ".$_POST['work']." # ".$_POST['relation'];
            $npc_wanted = $_POST['npc_wanted'];
            $desc = $_POST['description_wanted'];
            $ok = 1;

            $new_record = array(
                "username" => $db->escape_string($username),
                "anfrager" => $db->escape_string($anfrager),
                "angefragte" => $db->escape_string($angefragte),
                "kat" => $db->escape_string($kat),
                "art" => $db->escape_string($art),
                "shortfacts" => $db->escape_string($shortfacts),
                "npc_wanted" => $db->escape_string($npc_wanted),
                "description_wanted" => $db->escape_string($desc),
                "ok" => $db->escape_string($ok)
            );

            $db->insert_query("relationen", $new_record);
            redirect("member.php?action=profile&uid={$memprofile['uid']}");


        }



    }

//Im Profil ausgeben
    $uid = $memprofile['uid'];

    $rela_cat = $mybb->settings['relation_category'];

    $rela_cat = explode(", ", $rela_cat);
//Im Profil ausgeben
    $uid = $memprofile['uid'];

    foreach ($rela_cat as $cat){
        $characters = "";

        $select = $db->query("   Select *
     FROM " . TABLE_PREFIX . "relationen r
    LEFT JOIN " . TABLE_PREFIX . "users u
    ON r.angefragte = u.uid
    LEFT JOIN " . TABLE_PREFIX . "userfields uf
    ON u.uid = uf.ufid
    WHERE r.anfrager = '" . $uid . "'
    AND r.ok = '1'
    and r.kat = '".$cat."'
    ORDER BY art ASC, r.username ASC
  "  );
        while ($row = $db->fetch_array($select)) {
            $npc_wanted = "";
            $rela_select_edit = "";
            $rela_type = "";
            $delete = "";
            $rela_type = $row['kat'];
            $rela_desc = "";
            $edit = "";

            if(!empty($row['description_wanted'])) {
                $rela_desc = $parser->parse_message($row['description_wanted'], $options);
            }else{
                $rela_desc = "Keine Beziehungsbeschreibung angegeben.";
            }




            if ($row['angefragte'] == 0) {
                $rid = $row['rid'];
                $npc_query = $db->query("Select username
            FROM ".TABLE_PREFIX."relationen 
            WHERE anfrager = '" . $uid . "'
            AND rid = '".$rid."'
            ");

                $npc_name = $db->fetch_array($npc_query);
                $user = $npc_name['username'];
                $shortfacts = $row['shortfacts'];
                if(!empty($row['npc_wanted'])){
                    $npc_wanted = "<a href='{$row['npc_wanted']}' target='_blank' title='Wird gesucht!'><i class=\"fas fa-search\"></i></a>&nbsp;&nbsp;";
                } else{
                    $npc_wanted = "";
                }


                $rel_avatar = "<img src='{$theme['imgdir']}/noavatar.png'>";

                if ($mybb->user['uid'] == $memprofile['uid'] || $mybb->usergroup['canmodcp'] == 1 || $mybb->usergroup['canacp'] == 1) {
                    $delete = "&nbsp;<a href='member.php?action=profile&del=$row[rid]' title='Relation löschen'><i class=\"fa fa-times\" aria-hidden=\"true\"></i></a>&nbsp;";



                    foreach ($rela_cat as $edit_cat){
                        if($edit_cat == $rela_type){
                            $rela_select_edit .= "<option selected>{$edit_cat}</option>";
                        } elseif($rela_type!= $edit_cat){
                            $rela_select_edit .= "<option>{$edit_cat}</option>";
                        }

                    }

                    $edit = "<a onclick=\"$('#edit_{$row['rid']}').modal({ fadeDuration: 250, keepelement: true, zIndex: (typeof modal_zindex !== 'undefined' ? modal_zindex : 9999) }); return false;\" style=\"cursor: pointer;\"><i class=\"fa fa-edit\" aria-hidden=\"true\"></i></a> <br />";
                    eval("\$edit_rela = \"" . $templates->get("relationen_bit_profil_edit_npc") . "\";");
                }
            } else{
                $username = format_name($row['username'], $row['usergroup'], $row['displaygroup']);
                $user = build_profile_link($username, $row['uid']);

                $chara = intval($row['uid']);
    if (!empty($row['birthday'])) {

//Geburtstage
                $all_months = $mybb->settings['inplaykalender_months'];
                $year = $mybb->settings['inplaykalender_year'];
                //wir wollen nur den letzten Monat

                $month = strrpos($all_months, ',') + 1;
                $month = substr($all_months, $month); //$last_word = PHP.

                $monatsnamen = array(
                    1 => "Januar",
                    2 => "Februar",
                    3 => "Maerz",
                    4 => "April",
                    5 => "Mai",
                    6 => "Juni",
                    7 => "Juli",
                    8 => "August",
                    9 => "September",
                    10 => "Oktober",
                    11 => "November",
                    12 => "Dezember"
                );
                //wir wollen den Namen zu einer Zahl umwandeln
                $month_int = array_search($month, $monatsnamen);

                //Jetzt wollen wir eine Variable im Datumsformat
                $ingame = new DateTime("01-" . $month_int . "-" . $year);

                //Geburtstag des Users bekommen
                $gebu = $db->query("SELECT birthday FROM ".TABLE_PREFIX."users WHERE uid =$chara");
                while ($data = $db->fetch_array($gebu)) {
                    //datumsformat:
                    $geburtstag = new DateTime($data['birthday']);
                }


                $interval = $ingame->diff($geburtstag);
                $age = $interval->format("%Y Jahre");
    }else{
	    $ag = "";
    }
                //Shortfacts kannst du hier eingeben. Hierzu kannst du jegliche Profilfelder in der form $row['fidxx'] einfügen.
                $shortfacts = $age." Jahre # ".$row['job']." # ".$row['fidxx'];

                if($mybb->user['uid'] != 0) {
                    if (!empty($row['avatar'])) {
                        $rel_avatar = "<img src='{$row['avatar']}'>";
                    } else {
                        $rel_avatar = "<img src='{$theme['imgdir']}/noavatar.png'>";
                    }
                }else{
                    $rel_avatar = "<img src='{$theme['imgdir']}/noavatar.png'>";
                }
                if(!empty($row['description_wanted'])) {
                    eval("\$desc_popup = \"" . $templates->get("relationen_bit_profil_desc") . "\";");
                } else{
                    $desc_popup = "";
                }
                if ($mybb->user['uid'] == $memprofile['uid']) {
                    $delete = "<a href='member.php?action=profile&del=$row[rid]' title='Relation löschen'><i class=\"fa fa-times\" aria-hidden=\"true\"></i></a>&nbsp;&nbsp;";


                    foreach ($rela_cat as $edit_cat){

                        if($edit_cat == $rela_type){
                            $rela_select_edit .= "<option selected>{$edit_cat}</option>";
                        } elseif($edit_cat != $rela_type){
                            $rela_select_edit .= "<option>{$edit_cat}</option>";
                        }

                    }


                    $edit = "<a onclick=\"$('#edit_{$row['rid']}').modal({ fadeDuration: 250, keepelement: true, zIndex: (typeof modal_zindex !== 'undefined' ? modal_zindex : 9999) }); return false;\" style=\"cursor: pointer;\"><i class=\"fa fa-edit\" aria-hidden=\"true\"></i></a> <br />";

                    eval("\$edit_rela = \"" . $templates->get("relationen_bit_profil_edit") . "\";");

                }
            }

//Gäste dürfen kein Avatar sehen
            if ($mybb->user['uid'] == '0') {
                $rel_avatar = "<img src='{$theme['imgdir']}/noavatar.png'>";
            }


            eval("\$characters .= \"" . $templates->get("relationen_profil_bit") . "\";");
        }

        eval("\$relationen_bit_profil .= \"" . $templates->get("relationen_bit_profil") . "\";");
    }

    eval("\$relationen = \"" . $templates->get ("relationen") . "\";");

    $del = $mybb->input['del'];
    if($del){
        $db->delete_query("relationen", "rid = '$del'");
        redirect("member.php?action=profile&uid={$memprofile['uid']}");
    }

    if(isset($mybb->input['rela_edit'])) {
        $getrid = $mybb->input['getrid'];
        $anfrager = $mybb->input['anfrager'];
        $angefragte = $mybb->input['angefragte'];
        $desc = $mybb->input['description_wanted'];
        $kat = $mybb->input['kat'];
        $art = $mybb->input['art'];
        $shortfacts = $mybb->input['shortfacts'];
        $npc_wanted = "";



        $edit_record = array(
            "anfrager" => $db->escape_string($anfrager),
            "angefragte" => $db->escape_string($angefragte),
            "kat" => $db->escape_string($kat),
            "art" => $db->escape_string($art),
            "description_wanted" => $db->escape_string($desc),
            "shortfacts" => $db->escape_string($shortfacts),
            "npc_wanted" => $db->escape_string($npc_wanted)
        );


        $db->update_query("relationen", $edit_record, "rid='{$getrid}'");

        if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
    
                $alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('relation_change');
                if ($alertType != NULL && $alertType->getEnabled()) {
                    $alert = new MybbStuff_MyAlerts_Entity_Alert((int)$angefragte, $alertType, $kat, $art);
                    $alert->setExtraDetails([
                        'art' => $art,
                        'kat' => $kat
                    ]);
                    MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
                }
        }
        
        redirect("member.php?action=profile&uid={$memprofile['uid']}");

    }
    if(isset($mybb->input['npc_edit'])){
        // NPC bearbeiten
        $getrid = $mybb->input['getrid'];
        $anfrager = $mybb->input['anfrager'];
        $angefragte = $mybb->input['angefragte'];
        $username= $mybb->input['chara_name'];
        $kat = $mybb->input['kat'];
        $art = $mybb->input['art'];
        $desc = $mybb->input['description_wanted'];
        $npc_wanted = $mybb->input['npc_wanted'];
        $shortfacts = $mybb->input['shortfacts'];


        $edit_record = array(
            "anfrager" => $db->escape_string($anfrager),
            "angefragte" => $db->escape_string($angefragte),
            "username" => $db->escape_string($username),
            "kat" => $db->escape_string($kat),
            "art" => $db->escape_string($art),
            "description_wanted" =>$db->escape_string($desc),
            "shortfacts" => $db->escape_string($shortfacts),
            "npc_wanted" => $db->escape_string($npc_wanted)
        );

        $db->update_query("relationen", $edit_record, "rid='{$getrid}'");
        redirect("member.php?action=profile&uid={$memprofile['uid']}");
    }


}


function global_relation_alert(){
    global $db, $mybb, $templates,  $anfrage, $relationen_alert, $lang;

    $lang->load('relationen');

    //welcher user ist online
    $this_user = intval($mybb->user['uid']);

//für den fall nicht mit hauptaccount online
    $as_uid = intval($mybb->user['as_uid']);

// suche alle angehangenen accounts
    if ($as_uid == 0) {
        $select = $db->query("SELECT * FROM " . TABLE_PREFIX . "users 
        WHERE (as_uid = $this_user) OR (uid = $this_user) ORDER BY username ASC");
    } else if ($as_uid != 0) {
//id des users holen wo alle angehangen sind
        $select = $db->query("SELECT * FROM " . TABLE_PREFIX . "users WHERE (as_uid = $as_uid) OR (uid = $this_user) OR (uid = $as_uid) ORDER BY username ASC");
    }

    while($alert = $db->fetch_array($select)) {
        $select_alert = $db->query("SELECT *
    FROM " . TABLE_PREFIX . "relationen r
    LEFT JOIN ".TABLE_PREFIX."users u
    on (r.angefragte = u.uid)
    WHERE ok = '0'
    and  angefragte != '" . $mybb->user['uid'] . "'
    and angefragte = '".$alert['uid']."'
     ");

        $alert2 = $db->fetch_array($select_alert);
        $count = mysqli_num_rows($select_alert);

        $user = format_name($alert2['username'], $alert2['usergroup'], $alert2['displaygroup']);


        if ($mybb->user['uid'] != 0) {
            if ($count == '1') {
                $anfrage = "Relationsanfrage";
            } else {
                $anfrage = "Relationsanfragen";
            }
            if ($count != 0) {
                eval("\$relationen_alert = \"" . $templates->get("relationen_alert_other") . "\";");
            }
        }

    }

    $select = $db->query("SELECT *
    FROM ".TABLE_PREFIX."relationen
    WHERE ok = '0'
    AND angefragte = '".$mybb->user['uid']."'
     ");

    $row = $db->fetch_array($select);
    $count = mysqli_num_rows ($select);
    if($mybb->user['uid'] != 0) {
        if ($count == '1') {
            $anfrage = "Relationsanfrage";
        } else {
            $anfrage = "Relationsanfragen";
        }
        if ($count != 0) {
            eval("\$relationen_alert = \"" . $templates->get("relationen_alert") . "\";");
        }
    }
}

function usercp_relation(){

    global $mybb, $lang, $templates, $lang, $header, $headerinclude, $footer, $page, $usercpnav, $db, $optionen,  $anfragen_bit, $deine_anfragen, $rela_type, $rela_select_edit;
    $lang->load('relationen');
    if($mybb->get_input('action') == 'relationen')
    {

//Erstmal die Anzeige generieren
        add_breadcrumb('Relationsanfragen', "usercp.php?action=relationen");

        //usernameID ziehen
        $uid = $mybb->user['uid'];

        //PMhandler starten
        require_once MYBB_ROOT."inc/datahandlers/pm.php";
        $pmhandler = new PMDataHandler();


//ab geht es mit der Abfrage. Hier ist der Part für die erhaltenen Anfragen
        $select = $db->query("SELECT *
        FROM ".TABLE_PREFIX."relationen r
        LEFT JOIN ".TABLE_PREFIX."users u
        ON r.anfrager = u.uid
        WHERE r.ok = '0'
        AND r.angefragte = '".$uid."'
        ORDER BY u.username
        ");

        $rowcount = mysqli_num_rows($select);

        if($rowcount == '0'){
            $anfragen_bit = "<tr><td colspan='3'><div class='smalltext' align='center'>Aktuell keine Anfragen empfangen</div></td></tr>";
        } else {


            while ($row = $db->fetch_array($select)) {
                $username = format_name($row['username'], $row['usergroup'], $row['displaygroup']);
                $user = build_profile_link($username, $row['uid']);
                $optionen = "<div> <a href='usercp.php?action=relationen&ok=$row[rid]'><i class=\"fa fa-check\" aria-hidden=\"true\"></i></a></div>
                   <div></div><a href='usercp.php?action=relationen&del=$row[rid]'><i class=\"fa fa-times\" aria-hidden=\"true\"></i></a></";


                eval("\$anfragen_bit .= \"" . $templates->get("relationen_anfragen_bit") . "\";");
            }
        }
        //und hier noch die eigenen Anfragen (auch hier löschen möglich)
        $select = $db->query("SELECT *
        FROM ".TABLE_PREFIX."relationen r
            LEFT JOIN ".TABLE_PREFIX."users u
            ON r.angefragte = u.uid 
            WHERE r.anfrager = '".$uid."'
            AND r.ok = '0'
        ");

        $rowcount = mysqli_num_rows($select);

        if($rowcount == '0'){
            $deine_anfragen = "<tr><td colspan='3'><div class='smalltext' align='center'>Aktuell keine Anfragen offen</div></td></tr>";
        } else {

            while ($row = $db->fetch_array($select)) {
                $username = format_name($row['username'], $row['usergroup'], $row['displaygroup']);
                $user = build_profile_link($username, $row['uid']);
                $optionen = "<div><a href='usercp.php?action=relationen&del=$row[rid]' title='Eintrag löschen'><i class=\"fas fa-undo\"></i></a></div>";



                eval("\$deine_anfragen .= \"" . $templates->get("relationen_anfragen_bit") . "\";");
            }

        }



        //Alle Relationen bei denen man eingetragen ist.

        $all_relas_query = $db->query("SELECT *
        FROM ".TABLE_PREFIX."relationen r
        lEFT JOIN ".TABLE_PREFIX."users u
        on (r.anfrager = u.uid)
      WHERE r.angefragte = '".$uid."'
            AND r.ok = '1'
            ORDER BY u.username
        ");

        while($row = $db->fetch_array($all_relas_query)){
            $username = format_name($row['username'], $row['usergroup'], $row['displaygroup']);
            $user = build_profile_link($username, $row['uid']);
            $optionen = "<div><a onclick=\"$('#double_{$row['rid']}').modal({ fadeDuration: 250, keepelement: true, zIndex: (typeof modal_zindex !== 'undefined' ? modal_zindex : 9999) }); return false;\" style=\"cursor: pointer;\"><i class=\"fas fa-undo\" title='Ebenfalls eintragen'></i></a> </div>
<div><a href='usercp.php?action=relationen&olddel=$row[rid]' title='Eintrag löschen'><i class=\"fa fa-times\" aria-hidden=\"true\"></i></a></div>";


            $rela_cat = $mybb->settings['relation_category'];

            $rela_cat = explode(", ", $rela_cat);
            foreach ($rela_cat as $edit_cat){

                if($edit_cat == $rela_type){
                    $rela_select_edit .= "<option selected>{$edit_cat}</option>";
                } elseif($edit_cat != $rela_type){
                    $rela_select_edit .= "<option>{$edit_cat}</option>";
                }

            }
            eval("\$rela_back = \"" . $templates->get("relationen_anfragen_back") . "\";");
            eval("\$all_relas .= \"" . $templates->get("relationen_anfragen_bit") . "\";");
        }

        //Alle eigenen Relationen bei denen man eingetragen ist.

        $all_relas_query = $db->query("SELECT *
        FROM ".TABLE_PREFIX."relationen r
        lEFT JOIN ".TABLE_PREFIX."users u
        on (r.angefragte = u.uid)
      WHERE r.anfrager = '".$uid."'
            AND r.ok = '1'
            AND r.angefragte != '0'
            ORDER BY r.username asc
        ");

        while($row = $db->fetch_array($all_relas_query)){

            if($row['angefragte'] == '0'){
                $user = $row['username'];
            } else{
                $username = format_name($row['username'], $row['usergroup'], $row['displaygroup']);
                $user = build_profile_link($username, $row['uid']);
            }

            $optionen = "<div><a href='usercp.php?action=relationen&olddel=$row[rid]'><i class=\"fa fa-times\" aria-hidden=\"true\" title='Eintrag löschen'></i></a></div>";


            eval("\$all_own_relas .= \"" . $templates->get("relationen_anfragen_bit") . "\";");
        }


        //alte Relas löschen
        $del = $mybb->input['olddel'];
        if($del){

            $db->delete_query("relationen", "rid = '$del'");
            redirect("usercp.php?action=relationen");
        }

        //Anfragen können natürlich gelöscht werden (wenn man sich vertan hat zum Beispiel).
        $del = $mybb->input['del'];
        if($del){
            $select = $db->query("SELECT * 
            FROM ".TABLE_PREFIX."relationen r
            LEFT JOIN ".TABLE_PREFIX."users u
            ON r.anfrager = u.uid 
            WHERE r.rid = '".$del."'
            ");

            $row = $db->fetch_array($select);
            $anfrager = $row['anfrager'];
            $angefragte = $row['angefragte'];

            $db->query("UPDATE ".TABLE_PREFIX."relationen SET ok = '1'  WHERE rid = '$ok'");
            if(class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
                $alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('relation_refused');
                if ($alertType != NULL && $alertType->getEnabled()) {
                    $alert = new MybbStuff_MyAlerts_Entity_Alert((int)$anfrager, $alertType);
                    MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
                }
            }

            $db->delete_query("relationen", "rid = '$del'");
            redirect("usercp.php?action=relationen");
        }

        //und natürlich auch den Spaß annehmen
        $ok = $mybb->input['ok'];
        if($ok){
            $select = $db->query("SELECT * 
            FROM ".TABLE_PREFIX."relationen r
            LEFT JOIN ".TABLE_PREFIX."users u
            ON r.anfrager = u.uid 
            WHERE r.rid = '".$ok."'
            ");

            $row = $db->fetch_array($select);
            $anfrager = $row['anfrager'];
            $angefragte = $row['angefragte'];


            $db->query("UPDATE ".TABLE_PREFIX."relationen SET ok = '1'  WHERE rid = '$ok'");
            if(class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
                $alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('relation_accepted');
                if ($alertType != NULL && $alertType->getEnabled()) {
                    $alert = new MybbStuff_MyAlerts_Entity_Alert((int)$anfrager, $alertType);
                    MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
                }
            }

            redirect("usercp.php?action=relationen");
        }
        //beim anderen ebenso eintragen
        $double = $mybb->input['double'];

        if($double){
            $getrid = $mybb->input['getrid'];
            $username = $mybb->user['username'];
            $anfrager =$mybb->input['anfrager'];
            $angefragte = $mybb->input['angefragte'];
            $desc = $mybb->input['description_wanted'];
            $kat = $mybb->input['kat'];
            $art = $mybb->input['art'];
            $shortfacts = "";

            $new_record = array(
                "username" => $db->escape_string($username),
                "anfrager" => $db->escape_string($anfrager),
                "angefragte" => $db->escape_string($angefragte),
                "kat" => $db->escape_string($kat),
                "art" => $db->escape_string($art),
                "description_wanted" => $db->escape_string($desc),
                "shortfacts" => $db->escape_string($shortfacts)
            );

            $db->insert_query("relationen", $new_record);
            redirect("usercp.php?action=relationen");

        }

        eval("\$page = \"".$templates->get("relationen_anfragen")."\";");
        output_page($page);
    }
}
/**
 * Was passiert wenn ein User gelöscht wird
 * Relas bei anderen zu npc umtragen
 * die relas des users löschen
 */
$plugins->add_hook("admin_user_users_delete_commit_end", "relationen_user_delete");
function relationen_user_delete()
{
    global $db, $cache, $mybb, $user, $profile_fields;
    $username = $db->escape_string($user['username']);
    $todelete = (int)$user['uid'];
    $shortfacts_query = $db->simple_select("userfields", "*", "ufid='".(int)$user['uid']."'");
    $profile_fields = $db->fetch_array($shortfacts_query);
    $shortfacts = $db->escape_string($profile_fields['fid40']);

    $update_other_relas = array(
        'angefragte' => 0,
        'shortfacts' => $shortfacts,
        'username' => $username
    );
    //   $db->update_query("{name_of_table}", $update_array, "WHERE {options}");
    $db->update_query('relationen', $update_other_relas, "angefragte='" . (int)$user['uid'] . "'");
    $db->delete_query('relationen', "anfrager = " . (int)$user['uid'] . "");
}

function relationen_alerts()
{
    global $mybb, $lang;
    $lang->load('relationen');

    /**
     * Alert formatter for my custom alert type.
     */
    class MybbStuff_MyAlerts_Formatter_AcceptedRelationFormatter extends MybbStuff_MyAlerts_Formatter_AbstractFormatter
    {
        /**
         * Format an alert into it's output string to be used in both the main alerts listing page and the popup.
         *
         * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to format.
         *
         * @return string The formatted alert string.
         */
        public function formatAlert(MybbStuff_MyAlerts_Entity_Alert $alert, array $outputAlert)
        {
            return $this->lang->sprintf(
                $this->lang->relation_accepted,
                $outputAlert['from_user'],
                $outputAlert['dateline']
            );
        }

        /**
         * Init function called before running formatAlert(). Used to load language files and initialize other required
         * resources.
         *
         * @return void
         */
        public function init()
        {

        }

        /**
         * Build a link to an alert's content so that the system can redirect to it.
         *
         * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to build the link for.
         *
         * @return string The built alert, preferably an absolute link.
         */
        public function buildShowLink(MybbStuff_MyAlerts_Entity_Alert $alert)
        {

        }
    }

    if (class_exists('MybbStuff_MyAlerts_AlertFormatterManager')) {
        $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();

        if (!$formatterManager) {
            $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::createInstance($mybb, $lang);
        }

        $formatterManager->registerFormatter(
            new MybbStuff_MyAlerts_Formatter_AcceptedRelationFormatter($mybb, $lang, 'relation_accepted')
        );
    }

    /**
     * Alert formatter for my custom alert type.
     */
    class MybbStuff_MyAlerts_Formatter_RefusedRelationFormatter extends MybbStuff_MyAlerts_Formatter_AbstractFormatter
    {
        /**
         * Format an alert into it's output string to be used in both the main alerts listing page and the popup.
         *
         * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to format.
         *
         * @return string The formatted alert string.
         */
        public function formatAlert(MybbStuff_MyAlerts_Entity_Alert $alert, array $outputAlert)
        {
            return $this->lang->sprintf(
                $this->lang->relation_refused,
                $outputAlert['from_user'],
                $outputAlert['dateline']
            );
        }

        /**
         * Init function called before running formatAlert(). Used to load language files and initialize other required
         * resources.
         *
         * @return void
         */
        public function init()
        {

        }

        /**
         * Build a link to an alert's content so that the system can redirect to it.
         *
         * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to build the link for.
         *
         * @return string The built alert, preferably an absolute link.
         */
        public function buildShowLink(MybbStuff_MyAlerts_Entity_Alert $alert)
        {

        }
    }

    if (class_exists('MybbStuff_MyAlerts_AlertFormatterManager')) {
        $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();

        if (!$formatterManager) {
            $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::createInstance($mybb, $lang);
        }

        $formatterManager->registerFormatter(
            new MybbStuff_MyAlerts_Formatter_RefusedRelationFormatter($mybb, $lang, 'relation_refused')
        );
    }

    /**
     * Alert formatter for my custom alert type.
     */
    class MybbStuff_MyAlerts_Formatter_EditRelationFormatter extends MybbStuff_MyAlerts_Formatter_AbstractFormatter
    {
        /**
         * Format an alert into it's output string to be used in both the main alerts listing page and the popup.
         *
         * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to format.
         *
         * @return string The formatted alert string.
         */
        public function formatAlert(MybbStuff_MyAlerts_Entity_Alert $alert, array $outputAlert)
        {
            $alertContent = $alert->getExtraDetails();
            return $this->lang->sprintf(
                $this->lang->relation_change,
                $outputAlert['from_user'],
                $alertContent['art'],
                $alertContent['kat'],
                $outputAlert['dateline']
            );
        }

        /**
         * Init function called before running formatAlert(). Used to load language files and initialize other required
         * resources.
         *
         * @return void
         */
        public function init()
        {

        }

        /**
         * Build a link to an alert's content so that the system can redirect to it.
         *
         * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to build the link for.
         *
         * @return string The built alert, preferably an absolute link.
         */
        public function buildShowLink(MybbStuff_MyAlerts_Entity_Alert $alert)
        {

        }
    }

    if (class_exists('MybbStuff_MyAlerts_AlertFormatterManager')) {
        $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();

        if (!$formatterManager) {
            $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::createInstance($mybb, $lang);
        }

        $formatterManager->registerFormatter(
            new MybbStuff_MyAlerts_Formatter_EditRelationFormatter($mybb, $lang, 'relation_change')
        );
    }
}
