<?php
/**
 * Innomatic
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2012 Innoteam Srl
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
*/

/**
 * @since 5.0
 */

require_once('innomatic/webapp/WebAppHandler.php');

/**
 * @since 5.0
 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
 * @copyright Copyright 2012 Innoteam Srl
 */
class WebServicesWebAppHandler extends WebAppHandler
{
    public function init()
    {
    }

    public function doGet(WebAppRequest $req, WebAppResponse $res)
    {
        // Identify the requested resource path
        $path = $this->getRelativePath($req);

        // Bootstraps Innomatic
        $container = WebAppContainer::instance('webappcontainer');
        $home = $container->getCurrentWebApp()->getHome();

        require_once('innomatic/core/InnomaticContainer.php');

        $innomatic = InnomaticContainer::instance('innomaticcontainer');
        $innomatic->bootstrap($home, $home.'core/conf/innomatic.ini');
        $innomatic->setMode(InnomaticContainer::MODE_ROOT);
        $innomatic->setInterface(InnomaticContainer::INTERFACE_WEBSERVICES);

        if (InnomaticContainer::instance('innomaticcontainer')->getState() == InnomaticContainer::STATE_SETUP) {
            $innomatic->abort('Setup phase');
        }

        require_once('innomatic/webservices/WebServicesUser.php');
        require_once('innomatic/webservices/WebServicesProfile.php');
        require_once('innomatic/webservices/xmlrpc/XmlRpc_Server.php');
        require_once('innomatic/dataaccess/DataAccess.php');

        $xuser = new WebServicesUser(InnomaticContainer::instance('innomaticcontainer')->getDataAccess());
        if ($xuser->setByAccount($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
            $container = InnomaticContainer::instance('innomaticcontainer');
            $container->setWebServicesUser($_SERVER['PHP_AUTH_USER']);
            $container->setWebServicesProfile($xuser->mProfileId);

            if ($xuser->mDomainId) {
                $domain_query = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute('SELECT domainid FROM domains WHERE id='.$xuser->mDomainId);
                if ($domain_query->getNumberRows()) {
                    $innomatic = InnomaticContainer::instance('innomaticcontainer');
                    $innomatic->startDomain($domain_query->getFields('domainid'));
                }
            }

            $xprofile = new WebServicesProfile(InnomaticContainer::instance('innomaticcontainer')->getDataAccess(), $container->getWebServicesProfile());
            $container->setWebServicesMethods($xprofile->AvailableMethods());
        } else {
            if (InnomaticContainer::instance('innomaticcontainer')->getConfig()->Value('SecurityAlertOnWrongWebServicesLogin') == '1') {
                require_once('innomatic/security/SecurityManager.php');
                $innomatic_security = new SecurityManager();
                $innomatic_security->SendAlert('Wrong web services login for user '.$_SERVER['PHP_AUTH_USER'].' from remote address '.$_SERVER['REMOTE_ADDR']);
                unset($innomatic_security);
            }
        }

        $structure = array();

        $methods = InnomaticContainer::instance('innomaticcontainer')->getWebServicesMethods();
        while (list (, $tmpdata) = each($methods)) {
            if ($tmpdata['handler'] and $tmpdata['name'] and $tmpdata['function']) {
                // TODO Fixare gestione handler servizi remoti
                if (!defined(strtoupper($tmpdata['handler']).'_XMLRPCMETHOD')) {
                    require_once(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/classes/shared/webservices/'.ucfirst($tmpdata['handler']).'WebServicesHandler.php');
                }

                $structure[$tmpdata['name']]['function'] = $tmpdata['function'];
                if (isset($tmpdata['signature'])) {
                    $structure[$tmpdata['name']]['signature'] = $tmpdata['signature'];
                }
                if (isset($tmpdata['docstring'])) {
                    $structure[$tmpdata['name']]['docstring'] = $tmpdata['docstring'];
                }
            }
        }

        $xs = new XmlRpc_Server($structure);
    }

    public function doPost(WebAppRequest $req, WebAppResponse $res)
    {
        $this->doGet($req, $res);
    }

    public function destroy()
    {
    }

    protected function getRelativePath(WebAppRequest $request)
    {
        $result = $request->getPathInfo();
        require_once('innomatic/io/filesystem/DirectoryUtils.php');
        return DirectoryUtils::normalize(strlen($result) ? $result : '/');
    }

    /**
     * Prefix the context path, our webapp emulator and append the request
     * parameters to the redirection string before calling sendRedirect.
     *
     * @param $request WebAppRequest
     * @param $redirectPath string
     * @return string
     * @access protected
     */
    protected function getURL(WebAppRequest $request, $redirectPath)
    {
        $result = '';

        $container = WebAppContainer::instance('webappcontainer');
        $processor = $container->getProcessor();
        $webAppPath = $request->getUrlPath();

        if (!is_null($webAppPath) && $webAppPath != '/') {
            $result = $request->generateControllerPath($webAppPath, true);
        }

        $result .= '/webservices'.$redirectPath;

        $query = $request->getQueryString();
        if (!is_null($query)) {
            $result .= '?'.$query;
        }

        return $result;
    }
}
