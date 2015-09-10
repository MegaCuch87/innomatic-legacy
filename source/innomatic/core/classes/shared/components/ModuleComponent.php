<?php
/**
 * Innomatic
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  2014 Innoteam Srl
 * @license    http://www.innomatic.io/license/ New BSD License
 * @link       http://www.innomatic.io
 * @since      Class available since Release 5.1
 */
namespace Shared\Components;

/**
 * Module component handler.
 *
 * @copyright  2014 Innoteam Srl
 * @license    http://www.innomatic.io/license/ New BSD License
 * @link       http://www.innomatic.io
 * @since      Class available since Release 5.1
 */
class ModuleComponent extends \Innomatic\Application\ApplicationComponent
{
    public function __construct($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
        // Checks if the classes folder exists
        if (! is_dir($this->container->getHome() . 'core/modules/')) {
            \Innomatic\Io\Filesystem\DirectoryUtils::mktree($this->container->getHome() . 'core/modules/', 0755);
            clearstatcache();
        }
    }

    public static function getType()
    {
        return 'module';
    }

    public static function getPriority()
    {
        return 100;
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
        if (! strlen($params['name'])) {
            return false;
        }

        $deployer = new \Innomatic\Module\Deploy\ModuleDeployer();
        return $deployer->deploy($this->basedir . '/core/modules/' . $params['name']);
    }

    public function doUninstallAction($params)
    {
        $deployer = new \Innomatic\Module\Deploy\ModuleDeployer();
        return $deployer->undeploy($params['name']);
    }

    public function doUpdateAction($params)
    {
        $deployer = new \Innomatic\Module\Deploy\ModuleDeployer();
        return $deployer->redeploy($this->basedir . '/core/modules/' . $params['name']);
    }
}
