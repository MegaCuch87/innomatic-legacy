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

class DomainsPanelActions extends \Innomatic\Desktop\Panel\PanelActions
{
    public $localeCatalog;
    public $status;

    public function __construct(\Innomatic\Desktop\Panel\PanelController $controller)
    {
        parent::__construct($controller);
    }

    public function beginHelper()
    {
        $this->localeCatalog = new \Innomatic\Locale\LocaleCatalog(
            'innomatic::root_domains',
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLanguage()
        );
    }

    public function endHelper()
    {
    }

    public function executeCreatedomain($eventData)
    {
        $domain = new \Innomatic\Domain\Domain(\Innomatic\Core\InnomaticContainer::instance(
            '\Innomatic\Core\InnomaticContainer'
        )->getDataAccess(), 0, null);

        $domainData['domainid'] = $eventData['domainid'];
        $domainData['domainname'] = $eventData['domainname'];
        $domainData['domainpassword'] = $eventData['domainpassword'];
        $domainData['webappurl'] = $eventData['webappurl'];
        $domainData['domaindaname'] = $eventData['domaindaname'];
        $domainData['dataaccesshost'] = $eventData['dataaccesshost'];
        $domainData['dataaccessport'] = $eventData['dataaccessport'];
        $domainData['dataaccessuser'] = $eventData['dataaccessuser'];
        $domainData['dataaccesspassword'] = $eventData['dataaccesspassword'];
        $domainData['dataaccesstype'] = $eventData['dataaccesstype'];
        $domainData['webappskeleton'] = $eventData['webappskeleton'];
        $domainData['maxusers'] = $eventData['maxusers'];

        if ($domain->Create($domainData, $eventData['createdomainda'] == 'on' ? true : false)) {
            $this->status = $this->localeCatalog->getStr('domaincreated_status');
        } else {
            $this->status = $this->localeCatalog->getStr('domainnotcreated_status');
        }
        $this->setChanged();
        $this->notifyObservers('status');
    }

    public function executeUpdatedomain($eventData)
    {
        $domainQuery = \Innomatic\Core\InnomaticContainer::instance(
            '\Innomatic\Core\InnomaticContainer'
        )->getDataAccess()->execute(
            'SELECT domainid FROM domains WHERE id='
            . $eventData['domainserial']
        );

        $null = null;
        $domain = new \Innomatic\Domain\Domain(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
            $domainQuery->getFields('domainid'),
            $null
        );

        // Holds previous domain webapp skeleton information before updating.
        $currentWebappSkeleton = $domain->getWebappSkeleton();

        $domainData['domainserial'] = $eventData['domainserial'];
        $domainData['domainname'] = $eventData['domainname'];
        $domainData['domainpassword'] = $eventData['domainpassword'];
        $domainData['webappurl'] = $eventData['webappurl'];
        $domainData['dataaccesstype'] = $eventData['dataaccesstype'];
        $domainData['domaindaname'] = $eventData['domaindaname'];
        $domainData['dataaccesshost'] = $eventData['dataaccesshost'];
        $domainData['dataaccessport'] = $eventData['dataaccessport'];
        $domainData['dataaccessuser'] = $eventData['dataaccessuser'];
        $domainData['dataaccesspassword'] = $eventData['dataaccesspassword'];
        $domainData['dataaccessport'] = $eventData['dataaccessport'];
        $domainData['dataaccesstype'] = $eventData['dataaccesstype'];

        if ($domain->edit($domainData)) {
            // Changes max users limit.
            $domain->setMaxUsers($eventData['maxusers']);

            // Applies new webapp skeleton if changed.
            if ($eventData['webappskeleton'] != $currentWebappSkeleton) {
                $domain->setWebappSkeleton($eventData['webappskeleton']);
            }

            $this->status = $this->localeCatalog->getStr(
                'domainupdated_status'
            );
        } else {
            $this->status = $this->localeCatalog->getStr(
                'domainnotupdated_status'
            );
        }
        $this->setChanged();
        $this->notifyObservers('status');
    }

    public function executeEditdomainnotes($eventData)
    {
        $null = null;
        $domain = new \Innomatic\Domain\Domain(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
            $eventData['domainid'],
            $null
        );

        $domain->setNotes($eventData['notes']);

        $this->status = $this->localeCatalog->getStr('notes_set.status');
        $this->setChanged();
        $this->notifyObservers('status');
    }

    public function executeRemovedomain($eventData)
    {
        $null = null;
        $domain = new \Innomatic\Domain\Domain(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
            $eventData['domainid'],
            $null
        );

        if ($domain->Remove()) {
            $this->status = $this->localeCatalog->getStr(
                'domainremoved_status'
            );
        } else {
            $this->status = $this->localeCatalog->getStr(
                'domainnotremoved_status'
            );
        }
        $this->setChanged();
        $this->notifyObservers('status');
    }

    public function executeEnabledomain($eventData)
    {
        $null = null;
        $domain = new \Innomatic\Domain\Domain(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
            $eventData['domainid'],
            $null
        );

        if ($domain->enable()) {
            $this->status = $this->localeCatalog->getStr('domainenabled_status');
        } else {
            $this->status = $this->localeCatalog->getStr(
                'domainnotenabled_status'
            );
        }
        $this->setChanged();
        $this->notifyObservers('status');
    }

    public function executeDisabledomain($eventData)
    {
        $null = null;
        $domain = new \Innomatic\Domain\Domain(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
            $eventData['domainid'],
            $null
        );

        if ($domain->disable()) {
            $this->status = $this->localeCatalog->getStr(
                'domaindisabled_status'
            );
        } else {
            $this->status = $this->localeCatalog->getStr(
                'domainnotdisabled_status'
            );
        }
        $this->setChanged();
        $this->notifyObservers('status');
    }

    public function executeActivateapplication($eventData)
    {
        $domainQuery = \Innomatic\Core\InnomaticContainer::instance(
            '\Innomatic\Core\InnomaticContainer'
        )->getDataAccess()->execute(
            'SELECT domainid FROM domains WHERE id = '
            . $eventData['domainid']
        );

        if ($domainQuery) {
            $domainData = $domainQuery->getFields();

            $null = null;
            $domain = new \Innomatic\Domain\Domain(
                \Innomatic\Core\InnomaticContainer::instance(
                    '\Innomatic\Core\InnomaticContainer'
                )->getDataAccess(),
                $domainData['domainid'],
                $null
            );
            if (!$domain->enableApplication($eventData['appid'])) {
                $unmetDeps = $domain->getLastActionUnmetDeps();

                if (count($unmetDeps)) {
                    while (list (, $dep) = each($unmetDeps))
                    $unmetDepsStr.= ' '.$dep;

                    $this->status .= $this->localeCatalog->getStr('modnotenabled_status').' ';
                    $this->status .= $this->localeCatalog->getStr('unmetdeps_status').$unmetDepsStr.'.';
                }

                $unmetSuggestions = $domain->getLastActionUnmetSuggs();

                if (count($unmetSuggestions)) {
                    while (list (, $sugg) = each($unmetSuggestions))
                    $unmetSuggestionsString.= ' '.$sugg.$this->status .= $this->localeCatalog->getStr(
                        'unmetsuggs_status'
                    ).$unmetSuggestionsString.'.';
                }
            } else
            $this->status .= $this->localeCatalog->getStr('modenabled_status');
        }
        $this->setChanged();
        $this->notifyObservers('status');
    }

    public function executeActivateallapplications($eventData)
    {
        $domainQuery = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->execute(
            'SELECT domainid FROM domains WHERE id = '.$eventData['domainid']
        );

        if ($domainQuery) {
            $domainData = $domainQuery->getFields();

            $domain = new \Innomatic\Domain\Domain(
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
                $domainData['domainid'],
                ''
            );
            if ($domain->enableAllApplications()) {
                $this->status = $this->localeCatalog->getStr('applications_enabled.status');
            }
        } else {
            $this->status = $this->localeCatalog->getStr('applications_not_enabled.status');
        }
        $this->setChanged();
        $this->notifyObservers('status');
    }

    public function executeDeactivateallapplications($eventData)
    {
        $domainQuery = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->execute(
            'SELECT domainid FROM domains WHERE id = '.$eventData['domainid']
        );

        if ($domainQuery) {
            $domainData = $domainQuery->getFields();

            $domain = new \Innomatic\Domain\Domain(
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
                $domainData['domainid'],
                ''
            );
            if ($domain->disableAllApplications(false))
            $this->status = $this->localeCatalog->getStr('applications_disabled.status');
        } else
        $this->status = $this->localeCatalog->getStr('applications_not_disabled.status');
        $this->setChanged();
        $this->notifyObservers('status');
    }

    public function executeEnableoption($eventData)
    {
        $application = new \Innomatic\Application\Application(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
            $eventData['applicationid']
        );

        $application->enableOption($eventData['option'], $eventData['domainid']);

        $this->status = $this->localeCatalog->getStr('option_enabled.status');
        $this->setChanged();
        $this->notifyObservers('status');
    }

    public function executeDisableoption($eventData)
    {
        $application = new \Innomatic\Application\Application(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
            $eventData['applicationid']
        );
        $application->disableOption($eventData['option'], $eventData['domainid']);

        $this->status = $this->localeCatalog->getStr('option_disabled.status');
        $this->setChanged();
        $this->notifyObservers('status');
    }

    public function executeDeactivateapplication($eventData)
    {
        $domainQuery = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->execute(
            'SELECT domainid FROM domains WHERE id = '.$eventData['domainid']
        );

        if ($domainQuery) {
            $domainData = $domainQuery->getFields();

            $null = null;
            $domain = new \Innomatic\Domain\Domain(
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
                $domainData['domainid'],
                $null
            );
            if (!$domain->disableApplication($eventData['appid'])) {
                $unmetDeps = $domain->getLastActionUnmetDeps();

                if (count($unmetDeps)) {
                    while (list (, $dep) = each($unmetDeps))
                    $unmetDepsStr.= ' '.$dep;

                    $this->status.= $this->localeCatalog->getStr('modnotdisabled_status').' ';
                    $this->status.= $this->localeCatalog->getStr('disunmetdeps_status').$unmetDepsStr.'.';
                }
            } else
            $this->status.= $this->localeCatalog->getStr('moddisabled_status');
        }
        $this->setChanged();
        $this->notifyObservers('status');
    }

    public function executeCleandomainlog($eventData)
    {
        $tempLog = new \Innomatic\Logging\Logger(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome()
            .'core/domains/'.$eventData['domainid'].'/log/domain.log'
        );

        if ($tempLog->cleanLog()) {
            $this->status = $this->localeCatalog->getStr('logcleaned_status');
        } else {
            $this->status = $this->localeCatalog->getStr('lognotcleaned_status');
        }
        $this->setChanged();
        $this->notifyObservers('status');
    }

    public function executeCleandataaccesslog($eventData)
    {
        $query = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->execute(
            'SELECT domainid FROM domains WHERE id='.$eventData['domainid']
        );

        $tempLog = new \Innomatic\Logging\Logger(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome()
            .'core/domains/'.$query->getFields('domainid').'/log/dataaccess.log'
        );

        if ($tempLog->cleanLog()) {
            $this->status = $this->localeCatalog->getStr('logcleaned_status');
        } else {
            $this->status = $this->localeCatalog->getStr('lognotcleaned_status');
        }
        $this->setChanged();
        $this->notifyObservers('status');
    }
}
