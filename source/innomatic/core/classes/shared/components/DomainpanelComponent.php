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
 * Domainpanel component handler.
 */
class DomainpanelComponent extends \Innomatic\Application\ApplicationComponent
{
    public function __construct($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }
    public static function getType()
    {
        return 'domainpanel';
    }
    public static function getPriority()
    {
        return 0;
    }
    public static function getIsDomain()
    {
        return true;
    }
    public static function getIsOverridable()
    {
        return false;
    }
    public function doInstallAction($params)
    {
        $result = false;
        if (strlen($params['name'])) {
            $params['name'] = $this->basedir . '/domain/' . $params['name'];
            if (is_dir($params['name'] . '-panel')) {
                if (\Innomatic\Io\Filesystem\DirectoryUtils::dirCopy($params['name'] . '-panel/', $this->container->getHome() . 'domain/' . basename($params['name']) . '-panel/')) {
                    $result = true;
                } else
                    $this->mLog->logEvent('innomatic.domainpanel_component_domainpanelcomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to copy ' . $params['name'] . '-panel into destination ' . $this->container->getHome() . 'domain/' . basename($params['name']) . '-panel', \Innomatic\Logging\Logger::ERROR);
            } else
                if (file_exists($params['name'] . '.php')) {
                    if (@copy($params['name'] . '.php', $this->container->getHome() . 'domain/' . basename($params['name']) . '.php')) {
                        @chmod($this->container->getHome() . 'domain/' . basename($params['name']) . '.php', 0644);
                        $result = true;
                    } else
                        $this->mLog->logEvent('innomatic.domainpanel_component_domainpanelcomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to copy ' . $params['name'] . ' into destination ' . $this->container->getHome() . 'domain/' . basename($params['name']), \Innomatic\Logging\Logger::ERROR);
                }
        }
        if (! isset($params['icon']))
            $params['icon'] = '';
        if (strlen($params['icon'])) {
            $params['icon'] = $this->basedir . '/domain/' . $params['icon'];
            if (@copy($params['icon'], $this->container->getHome() . 'domain/' . basename($params['icon']))) {
                @chmod($this->container->getHome() . 'domain/' . basename($params['icon']), 0644);
                $result = true;
            } else
                $this->mLog->logEvent('innomatic.domainpanel_component_domainpanelcomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to copy ' . $params['icon'] . ' into destination ' . $this->container->getHome() . 'domain/' . basename($params['icon']), \Innomatic\Logging\Logger::ERROR);
        }
        return $result;
    }
    public function doUninstallAction($params)
    {
        $result = false;
        if (strlen($params['name'])) {
            // Removes the new style application
            if (is_dir($this->container->getHome() . 'domain/' . basename($params['name']) . '-panel')) {
                if (\Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree($this->container->getHome() . 'domain/' . basename($params['name']) . '-panel')) {
                    $result = true;
                } else
                    $this->mLog->logEvent('innomatic.domainpanel_component_domainpanelcomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to remove ' . $this->container->getHome() . 'domain/' . basename($params['name']) . '-panel', \Innomatic\Logging\Logger::ERROR);
            }
            // Removes the old style application
            if (file_exists($this->container->getHome() . 'domain/' . basename($params['name']) . '.php')) {
                if (@unlink($this->container->getHome() . 'domain/' . basename($params['name']) . '.php')) {
                    $result = true;
                } else
                    $this->mLog->logEvent('innomatic.domainpanel_component_domainpanelcomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to remove ' . $this->container->getHome() . 'domain/' . basename($params['name']), \Innomatic\Logging\Logger::ERROR);
            }
        }
        if (! isset($params['icon']))
            $params['icon'] = '';
        if (strlen($params['icon'])) {
            if (@unlink($this->container->getHome() . 'domain/' . basename($params['icon']))) {
                $result = true;
            } else
                $this->mLog->logEvent('innomatic.domainpanel_component_domainpanelcomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to remove ' . $this->container->getHome() . 'domain/' . basename($params['icon']), \Innomatic\Logging\Logger::ERROR);
        }
        return $result;
    }
    
    public function doUpdateAction($params)
    {
        return $this->doInstallAction($params);
    }
    
    public function doEnableDomainAction($domainid, $params)
    {
        $result = false;
        if (! isset($params['icon'])) {
            $params['icon'] = '';
        }
        if (! isset($params['themeicon'])) {
            $params['themeicon'] = '';
        }
        if (! isset($params['themeicontype'])) {
            $params['themeicontype'] = '';
        }
        // If the page has no group, puts it in the generic tools group
        //
        if (empty($params['category']))
            $params['category'] = 'tools';
        $grquery = $this->domainda->execute('SELECT * FROM domain_panels_groups WHERE name = ' . $this->domainda->formatText($params['category']));
        if ($grquery->getNumberRows() > 0)
            $grdata = $grquery->getFields();
        if (strlen($params['catalog']) > 0) {
            $ins = 'INSERT INTO domain_panels VALUES (' . $this->domainda->getNextSequenceValue('domain_panels_id_seq') . ',';
            $ins .= $this->domainda->formatText($grdata['id']) . ',';
            $ins .= $this->domainda->formatText($params['name']) . ',';
            $ins .= $this->domainda->formatText($params['icon']) . ',';
            $ins .= $this->domainda->formatText($params['catalog']) . ',';
            $ins .= $this->domainda->formatText($params['themeicon']) . ',';
            $ins .= $this->domainda->formatText($params['themeicontype']) . ',';
            $ins .= $this->domainda->formatText((isset($params['hidden']) and $params['hidden'] == 'true') ? $this->domainda->fmttrue : $this->domainda->fmtfalse) . ')';
            $result = $this->domainda->execute($ins);
        } else {
            $result = true;
        }
        return $result;
    }
    public function doDisableDomainAction($domainid, $params)
    {
        $result = false;
        if (! isset($params['icon']))
            $params['icon'] = '';
        if (! isset($params['themeicon']))
            $params['themeicon'] = '';
        if (! isset($params['themeicontype']))
            $params['themeicontype'] = '';
        if (! empty($params['name'])) {
            if (! empty($params['catalog'])) {
                $tmpquery = $this->domainda->execute('SELECT id FROM domain_panels where name = ' . $this->domainda->formatText($params['name']));
                if ($tmpquery->getNumberRows() > 0) {
                    $tmpperm = new \Innomatic\Desktop\Auth\DesktopPanelAuthorizator($this->domainda, 0);
                    $tmpperm->RemoveNodes($tmpquery->getFields('id'), 'page');
                    $result = $this->domainda->execute('delete from domain_panels where name = ' . $this->domainda->formatText($params['name']));
                    if (! $result) {
                        $this->mLog->logEvent('innomatic.domainpanelcomponent.domainpanelcomponent.dodisabledomainaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to remove desktop panel from domain_panels table', \Innomatic\Logging\Logger::ERROR);
                    }
                } else {
                    $result = true;
                }
            } else {
                $result = true;
            }
        } else {
            $this->mLog->logEvent('innomatic.domainpanelcomponent.domainpanelcomponent.dodisabledomainaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Name attribute of desktop panel component is empty', \Innomatic\Logging\Logger::ERROR);
        }
        return $result;
    }
    public function doUpdateDomainAction($domainid, $params)
    {
        $result = false;
        if (empty($params['category']))
            $params['category'] = 'tools';
        if (! isset($params['themeicon']))
            $params['themeicon'] = '';
        if (! isset($params['themeicontype']))
            $params['themeicontype'] = '';
        if ($grquery = $this->domainda->execute('SELECT * FROM domain_panels_groups WHERE name = ' . $this->domainda->formatText($params['category']))) {
            if ($grquery->getNumberRows() > 0) {
                $grdata = $grquery->getFields();
                $check_query = $this->domainda->execute(
                    'SELECT id
                    FROM domain_panels
                    WHERE name=' . $this->domainda->formatText($params['name']));
                if ($check_query->getNumberRows()) {
                    if ($this->domainda->execute(
                        'UPDATE domain_panels
                        SET groupid=' . $grdata['id'] . ',
                        hidden='.$this->domainda->formatText((isset($params['hidden']) and $params['hidden'] == 'true') ? $this->domainda->fmttrue : $this->domainda->fmtfalse).',
                        catalog=' . $this->domainda->formatText($params['catalog']) . ',
                        themeicon=' . $this->domainda->formatText($params['themeicon']) . ',
                        themeicontype=' . $this->domainda->formatText($params['themeicontype']) . '
                        WHERE name=' . $this->domainda->formatText($params['name']))) {
                        $result = true;
                    }
                } else {
                    $result = $this->doEnableDomainAction($domainid, $params);
                }
                // !!! nodes
            } else
                $this->mLog->logEvent('innomatic.domainpanelcomponent.domainpanelcomponent.doupdatedomainaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to find a suitable admin group for desktop application ' . $params['name'], \Innomatic\Logging\Logger::ERROR);
        } else
            $this->mLog->logEvent('innomatic.domainpanelcomponent.domainpanelcomponent.doupdatedomainaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to execute select query for desktop application ' . $params['name'], \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
}
