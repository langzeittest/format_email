<?php

if (!defined('PHORUM')) return;

function mod_format_email_email_user_start($array)
{

    include_once('./include/api/mail.php');

    global $PHORUM;

    $data = $array[1];

    if (!(is_array($data) && isset($data['plain_body']) && isset($data['full_body']))) return $array;

    $stripped = $data['full_body'];

    // Strip HTML <tags>
    if (    isset($PHORUM['mod_format_email']['strip_html'])
         && $PHORUM['mod_format_email']['strip_html'] ) {
        $stripped = preg_replace('|</*[a-z][^>]*>|i', '', $stripped);
    }

    // Strip BB Code [tags]
    if (    isset($PHORUM['mod_format_email']['strip_bbcode'])
         && $PHORUM['mod_format_email']['strip_bbcode'] ) {
        $stripped = preg_replace('|\[/*[a-z][^\]]*\]|i', '', $stripped);
    }

    // Do badwords check
    // Prepare the bad-words replacement code.
    if (    isset($PHORUM['mod_format_email']['banlist'])
         && $PHORUM['mod_format_email']['banlist'] ) {
        $bad_word_check= false;

        $banlists = NULL;
        if(!empty($PHORUM['cache_banlists']) && !empty($PHORUM['banlist_version'])){
            $cache_key = $PHORUM['forum_id'];
            $banlists=phorum_cache_get('banlist',$cache_key,$PHORUM['banlist_version']);
        }
        // not found or no caching enabled
        if($banlists === NULL ) {
            $banlists = phorum_db_get_banlists();

            if(!empty($PHORUM['cache_banlists']) && !empty($PHORUM['banlist_version'])) {
                phorum_cache_put('banlist',$cache_key,$banlists,7200,$PHORUM['banlist_version']);
            }
        }

        if (isset($banlists[PHORUM_BAD_WORDS]) && is_array($banlists[PHORUM_BAD_WORDS])) {
            $replace_vals  = array();
            $replace_words = array();
            foreach ($banlists[PHORUM_BAD_WORDS] as $item) {
                $replace_words[] = '/\b'.preg_quote($item['string'],'/').'(ing|ed|s|er|es)*\b/i';
                $replace_vals[]  = PHORUM_BADWORD_REPLACE;
                $bad_word_check  = true;
            }
        }

        if ($bad_word_check) {
            $stripped = preg_replace($replace_words, $replace_vals, $stripped);
        }
    }

    // Wordwrap
    if (    isset($PHORUM['mod_format_email']['wordwrap'])
         && $PHORUM['mod_format_email']['wordwrap'] ) {
        $stripped = wordwrap($stripped, 72);
    }

    $data['plain_body'] = $stripped;

    $array[1] = $data;

    return $array;
}

//
// Add sanity checks
//
function mod_format_email_sanity_checks($sanity_checks) {
    if (    isset($sanity_checks)
         && is_array($sanity_checks) ) {
        $sanity_checks[] = array(
            'function'    => 'mod_format_email_do_sanity_checks',
            'description' => 'Format Email Module'
        );
    }
    return $sanity_checks;
}

//
// Do sanity checks
//
function mod_format_email_do_sanity_checks() {

    include_once('./include/version_functions.php');

    global $PHORUM;

    // Check if the Phorum version is high enough.
    if (phorum_compare_version(PHORUM, '5.2.8') == -1) {
          return array(
                     PHORUM_SANITY_CRIT,
                     'The Phorum version is not high enough.',
                     'This module needs at least Phorum version 5.2.8.'
                 );
    }

    // Check if the Phorum version is to high.
    if (!phorum_compare_version(PHORUM, '5.2.21') == -1) {
          return array(
                     PHORUM_SANITY_CRIT,
                     'The Phorum version is to high.',
                     'From Phorum version 5.2.21 this module is not any '
                         .'longer needed. Use the new &quot;How to strip quotes '
                         .'in mails&quot; setting in &quot;General Settings&quot;.'
                 );
    }

    // Check if module settings exists.
    if (    !isset($PHORUM['mod_format_email']['strip_html'])
         || !isset($PHORUM['mod_format_email']['strip_bbcode'])
         || !isset($PHORUM['mod_format_email']['banlist'])
         || !isset($PHORUM['mod_format_email']['wordwrap']) ) {
          return array(
                     PHORUM_SANITY_CRIT,
                     'The default settings for the module are missing.',
                     "Login as administrator in Phorum's administrative "
                         .'interface and go to the &quot;Modules&quot; '
                         .'section. Open the module settings for the Format '
                         .'Email Module and save the default values.'
                 );
    }

    return array(PHORUM_SANITY_OK, NULL);
}

?>
