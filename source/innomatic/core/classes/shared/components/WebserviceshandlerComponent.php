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
namespace Shared\Components;

/**
 * Webserviceshandler component handler.
 */
class WebserviceshandlerComponent extends \Innomatic\Application\ApplicationComponent
{
    public function __construct($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }
    public static function getType()
    {
        return 'webserviceshandler';
    }
    public static function getPriority()
    {
        return 0;
    }
    public static function getIsDomain()
    {
        return false;
    }
    public static function getIsOverridable()
    {
        return false;
    }
    public function doInstallAction($params)
    {
        $result = false;
        if (strlen($params['file'])) {
            $params['file'] = $this->basedir . '/core/classes/shared/webservices/' . $params['file'];
            if (@copy($params['file'], $this->container->getHome() . 'core/classes/shared/webservices/' . basename($params['file']))) {
                @chmod($this->container->getHome() . 'core/classes/shared/webservices/' . basename($params['file']), 0644);
                $result = true;
            } else
                $this->mLog->logEvent('shared.components.webserviceshandlercomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to copy handler file', \Innomatic\Logging\Logger::ERROR);
        } else
            $this->mLog->logEvent('shared.components.webserviceshandlercomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty xmlrpc handler file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function doUninstallAction($params)
    {
        $result = false;
        if (strlen($params['file'])) {
            if (@unlink($this->container->getHome() . 'core/classes/shared/webservices/' . basename($params['file']))) {
                // :NOTE: 20020614 teg - Excessive delete.
                // It deletes all permissions, not only the ones referring to this handler.
                $this->rootda->execute('DELETE FROM webservices_permissions WHERE application=' . $this->rootda->formatText($this->appname));
                $result = true;
            } else
                $this->mLog->logEvent('shared.components.webserviceshandlercomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to remove ' . $this->container->getHome() . 'core/classes/shared/webservices/' . basename($params['file']), \Innomatic\Logging\Logger::ERROR);
        } else
            $this->mLog->logEvent('shared.components.webserviceshandlercomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty xmlrpc handler file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function doUpdateAction($params)
    {
        return $this->doInstallAction($params);
    }
}
