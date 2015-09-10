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
namespace Innomatic\Scripts;

/**
 * This class provides methods to add, get and remove pending actions to be
 * executed by a cronjob script.
 *
 * Innomatic does not provide a standard cronjob script to process pending
 * actions. Each application should manage its own pending actions with a
 * dedicated cronjob script or a maintenance task (see
 * \Innomatic\Maintenance\MaintenanceTask and
 * \Shared\Components\MaintenancetaskComponent).
 *
 * When processing pending actions, the application must remove the related
 * entries as soon as the action are positively completed using the
 * PendingActionsUtils::removeBy*() methods.
 *
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 * @copyright Copyright 2014 Innoteam Srl
 * @since 6.4.0
 */
class PendingActionsUtils
{
    /**
     * Adds a new pending action.
     *
     * @param string $application The application identifier.
     * @param integer $domain_id The optional domain id by which the action should be executed.
     * @param integer $user_id The optional user id by which the action should be executed.
     * @param string $action The application defined action name.
     * @param array $parameters An optional array of parameters that will be serialized.
     */
    public static function add($application, $domain_id, $user_id, $action, $parameters = array())
    {
        $root_da = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess();

        $id = $root_da->getNextSequenceValue('pending_actions_id_seq');

        // Insert the pending action in the database
        return $root_da->execute(
            'INSERT INTO pending_actions VALUES('.
            $id.','.
            $root_da->formatText($application).','.
            $root_da->formatText((int)$domain_id).','.
            $root_da->formatText((int)$user_id).','.
            $root_da->formatText(time()).','.
            $root_da->formatText($action).','.
            $root_da->formatText(serialize($parameters)).
            ')'
        );
    }

    /**
     * Fetches a list of pending actions for a given application and action
     * type, ordered by creation time.
     *
     * @param string $application Required application name.
     * @param string $action Required action name.
     */
    public static function get($application, $action)
    {
        $root_da = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess();

        // Extract the pending actions
        $query = $root_da->execute(
            'SELECT * FROM pending_actions'
            .' WHERE application='.$root_da->formatText($application)
            .' AND action='.$root_da->formatText($action)
            .' ORDER BY created'
        );

        // Build the list of pending actions
        $actions = array();
        while (!$query->eof) {
            $actions[] = array(
                'id'          => $query->getFields('id'),
                'application' => $query->getFields('application'),
                'domainid'    => $query->getFields('domainid'),
                'userid'      => $query->getFields('userid'),
                'created'     => $query->getFields('created'),
                'action'      => $query->getFields('action'),
                'parameters'  => unserialize($query->getFields('parameters'))
            );
            $query->moveNext();
        }

        return $actions;
    }

    /**
     * Fetches pending actions by id.
     * 
     * @param string $id Id of pending action.
     */
    public static function getById($id)
    {
        $root_da = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess();

        // Extract the pending actions
        $query = $root_da->execute(
            'SELECT * FROM pending_actions'
            .' WHERE application='.$root_da->formatText($application)
            .' AND id='.$root_da->formatText($id)
            .' AND action='.$root_da->formatText($action)
        );

        // Build the list of pending actions
        $actions = array(
            'id'          => $query->getFields('id'),
            'application' => $query->getFields('application'),
            'domainid'    => $query->getFields('domainid'),
            'userid'      => $query->getFields('userid'),
            'created'     => $query->getFields('created'),
            'action'      => $query->getFields('action'),
            'parameters'  => unserialize($query->getFields('parameters'))
        );
        return $actions;
    }

    /**
     * Fetches id of pending actions by parameters.
     *
     * This method fetches a list of pending actions identifiers, filtering by
     * an array of parameters in the key/value format (with an AND condition).
     *
     * @param array $params Parameters of pending action.
     * @return array|null An array of the found identifiers.
     */
    public static function getIdByParams($params, $application=null, $domain_id=null, $user_id=null, $action=null)
    {
        $root_da = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess();

        $count = 0;
        $where = '';
        foreach ($params as $key => $value) {
            $len = strlen($value);
            if ($count > 0) {
                $where .= " AND ";
            }
            $where .= "parameters LIKE '%\"$key\";s:$len:\"$value\"%'";
        }

        if (strlen($where)) {
            $query = $root_da->execute(
                "SELECT id"
                ." FROM pending_actions"
                ." WHERE $where"
                .(!is_null($application) ? ' AND application='.$root_da->formatText($application) : "")
                .(!is_null($domain_id) ? ' AND domainid='.$root_da->formatText((int)$domain_id) : "")
                .(!is_null($user_id) ? ' AND userid='.$root_da->formatText((int)$user_id) : "")
                .(!is_null($action) ? ' AND action='.$root_da->formatText($action) : "")
            );

            $result = array();

            while (!$query->eof) {
                $result[] = $query->getFields('id');
                $query->moveNext();
            }
            return $result;
        }

        return false;
    }

    /**
     * Removes a pending action with a given id.
     *
     * @param integer $id Pending action id.
     */
    public static function removeById($id)
    {
        $root_da = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess();
        if ($id > 0) {
            $root_da->execute('DELETE FROM pending_actions WHERE id='.$id);
        }
    }

    /**
     * Removes all pending actions related to a given application and action name.
     *
     * @param string $application Application identifier string.
     * @param string $action Action name.
     */
    public static function removeByAction($application, $action)
    {
        $root_da = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess();

        $root_da->execute(
            'DELETE FROM pending_actions'
            .' WHERE application='.$root_da->formatText($application)
            .' AND action='.$root_da->formatText($action)
        );
    }

    /**
     * Removes all pending actions related to a given application.
     *
     * @param string $application Application identifier string.
     */
    public static function removeByApplication($application)
    {
        $root_da = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess();

        $root_da->execute(
            'DELETE FROM pending_actions'
            .' WHERE application='.$root_da->formatText($application)
        );
    }
}
