<?php

// Make sure that this script is loaded from the admin interface.
if (!defined('PHORUM_ADMIN')) return;

// Save settings in case this script is run after posting
// the settings form.
if ( count($_POST) ) {

    // Create the settings array for this module.
    $PHORUM['mod_format_email'] = array
        ( 'strip_html' => $_POST['strip_html'] ? 1 : 0,
          'strip_bbcode' => $_POST['strip_bbcode'] ? 1 : 0,
          'banlist' => $_POST['banlist'] ? 1 : 0,
          'wordwrap' => $_POST['wordwrap'] ? 1 : 0 );

    // Force the options to be integer values.
    settype($PHORUM['mod_format_email']['strip_html'], 'int');
    settype($PHORUM['mod_format_email']['strip_bbcode'], 'int');
    settype($PHORUM['mod_format_email']['banlist'], 'int');
    settype($PHORUM['mod_format_email']['wordwrap'], 'int');

    if (!phorum_db_update_settings(array('mod_format_email'=>$PHORUM['mod_format_email']))){
        $error = 'Database error while updating settings.';
    } else {
        phorum_admin_okmsg('Settings Updated');
    }
}

// Apply default values for the settings.
if (!isset($PHORUM['mod_format_email']['strip_html'])) {
    $PHORUM['mod_format_email']['strip_html'] = 1;
}

if (!isset($PHORUM['mod_format_email']['strip_bbcode'])) {
    $PHORUM['mod_format_email']['strip_bbcode'] = 1;
}

if (!isset($PHORUM['mod_format_email']['banlist'])) {
    $PHORUM['mod_format_email']['banlist'] = 1;
}

if (!isset($PHORUM['mod_format_email']['wordwrap'])) {
    $PHORUM['mod_format_email']['wordwrap'] = 0;
}

// We build the settings form by using the PhorumInputForm object.
include_once './include/admin/PhorumInputForm.php';
$frm = new PhorumInputForm ('', 'post', 'Save settings');
$frm->hidden('module', 'modsettings');
$frm->hidden('mod', 'format_email');

// Here we display an error in case one was set by saving
// the settings before.
if (!empty($error)){
    phorum_admin_error($error);
}

$frm->addbreak('Edit settings for the Format Email Module');
// Strip HTML tags
$row = $frm->addrow('Strip HTML &lt;tags&gt;?', $frm->checkbox('strip_html', '1', '', $PHORUM['mod_format_email']['strip_html']));
$frm->addhelp($row, 'Strip HTML &lt;tags&gt;?', 'If this option is marked HTML tags are striped from the body. This is the default behavior of Phorum.');
// Strip BBCode tags
$row = $frm->addrow('Strip BBCode [tags]?', $frm->checkbox('strip_bbcode', '1', '', $PHORUM['mod_format_email']['strip_bbcode']));
$frm->addhelp($row, 'Strip BBCode [tags]?', 'If this option is marked BBCode tags are striped from the body. This is the default behavior of Phorum.');
// Replace bad words from censor list
$row = $frm->addrow('Replace bad words from censor list?', $frm->checkbox('banlist', '1', '', $PHORUM['mod_format_email']['banlist']));
$frm->addhelp($row, 'Replace bad words from censor list?', 'If this option is marked bad words from censor list are replaced in the body. This is the default behavior of Phorum.');
// Wordwrap
$row = $frm->addrow('Wordwrap?', $frm->checkbox('wordwrap', '1', '', $PHORUM['mod_format_email']['wordwrap']));
$frm->addhelp($row, 'Wordwrap?', "If this option is marked the body ist wordwrap (72 characters per line). Private messages are always wordwraped because it's always done in the Phorum core.");
// Show settings form
$frm->show();

?>
