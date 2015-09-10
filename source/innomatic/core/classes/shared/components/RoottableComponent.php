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
 * Roottable component handler.
 */
class RoottableComponent extends \Innomatic\Application\ApplicationComponent
{
    public function __construct($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }
    public static function getType()
    {
        return 'roottable';
    }
    public static function getPriority()
    {
        return 150;
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
            $params['file'] = $this->basedir . '/core/db/' . $params['file'];
            if (@copy($params['file'], $this->container->getHome() . 'core/db/' . basename($params['file']))) {
                @chmod($this->container->getHome() . 'core/db/' . basename($params['file']), 0644);
                $xmldb = new \Innomatic\Dataaccess\DataAccessXmlTable($this->rootda, \Innomatic\Dataaccess\DataAccessXmlTable::SQL_CREATE);
                $xmldb->load_deffile($this->container->getHome() . 'core/db/' . basename($params['file']));
                if ($this->rootda->execute($xmldb->getSQL())) {
                    $result = true;
                } else
                    $this->mLog->logEvent('innomatic.roottablecomponent.roottablecomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to create root table from ' . basename($params['file']) . ' table file', \Innomatic\Logging\Logger::ERROR);
                $xmldb->free();
            } else
                $this->mLog->logEvent('innomatic.roottablecomponent.roottablecomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to copy root table file ' . $params['file'] . ' to destination ' . $this->container->getHome() . 'core/db/' . basename($params['file']), \Innomatic\Logging\Logger::ERROR);
        } else
            $this->mLog->logEvent('innomatic.roottablecomponent.roottablecomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty table file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function doUninstallAction($params)
    {
        $result = false;
        if (strlen($params['file'])) {
            $xmldb = new \Innomatic\Dataaccess\DataAccessXmlTable($this->rootda, \Innomatic\Dataaccess\DataAccessXmlTable::SQL_DROP);
            $xmldb->load_deffile($this->container->getHome() . 'core/db/' . basename($params['file']));
            if ($this->rootda->execute($xmldb->getSQL())) {
                if (@unlink($this->container->getHome() . 'core/db/' . basename($params['file']))) {
                    $result = true;
                } else
                    $this->mLog->logEvent('innomatic.roottablecomponent.roottablecomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to remove root table file ' . $this->container->getHome() . 'core/db/' . basename($params['file']), \Innomatic\Logging\Logger::ERROR);
            } else
                $this->mLog->logEvent('innomatic.roottablecomponent.roottablecomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to drop root table from ' . basename($params['file']) . ' table file', \Innomatic\Logging\Logger::ERROR);
            $xmldb->free();
        } else
            $this->mLog->logEvent('innomatic.roottablecomponent.roottablecomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty table file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function doUpdateAction($params)
    {
        $result = true;
        if (strlen($params['file'])) {
            $params['file'] = $this->basedir . '/core/db/' . $params['file'];
            if (file_exists($this->container->getHome() . 'core/db/' . basename($params['file']) . '.old'))
                @copy($this->container->getHome() . 'core/db/' . basename($params['file']) . '.old', $this->container->getHome() . 'core/db/' . basename($params['file']) . '.old2');
            @copy($this->container->getHome() . 'core/db/' . basename($params['file']), $this->container->getHome() . 'core/db/' . basename($params['file']) . '.old');
            $xml_upd = new \Innomatic\Dataaccess\DataAccessXmlTableUpdater($this->rootda, $this->container->getHome() . 'core/db/' . basename($params['file']), $params['file']);
            $xml_upd->CheckDiffs();
            $old_columns = $xml_upd->getOldColumns();
            if (is_array($old_columns)) {
                while (list (, $column) = each($old_columns)) {
                    $upd_data['tablename'] = $params['name'];
                    $upd_data['column'] = $column;
                    $this->rootda->RemoveColumn($upd_data);
                }
            }
            $new_columns = $xml_upd->getNewColumns();
            if (is_array($new_columns)) {
                while (list (, $column) = each($new_columns)) {
                    $upd_data['tablename'] = $params['name'];
                    $upd_data['columnformat'] = $column;
                    $this->rootda->AddColumn($upd_data);
                }
            }
            if (@copy($params['file'], $this->container->getHome() . 'core/db/' . basename($params['file']))) {
                @chmod($this->container->getHome() . 'core/db/' . basename($params['file']), 0644);
                $result = true;
            } else
                $this->mLog->logEvent('innomatic.roottablecomponent.roottablecomponent.doupdateaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to copy ' . $params['file'] . ' to destination ' . $this->container->getHome() . 'core/db/' . basename($params['file']), \Innomatic\Logging\Logger::ERROR);
        } else
            $this->mLog->logEvent('innomatic.roottablecomponent.roottablecomponent.doupdateaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty table file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
}
