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
 */
namespace Innomatic\Desktop\Auth;

use \Innomatic\Core\InnomaticContainer;
use \Innomatic\Desktop\Auth\DesktopRootAuthenticatorHelper;

/**
 *
 * @package Desktop
 */
class DesktopDomainAuthenticatorHelper implements \Innomatic\Desktop\Auth\DesktopAuthenticatorHelper
{

    public function authenticate()
    {
        $container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');
        $session = \Innomatic\Desktop\Controller\DesktopFrontController::instance('\Innomatic\Desktop\Controller\DesktopFrontController')->session;

        if (isset(\Innomatic\Wui\Wui::instance('\Innomatic\Wui\Wui')->parameters['wui']['login'])) {
            $loginDispatcher = new \Innomatic\Wui\Dispatch\WuiDispatcher('login');
            $loginDispatcher->addEvent('logout', '\Innomatic\Desktop\Auth\tenant_login_logout');
            $loginDispatcher->addEvent('login', '\Innomatic\Desktop\Auth\tenant_login_login');
            $loginDispatcher->Dispatch();
        }

        if ($container->getConfig()->value('SecurityOnlyHttpsDomainAccessAllowed') == '1') {
            if (! isset($_SERVER['HTTPS']) or ($_SERVER['HTTPS'] != 'on')) {
                self::doAuth(true, 'only_https_allowed');
            }
        }

        // Check if the session is valid
        if (! \Innomatic\Desktop\Controller\DesktopFrontController::instance('\Innomatic\Desktop\Controller\DesktopFrontController')->session->isValid('INNOMATIC_AUTH_USER')) {
            self::doAuth();
        }

        $domainsquery = $container->getDataAccess()->execute('SELECT id FROM domains WHERE domainid=' . $container->getDataAccess()
                ->formatText(\Innomatic\Domain\User\User::extractDomainID($session->get('INNOMATIC_AUTH_USER'))));
        if ($domainsquery->getNumberRows() == 0) {
            self::doAuth();
        } else {
            $domainsquery->free();
            $container->startDomain(\Innomatic\Domain\User\User::extractDomainID($session->get('INNOMATIC_AUTH_USER')), $session->get('INNOMATIC_AUTH_USER'));
        }

        // Check if the user still exists
        $user = new \Domain\User\User(
            $container->getCurrentDomain()->domaindata['id'],
            \Domain\User\User::getUserIdByUsername($session->get('INNOMATIC_AUTH_USER'))
        );

        if (!$user->exists()) {
            // User no more exists; remove the session key and redo auth
            \Innomatic\Desktop\Controller\DesktopFrontController::instance('\Innomatic\Desktop\Controller\DesktopFrontController')->session->remove('INNOMATIC_AUTH_USER');
            $container->stopDomain();
            self::doAuth();
        }

        // Check if the user is enabled
        if (!$user->isEnabled()) {
            $container->stopDomain();
            self::doAuth(true, 'userdisabled');
        }

        if ($session->isValid('domain_login_attempts')) {
            $session->remove('domain_login_attempts');
        }

        // Check if the domain is enabled
        //
        if ($container->getCurrentDomain()->domaindata['domainactive'] != $container->getDataAccess()->fmttrue) {
            self::doAuth(true, 'domaindisabled');
        }

        return true;
    }

    public static function doAuth($wrong = false, $reason = '')
    {
        $innomatic = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');
        $innomaticLocale = new \Innomatic\Locale\LocaleCatalog('innomatic::authentication', $innomatic->getLanguage());

        $wui = \Innomatic\Wui\Wui::instance('\Innomatic\Wui\Wui');
        $wui->loadWidget('button');
        $wui->loadWidget('formarg');
        $wui->loadWidget('form');
        $wui->loadWidget('grid');
        $wui->loadWidget('horizbar');
        $wui->loadWidget('horizframe');
        $wui->loadWidget('horizgroup');
        $wui->loadWidget('image');
        $wui->loadWidget('label');
        $wui->loadWidget('link');
        $wui->loadWidget('page');
        $wui->loadWidget('sessionkey');
        $wui->loadWidget('statusbar');
        $wui->loadWidget('string');
        $wui->loadWidget('submit');
        $wui->loadWidget('titlebar');
        $wui->loadWidget('vertframe');
        $wui->loadWidget('vertgroup');

        $wuiPage = new WuiPage('loginpage', array(
            'title' => $innomaticLocale->getStr('desktoplogin'),
            'border' => 'false',
            'align' => 'center',
            'valign' => 'middle'
        ));
        $wuiTopGroup = new WuiVertgroup('topgroup', array(
            'align' => 'center',
            'groupalign' => 'center',
            'groupvalign' => 'middle',
            'height' => '189px',
            'width' => '350px'
        ));
        $wuiMainGroup = new WuiVertgroup('maingroup', array(
            'align' => 'center'
        ));
        $wuiTitleBar = new WuiTitleBar('titlebar', array(
            'title' => $innomaticLocale->getStr('desktoplogin')
        ));
        $wuiMainBFrame = new WuiVertframe('vframe', array(
            'align' => 'center'
        ));
        $wuiMainFrame = new WuiHorizgroup('horizframe');
        $wuiMainStatus = new WuiStatusBar('mainstatusbar', array(
            'width' => '350px'
        ));

        // Main frame
        //
        $wuiGrid = new WuiGrid('grid', array(
            'rows' => '2',
            'cols' => '2'
        ));

        $wuiGrid->addChild(new WuiLabel('usernamelabel', array(
            'label' => $innomaticLocale->getStr('username')
        )), 0, 0);
        $wuiGrid->addChild(new WuiString('username', array(
            'disp' => 'login'
        )), 0, 1);

        $wuiGrid->addChild(new WuiLabel('passwordlabel', array(
            'label' => $innomaticLocale->getStr('password')
        )), 1, 0);
        $wuiGrid->addChild(new WuiString('password', array(
            'disp' => 'login',
            'password' => 'true'
        )), 1, 1);

        $wuiVGroup = new WuiVertgroup('vertgroup', array(
            'align' => 'center'
        ));
        // $wui_vgroup->addChild( new WuiLabel( 'titlelabel', array( 'label' => $innomatic_locale->getStr( 'rootlogin' ) ) ) );
        $wuiVGroup->addChild($wuiGrid);
        $wuiVGroup->addChild(new WuiSubmit('submit', array(
            'caption' => $innomaticLocale->getStr('enter')
        )));

        $formEventsCall = new \Innomatic\Wui\Dispatch\WuiEventsCall();
        $formEventsCall->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('login', 'login', ''));
        $formEventsCall->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('view', 'default', ''));

        $wuiForm = new WuiForm('form', array(
            'action' => $formEventsCall->getEventsCallString()
        ));

        $wuiHGroup = new WuiHorizgroup('horizgroup', array(
            'align' => 'middle'
        ));
        $wuiHGroup->addChild(new WuiButton('password', array(
            'themeimage' => 'keyhole',
            'themeimagetype' => 'big',
            'action' => $innomatic->getBaseUrl() . '/',
            'highlight' => false
        )));
        $wuiHGroup->addChild($wuiVGroup);

        $wuiForm->addChild($wuiHGroup);
        $wuiMainFrame->addChild($wuiForm);

        // Wrong account check
        //
        $session = \Innomatic\Desktop\Controller\DesktopFrontController::instance('\Innomatic\Desktop\Controller\DesktopFrontController')->session;

        if ($wrong) {
            if ($innomatic->getConfig()->value('SecurityAlertOnWrongLocalUserLogin') == '1') {
                $loginDispatcher = new \Innomatic\Wui\Dispatch\WuiDispatcher('login');
                $eventData = $loginDispatcher->getEventData();

                $innomaticSecurity = new \Innomatic\Security\SecurityManager();
                $innomaticSecurity->sendAlert('Wrong user local login for user ' . $eventData['username'] . ' from remote address ' . $_SERVER['REMOTE_ADDR']);
                $innomaticSecurity->logFailedAccess($eventData['username'], false, $_SERVER['REMOTE_ADDR']);

                unset($innomaticSecurity);
            }

            $sleepTime = $innomatic->getConfig()->value('WrongLoginDelay');
            if (! strlen($sleepTime))
                $sleepTime = 1;
            $maxAttempts = $innomatic->getConfig()->value('MaxWrongLogins');
            if (! strlen($maxAttempts))
                $maxAttempts = 3;

            sleep($sleepTime);

            if ($session->isValid('domain_login_attempts')) {
                $session->put('domain_login_attempts', $session->get('domain_login_attempts') + 1);
                if ($session->get('domain_login_attempts') >= $maxAttempts)
                    $innomatic->abort($innomaticLocale->getStr('wrongpwd'));
            } else {
                $session->put('domain_login_attempts', 1);
            }

            if ($reason) {
                $wuiMainStatus->mArgs['status'] = $innomaticLocale->getStr($reason);
            } else {
                $wuiMainStatus->mArgs['status'] = $innomaticLocale->getStr('wrongpwd');
            }
        } else {
            $session->put('domain_login_attempts', 0);
        }

        // Page render
        //
        $wuiMainGroup->addChild($wuiTitleBar);
        // $wui_maingroup->addChild( new WuiButton( 'innomaticlogo', array( 'image' => $innomatic->getBaseUrl(false).'/shared/styles/cleantheme/innomatic_big_asp.png', 'action' => $innomatic->getBaseUrl().'/' ) ) );
        $wuiMainBFrame->addChild($wuiMainFrame);
        $wuiMainGroup->addChild($wuiMainBFrame);
        // $wuiMainGroup->addChild($wuiMainStatus);
        $wuiTopGroup->addChild($wuiMainGroup);
        $wuiPage->addChild($wuiTopGroup);
        $wuiPage->addChild($wuiMainStatus);
        $wui->addChild($wuiPage);
        $wui->render();

        $innomatic->halt();
    }

    public function authorize()
    {}
}

function tenant_login_login($eventData)
{
    $container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');
    $username = $eventData['username'];

    // Handle the case when the root user tries to login from the tenant login form
    if (strcmp($username, 'root') === 0) {
        require_once('innomatic/desktop/auth/DesktopRootAuthenticatorHelper.php');

        \Innomatic\Desktop\Auth\root_login_login($eventData);

        $response = \Innomatic\Webapp\WebAppContainer::instance('\Innomatic\Webapp\WebAppContainer')
            ->getProcessor()
            ->getResponse();

        $response->sendRedirect($container->getBaseUrl(false).'/root/');
        $response->flushBuffer();

        return;
    }

    $domainId = \Innomatic\Domain\User\User::extractDomainID($username);

    // Checks it it can find the domain by hostname
    if (! strlen($domainId)) {
        $domainId = \Innomatic\Domain\Domain::getDomainByHostname();
        if (strlen($domainId)) {
            $username .= '@' . $domainId;
        }
    }

    // If no domain is found when in Multi Tenant edition, it must be reauth without
    // checking database, since no Domain can be accessed.
    if (! strlen($domainId)) {
        DesktopDomainAuthenticatorHelper::doAuth(true);
    }
    $tmpDomain = new \Innomatic\Domain\Domain($container->getDataAccess(), $domainId, null);
    $domainDA = $tmpDomain->getDataAccess();
    $userQuery = $domainDA->execute('SELECT * FROM domain_users WHERE username=' . $domainDA->formatText($username) . ' AND password=' . $domainDA->formatText(md5($eventData['password'])));

    // Check if the user/password couple exists
    if ($userQuery->getNumberRows()) {
        // Check if the user is not disabled
        if ($userQuery->getFields('disabled') == $container->getDataAccess()->fmttrue) {
            DesktopDomainAuthenticatorHelper::doAuth(true, 'userdisabled');
        } else {
            // Login ok, set the session key
            \Innomatic\Desktop\Controller\DesktopFrontController::instance('\Innomatic\Desktop\Controller\DesktopFrontController')->session->put('INNOMATIC_AUTH_USER', $username);

            $innomaticSecurity = new \Innomatic\Security\SecurityManager();
            $innomaticSecurity->logAccess($username, false, false, $_SERVER['REMOTE_ADDR']);

            unset($innomaticSecurity);
        }
    } else {
        DesktopDomainAuthenticatorHelper::doAuth(true);
    }

    // unset( $INNOMATIC_ROOT_AUTH_USER );
}

function tenant_login_logout($eventData)
{
    $innomaticSecurity = new \Innomatic\Security\SecurityManager();
    $innomaticSecurity->logAccess(\Innomatic\Desktop\Controller\DesktopFrontController::instance('\Innomatic\Desktop\Controller\DesktopFrontController')->session->get('INNOMATIC_AUTH_USER'), true, false, $_SERVER['REMOTE_ADDR']);

    \Innomatic\Desktop\Controller\DesktopFrontController::instance('\Innomatic\Desktop\Controller\DesktopFrontController')->session->remove('INNOMATIC_AUTH_USER');
    unset($innomaticSecurity);

    DesktopDomainAuthenticatorHelper::doAuth();
}
