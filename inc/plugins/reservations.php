<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 22.10.2017
 * Time: 10:10
 */


// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.");
}


//hooks
$plugins->add_hook('misc_start', 'reservations');
$plugins->add_hook('global_intermediate', 'reservations_header');
$plugins->add_hook('global_intermediate', 'reservations_alert_global');

// Alerts
if(class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
    $plugins->add_hook("global_start", "reservations_alerts");
}


function reservations_info()
{
    return array(
        "name"			=> "Userreservierungen",
        "description"	=> "Dieser Plugin erlaubt das automatische Reservieren.",
        "website"		=> "",
        "author"		=> "Ales",
        "authorsite"	=> "",
        "version"		=> "2.0",
        "guid" 			=> "",
        "codename"		=> "",
        "compatibility" => "*"
    );
}

function reservations_install()
{
    global $db, $mybb;

    if($db->engine=='mysql'||$db->engine=='mysqli')
    {
        $db->query("CREATE TABLE `".TABLE_PREFIX."reservations` (
          `reservation_id` int(10) NOT NULL auto_increment,   
          `reservation` varchar(255) NOT NULL,
          `cat` varchar(255) NOT NULL,
          `gender` varchar(255) NOT NULL,
          `wanted` varchar(255) NOT NULL,
          `uid` int(10) NOT NULL,
          `username` varchar(255) NOT NULL,
          `reserved` int(11) NOT NULL,
          `days` int(11) NOT NULL,
          `count` int(11) NOT NULL default '0',
          PRIMARY KEY (`reservation_id`)
        ) ENGINE=MyISAM".$db->build_create_table_collation());
    }

    $setting_group = array(
        'name' => 'reservation',
        'title' => 'Reservierung',
        'description' => 'Hier kannst du alles zu deinen Plugin einstellen.',
        'disporder' => 5, // The order your setting group will display
        'isdefault' => 0
    );
    $gid = $db->insert_query ("settinggroups", $setting_group);

    $setting_array = array(
        'name' => 'reservation_userpf',
        'title' => 'FID für Usernamen',
        'description' => 'Gib hier die Profilfeld ID an, worin der Username gespeichert wird. fid dort stehen lassen!',
        'optionscode' => 'text',
        'value' => 'fid2',
        'disporder' => 1,
        "gid" => (int)$gid
    );
    $db->insert_query ('settings', $setting_array);

    $setting_array = array(
        'name' => 'reservation_userres',
        'title' => 'Tagesanzahl für Userreservierungen',
        'description' => 'Wie lange dürfen User reservieren?',
        'optionscode' => 'numeric',
        'value' => '30',
        'disporder' => 2,
        "gid" => (int)$gid
    );
    $db->insert_query ('settings', $setting_array);

    $setting_array = array(
        'name' => 'reservation_userextend',
        'title' => 'Anzahl der Verlängerungen für User',
        'description' => 'Wie oft dürfen User ihre Reservierungen verlängern?',
        'optionscode' => 'numeric',
        'value' => '2',
        'disporder' => 3,
        "gid" => (int)$gid
    );
    $db->insert_query ('settings', $setting_array);

    $setting_array = array(
        'name' => 'reservation_userextend_days',
        'title' => 'Tagesanzahl Verlängerung',
        'description' => 'Wie lange dürfen User ihre Reservierung verlängern?',
        'optionscode' => 'numeric',
        'value' => '14',
        'disporder' => 4,
        "gid" => (int)$gid
    );
    $db->insert_query ('settings', $setting_array);

    $setting_array = array(
        'name' => 'reservation_count_user',
        'title' => 'Anzahl möglicher Reservierungen für Avatar',
        'description' => 'Wie viele Avatarpersonen dürfen User reservieren?',
        'optionscode' => 'numeric',
        'value' => '4',
        'disporder' => 5,
        "gid" => (int)$gid
    );
    $db->insert_query ('settings', $setting_array);

    $setting_array = array(
        'name' => 'reservation_wantedextend_days',
        'title' => 'Tagesanzahl Verlängerung Gesuche',
        'description' => 'Wie lang dürfen User ihre Reservierung verlängern?',
        'optionscode' => 'numeric',
        'value' => '14',
        'disporder' => 6,
        "gid" => (int)$gid
    );
    $db->insert_query ('settings', $setting_array);
    $setting_array = array(
        'name' => 'reservation_count_wanted',
        'title' => 'Anzahl möglicher Gesuchsreservierungen',
        'description' => 'Wie viele Gesuchsreservierungen dürfen User reservieren?',
        'optionscode' => 'numeric',
        'value' => '4',
        'disporder' => 7,
        "gid" => (int)$gid
    );
    $db->insert_query ('settings', $setting_array);

    $setting_array = array(
        'name' => 'reservation_guest_allow',
        'title' => 'Gästereservierung',
        'description' => 'Dürfen Gäste reservieren? (Standard auf Ja)',
        'optionscode' => 'yesno',
        'value' => '1',
        'disporder' => 8,
        "gid" => (int)$gid
    );
    $db->insert_query ('settings', $setting_array);


    $setting_array = array(
        'name' => 'reservation_guest',
        'title' => 'Tagesanzahl für Gästereservierungen',
        'description' => 'Wie lange dürfen Gäste reservieren?',
        'optionscode' => 'numeric',
        'value' => '7',
        'disporder' => 10,
        "gid" => (int)$gid
    );
    $db->insert_query ('settings', $setting_array);

    $setting_array = array(
        'name' => 'reservation_avatar_exist_control',
        'title' => 'Kontrolle vergebene Avatarpersonen',
        'description' => 'Soll kontrolliert werden, ob die Avatarperson schon vergeben ist?',
        'optionscode' => 'yesno',
        'value' => '0',
        'disporder' => 11,
        "gid" => (int)$gid
    );
    $db->insert_query ('settings', $setting_array);

    $setting_array = array(
        'name' => 'reservation_avatar_exist',
        'title' => 'Existierende Avatar',
        'description' => 'Gib hier die Profilfeld ID ein, worin der User sein Avatar angibt.',
        'optionscode' => 'text',
        'value' => 'fid1',
        'disporder' => 12,
        "gid" => (int)$gid
    );
    $db->insert_query ('settings', $setting_array);

    rebuild_settings();

    $setting_array = array(
        'name' => 'reservation_rule',
        'title' => 'Regeltext',
        'description' => 'Hier kannst du einen Regeltext eintragen',
        'optionscode' => 'textarea',
        'value' => 'Hier kommt ein Regeltext rein.',
        'disporder' => 12,
        "gid" => (int)$gid
    );
    $db->insert_query ('settings', $setting_array);

    rebuild_settings();

    // Templates

    $insert_array = array(
        'title' => 'reservations',
        'template' => $db->escape_string('<html>
<head>
<title>{$mybb->settings[\'bbname\']} - {$lang->reservation}</title>
{$headerinclude}
</head>
<body>
{$header}

<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead"><strong>{$lang->reservation}</strong></td>
</tr>
<tr>
<td class="trow1" align="center">
	{$reservation_rules}
	{$missing_wantedlink}  {$no_reserv}
	{$reservation_form}
	{$reserv_count}
	<br />
	<table style="margin:auto; width:100%;">
	<tr><td class="thead" colspan="3"><div class="headline2">{$lang->reservation_avatarperson}</div></td></tr>
	<tr>
		<td width="33%" class="tcat"><div class="headline3">{$lang->reservation_female}</div></td>
		<td class="tcat" width="33%"><div class="headline3">{$lang->reservation_male}</div></td>
		<td class="tcat" width="33%"><div class="headline3">{$lang->reservation_divers}</div></td>
	</tr>
<tr>
	<td class="smalltext"  valign="top">{$reservation_female}</td>
	<td class="smalltext"   valign="top">{$reservation_male}</td>
		<td class="smalltext"   valign="top">{$reservation_diverse}</td>
	</tr>
		</table>
		<table style="margin:auto; width:100%;">
			<tr>
		<td width="50%" class="thead"><div class="headline2">{$lang->reservation_wanted}</div></td>
		<td class="thead" width="50%"><div class="headline2">{$lang->reservation_name}</div></td>
	</tr>
<tr>
	<td class="smalltext"  valign="top">{$reservation_searched}</td>
	<td class="smalltext"   valign="top">{$reservation_name}</td>
	</tr>
			</table>
</td>
</tr>
</table>
{$footer}
</body>
</html>
		'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'reservations_alert',
        'template' => $db->escape_string('
        <div class="red_alert">
	{$reserv_reservationtime}
</div>
		'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'reservations_entry',
        'template' => $db->escape_string('<div class="reservation"><b>{$reservations[\'reservation\']}</b> {$wanted_link} {$lang->reservation_between} {$username}</div>
<div class="reservation_info">{$reserv_infos} {$extend} {$reservation_options}</div>
		'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'reservations_entry_edit',
        'template' => $db->escape_string('<form id="reserve_edit" method="post" action="misc.php?action=reservations">
	<input type="hidden" name="reservation_id" value="{$reservation_id}">

<table border="0" cellspacing="5" cellpadding="{$theme[\'tablespace\']}" class="tborder" style="width: 50%; margin:auto;">
		<tr><td class="trow1" width="50%"><strong>{$lang->reservation_username}</strong></td><td class="trow1" width="50%"><input type="text" name="username" id="username" value="{$reservations[\'username\']}" class="textbox" /></td></tr>
			<tr><td class="trow1" width="50%"><strong>{$lang->reservation_userid}</strong></td><td class="trow1" width="50%"><input type="number" name="uid" id="uid" value="{$reservations[\'uid\']}" class="textbox" /></td></tr>
		<tr><td class="trow1"><strong>{$lang->reservation_cat}</strong></td><td class="trow1"><select name=\'cat\'>{$reservation_cat}</select></td></tr>
	<tr><td class="trow1"><strong>{$lang->reservation_reservation}</strong></td><td class="trow1"><input type="text" name="reservation" id="reservation" value="{$reservations[\'reservation\']}" class="textbox" /></td></tr>
	<tr><td class="trow1"><strong>{$lang->reservation_gender}</strong></td><td class="trow1">
			<select name="gender">
		{$reservation_gender_select}
		</select></td></tr>
{$reservation_wanted}
		<tr class="trow2">
<td colspan="2" align="center"><input type="submit" name="reserve_edit" value="{$lang->reservation_reserve_edit}" id="submit" class="button"></td>
		</tr>
		</table>
</form>
		'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'reservations_formular',
        'template' => $db->escape_string('<form id="reserve" method="post" action="misc.php?action=reservations">
<table border="0" cellspacing="5" cellpadding="{$theme[\'tablespace\']}" class="tborder" style="width: 50%; margin:auto;">
		<tr><td class="trow1" width="50%"><strong>{$lang->reservation_username}</strong></td><td class="trow1" width="50%">{$username}</td></tr>
		<tr><td class="trow1"><strong>{$lang->reservation_cat}</strong></td><td class="trow1">{$reservation_cat}</td></tr>
	<tr><td class="trow1"><strong>{$lang->reservation_reservation}</strong></td><td class="trow1"><input type="text" name="reservation" id="reservation" placeholder="Reservierung" class="textbox" /></td></tr>
	<tr><td class="trow1"><strong>{$lang->reservation_gender}</strong></td><td class="trow1">
			<select name="gender">
	<option>männlich</option>
<option >weiblich</option>
				<option>divers</option>
		</select></td></tr>
{$reservation_wanted}
		{$nh_spamprotect}
		<tr class="trow2">
<td colspan="2" align="center"><input type="submit" name="reserve" value="{$lang->reservation_reserve}" id="submit" class="button"></td>
		</tr>
		</table>
</form>
		'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'reservations_options',
        'template' => $db->escape_string('{$reservation_extend} <a href="misc.php?action=reservations&delete_reservation={$reservation_id}">{$lang->reservation_delete}</a> <a onclick="$(\'#edit_{$reservation_id}\').modal({ fadeDuration: 250, keepelement: true, zIndex: (typeof modal_zindex !== \'undefined\' ? modal_zindex : 9999) }); return false;" style="cursor: pointer;">{$lang->reservation_edit}</a> <div class="modal" id="edit_{$reservation_id}" style="display: none;">{$reservation_edit}</div>
		'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);
}

function reservations_is_installed()
{
    global $db;
    if($db->table_exists("reservations"))
    {
        return true;
    }
    return false;
}

function reservations_uninstall()
{

    global $db;
    //Settings Löschen
    $db->query("DELETE FROM ".TABLE_PREFIX."settinggroups WHERE name='reservation'");
    $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='reservation_userpf'");
    $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='reservation_userres'");
    $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='reservation_userextend'");
    $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='reservation_userextend_days'");
    $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='reservation_count_user'");
    $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='reservation_wantedextend_days'");
    $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='reservation_count_wanted'");
    $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='reservation_guest_allow'");
    $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='reservation_guest'");
    $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='reservation_avatar_exist_control'");
    $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='reservation_avatar_exist'");
    $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='reservation_rule'");
    //Tabelle aus Datenbank löschen
    if($db->table_exists("reservations"))
    {
        $db->drop_table("reservations");
    }
    $db->delete_query("templates", "title LIKE '%reservations%'");
    rebuild_settings();
}

function reservations_activate()
{
    global $db, $cache;
    if(class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
        $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

        if (!$alertTypeManager) {
            $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
        }

        $alertType = new MybbStuff_MyAlerts_Entity_AlertType();
        $alertType->setCode('myalerts_reservation_delete'); // The codename for your alert type. Can be any unique string.
        $alertType->setEnabled(true);
        $alertType->setCanBeUserDisabled(true);

        $alertTypeManager->add($alertType);

    }

    require MYBB_ROOT."/inc/adminfunctions_templates.php";
    find_replace_templatesets("header", "#".preg_quote('<navigation>').'#i', '{$reservation_global_alerts} <navigation>');

}

function reservations_deactivate()
{
    global $db, $cache;

    if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
        $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

        if (!$alertTypeManager) {
            $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
        }

        $alertTypeManager->deleteByCode('myalerts_reservation_delete');
    }
    require MYBB_ROOT."/inc/adminfunctions_templates.php";
    find_replace_templatesets("header", "#".preg_quote('{$reservation_global_alerts}')."#i", '', 0);

}



// In the body of your plugin
function reservations()
{
    global $mybb, $templates, $lang, $header, $headerinclude, $footer, $page, $db, $parser, $username,$reservation_cat, $reservation_wanted, $extend, $reservation_options, $count_avatar, $no_reserv, $reserv_count, $count_wanted, $reservation_gender_select, $reservation_rules, $missing_wantedlink, $days_remain, $reserv_infos,$reservation_options,$reservation_edit ;

    $lang->load('reservation');
    ///der Parser halt
    require_once MYBB_ROOT."inc/class_parser.php";;
    $parser = new postParser;
    // Do something, for example I'll create a page using the hello_world_template
    $text_options = array(
        "allow_html" => 1,
        "allow_mycode" => 1,
        "allow_smilies" => 1,
        "allow_imgcode" => 1,
        "filter_badwords" => 0,
        "nl2br" => 1,
        "allow_videocode" => 0
    );

    if($mybb->get_input('action') == 'reservations') {
        // Add a breadcrumb
        add_breadcrumb('Reservierungen', "misc.php?action=reservations");

        //Einstellungen ziehen
        $userpf = $mybb->settings['reservation_userpf'];
        $userres = intval($mybb->settings['reservation_userres']);
        $userextend = intval($mybb->settings['reservation_userextend']);
        $extend_days = intval($mybb->settings['reservation_userextend_days']);
        $wantedextend_days = intval($mybb->settings['reservation_wantedextend_days']);
        $max_avatar = intval($mybb->settings['reservation_count_user']);
        $max_wanted = intval($mybb->settings['reservation_count_wanted']);
        $guest_allow = intval($mybb->settings['reservation_guest_allow']);
        $guest_res = intval($mybb->settings['reservation_guest']);
        $avatar_exist_control = $mybb->settings['reservation_avatar_exist_control'];
        $avatar_exist = $mybb->settings['reservation_avatar_exist'];
        $reservation_rules = $mybb->settings['reservation_rule'];

        // weitere Zählungen
        $reservation_name_count = 2;

        // array erstellen

        $reservation_categories = array(
            $lang->reservation_avatar => $lang->reservation_avatar,
            $lang->reservation_wanted  => $lang->reservation_wanted,
            $lang->reservation_name  => $lang->reservation_name,
        );

        $reservation_genders = array(
            $lang->reservation_female  => $lang->reservation_female,
            $lang->reservation_male  => $lang->reservation_male,
            $lang->reservation_divers  => $lang->reservation_divers,
        );


        // geben wir mal die Reservierungen aus
        // zunächst die Avatar


        $count_avatar = 0;
        $count_wanted = 0;
        $reservation_avatar_query = $db->query("SELECT *
        from " . TABLE_PREFIX . "reservations
        order by reservation ASC
        ");

        while ($reservations = $db->fetch_array($reservation_avatar_query)) {
            $username = "";
            $reserv_length = "";
            $reserved = "";
            $days = "";
            $reservation_id = "";
            $editallowd = false;
            $reservation_options  = "";
            $wanted = "";

// Informationen holen
            $user = get_user($reservations['uid']);
            //welcher user ist online
            $this_user = intval($mybb->user['uid']);

//für den fall nicht mit hauptaccount online
            $as_uid = intval($mybb->user['as_uid']);

// suche alle angehangenen accounts
            if ($as_uid == 0) {
                $chara_select = $db->query("SELECT uid FROM " . TABLE_PREFIX . "users WHERE (as_uid = $this_user) OR (uid = $this_user)");
            } else if ($as_uid != 0) {
//id des users holen wo alle angehangen sind
                $chara_select = $db->query("SELECT uid FROM " . TABLE_PREFIX . "users WHERE (as_uid = $as_uid) OR (uid = $this_user) OR (uid = $as_uid) ");
            }

            while ($all_charas = $db->fetch_array($chara_select)) {


                if ($user['uid'] == $all_charas['uid']) {
                    //erlaube das editieren
                    $editallowd = true;

                    // zähle alle Avatarreservierungen
                    if ($reservations['cat'] == $lang->reservation_avatar) {
                        $count_avatar++;
                    }

                    // zähle alle Gesuchsreservierungen
                    if ($reservations['cat'] == $lang->reservation_wanted) {
                        $count_wanted++;
                    }
                }
            }

            $reservation_id = $reservations['reservation_id'];


            if ($mybb->user['uid'] == 0) {
                $options = "";
                $res_count = 0;
                $wanted_count = 0;
            }
            // Reservierung übergeben

            $faktor = 86400;
            $days = $reservations['days'] * $faktor;
            $complete_days = $reservations['reserved'] + $days;
            $reserved_date = date('d.m.Y', $reservations['reserved']);
            $end_date = date('d.m.Y', $complete_days);
            $left_days = round(($complete_days - TIME_NOW) / $faktor);

            if ($days > 0) {
                $days_remain = "{$left_days} Tage verbleiben";
            } else {
                $days_remain = "abgelaufen";
            }

            $reserv_infos = "reserviert bis {$end_date} &raquo; {$days_remain}";

            if ($reservations['uid'] != 0) {

                $playername = $db->fetch_field($db->simple_select("userfields", $userpf, "ufid = {$reservations['uid']}"), $userpf);
                if(!empty($playername)) {
                    $username = build_profile_link($playername, $reservations['uid']);
                } else{
                    $playername = $db->fetch_field($db->simple_select("users", "username", "uid = {$reservations['uid']}"), "username");
                    $username = build_profile_link($playername, $reservations['uid']);
                }
            } else {
                $username = $reservations['username'];
            }

            // optionen
            // Bearbeiten
            $reserv_days = $reservations['days'];
            $count_extend = $reservations['count'];
            foreach ($reservation_categories as $reservation_category){
                $select = "";
                if($reservation_category == $reservations['cat']){
                    $select = "selected";
                }

                $reservation_cat .= "<option value='{$reservation_category}' $select>{$reservation_category}</option>";
            }

            foreach ($reservation_genders as $reservation_genders){
                $select = "";
                if($reservation_category == $reservations['cat']){
                    $select = "selected";
                }

                $reservation_gender_select .= "<option value='{$reservation_genders}' $select>{$reservation_genders}</option>";
            }

            eval("\$reservation_edit = \"".$templates->get("reservations_entry_edit")."\";");
            if ($mybb->usergroup['canmodcp'] == 1) {
                if ($reservations['count'] < $userextend) {
                    $reservation_extend = "<a href=\"misc.php?action=reservations&extend_reservation={$reservation_id}&days={$reserv_days}&extend={$count_extend}\">{$lang->reservation_extend}</a>";
                } else {
                    $reservation_extend = "";
                }
                eval("\$reservation_options .= \"" . $templates->get("reservations_options") . "\";");
            } elseif ($editallowd == true) {
                if ($reservations['count'] < $userextend) {
                    $reservation_extend = "<a href=\"misc.php?action=reservations&extend_reservation={$reservation_id}&days={$reserv_days}}&extend={$count_extend}\">{$lang->reservation_extend}</a>";
                } else {
                    $reservation_extend = "";
                }
                eval("\$reservation_options = \"" . $templates->get("reservations_options") . "\";");
            } else{
                $reservation_options = "";
            }

            $wanted_link = "";

            if($count_extend > 0){
                $extend = $count_extend.". Verl";
            }

            // Reservierungen ausgeben
            // Avatar ausgeben
            if($reservations['cat'] == $lang->reservation_avatar) {
                if ($reservations['gender'] == 'weiblich') {
                    eval("\$reservation_female .= \"" . $templates->get("reservations_entry") . "\";");
                } elseif ($reservations['gender'] == 'männlich') {
                    eval("\$reservation_male .= \"" . $templates->get("reservations_entry") . "\";");
                } elseif ($reservations['gender'] == 'divers') {
                    eval("\$reservation_divers .= \"" . $templates->get("reservations_entry") . "\";");
                }
            }

            // Gesuche ausgeben
            if($reservations['cat'] == $lang->reservation_wanted){
                eval("\$reservation_searched .= \"" . $templates->get("reservations_entry") . "\";");
            }

            // Namen ausgeben
            if($reservations['cat'] == $lang->reservation_name){
                eval("\$reservation_name .= \"" . $templates->get("reservations_entry") . "\";");
            }

        }

        if($mybb->user['uid'] != 0) {
            $reserv_count = "Aktuell hast du {$count_avatar} Avatarpersonen und {$count_wanted} Gesuche reserviert!";
        }

        /*
         * Hier speichern wir mal alles
         */
        // Username ziehen bzw. Gäste ein Feld anzeigen
            if($mybb->user['uid'] != 0){
                $id = $mybb->user['uid'];
               $username = $db->fetch_field($db->simple_select("userfields",$userpf,"ufid = {$id}"), $userpf);
               $reservation_cat = "<select name='cat'>
		<option>{$lang->reservation_avatar}</option>
		<option>{$lang->reservation_wanted}</option>
		<option>{$lang->reservation_name}</option>
	        </select>";
               $reservation_wanted = "<tr><td class='trow1'><strong>{$lang->reservation_wantedlink}</strong></td><td class='trow1'><input type='text' name='wanted' id='wanted' placeholder='https://' class='textbox' /></td></tr>";
            } else{
                $username = "<input type='text' name='username' id='username' class='textbox' placeholder='Dein Name'>";
                $reservation_cat = "<select name='cat'>
		<option>{$lang->reservation_avatar}</option>
		<option>{$lang->reservation_name}</option>
	        </select>";
                $reservation_wanted = "";
            }
        if($mybb->user['uid'] == 0) {
            eval("\$nh_spamprotect = \"".$templates->get("nh_spamprotect")."\";");
        }

        eval("\$reservation_form = \"".$templates->get("reservations_formular")."\";");
        if($guest_allow == 0){
            $reservation_form = "Gäste dürfen nicht Reservieren!";
        }

        // Regeln
        $reservation_rules =  $parser->parse_message($reservation_rules, $text_options);

// Reservierung eintragen
        if(isset($_POST['reserve'])){
            if($mybb->user['uid'] != 0){
            $username = $mybb->user[$userpf];
            } else{
                $username = $_POST['username'];
            }

            $new_reservation = array(
                "reservation" => $db->escape_string($_POST['reservation']),
                "cat" => $db->escape_string($_POST['cat']),
                "gender" => $db->escape_string($_POST['gender']),
                "wanted" => $db->escape_string($_POST['wanted']),
                "uid" => $mybb->user['uid'],
                "reserved" => TIME_NOW,
                "days" => $userres,
                "username" => $db->escape_string($username),
            );

            if($_POST['cat'] == "{$lang->reservation_wanted}" && $_POST['wanted'] == ""){
                $missing_wantedlink = "<div class='red_alert'>{$lang->reservation_missing_link}</div>";
            } elseif($_POST['cat'] == "{$lang->reservation_wanted}" AND $count_wanted == $max_wanted){
                $no_reserv ="<div class='red_alert'>{$lang->reservation_maxwanted}</div>";
            }  elseif($_POST['cat'] == "{$lang->reservation_avatar}" AND $count_avatar == $max_avatar){
                $no_reserv = "<div class='red_alert'>{$lang->reservation_maxavatar}</div>";
            } else{
                $db->insert_query("reservations", $new_reservation);
                redirect("misc.php?action=reservations");
            }

        }

// Reservierung editieren
        if(isset($mybb->input['reserve_edit'])){
            $reservation_uid = $mybb->input['reservation_id'];
            $edit_reservation = array(
                "username" => $db->escape_string($mybb->input['username']),
                "uid" => $db->escape_string($mybb->input['uid']),
                "reservation" => $db->escape_string($mybb->input['reservation']),
                "cat" => $db->escape_string($mybb->input['cat']),
                "gender" => $db->escape_string($mybb->input['gender']),
                "wanted" => $db->escape_string($mybb->input['wanted']),
            );
            $db->update_query("reservations", $edit_reservation, "reservation_id = '".$reservation_uid."'");
            redirect("misc.php?action=reservations");

        }

        // Reservierung löschen
        $delete_reservation = $mybb->input['delete_reservation'];
        if($delete_reservation){

            $userquery = $db->simple_select("reservations", "*", "reservation_id='".$delete_reservation."'");

            $row = $db->fetch_array($userquery);


            $uid = $row['uid'];
            $from_uid = $mybb->user['uid'];
            $reservation = $row['reservation'];
            $cat = $row['cat'];
            if(class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
                $alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('reservation_delete');
                if ($alertType != NULL && $alertType->getEnabled() && $from_uid != $uid) {
                    $alert = new MybbStuff_MyAlerts_Entity_Alert((int)$uid, $alertType);
                    $alert->setExtraDetails([
                        'reservation' => $reservation,
                        'cat' => $cat
                    ]);
                    MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
                }
            }
            $db->delete_query("reservations", "reservation_id = '".$delete_reservation."'");
            redirect("misc.php?action=reservations");
        }

        // Verlängern

        $extend_reservation = $mybb->input['extend_reservation'];
        if($extend_reservation){

            $reservation_kind_query = $db->simple_select("reservations", "cat", "reservation_id = '$extend_reservation'");
            $reservation_kind = $db->fetch_array($reservation_kind_query);

            if($reservation_kind['cat'] == $lang->reservation_avatar){
                $new_days = (int)$mybb->input['days'] + $extend_days;
            } else{
                $new_days = (int)$mybb->input['days'] + $wantedextend_days;
            }


            $new_count = (int)$mybb->input['extend'] + 1;
            $reservation_extend = array(
                "days" => $new_days,
                "count" => $new_count
            );
            $db->update_query("reservations", $reservation_extend, "reservation_id = '".$extend_reservation."'");
            redirect("misc.php?action=reservations");
        }

        eval("\$menu = \"".$templates->get("listen_nav")."\";");
        // Using the misc_help template for the page wrapper
        eval("\$page = \"".$templates->get("reservations")."\";");
        output_page($page);
    }

}

function reservations_header(){
    global $templates, $header_reservierung;
    eval("\$header_reservierung = \"".$templates->get("reservierung_header")."\";");
}

function reservations_alert_global()
{
    global $mybb, $db, $templates, $left_days, $reservation_global_alerts, $extend, $reserv_reservationtime;

    $select = $db->query("SELECT *
        FROM " . TABLE_PREFIX . "reservations
        ");
    while ($reservations = $db->fetch_array($select)) {
        $extend = "";
        $reserv_reservationtime = "";

        //Tage Berechnung
        $faktor = 86400;
        $days = $reservations['days'] * $faktor;
        $complete_days = $reservations['reserved'] + $days;
        $reserved_date = date('d.m.Y', $reservations['reserved']);
        $end_date = date('d.m.Y', $complete_days);
        $left_days = round(($complete_days - TIME_NOW) / $faktor);

        $user = get_user($reservations['uid']);
        $reservierung = $reservations['reservation'];

        if ($user['as_uid'] == 0) {
            $user_uid = $user['uid'];
        } elseif ($user['as_uid'] != 0) {
            $user_uid = $user['as_uid'];
        }

        if ($reservations['uid'] != 0) {
            if ($mybb->user['uid'] == $user_uid or $mybb->user['as_uid'] == $user_uid) {

                if ($reservations['count'] == 0) {
                    $extend = "Du kannst noch 2x Verlängern!";
                } elseif ($reservations['count'] == 1) {
                    $extend = "Du kannst noch 1x Verlängern!";
                } elseif ($reservations['count'] == 2) {
                    $extend = "Du kannst nicht mehr Verlängern!";
                }
                if ($left_days <= 8) {
                    if ($left_days > 1) {
                        $reserv_reservationtime = "<a href='misc.php?action=reservations'>Deine Reservierung für <b>" . $reservierung . "</b> läuft in " . $left_days . " Tagen ab. {$extend}</a>";
                    } elseif ($left_days == 1) {
                        $reserv_reservationtime = "<a href='misc.php?action=reservations'>Deine Reservierung für <b>" . $reservierung . "</b> läuft in einem Tag ab. {$extend}</a>";
                    } else{
                        $reserv_reservationtime = "<a href='misc.php?action=reservations'>Deine Reservierung für <b>" . $reservierung . "</b> ist abgelaufen. {$extend}</a>";

                    }
                    eval("\$reservation_global_alerts .= \"" . $templates->get("reservations_alert") . "\";");
                }
            }
        }
    }

}

//wer ist wo
$plugins->add_hook('fetch_wol_activity_end', 'reservations_user_activity');
$plugins->add_hook('build_friendly_wol_location_end', 'reservations_location_activity');

function reservations_user_activity($user_activity){
    global $user;

    if(my_strpos($user['location'], "misc.php?action=reservations") !== false) {
        $user_activity['activity'] = "reservations";
    }

    return $user_activity;
}

function reservations_location_activity($plugin_array) {
    global $db, $mybb, $lang;

    if($plugin_array['user_activity']['activity'] == "reservations")
    {
        $plugin_array['location_name'] = "Sieht sich die <a href='misc.php?action=reservations'>Reservierungen</a> an";
    }


    return $plugin_array;
}


/*
 * Generiere doch bitte eine MyAlert, so dass der user informiert wird, dass sein Avatar gelöscht wurde!
 * Avatar steht natürlich mit in der Datei!
 */
function reservations_alerts() {
    global $mybb, $lang;
    $lang->load('reservation');

    class MybbStuff_MyAlerts_Formatter_myalerts_reservation_deleteFormatter extends MybbStuff_MyAlerts_Formatter_AbstractFormatter
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
                $this->lang->myalerts_reservation_delete,
                $outputAlert['from_user'],
                $alertContent['reservation'],
                $alertContent['cat']
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
            $alertContent = $alert->getExtraDetails();
            return $this->mybb->settings['bburl'] . '/misc.php?action=reservations';
        }
    }

    if (class_exists('MybbStuff_MyAlerts_AlertFormatterManager')) {
        $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();

        if (!$formatterManager) {
            $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::createInstance($mybb, $lang);
        }

        $formatterManager->registerFormatter(
            new MybbStuff_MyAlerts_Formatter_myalerts_reservation_deleteFormatter($mybb, $lang, 'myalerts_reservation_delete')
        );
    }

}

// Mod cp


?>