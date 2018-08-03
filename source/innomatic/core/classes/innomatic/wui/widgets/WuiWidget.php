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
namespace Innomatic\Wui\Widgets;

/**
 * Base widget abstract class.
 *
 * @abstract
 */
abstract class WuiWidget
{
    /**
     * Internal dispatcher.
     *
     * @var \Innomatic\Wui\Dispatch\WuiDispatcher
     * @access public
     */
    public $mrWuiDisp;

    /**
     * Widget layout.
     *
     * @var string
     * @access public
     */
    public $mLayout;

    /**
     * Widget unique instance name.
     *
     * @var string
     * @access public
     */
    public $mName;

    /**
     * Array of widget arguments and attributes.
     *
     * @var array
     * @access public
     */
    public $mArgs = array();

    /**
     * Theme applied to the widget.
     *
     * @var string
     * @access public
     */
    public $mTheme;

    /**
     * Widget theme handler.
     *
     * @var \Innomatic\Wui\WuiTheme
     * @access public
     */
    public $mThemeHandler;

    /**
     * Dispatcher events.
     *
     * @var array
     * @access public
     */
    public $mDispEvents = array();

    /**
     * Set to true if the widget should print comment blocks in the output code.
     *
     * @var boolean
     * @access public
     */
    public $mComments;

    /**
     * Set to true if the widget instance should use the stored session parameters.
     *
     * @var boolean
     * @access public
     */
    public $mUseSession;

    /**
     * Widget object name in the session.
     *
     * @var string
     * @access public
     */
    public $mSessionObjectName;

    public $mSessionObjectUserName;

    public $mSessionObjectNoUser;

    public $mSessionObjectNoPage;

    public $mSessionObjectNoType;

    public $mSessionObjectNoName;

    public $events = array();

    /**
     * Class constructor.
     *
     * @param string $elemName Widget instance unique name.
     * @param array $elemArgs Widget arguments and attributes.
     * @param string $elemTheme Theme name to be applied to the element. Not used anymore.
     * @param array $dispEvents Dispatcher events.
     */
    public function __construct(
        $elemName,
        $elemArgs = '',
        $elemTheme = '',
        $dispEvents = ''
    ) {
        $this->mName = $elemName;
        if (is_array($elemArgs)) {
            $this->mArgs = &$elemArgs;
        }
        $this->mComments = \Innomatic\Wui\Wui::showSourceComments();

        $container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');
        $wuiContainer = \Innomatic\Wui\Wui::instance('\Innomatic\Wui\Wui');

        if (is_array($dispEvents)) {
            $this->mDispEvents = &$dispEvents;
        }

        $currentWuiTheme = $wuiContainer->getThemeName();
        if (strlen($elemTheme) and $elemTheme != $currentWuiTheme) {
            $this->mTheme = $elemTheme;

            $this->mThemeHandler = new \Innomatic\Wui\WuiTheme(
                $container->getDataAccess(),
                $this->mTheme
            );
        } else {
            $this->mTheme = $currentWuiTheme;
            $this->mThemeHandler = $wuiContainer->getTheme();
        }

        if (
            isset($this->mArgs['usesession'])
            and ($this->mArgs['usesession'] == 'true'
            or $this->mArgs['usesession'] == 'false')
        ) {
            $this->mUseSession = $this->mArgs['usesession'];
        } else {
            $this->mUseSession = 'true';
        }

        // Initialise session parameters.
        if (isset($this->mArgs['sessionobjectnouser'])) {
            $this->mSessionObjectNoUser = $this->mArgs['sessionobjectnouser'];
        }
        if (isset($this->mArgs['sessionobjectnopage'])) {
            $this->mSessionObjectNoPage = $this->mArgs['sessionobjectnopage'];
        }
        if (isset($this->mArgs['sessionobjectnotype'])) {
            $this->mSessionObjectNoType = $this->mArgs['sessionobjectnotype'];
        }
        if (isset($this->mArgs['sessionobjectnoname'])) {
            $this->mSessionObjectNoName = $this->mArgs['sessionobjectnoname'];
        }
        if (isset($this->mArgs['sessionobjectusername'])) {
            $this->mSessionObjectUserName =
                $this->mArgs['sessionobjectusername'];
        }

        $url_path = '';

        if ($this->mSessionObjectNoPage != 'true') {
            $url_path = $_SERVER['REQUEST_URI'];
            if (strpos($url_path, '?')) {
                $url_path = substr($url_path, 0, strpos($url_path, '?'));
            }
            $url_path .= '_';
        }

        $this->mSessionObjectName = ($this->mSessionObjectNoUser == 'true' ? ''
            : (is_object($container->getCurrentUser()) ?
            $container->getCurrentUser()->getUserName() : 'root')
             .'_') .$url_path.
             ($this->mSessionObjectNoType == 'true' ? '' : get_class($this).'_') .
             ($this->mSessionObjectNoName == 'true' ? '' : $this->mName) .
             (strlen($this->mSessionObjectUserName) ? '_'.$this->mSessionObjectUserName : '');

        // Check if the widget should be prepared once for all instances
        $widgetName = substr(get_class($this), 3);
        if (!isset($wuiContainer->preparedWidgets[$widgetName])) {
            // Register action ajax calls
            $theObject = new \ReflectionObject($this);
            $methods = $theObject->getMethods();
            foreach ($methods as $method) {
                // Ignore private methods
                $theMethod = new \ReflectionMethod($theObject->getName(), $method->getName());
                if (!$theMethod->isPublic()) {
                    continue;
                }

                // Expose only methods beginning with "ajax" prefix
                if (!(substr($method->getName(), 0, 4) == 'ajax')) {
                    continue;
                }

                // Register the ajax call
                $call_name = substr($method->getName(), 4);
                $wuiContainer->registerAjaxCall($call_name);
                $wuiContainer->getXajax()->registerExternalFunction(array($call_name, get_class($this), $method->getName()), 'shared/wui/'.get_class($this).'.php');
            }

            // Set the widget as prepared
            $wuiContainer->preparedWidgets[$widgetName] = $widgetName;
        }
    }

    /*!
     @function build
     @abstract Builds the structure.
     @discussion Builds the structure.
     @param rwuiDisp WuiDispatcher class - Wui internal dispatcher handler.
     @result True it the structure has been built by the member.
     */
    public function build(\Innomatic\Wui\Dispatch\WuiDispatcher $rwuiDisp)
    {
        $this->mrWuiDisp = $rwuiDisp;
        return $this->generateSource();
    }

    /*!
     @function Render
     @abstract Renders the structure.
     @discussion If the structure has not been built, it will call the
    Wui->Build() member.
     @result True if the structure has been rendered.
     */
    public function &render()
    {
        return $this->mLayout;
    }

    public function destroy()
    {
        $this->mLayout = '';
        $this->mArgs = array();
    }

    /*!
     @function generateSource
     @abstract Wrapped build function, redefined by extension classes.
     @discussion Wrapped build function, redefined by extension classes.
     @result Always true if not extended.
     */
    protected function generateSource()
    {
        $this->mLayout = '';
    }

    /*!
     @function StoreSession
     @abstract Stores widget parameters to be saved in the session.
     @param args array - Array of the parameters to be stored.
     @result Always true.
     */
    public function storeSession($args)
    {
        if ($this->mUseSession) {
            \Innomatic\Desktop\Controller\DesktopFrontController::instance(
                '\Innomatic\Desktop\Controller\DesktopFrontController'
            )->session->put(
                $this->mSessionObjectName, serialize($args)
            );
        }
    }

    /* public retrieveSession() {{{ */
    /**
     * Gets the widget parameters stored in the session.
     *
     * @access public
     * @return array Widget parameters in session.
     */
    public function retrieveSession()
    {
        if (
            $this->mUseSession == 'true'
            and \Innomatic\Desktop\Controller\DesktopFrontController::instance(
                '\Innomatic\Desktop\Controller\DesktopFrontController'
            )->session->isValid($this->mSessionObjectName)
        ) {
            return unserialize(
                \Innomatic\Desktop\Controller\DesktopFrontController::instance(
                    '\Innomatic\Desktop\Controller\DesktopFrontController'
                )->session->get($this->mSessionObjectName)
            );
        } else {
            return false;
        }
    }
    /* }}} */

    // --- Javascript Events --------------------------------------------------

    /**
     * Adds a Javascript event.
     *
     * @param string $event Event name, without the "on" prefix, e.g. "onclick" must be given as "click".
     * @param string $call Javascript function to be called.
     */
    public function addEvent($event, $call)
    {
        $this->events[$event][] = $call;
    }

    /**
     * Gets a javascript event.
     *
     * This methods returns the action of an event, if set, false otherwise.
     *
     * @since 5.1
     * @param string $event Name of the event.
     * @return mixed Option value.
     */
    public function getEvent($event)
    {
        return isset($this->events[$event]) ? $this->events[$event] : false;
    }

    /**
     * Gets all javascript events.
     *
     * This methods returns an array of all the events.
     *
     * @since 5.1
     * @return array Events.
     */
    public function getEvents()
    {
        return $this->events;
    }


    /**
     * Tells if a javascript event has been set.
     *
     * @since 5.1
     * @param string $event Name of the event.
     * @return boolean
     */
    public function isEvent($event)
    {
        return isset($this->events[$event]);
    }

    /**
     * Tells the number of javascript events.
     *
     * @since 5.1
     * @return integer
     */
    public function hasEvents()
    {
        return count($this->events);
    }

    /**
     * Unsets a javascript event.
     *
     * @since 5.1
     * @param string $event Name of the event.
     * @return boolean
     */
    public function unsetEvent($event)
    {
        if (isset($this->events[$event])) {
            unset($this->events[$event]);
        }
    }

    /**
     * Builds the event content string, e.g. action_a();action_b().
     *
     * @param string $event Event name.
     * @return string Javascript functions list.
     */
    public function getEventString($event)
    {
        if (!isset($this->events[$event])) {
            return '';
        }
        return implode(';', $this->events[$event]);
    }

    /**
     * Builds the event string containing the event plus the the Javascript
     * function calls, e.g. onclick="action_a();action_b()".
     *
     * @param String $event Event name.
     * @return string Event with Javascript functions list, prepared for HTML.
     */
    public function getEventCompleteString($event)
    {
        $string = $this->getEventString($event);
        if (strlen($string)) {
            return 'on'.$event.'="'.$string.'"';
        }
        return '';
    }

    /**
     * Builds the events strings containing the events plus the the Javascript
     * function calls, e.g. onclick="action_a();action_b()"
     * onmouseover="action_c()".
     *
     * @param String $event Event name.
     * @return string Event with Javascript functions list, prepared for HTML.
     */
    public function getEventsCompleteString()
    {
        if (!count($this->events)) {
            return '';
        }

        $string = '';
        foreach ($this->events as $eventName => $calls) {
            $string .= ' on'.$eventName.'="'.implode(';', $calls).'"';
        }
        return $string;
    }
}
