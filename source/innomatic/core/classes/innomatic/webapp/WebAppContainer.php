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
namespace Innomatic\Webapp;

/**
 * @since 5.0
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 * @copyright Copyright 2005-2012 Innoteam Srl
 */
class WebAppContainer extends \Innomatic\Util\Singleton
{
    private $config;
    private $useDefaults;
    private $home;
    private $currentWebApp;
    private $processor;

    public function ___construct()
    {
        $root_home = \Innomatic\Core\RootContainer::instance('\Innomatic\Core\RootContainer')->getHome();
        $this->home = $root_home;
        $this->config = array();
        if (file_exists($root_home.'innomatic/core/conf/webapp.ini')) {
            $this->useDefaults = false;
            $this->config = @parse_ini_file($root_home.'innomatic/core/conf/webapp.ini');
        } else {
            $this->useDefaults = true;
        }
        $this->processor = new WebAppProcessor();
    }

    /**
     * Starts a webapp from its home directory.
     *
     * @param string $home Directory name
     */
    public function startWebApp($home)
    {
           $this->setCurrentWebApp(new WebApp($home));
        $this->processor->process($this->currentWebApp);
    }

    public function getHome()
    {
        return $this->home;
    }

    public function getKey($key)
    {
        return isset($this->config[$key]) ? $this->config[$key] : false;
    }

    public function isKey($key)
    {
        return isset($this->config[$key]);
    }

    public function useDefaults()
    {
        return $this->useDefaults;
    }

    /**
     * Gets the list of the available valid webapps.
     *
     * @return array
     */
    public function &getWebAppsList() {
        $list = array();
        if ($dh = opendir($this->home)) {
            while (($file = readdir($dh)) !== false) {
                if ($file != '.' and $file != '..' and is_dir($this->home.$file)) {
                    if (WebApp::isValid($this->home.$file)) {
                        $list[] = $file;
                    }
                }
            }
            closedir($dh);
        }
        return $list;
    }

    public function setCurrentWebApp(WebApp $wa)
    {
        $this->currentWebApp = $wa;
    }

    /**
     * Gets current webapp object.
     *
     * @return WebApp
     */
    public function getCurrentWebApp()
    {
        return $this->currentWebApp;
    }

    /**
     * Gets current processor object.
     *
     * @return WebAppProcessor
     */
    public function getProcessor()
    {
        return $this->processor;
    }

    /**
     * Creates a new webapp folder, using an optional skeleton in place
     * of the default one.
     *
     * @param string $webappName
     * @param string $skeleton
     * @return bool
     */
    public static function createWebApp($webappName, $skeleton = 'default')
    {
        $home = \Innomatic\Webapp\WebAppContainer::instance('\Innomatic\Webapp\WebAppContainer')->getHome();

        // Strips any path info from the skeleton name.
        $skeleton = basename($skeleton);

        // Checks that the webapp name doesn't contain a malicious path.
        if (\Innomatic\Security\SecurityManager::isAboveBasePath($home.$webappName, $home)) {
            return false;
        }

        // Creates the webapp folder.
        mkdir($home.$webappName);

        // Checks if the given skeleton exits, otherwise uses default one.
        if (!is_dir($home.'innomatic/core/conf/skel/webapps/'.$skeleton.'-skel/')) {
            $skeleton = 'default';
        }

        // Copies the skeleton to the webapp directory.
        return \Innomatic\Io\Filesystem\DirectoryUtils::dirCopy(
              $home.'innomatic/core/conf/skel/webapps/'.$skeleton.'-skel/',
            $home.$webappName.'/');
    }

    /**
     * Completely erases a webapp folder and its content.
     *
     * @param string $webappName
     * @return bool
     */
    public static function eraseWebApp($webappName)
    {
        $home = \Innomatic\Webapp\WebAppContainer::instance('\Innomatic\Webapp\WebAppContainer')->getHome();

        // Cannot remove innomatic webapp.
        if ($webappName == 'innomatic') {
            return false;
        }

        // Checks that the webapp name doesn't contain a malicious path.
        if (\Innomatic\Security\SecurityManager::isAboveBasePath($home.$webappName, $home)) {
            return false;
        }

        // Removes the webapp.
        \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree($home.$webappName);
    }

    /**
     * Overwrites webapp skeleton with a new one.
     * The previous skeleton is not deleted, it is only overwritten.
     *
     * @param string $webappName
     * @param string $skeletonName
     * @return bool
     */
    public static function applyNewSkeleton($webappName, $skeletonName)
    {
        $home = \Innomatic\Webapp\WebAppContainer::instance('\Innomatic\Webapp\WebAppContainer')->getHome();

        // Checks that the webapp name doesn't contain a malicious path.
        if (\Innomatic\Security\SecurityManager::isAboveBasePath($home.$webappName, $home)) {
            return false;
        }

        // Strips any path info from the skeleton name.
        $skeletonName = basename($skeletonName);

        // Checks if the given skeleton exits, otherwise uses default one.
        if (!is_dir($home.'innomatic/core/conf/skel/webapps/'.$skeletonName.'-skel/')) {
            return false;
        }

        // Copies the skeleton to the webapp directory, overwriting previos skeleton.
        return \Innomatic\Io\Filesystem\DirectoryUtils::dirCopy(
              $home.'innomatic/core/conf/skel/webapps/'.$skeletonName.'-skel/',
            $home.$webappName.'/');
    }
}
