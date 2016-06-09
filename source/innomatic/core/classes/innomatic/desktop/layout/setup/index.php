<?php
/**
 * Innomatic
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2014 Innoteam Srl
 * @license    http://www.innomatic.io/license/ New BSD License
 * @link       http://www.innomatic.io
 * @since      Class available since Release 5.0
*/

use \Innomatic\Core\InnomaticContainer;
use \Share\Wui;

\Innomatic\Webapp\WebAppContainer::instance('\Innomatic\Webapp\WebAppContainer')->getProcessor()->getResponse()->addHeader('P3P', 'CP="CUR ADM OUR NOR STA NID"');
\Innomatic\Webapp\WebAppContainer::instance('\Innomatic\Webapp\WebAppContainer')->getProcessor()->getResponse()->addHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
\Innomatic\Webapp\WebAppContainer::instance('\Innomatic\Webapp\WebAppContainer')->getProcessor()->getResponse()->addHeader('Last-Modified', gmdate('D, d M Y H:i:s'));
\Innomatic\Webapp\WebAppContainer::instance('\Innomatic\Webapp\WebAppContainer')->getProcessor()->getResponse()->addHeader('Cache-control', 'no-cache, must-revalidate');
\Innomatic\Webapp\WebAppContainer::instance('\Innomatic\Webapp\WebAppContainer')->getProcessor()->getResponse()->addHeader('Pragma', 'no-cache');

function setup_entry(&$progress, $phases, $phaseMark, $phaseCompleted, $phaseName, $wui_table, $row)
{
    if (file_exists(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'core/temp/'.$phaseMark)) {
        $ball_icon = $wui_table->mThemeHandler->mStyle['goldball'];
        $font_color = 'yellow';
        $pre = '<b>';
        $post = '</b>';
    } elseif (!file_exists(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'core/temp/'.$phaseCompleted)) {
        $ball_icon = $wui_table->mThemeHandler->mStyle['redball'];
        $font_color = 'black';
        $pre = '';
        $post = '';
    } else {
        $ball_icon = $wui_table->mThemeHandler->mStyle['greenball'];
        $font_color = 'black';
        $pre = '';
        $post = '';
        $progress = $row +1;
    }

    $wui_table->addChild(new WuiImage('statusimage'.$row, array('imageurl' => $ball_icon)), $row, 0);
    $wui_table->addChild(new WuiLabel('phaselabel'.$row, array('label' => $pre.$phaseName.$post, 'nowrap' => 'false')), $row, 1);
}

// Checks if Innomatic is in setup phase
//
/*
 if (\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getState() != \Innomatic\Core\InnomaticContainer::STATE_SETUP) {
 main_page();
 }
 */

$container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');
$innomaticLocale = new \Innomatic\Locale\LocaleCatalog('innomatic::setup', isset($language) ? $language : $container->getLanguage());
$log = $container->getLogger();

$wui = \Innomatic\Wui\Wui::instance('\Innomatic\Wui\Wui');
$wui->loadWidget('button');
$wui->loadWidget('checkbox');
$wui->loadWidget('combobox');
$wui->loadWidget('form');
$wui->loadWidget('horizbar');
$wui->loadWidget('horizframe');
$wui->loadWidget('horizgroup');
$wui->loadWidget('grid');
$wui->loadWidget('image'      );
$wui->loadWidget('label'      );
$wui->loadWidget('page'      );
$wui->loadWidget('statusbar'  );
$wui->loadWidget('string'      );
$wui->loadWidget('submit'      );
$wui->loadWidget('table'      );
$wui->loadWidget('text'      );
$wui->loadWidget('titlebar'  );
$wui->loadWidget('vertframe'  );
$wui->loadWidget('vertgroup'  );
$wui->loadWidget('progressbar');

$wuiPage = new WuiPage('page', array('title' => $innomaticLocale->getStr('innomaticsetup_title')));
$wuiMainVertGroup = new WuiVertgroup('mainvertgroup');
$wuiTitleBar = new WuiTitleBar('titlebar', array('title' => $innomaticLocale->getStr('innomaticsetup_title')));
$wuiMainVertGroup->addChild($wuiTitleBar);

$wui_mainframe1 = new WuiHorizframe('mainframe', array('width' => '100%'));
$wuiMainFrame = new WuiVertgroup('mainvertgroup2');

// Pass dispatcher
//
$actionDispatcher = new WuiDispatcher('action');
$actionDispatcher->addEvent('checksystem', 'pass_checksystem');
function pass_checksystem($eventData)
{
    global $innomaticLocale;
    $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
    \Innomatic\Setup\InnomaticSetup::checksystem($eventData, $log);
}

$actionDispatcher->addEvent('installfiles', 'pass_installfiles');
function pass_installfiles($eventData)
{
    global $innomaticLocale, $log;
    $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
    \Innomatic\Setup\InnomaticSetup::installfiles($eventData, $log);
}

$actionDispatcher->addEvent('setedition', 'pass_setedition');
function pass_setedition($eventData)
{
    global $innomaticLocale;
    $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
    \Innomatic\Setup\InnomaticSetup::setedition($eventData, $log);
}

$actionDispatcher->addEvent('createdataaccessdrivers', 'pass_createdataaccessdrivers');
function pass_createdataaccessdrivers($eventData)
{
    global $innomaticLocale;
    $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
    \Innomatic\Setup\InnomaticSetup::dataaccessdrivers($eventData, $log);
}

$actionDispatcher->addEvent('createdb', 'pass_createdb');
function pass_createdb($eventData)
{
    global $innomaticLocale;
    $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
    \Innomatic\Setup\InnomaticSetup::createdb($eventData, $log);
}

$actionDispatcher->addEvent('initializecomponents', 'pass_initializecomponents');
function pass_initializecomponents($eventData)
{
    global $innomaticLocale;
    $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
    \Innomatic\Setup\InnomaticSetup::initializecomponents($eventData, $log);
}

$actionDispatcher->addEvent('setpassword', 'pass_setpassword');
function pass_setpassword($eventData)
{
    global $innomaticLocale;
    $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
    \Innomatic\Setup\InnomaticSetup::setpassword($eventData, $log);
}

$actionDispatcher->addEvent('setinnomatichost', 'pass_setinnomatichost');
function pass_setinnomatichost($eventData)
{
    global $innomaticLocale;
    $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
    \Innomatic\Setup\InnomaticSetup::setinnomatichost($eventData, $log);
}

$actionDispatcher->addEvent('setcountry', 'pass_setcountry');
function pass_setcountry($eventData)
{
    global $innomaticLocale;
    $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
    \Innomatic\Setup\InnomaticSetup::setcountry($eventData, $log);
}

$actionDispatcher->addEvent('setlanguage', 'pass_setlanguage');
function pass_setlanguage($eventData)
{
    global $innomaticLocale, $log;
    \Innomatic\Setup\InnomaticSetup::setlanguage($eventData, $log);
}
/*
 $pass_disp->addEvent('setappcentral', 'pass_setappcentral');
 function pass_setappcentral($eventData)
 {
 global $wui_mainstatus, $innomatic_locale;
 $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
 \Innomatic\Setup\InnomaticSetup::appcentral($eventData, $log);
 }
 */
$actionDispatcher->addEvent('cleanup', 'pass_cleanup');
function pass_cleanup($eventData)
{
    global $innomaticLocale;
    $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
    \Innomatic\Setup\InnomaticSetup::setBaseUrl();
    \Innomatic\Setup\InnomaticSetup::cleanup($eventData, $log);
}

$actionDispatcher->addEvent('finish', 'pass_finish');
function pass_finish($eventData)
{
    global $innomaticLocale;
    $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
    \Innomatic\Setup\InnomaticSetup::finish($eventData, $log);
}

$actionDispatcher->Dispatch();

// Checks if all setup phases are completed
//
if (\Innomatic\Setup\InnomaticSetup::check_lock_files()) {
    // Removes setup lock files
    //
    \Innomatic\Setup\InnomaticSetup::remove_lock_files();

    if (!\Innomatic\Setup\InnomaticSetup::remove_setup_lock_file()) {
    $log->logEvent(
        'innomatic.root.main_php',
        'Unable to remove lock file during initialization',
        \Innomatic\Logging\Logger::ERROR);
    }
}

clearstatcache();





// Progressbar

$progress_vert_group = new WuiVertgroup('mainvertgroup', array('width' => '0%'));

$progress_headers[1]['label'] = $innomaticLocale->getStr('setupphase_header');

$progress_table = new WuiTable('sumtable', array('headers' => $progress_headers));

$phase = 0;
$phases = 13;
$progress = 0;

setup_entry($progress, $phases, 'setup_checkingsystem', 'setup_systemchecked', $innomaticLocale->getStr('systemcheckphase_label'), $progress_table, $phase ++);
setup_entry($progress, $phases, 'setup_installingfiles', 'setup_filesinstalled', $innomaticLocale->getStr('filesinstallphase_label'), $progress_table, $phase ++);
setup_entry($progress, $phases, 'setup_settingedition', 'setup_editionset', $innomaticLocale->getStr('editionchoicephase_label'), $progress_table, $phase ++);
setup_entry($progress, $phases, 'setup_creatingdataaccessdrivers', 'setup_dataaccessdriverscreated', $innomaticLocale->getStr('dataaccessdriversphase_label'), $progress_table, $phase ++);
setup_entry($progress, $phases, 'setup_creatingdb', 'setup_dbcreated', $innomaticLocale->getStr('rootdaphase_label'), $progress_table, $phase ++);
setup_entry($progress, $phases, 'setup_initializingcomponents', 'setup_componentsinitialized', $innomaticLocale->getStr('componentsphase_label'), $progress_table, $phase ++);
setup_entry($progress, $phases, 'setup_settinginnomatichost', 'setup_innomatichostset', $innomaticLocale->getStr('innomatichostchoicephase_label'), $progress_table, $phase ++);
setup_entry($progress, $phases, 'setup_settingcountry', 'setup_countryset', $innomaticLocale->getStr('countrychoicephase_label'), $progress_table, $phase ++);
setup_entry($progress, $phases, 'setup_settinglanguage', 'setup_languageset', $innomaticLocale->getStr('languagechoicephase_label'), $progress_table, $phase ++);
setup_entry($progress, $phases, 'setup_settingpassword', 'setup_passwordset', $innomaticLocale->getStr('passwordphase_label'), $progress_table, $phase ++);
//    setup_entry($wui_page, $progress, $phases, 'setup_settingappcentral', 'setup_appcentralset', $innomatic_locale->getStr('appcentralphase_label'), $progress_table, $phase ++);
setup_entry($progress, $phases, 'setup_cleaningup', 'setup_cleanedup', $innomaticLocale->getStr('cleanupphase_label'), $progress_table, $phase ++);
setup_entry($progress, $phases, 'setup_finishingsetup', 'setup_setupfinished', $innomaticLocale->getStr('finishphase_label'), $progress_table, $phase ++);

$progress_vert_group->addChild($progress_table);
$progress_vert_group->addChild(new WuiProgressBar('progress', array('progress' => $progress, 'totalsteps' => $phases)));






// Checks if there are remaining setup phases
//
if (!file_exists($container->getHome().'core/temp/setup_lock')) {
    $uri = dirname(\Innomatic\Webapp\WebAppContainer::instance('\Innomatic\Webapp\WebAppContainer')->getProcessor()->getRequest()->getRequestURI());
    $wuiPage = new WuiPage('page', array('title' => $innomaticLocale->getStr('innomaticsetup_title'), 'javascript' => "parent.location.href='".$uri."'"));
    $wuiPage->addChild($wuiMainVertGroup);
    $wui->addChild($wuiPage);
    $wui->render();
} else {
    // System check
    //
    if (!file_exists($container->getHome().'core/temp/setup_systemchecked')) {
        $wuiPage = new WuiPage('page', array('title' => $innomaticLocale->getStr('innomaticsetup_title')));

        $systemok = true;
        $row = 0;

        @touch($container->getHome().'core/temp/setup_checkingsystem', time());

        $wuiTitleBar->mArgs['title'] .= ' - '.$innomaticLocale->getStr('systemcheck_title');

        $headers = array();
        $wui_info_table = new WuiTable('sysinfotable', array('headers' => $headers));

        // Required features

        // PHP version check
        //
        $row = 0;
	
	if(version_compare(phpversion(), '5.4.0', '>')) {
            $ball = $wuiPage->mThemeHandler->mStyle['greenball'];
            $check_result = sprintf($innomaticLocale->getStr('php_available_label'), phpversion());
        } else {
            $ball = $wuiPage->mThemeHandler->mStyle['redball'];
            $check_result = sprintf($innomaticLocale->getStr('php_not_available_label'), phpversion());
            $systemok = false;
        }

        $wui_info_table->addChild(new WuiLabel('required'.$row, array('label' => $innomaticLocale->getStr('required_label'))), $row, 0);
        $wui_info_table->addChild(new WuiImage('status'.$row, array('imageurl' => $ball)), $row, 1);
        $wui_info_table->addChild(new WuiLabel('shared'.$row, array('label' => $innomaticLocale->getStr('php_test_label'))), $row, 2);
        $wui_info_table->addChild(new WuiLabel('checkresult'.$row, array('label' => $check_result)), $row, 3);

        // File upload support
        //
        $row++;

        if (ini_get('file_uploads') == '1') {
            $ball = $wuiPage->mThemeHandler->mStyle['greenball'];
            $check_result = $innomaticLocale->getStr('fileupload_available_label');
        } else {
            $ball = $wuiPage->mThemeHandler->mStyle['redball'];
            $check_result = $innomaticLocale->getStr('fileupload_not_available_label');
            $systemok = false;
        }

        $wui_info_table->addChild(new WuiLabel('required'.$row, array('label' => $innomaticLocale->getStr('required_label'))), $row, 0);
        $wui_info_table->addChild(new WuiImage('status'.$row, array('imageurl' => $ball)), $row, 1);
        $wui_info_table->addChild(new WuiLabel('shared'.$row, array('label' => $innomaticLocale->getStr('fileupload_test_label'))), $row, 2);
        $wui_info_table->addChild(new WuiLabel('checkresult'.$row, array('label' => $check_result)), $row, 3);

        // XML support
        //
        $row++;

        if (function_exists('xml_set_object')) {
            $ball = $wuiPage->mThemeHandler->mStyle['greenball'];
            $check_result = $innomaticLocale->getStr('xml_available_label');
        } else {
            $ball = $wuiPage->mThemeHandler->mStyle['redball'];
            $check_result = $innomaticLocale->getStr('xml_not_available_label');
            $systemok = false;
        }

        $wui_info_table->addChild(new WuiLabel('required'.$row, array('label' => $innomaticLocale->getStr('required_label'))), $row, 0);
        $wui_info_table->addChild(new WuiImage('status'.$row, array('imageurl' => $ball)), $row, 1);
        $wui_info_table->addChild(new WuiLabel('shared'.$row, array('label' => $innomaticLocale->getStr('xml_test_label'))), $row, 2);
        $wui_info_table->addChild(new WuiLabel('checkresult'.$row, array('label' => $check_result)), $row, 3);

        // Zlib support
        //
        $row++;

        if (function_exists('gzinflate')) {
            $ball = $wuiPage->mThemeHandler->mStyle['greenball'];
            $check_result = $innomaticLocale->getStr('zlib_available_label');
        } else {
            $ball = $wuiPage->mThemeHandler->mStyle['redball'];
            $check_result = $innomaticLocale->getStr('zlib_not_available_label');
            $systemok = false;
        }

        $wui_info_table->addChild(new WuiLabel('required'.$row, array('label' => $innomaticLocale->getStr('required_label'))), $row, 0);
        $wui_info_table->addChild(new WuiImage('status'.$row, array('imageurl' => $ball)), $row, 1);
        $wui_info_table->addChild(new WuiLabel('shared'.$row, array('label' => $innomaticLocale->getStr('zlib_test_label'))), $row, 2);
        $wui_info_table->addChild(new WuiLabel('checkresult'.$row, array('label' => $check_result)), $row, 3);

        // Database support
        //
        $row++;

        if (function_exists('mysqli_connect') or function_exists('pg_connect')) {
            $ball = $wuiPage->mThemeHandler->mStyle['greenball'];
            $check_result = $innomaticLocale->getStr('db_available_label');
        } else {
            $ball = $wuiPage->mThemeHandler->mStyle['redball'];
            $check_result = $innomaticLocale->getStr('db_not_available_label');
            $systemok = false;
        }

        $wui_info_table->addChild(new WuiLabel('required'.$row, array('label' => $innomaticLocale->getStr('required_label'))), $row, 0);
        $wui_info_table->addChild(new WuiImage('status'.$row, array('imageurl' => $ball)), $row, 1);
        $wui_info_table->addChild(new WuiLabel('shared'.$row, array('label' => $innomaticLocale->getStr('db_test_label'))), $row, 2);
        $wui_info_table->addChild(new WuiLabel('checkresult'.$row, array('label' => $check_result)), $row, 3);

        // Optional features

        // XMLRPC auth
        //
        $row++;

        if (php_sapi_name() != 'cgi') {
            $ball = $wuiPage->mThemeHandler->mStyle['greenball'];
            $check_result = $innomaticLocale->getStr('xmlrpc_available_label');
        } else {
            $ball = $wuiPage->mThemeHandler->mStyle['goldball'];
            $check_result = $innomaticLocale->getStr('xmlrpc_not_available_label');
        }

        $wui_info_table->addChild(new WuiLabel('required'.$row, array('label' => $innomaticLocale->getStr('optional_label'))), $row, 0);
        $wui_info_table->addChild(new WuiImage('status'.$row, array('imageurl' => $ball)), $row, 1);
        $wui_info_table->addChild(new WuiLabel('shared'.$row, array('label' => $innomaticLocale->getStr('xmlrpc_test_label'))), $row, 2);
        $wui_info_table->addChild(new WuiLabel('checkresult'.$row, array('label' => $check_result)), $row, 3);

        // XMLRPC SSL
        //
        $row++;

        if (function_exists('curl_init')) {
            $ball = $wuiPage->mThemeHandler->mStyle['greenball'];
            $check_result = $innomaticLocale->getStr('xmlrpc_ssl_available_label');
        } else {
            $ball = $wuiPage->mThemeHandler->mStyle['goldball'];
            $check_result = $innomaticLocale->getStr('xmlrpc_ssl_not_available_label');
        }

        $wui_info_table->addChild(new WuiLabel('required'.$row, array('label' => $innomaticLocale->getStr('optional_label'))), $row, 0);
        $wui_info_table->addChild(new WuiImage('status'.$row, array('imageurl' => $ball)), $row, 1);
        $wui_info_table->addChild(new WuiLabel('shared'.$row, array('label' => $innomaticLocale->getStr('xmlrpc_ssl_test_label'))), $row, 2);
        $wui_info_table->addChild(new WuiLabel('checkresult'.$row, array('label' => $check_result)), $row, 3);

        $wui_vgroup = new WuiVertgroup('nextvgroup', array('halign' => 'left', 'groupalign' => 'left'));
        $wui_vgroup->addChild($wui_info_table);

        if ($systemok) {
            $next_action = new \Innomatic\Wui\Dispatch\WuiEventsCall();
            $next_action->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('action', 'checksystem', ''));
            $next_button = new WuiButton('nextbutton', array('label' => $innomaticLocale->getStr('next_button'), 'horiz' => 'true', 'image' => $container->getBaseUrl(false).'/shared/icons/subway/icons/arrowright.png', 'width' => '20', 'height' => '20', 'action' => $next_action->getEventsCallString()));
        } else {
            $next_action = new \Innomatic\Wui\Dispatch\WuiEventsCall();
            $next_button = new WuiButton('nextbutton', array('label' => $innomaticLocale->getStr('retry_button'), 'horiz' => 'true', 'image' => $container->getBaseUrl(false).'/shared/icons/subway/icons/cycle.png', 'width' => '20', 'height' => '20', 'action' => $next_action->getEventsCallString()));
        }

        $wui_vgroup->addChild(new WuiHorizBar('horizbar'));
        $wui_vgroup->addChild($next_button);

        \Innomatic\Setup\InnomaticSetup::check_log($wui_vgroup);
        $wuiMainFrame->addChild($wui_vgroup);
    }
    // Files installation
    //
    else if (!file_exists($container->getHome().'core/temp/setup_filesinstalled')) {
        @touch($container->getHome().'core/temp/setup_installingfiles', time());
        $next_action = new \Innomatic\Wui\Dispatch\WuiEventsCall();
        $next_action->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('action', 'installfiles', ''));
        \Innomatic\Webapp\WebAppContainer::instance('\Innomatic\Webapp\WebAppContainer')->getProcessor()->getResponse()->sendRedirect($next_action->getEventsCallString());
    }
    // Innomatic edition
    //
    else if (!file_exists($container->getHome().'core/temp/setup_editionset')) {
        @touch($container->getHome().'core/temp/setup_settingedition', time());

        $wui_vgroup = new WuiVertgroup('vgroup');

        $editions['multitenant'] = $innomaticLocale->getStr('multitenant_edition_label');
        $editions['singletenant'] = $innomaticLocale->getStr('singletenant_edition_label');

        $wui_edition_grid = new WuiGrid('localegrid');

        $wui_edition_grid->addChild(new WuiLabel('editionlabel', array('label' => $innomaticLocale->getStr('edition_label'))), 0, 0);
        $wui_edition_grid->addChild(new WuiComboBox('edition', array('disp' => 'action', 'elements' => $editions)), 0, 1);

        $wui_vgroup->addChild($wui_edition_grid);
        $wui_vgroup->addChild(new WuiHorizBar('horizbar1'));
        $wui_vgroup->addChild(new WuiLabel('editionlabel', array('label' => $innomaticLocale->getStr('edition_explain_label'), 'nowrap' => 'false')));

        $form_events_call = new \Innomatic\Wui\Dispatch\WuiEventsCall();
        $form_events_call->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('action', 'setedition', ''));
        $form_events_call->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('view', 'edition', ''));

        $wui_form = new WuiForm('edition', array('action' => $form_events_call->getEventsCallString()));
        $wui_form->addChild($wui_vgroup);

        $next_action = new \Innomatic\Wui\Dispatch\WuiEventsCall();
        $next_action->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('action', 'setedition', ''));
        $next_action->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('view', 'edition', ''));
        $next_button = new WuiButton('nextbutton', array('label' => $innomaticLocale->getStr('next_button'), 'horiz' => 'true', 'formsubmit' => 'edition', 'image' => $container->getBaseUrl(false).'/shared/'.'icons/subway/icons/arrowright.png', 'width' => '20', 'height' => '20', 'action' => $next_action->getEventsCallString()));

        $wui_vgroup2 = new WuiVertgroup('vgroup2');

        $wui_vgroup2->addChild($wui_form);
        $wui_vgroup2->addChild(new WuiHorizBar('hr'));
        $wui_vgroup2->addChild($next_button);

        \Innomatic\Setup\InnomaticSetup::check_log($wui_vgroup2);

        $wuiMainFrame->addChild($wui_vgroup2);

        $wuiTitleBar->mArgs['title'] .= ' - '.$innomaticLocale->getStr('edition_title');
    }
    // Database creation
    //
    else if (!file_exists($container->getHome().'core/temp/setup_dataaccessdriverscreated')) {
        @touch($container->getHome().'core/temp/setup_creatingdataaccessdrivers', time());

        $next_action = new \Innomatic\Wui\Dispatch\WuiEventsCall();
        $next_action->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('action', 'createdataaccessdrivers', ''));
        \Innomatic\Webapp\WebAppContainer::instance('\Innomatic\Webapp\WebAppContainer')->getProcessor()->getResponse()->sendRedirect($next_action->getEventsCallString());
    } elseif (!file_exists($container->getHome().'core/temp/setup_dbcreated')) {
        @touch($container->getHome().'core/temp/setup_creatingdb', time());
        $wuiTitleBar->mArgs['title'] .= ' - '.$innomaticLocale->getStr('dbcreation_title');

        $wui_vgroup = new WuiVertgroup('vgroup');
        $wui_vgroup->addChild(new WuiLabel('phaselabel', array('label' => $innomaticLocale->getStr('dbcreation_phase_label'))));

        $wui_domain_grid = new WuiGrid('dbgrid', array('rows' => '6', 'cols' => '2'));

        $wui_domain_grid->addChild(new WuiLabel('dbtype_label', array('label' => $innomaticLocale->getStr('dbtype_label').' (*)')), 0, 0);
        $wui_domain_grid->addChild(new WuiComboBox('dbtype', array('disp' => 'action', 'elements' => \Innomatic\Dataaccess\DataAccessFactory::getDrivers())), 0, 1);

        $wui_domain_grid->addChild(new WuiLabel('dbname_label', array('label' => $innomaticLocale->getStr('dbname_label').' (*)')), 1, 0);
        $wui_domain_grid->addChild(new WuiString('dbname', array('disp' => 'action', 'value' => 'innomatic_root')), 1, 1);

        $wui_domain_grid->addChild(new WuiLabel('dbhost_label', array('label' => $innomaticLocale->getStr('dbhost_label').' (*)')), 2, 0);
        $wui_domain_grid->addChild(new WuiString('dbhost', array('disp' => 'action', 'value' => 'localhost')), 2, 1);

        $wui_domain_grid->addChild(new WuiLabel('dbport_label', array('label' => $innomaticLocale->getStr('dbport_label'))), 3, 0);
        $wui_domain_grid->addChild(new WuiString('dbport', array('disp' => 'action')), 3, 1);

        $wui_domain_grid->addChild(new WuiLabel('dbuser_label', array('label' => $innomaticLocale->getStr('dbuser_label').' (*)')), 4, 0);
        $wui_domain_grid->addChild(new WuiString('dbuser', array('disp' => 'action')), 4, 1);

        $wui_domain_grid->addChild(new WuiLabel('dbpassword_label', array('label' => $innomaticLocale->getStr('dbpassword_label').' (*)')), 5, 0);
        $wui_domain_grid->addChild(new WuiString('dbpass', array('disp' => 'action')), 5, 1);

        $wui_vgroup->addChild($wui_domain_grid);

        $wui_vgroup->addChild(new WuiHorizBar('horizbar1'));
        $wui_vgroup->addChild(new WuiLabel('reqfieldslabel', array('label' => $innomaticLocale->getStr('requiredfields_label'))));

        $form_events_call = new \Innomatic\Wui\Dispatch\WuiEventsCall();
        $form_events_call->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('action', 'createdb', ''));

        $wui_form = new WuiForm('createdb', array('action' => $form_events_call->getEventsCallString()));
        $wui_form->addChild($wui_vgroup);

        $next_action = new \Innomatic\Wui\Dispatch\WuiEventsCall();
        $next_action->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('action', 'createdb', ''));
        $next_button = new WuiButton('nextbutton', array('label' => $innomaticLocale->getStr('next_button'), 'horiz' => 'true', 'formsubmit' => 'createdb', 'image' => $container->getBaseUrl(false).'/shared/'.'icons/subway/icons/arrowright.png', 'width' => '20', 'height' => '20', 'action' => $next_action->getEventsCallString()));

        $wui_vgroup2 = new WuiVertgroup('vgroup2');

        $wui_vgroup2->addChild($wui_form);
        $wui_vgroup2->addChild(new WuiHorizBar('hr'));
        $wui_vgroup2->addChild($next_button);

        \Innomatic\Setup\InnomaticSetup::check_log($wui_vgroup2);

        $wuiMainFrame->addChild($wui_vgroup2);
    }
    // Components initialization
    //
    else if (!file_exists($container->getHome().'core/temp/setup_componentsinitialized')) {
        @touch($container->getHome().'core/temp/setup_initializingcomponents', time());

        $wuiTitleBar->mArgs['title'] .= ' - '.$innomaticLocale->getStr('components_title');

        $wui_vgroup = new WuiVertgroup('nextvgroup', array('halign' => 'left', 'groupalign' => 'left'));
        $wui_hgroup1 = new WuiHorizgroup('nexthgroup', array('align' => 'middle', 'groupalign' => 'center'));
        $wui_hgroup1->addChild(new WuiLabel('nextlabel', array('label' => $innomaticLocale->getStr('components_phase_label'))));
        $wui_vgroup->addChild($wui_hgroup1);

        $next_action = new \Innomatic\Wui\Dispatch\WuiEventsCall();
        $next_action->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('action', 'initializecomponents', ''));
        $next_button = new WuiButton('nextbutton', array('label' => $innomaticLocale->getStr('next_button'), 'horiz' => 'true', 'image' => $container->getBaseUrl(false).'/shared/icons/subway/icons/arrowright.png', 'width' => '20', 'height' => '20', 'action' => $next_action->getEventsCallString()));

        $wui_vgroup->addChild(new WuiHorizBar('hr'));
        $wui_vgroup->addChild($next_button);
        \Innomatic\Setup\InnomaticSetup::check_log($wui_vgroup);
        $wuiMainFrame->addChild($wui_vgroup);
    }
    // Innomatic host name and group
    //
    else if (!file_exists($container->getHome().'core/temp/setup_innomatichostset')) {
        @touch($container->getHome().'core/temp/setup_settinginnomatichost', time());

        $wuiTitleBar->mArgs['title'] .= ' - '.$innomaticLocale->getStr('innomatichost_title');

        $wui_vgroup = new WuiVertgroup('vgroup');
        $wui_vgroup->addChild(new WuiLabel('phaselabel', array('label' => $innomaticLocale->getStr('innomatichost_phase_label'))));

        $wui_domain_grid = new WuiGrid('hostgrid');

        $wui_domain_grid->addChild(new WuiLabel('innomatichostlabel', array('label' => $innomaticLocale->getStr('innomatichost_label'))), 0, 0);
        $wui_domain_grid->addChild(new WuiString('innomatichost', array('disp' => 'action')), 0, 1);

        $wui_domain_grid->addChild(new WuiLabel('innomaticgrouplabel', array('label' => $innomaticLocale->getStr('innomaticgroup_label'))), 1, 0);
        $wui_domain_grid->addChild(new WuiString('innomaticgroup', array('disp' => 'action')), 1, 1);

        $wui_vgroup->addChild($wui_domain_grid);

        $form_events_call = new \Innomatic\Wui\Dispatch\WuiEventsCall();
        $form_events_call->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('action', 'setinnomatichost', ''));

        $wui_form = new WuiForm('setinnomatichost', array('action' => $form_events_call->getEventsCallString()));
        $wui_form->addChild($wui_vgroup);

        $next_action = new \Innomatic\Wui\Dispatch\WuiEventsCall();
        $next_action->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('action', 'setinnomatichost', ''));
        $next_button = new WuiButton('nextbutton', array('label' => $innomaticLocale->getStr('next_button'), 'horiz' => 'true', 'formsubmit' => 'setinnomatichost', 'image' => $container->getBaseUrl(false).'/shared/icons/subway/icons/arrowright.png', 'width' => '20', 'height' => '20', 'action' => $next_action->getEventsCallString()));

        $wui_vgroup2 = new WuiVertgroup('vgroup2');

        $wui_vgroup2->addChild($wui_form);
        $wui_vgroup2->addChild(new WuiHorizBar('hr'));
        $wui_vgroup2->addChild($next_button);

        \Innomatic\Setup\InnomaticSetup::check_log($wui_vgroup2);

        $wuiMainFrame->addChild($wui_vgroup2);
    }
    // Root administration country
    //
    else if (!file_exists($container->getHome().'core/temp/setup_countryset')) {
        @touch($container->getHome().'core/temp/setup_settingcountry', time());

        $args['dbname'] = $container->getConfig()->value('RootDatabaseName');
        $args['dbhost'] = $container->getConfig()->value('RootDatabaseHost');
        $args['dbport'] = $container->getConfig()->value('RootDatabasePort');
        $args['dbuser'] = $container->getConfig()->value('RootDatabaseUser');
        $args['dbpass'] = $container->getConfig()->value('RootDatabasePassword');
        $args['dbtype'] = $container->getConfig()->value('RootDatabaseType');
        $args['dblog']  = $container->getHome().'core/log/innomatic_root_db.log';
        $dasn_string = $args['dbtype'].'://'.
        $args['dbuser'].':'.
        $args['dbpass'].'@'.
        $args['dbhost'].':'.
        $args['dbport'].'/'.
        $args['dbname'].'?'.
                        'logfile='.$args['dblog'];
        $tmpdb = \Innomatic\Dataaccess\DataAccessFactory::getDataAccess(new \Innomatic\Dataaccess\DataAccessSourceName($dasn_string));
        if ($tmpdb->Connect()) {
            $tmploc = new \Innomatic\Locale\LocaleCatalog('innomatic::localization', $container->getLanguage());

            $country_query = $tmpdb->execute('SELECT * '.
                                                  'FROM locale_countries');

            $country_locale = new \Innomatic\Locale\LocaleCatalog('innomatic::localization', $container->getLanguage());

            $wui_vgroup = new WuiVertgroup('vgroup');

			$defaultCountry = 'unitedstates';
			while (!$country_query->eof) {
			    $countries[$country_query->getFields('countryname')] = $country_locale->getStr($country_query->getFields('countryname'));
			    // Get the default country for the form based on HTTP_ACCEPT_LANGUAGE header
                if (strcmp($country_query->getFields('countryshort'), substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2)) == 0) {
			        $defaultCountry = $country_query->getFields('countryname');
			    }
			    $country_query->moveNext();
			}

            $wui_locale_grid = new WuiGrid('localegrid');

            $wui_locale_grid->addChild(new WuiLabel('countrylabel', array('label' => $innomaticLocale->getStr('country_label'))), 0, 0);
            $wui_locale_grid->addChild(new WuiComboBox('country', array('disp' => 'action', 'elements' => $countries, 'default' => $defaultCountry)), 0, 1);

            $wui_vgroup->addChild($wui_locale_grid);

            $form_events_call = new \Innomatic\Wui\Dispatch\WuiEventsCall();
            $form_events_call->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('action', 'setcountry', ''));

            $wui_form = new WuiForm('country', array('action' => $form_events_call->getEventsCallString()));
            $wui_form->addChild($wui_vgroup);

            $next_action = new \Innomatic\Wui\Dispatch\WuiEventsCall();
            $next_action->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('action', 'setcountry', ''));
            $next_button = new WuiButton('nextbutton', array('label' => $innomaticLocale->getStr('next_button'), 'horiz' => 'true', 'formsubmit' => 'country', 'image' => $container->getBaseUrl(false).'/shared/icons/subway/icons/arrowright.png', 'width' => '20', 'height' => '20', 'action' => $next_action->getEventsCallString()));

            $wui_vgroup2 = new WuiVertgroup('vgroup2');

            $wui_vgroup2->addChild($wui_form);
            $wui_vgroup2->addChild(new WuiHorizBar('hr'));
            $wui_vgroup2->addChild($next_button);

            \Innomatic\Setup\InnomaticSetup::check_log($wui_vgroup2);

            $wuiMainFrame->addChild($wui_vgroup2);

            $wuiTitleBar->mArgs['title'] .= ' - '.$innomaticLocale->getStr('rootcountry_title');
        } else $log->logEvent('innomatic.root.main_php',
                                'Unable to connect to root database during initialization', \Innomatic\Logging\Logger::ERROR);
    }
    // Root administration language
    //
    else if (!file_exists($container->getHome().'core/temp/setup_languageset')) {
        @touch($container->getHome().'core/temp/setup_settinglanguage', time());

        $pass_data = $actionDispatcher->getEventData();
        $country = isset($pass_data['country']) ? $pass_data['country'] : '';

        if (!strlen($country)) {
            $country = $container->getCountry();
        }

        $args['dbname'] = $container->getConfig()->value('RootDatabaseName');
        $args['dbhost'] = $container->getConfig()->value('RootDatabaseHost');
        $args['dbport'] = $container->getConfig()->value('RootDatabasePort');
        $args['dbuser'] = $container->getConfig()->value('RootDatabaseUser');
        $args['dbpass'] = $container->getConfig()->value('RootDatabasePassword');
        $args['dbtype'] = $container->getConfig()->value('RootDatabaseType');
        $args['dblog']  = $container->getHome().'core/log/innomatic_root_db.log';
        $dasn_string = $args['dbtype'].'://'.
        $args['dbuser'].':'.
        $args['dbpass'].'@'.
        $args['dbhost'].':'.
        $args['dbport'].'/'.
        $args['dbname'].'?'.
                        'logfile='.$args['dblog'];
        $tmpdb = \Innomatic\Dataaccess\DataAccessFactory::getDataAccess(new \Innomatic\Dataaccess\DataAccessSourceName($dasn_string));
        if ($tmpdb->Connect()) {
            $loc_country = new \Innomatic\Locale\LocaleCountry($country);
            $country_language = $loc_country->Language();

            $language_locale = new \Innomatic\Locale\LocaleCatalog('innomatic::localization', $container->getLanguage());

            $selected_language = $actionDispatcher->getEventData();
            if (isset($selected_language['language'])) {
                $selected_language = $selected_language['language'];
            } else {
                $selected_language = false;
            }

            $wui_vgroup = new WuiVertgroup('vgroup');

            $language_query = $tmpdb->execute('SELECT * FROM locale_languages');

            while (!$language_query->eof) {
                $languages[$language_query->getFields('langshort')] = $language_locale->getStr($language_query->getFields('langname'));
                $language_query->moveNext();
            }

            $wui_locale_grid = new WuiGrid('localegrid');

            $wui_locale_grid->addChild(new WuiLabel('languagelabel', array('label' => $innomaticLocale->getStr('language_label'))), 0, 0);
            $wui_locale_grid->addChild(new WuiComboBox('language', array('disp' => 'action', 'elements' => $languages, 'default' => ($selected_language ? $selected_language : $country_language))), 0, 1);

            $wui_vgroup->addChild($wui_locale_grid);
            $wui_vgroup->addChild(new WuiHorizBar('horizbar1'));
            $wui_vgroup->addChild(new WuiLabel('deflanglabel', array('label' => sprintf($innomaticLocale->getStr('countrylanguage_label'), $languages[$country_language]))));

            $form_events_call = new \Innomatic\Wui\Dispatch\WuiEventsCall();
            $form_events_call->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('action', 'setlanguage', ''));
            $form_events_call->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('view', 'language', ''));

            $wui_form = new WuiForm('language', array('action' => $form_events_call->getEventsCallString()));
            $wui_form->addChild($wui_vgroup);

            $next_action = new \Innomatic\Wui\Dispatch\WuiEventsCall();
            $next_action->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('action', 'setlanguage', ''));
            $next_action->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('view', 'language', ''));
            $next_button = new WuiButton('nextbutton', array('label' => $innomaticLocale->getStr('next_button'), 'horiz' => 'true', 'formsubmit' => 'language', 'image' => $container->getBaseUrl(false).'/shared/icons/subway/icons/arrowright.png', 'width' => '20', 'height' => '20', 'action' => $next_action->getEventsCallString()));

            $wui_vgroup2 = new WuiVertgroup('vgroup2');

            $wui_vgroup2->addChild($wui_form);
            $wui_vgroup2->addChild(new WuiHorizBar('hr'));
            $wui_vgroup2->addChild($next_button);

            \Innomatic\Setup\InnomaticSetup::check_log($wui_vgroup2);

            $wuiMainFrame->addChild($wui_vgroup2);

            $wuiTitleBar->mArgs['title'] .= ' - '.$innomaticLocale->getStr('rootlanguage_title');
        } else $log->logEvent('innomatic.root.main_php',
                                'Unable to connect to root database during initialization', \Innomatic\Logging\Logger::ERROR);
    }
    // Password choice
    //
    else if (!file_exists($container->getHome().'core/temp/setup_passwordset')) {
        @touch($container->getHome().'core/temp/setup_settingpassword', time());

        $wui_grid = new WuiGrid('grid');

        $wui_grid->addChild(new WuiLabel('passwordalabel', array('label' => $innomaticLocale->getStr('rootpassworda_label'))), 0, 0);
        $wui_grid->addChild(new WuiString('passworda', array('disp' => 'action', 'password' => 'true')), 0, 1);

        $wui_grid->addChild(new WuiLabel('passwordblabel', array('label' => $innomaticLocale->getStr('rootpasswordb_label'))), 1, 0);
        $wui_grid->addChild(new WuiString('passwordb', array('disp' => 'action', 'password' => 'true')), 1, 1);

        $wui_vgroup = new WuiVertgroup('vertgroup', array('align' => 'center'));
        $wui_vgroup->addChild(new WuiLabel('phaselabel', array('label' => $innomaticLocale->getStr('password_phase_label'))));
        $wui_vgroup->addChild($wui_grid);

        $form_events_call = new \Innomatic\Wui\Dispatch\WuiEventsCall();
        $form_events_call->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('action', 'setpassword', ''));

        $wui_form = new WuiForm('password', array('action' => $form_events_call->getEventsCallString()));
        $wui_form->addChild($wui_vgroup);

        $next_action = new \Innomatic\Wui\Dispatch\WuiEventsCall();
        $next_action->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('action', 'setpassword', ''));
        $next_button = new WuiButton('nextbutton', array('label' => $innomaticLocale->getStr('next_button'), 'horiz' => 'true', 'formsubmit' => 'password', 'image' => $container->getBaseUrl(false).'/shared/icons/subway/icons/arrowright.png', 'width' => '20', 'height' => '20', 'action' => $next_action->getEventsCallString()));

        $wui_vgroup2 = new WuiVertgroup('vgroup2');

        $wui_vgroup2->addChild($wui_form);
        $wui_vgroup2->addChild(new WuiHorizBar('hr'));
        $wui_vgroup2->addChild($next_button);

        \Innomatic\Setup\InnomaticSetup::check_log($wui_vgroup2);

        $wuiMainFrame->addChild($wui_vgroup2);

        $wuiTitleBar->mArgs['title'] .= ' - '.$innomaticLocale->getStr('password_title');
    }
    // AppCentral
    //
    /*
    else if (!file_exists($container->getHome().'core/temp/setup_appcentralset')) {
    @touch($container->getHome().'core/temp/setup_settingappcentral', time());

    $wui_vgroup = new WuiVertgroup('vgroup');

    $wui_appcentral_grid = new WuiGrid('grid');

    $wui_appcentral_grid->addChild(new WuiCheckBox('appcentral', array('disp' => 'action', 'checked' => 'true')), 0, 0);
    $wui_appcentral_grid->addChild(new WuiLabel('appcentrallabel', array('label' => $innomatic_locale->getStr('appcentral_label'))), 0, 1);

    $wui_vgroup->addChild($wui_appcentral_grid);
    $wui_vgroup->addChild(new WuiHorizBar('horizbar1'));
    $wui_vgroup->addChild(new WuiLabel('appcentrallabel', array('label' => $innomatic_locale->getStr('appcentral_explain_label'), 'nowrap' => 'false')));

    $form_events_call = new \Innomatic\Wui\Dispatch\WuiEventsCall();
    $form_events_call->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('action', 'setappcentral', ''));

    $wui_form = new WuiForm('appcentral', array('action' => $form_events_call->getEventsCallString()));
    $wui_form->addChild($wui_vgroup);

    $next_action = new \Innomatic\Wui\Dispatch\WuiEventsCall();
    $next_action->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('action', 'setappcentral', ''));
    $next_button = new WuiButton('nextbutton', array('label' => $innomatic_locale->getStr('next_button'), 'horiz' => 'true', 'formsubmit' => 'appcentral', 'image' => $container->getBaseUrl(false).'/shared/'.'icons/subway/icons/arrowright.png', 'width' => '20', 'height' => '20', 'action' => $next_action->getEventsCallString()));

    $wui_vgroup2 = new WuiVertgroup('vgroup2');

    $wui_vgroup2->addChild($wui_form);
    $wui_vgroup2->addChild(new WuiHorizBar('hr'));
    $wui_vgroup2->addChild($next_button);

    \Innomatic\Setup\InnomaticSetup::check_log($wui_vgroup2);

    $wui_mainframe->addChild($wui_vgroup2);

    $wui_titlebar->mArgs['title'] .= ' - '.$innomatic_locale->getStr('appcentral_title');
    }
    */
    // Final cleanup
    //
    else if (!file_exists($container->getHome().'core/temp/setup_cleanedup')) {
        @touch($container->getHome().'core/temp/setup_cleaningup', time());

        $wuiTitleBar->mArgs['title'] .= ' - '.$innomaticLocale->getStr('cleanup_title');

        $wui_vgroup = new WuiVertgroup('nextvgroup', array('halign' => 'left', 'groupalign' => 'left'));
        $wui_hgroup1 = new WuiHorizgroup('nexthgroup', array('align' => 'middle', 'groupalign' => 'center'));
        $wui_hgroup1->addChild(new WuiLabel('nextlabel', array('label' => $innomaticLocale->getStr('cleanup_label'))));
        $wui_vgroup->addChild($wui_hgroup1);

        $next_action = new \Innomatic\Wui\Dispatch\WuiEventsCall();
        $next_action->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('action', 'cleanup', ''));
        $next_button = new WuiButton('nextbutton', array('label' => $innomaticLocale->getStr('next_button'), 'horiz' => 'true', 'image' => $container->getBaseUrl(false).'/shared/icons/subway/icons/arrowright.png', 'width' => '20', 'height' => '20', 'action' => $next_action->getEventsCallString()));

        $wui_vgroup->addChild(new WuiHorizBar('hr'));
        $wui_vgroup->addChild($next_button);
        \Innomatic\Setup\InnomaticSetup::check_log($wui_vgroup);
        $wuiMainFrame->addChild($wui_vgroup);
    } elseif (!file_exists($container->getHome().'core/temp/setup_done')) {
        $wuiTitleBar->mArgs['title'] .= ' - '.$innomaticLocale->getStr('finish_title');

        $wui_vgroup = new WuiVertgroup('nextvgroup', array('halign' => 'left', 'groupalign' => 'left'));
        $wui_hgroup1 = new WuiHorizgroup('nexthgroup', array('align' => 'middle', 'groupalign' => 'center'));
        $wui_hgroup1->addChild(new WuiLabel('nextlabel', array('label' => $innomaticLocale->getStr('finish_label'))));
        $wui_vgroup->addChild($wui_hgroup1);

        $next_action = new \Innomatic\Wui\Dispatch\WuiEventsCall();
        $next_action->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('action', 'finish', ''));
        $next_button = new WuiButton('nextbutton', array('label' => $innomaticLocale->getStr('next_button'), 'horiz' => 'true', 'image' => $container->getBaseUrl(false).'/shared/icons/subway/icons/arrowright.png', 'width' => '20', 'height' => '20', 'action' => $next_action->getEventsCallString()));

        $wui_vgroup->addChild(new WuiHorizBar('hr'));
        $wui_vgroup->addChild($next_button);
        \Innomatic\Setup\InnomaticSetup::check_log($wui_vgroup);
        $wuiMainFrame->addChild($wui_vgroup);
    }

    // Page render
    //
    $wui_mainframe1->addChild($wuiMainFrame);

    $horiz_frame = new WuiHorizgroup('', array('groupvalign' => 'top'));

    $horiz_frame->addChild($progress_vert_group);
    $horiz_frame->addChild($wui_mainframe1);

    $wuiMainVertGroup->addChild($horiz_frame);
    $wuiPage->addChild($wuiMainVertGroup);
    $wui->addChild($wuiPage);
    $wui->render();
}
