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
namespace Innomatic\Application;

/**
 * This class provides some helper methods for handling AppCentral operations
 * like retrieving list of all the available applications, updating
 * applications and so on.
 *
 * @since 7.0.0 introduced
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 */
class AppCentralHelper
{
    /* public updateApplications() {{{ */
    /**
     * Updates all the installed applications fetching new application versions
     * found in AppCentral repositories.
     *
     * @access public
     * @return array List of updated applications with their versions.
     */
    public function updateApplications()
    {
    }
    /* }}} */

    /* public getUpdatedApplications() {{{ */
    /**
     * Gets a list of the installed applications for which a new version is
     * available in AppCentral repositories.
     *
     * This method compares the installed applications with the ones found in
     * AppCentral repositories.
     *
     * @access public
     * @return array List of the available updated applications with their versions.
     */
    public function getUpdatedApplications()
    {
    }
    /* }}} */

    /* public getAvailableApplications() {{{ */
    /**
     * Gets a list of all the available applications in the registered
     * AppCentral repositories.
     *
     * @param bool   $refresh     True if the cache must be refreshed.
     * @access public
     * @return array
     */
    public function getAvailableApplications($refresh = false)
    {
        $apps = array();

        // Fetch the list of the registered AppCentral servers.
        $dataAccess = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')
            ->getDataAccess();

        $serverList = $dataAccess->execute(
            "SELECT id FROM applications_repositories"
        );

        while (!$serverList->eof) {
            $serverId = $serverList->getFields('id');
            $server = new AppCentralRemoteServer($serverId);

            // Fetch the list of the available repositories, refreshing the cache.
            $repositories = $server->listAvailableRepositories($refresh);

            foreach ($repositories as $repoId => $repoData) {
                // Fetch the list of the available repository applications.
                $repoApplications = $server->listAvailableApplications($repoId, $refresh);

                foreach ($repoApplications as $appId => $appData) {
                    // Fetch the list of the available application versions.
                    $versions = $server->listAvailableApplicationVersions(
                        $repoId,
                        $appId,
                        $refresh
                    );

                    // Add the application version to the applications list.
                    foreach ($versions as $version => $versionData) {
                        $apps[$appData['appid']][$version][] = [
                            'server' => $serverId,
                            'repository' => $repoId,
                            'appid' => $appId
                        ];
                    }
                }
            }
            $serverList->moveNext();
        }

        return $apps;
    }
    /* }}} */

    /* public findApplication($application) {{{ */
    /**
     * Checks if the given application is available in the registered
     * AppCentral servers.
     *
     * @param string $application Application name.
     * @param string $version     Optional minimun version number.
     * @param bool   $refresh     True if the cache must be refreshed.
     * @access public
     * @return mixed false if the applications has not been found or an array of the servers
     * containing the application.
     */
    public function findApplication($application, $version = null, $refresh = false)
    {
        // Get the list of the available applications.
        $apps = $this->getAvailableApplications($refresh);

        // Check if the application has been found.
        if (!isset($apps[$application])) {
            return false;
        }

        $found = $apps[$application];

        // If a minimum version number has been given, remove the application
        // versions under the latter.
        if (!is_null($version)) {
            foreach ($found as $appVersion => $appData) {
                $compare = ApplicationDependencies::compareVersionNumbers($appVersion, $version);
                if ($compare == ApplicationDependencies::VERSIONCOMPARE_LESS) {
                    unset($found[$appVersion]);
                }
            }
        }

        if (!count($found)) {
            return false;
        }

        return $found;
    }
    /* }}} */

    /* public resolveDependencies($dependencies) {{{ */
    /**
     * Automatically resolves a list of application dependencies and installs
     * the found ones.
     *
     * The method automatically tries to retrieve applications from other
     * available servers when is unable to retrieve if from the first one.
     *
     * @param string $dependencies A list of dependencies as returned by \Innomatic\Application\ApplicationDependencies::checkApplicationDependencies()
     * @access public
     * @return array An array of resolved and missing dependencies.
     */
    public function resolveDependencies($dependencies)
    {
        if (!is_array($dependencies)) {
            return false;
        }

        // Build the list of the found and missing dependencies.
        $found = $missing = [];

        foreach ($dependencies as $dependency) {
            // Extract application and version.
            list($application, $version) = sscanf(str_replace(['[', ']'], ' ', $dependency), "%s %s");

            // Check if the application is available in some AppCentral servers.
            $versions = $this->findApplication($application, strlen($version) ? $version : null);

            if ($versions != false) {
                // Application found.

                // Extract the latest version.
                $versionList = [];
                foreach ($versions as $versionId => $versionData) {
                    $versionList[] = $versionId;
                }

                $lastVersion = $this->getLastVersion($versionList);

                // Add the latest application version to the resolved dependencies list.
                $found[$application] = ['version' => $lastVersion, 'serverinfo' => $versions[$lastVersion]];
            } else {
                // Application not found.

                // Add the application to the missing list.
                $missing[$application] = $version;
            }
        }

        // Retrieve and install the found applications.
        foreach ($found as $appName => $appData) {
            $result = false;

            // Check AppCentral servers until it can retrieve and install the application.
            foreach ($appData['serverinfo'] as $serverInfo) {
                $server = new AppCentralRemoteServer($serverInfo['server']);
                $result = $server->retrieveApplication($serverInfo['repository'], $serverInfo['appid']);
                if ($result == true) {
                    break 1;
                }
            }

            if ($result == false) {
                // Unable to retrieve and install the application.
                // Move it from the found list to the missing ones.
                $missing[$appName] = $appData['version'];
                unset($found[$appName]);
            }
        }

        return ['found' => $found, 'missing' => $missing];
    }
    /* }}} */

    /* public updateApplicationsList(\Closure $item = null, \Closure $result = null) {{{ */
    /**
     * Refreshes the list of the available repositories and applications from
     * the registered AppCentral servers.
     *
     * @param \Closure $item Optional callback that is called before refreshing the applications list of a repository.
     * @param \Closure $result Optional callback that is called after refreshing each repository.
     * @access public
     * @return void
     */
    public function updateApplicationsList(\Closure $item = null, \Closure $result = null)
    {
        // Fetch the list of the registered AppCentral servers.
        $dataAccess = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')
            ->getDataAccess();

        $serverList = $dataAccess->execute(
            "SELECT id FROM applications_repositories"
        );

        // Refresh each AppCentral server.
        while (!$serverList->eof) {
            $serverId = $serverList->getFields('id');
            $server = new AppCentralRemoteServer($serverId);

            // Fetch the list of the available repositories, refreshing the cache.
            $repositories = $server->listAvailableRepositories(true);

            foreach ($repositories as $repoId => $repoData) {
                // Call the repository refresh data callback.
                if (is_callable($item)) {
                    $item($serverId, $server->getAccount()->getName(), $repoId, $repoData);
                }

                // Fetch the list of the available repository applications.
                $repoApplications = $server->listAvailableApplications($repoId, true);

                foreach ($repoApplications as $appId => $appData) {
                    // Fetch the list of the available application versions.
                    $versions = $server->listAvailableApplicationVersions(
                        $repoId,
                        $appId,
                        true
                    );
                }

                // Call the repository refresh result callback.
                if (is_callable($result)) {
                    $result(true);
                }
            }
            $serverList->moveNext();
        }
    }
    /* }}} */

    /* public getLastVersion($versionList) {{{ */
    /**
     * Find the highest version among a list of version numbers.
     *
     * @param array $versionList Version list.
     * @access public
     * @return string Highest version number found.
     */
    public function getLastVersion($versionList) {
        if (!is_array($versionList)) {
            return false;
        }

        $lastVersion = '0';

        foreach ($versionList as $version) {
            $compare = ApplicationDependencies::compareVersionNumbers($version, $lastVersion);
            if ($compare == ApplicationDependencies::VERSIONCOMPARE_EQUAL or $compare == ApplicationDependencies::VERSIONCOMPARE_MORE) {
                $lastVersion = $version;
            }
        }

        return $lastVersion;
    }
    /* }}} */
}
