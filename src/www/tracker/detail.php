<?php
/**
  *
  * SourceForge Generic Tracker facility
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  *
  */

// Check if this tracker is valid (not deleted)
if ( !$ath->isValid() ) {
    exit_error($Language->getText('global','error'),$Language->getText('tracker_add', 'invalid'));
}

// Create factories
$art_field_fact = new ArtifactFieldFactory($ath);
$art_fieldset_fact = new ArtifactFieldSetFactory($ath);

// Printer version ?
if ( !$request->exist('pv') ) {
    $pv = false;
    $ro = !user_isloggedin();
} else {
    $pv = $request->get('pv');
    if ( $pv ) $ro = true;
}

$GLOBALS['HTML']->addFeed(
    $group->getPublicName().' '.$ath->getName() .' #'. $ah->getId() .' - '. html_entity_decode($ah->getValue('summary'), ENT_QUOTES) .' - '. $Language->getText('tracker_include_artifact','follow_ups'), 
    '/tracker/?func=rss&aid='. (int)$ah->getId() .'&atid='. (int)$ath->getID() .'&group_id='. (int)$group->getGroupId()
);
$params=array('title'=>$group->getPublicName().' '.$ath->getName().' #'.$ah->getID(). ' - \'' . $ah->getSummary().'\'',
              'pagename'=>'tracker',
              'atid'=>$ath->getID(),
              'sectionvals'=>array($group->getPublicName()),
              'pv'=>$pv,
              'help' => 'tracker-v3.html#new-artifact-submission' );

$ath->header($params);


// artifact object (and field values) initialized in script above (index.php)
$ah->display($ro,$pv,user_getid());

echo '<script type="text/javascript">'. "\n";
$armh = new ArtifactRulesManagerHtml($ath);
$armh->displayRulesAsJavascript();
echo "Event.observe(window, 'load', function() {
        if ($('tracker_details')) {
            new com.xerox.codendi.FieldEditor('tracker_details', {
                edit:    '". addslashes($Language->getText('tracker_fieldeditor','edit')) ."',
                preview: '". addslashes($Language->getText('tracker_fieldeditor','preview')) ."',
                warning: '". addslashes($Language->getText('tracker_fieldeditor','warning')) ."',
                group_id:". (int)$ath->getGroupId(). "
            });
        }
});";

echo "new UserAutoCompleter('tracker_cc',
                          '".util_get_dir_image_theme()."',
                          true);\n";
echo "</script>";

// Display footer page
$ath->footer($params);

?>
