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
namespace Innomatic\Desktop\Dashboard;

/**
 * @since 6.1.0
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 */
abstract class DashboardWidget
{
    /**
     * Tells if the widget should be visible.
     * 
     * @var boolean
     */
    protected $visible = true;
    /**
     * Tells if the widget should be loaded with AJAX after the dashboard panel
     * view has been sent to the client.
     *
     * @var boolean
     */
    protected $deferred = false;
    
    /**
     * Returns the widget WUI xml definition.
     *
     * @since 6.1.0
     */
    abstract public function getWidgetXml();

    /**
     * Returns widget widget in units (not pixels).
     * Each unit is multiplied per the default unit width by the dashboard.
     *
     * @since 6.1.0
     */
    abstract public function getWidth();

    /**
     * Returns widget height in pixels.
     *
     * @since 6.1.0
     */
    abstract public function getHeight();

    /**
     * Returns the default width in pixels.
     *
     * @since 6.1.0
     * @return int
     */
    public function getDefaultWidth()
    {
        return 400;
    }

    /**
     * Returns the default height in pixel.
     *
     * @since 6.1.0
     * @return int
     */
    public function getDefaultHeight()
    {
        return 250;
    }

    /**
     * Tells if the widget should be visible.
     *
     * This is useful when there is some sort of check in order to prevent
     * the widget to be shown, eg. when checking assigned roles.
     *
     * By default this method returns true and should be extended by
     * widgets handling the above mentioned cases.
     *
     * @since 6.4.0 introduced
     * @return boolean
     */
    public function isVisible()
    {
        return $this->visible;
    }

    /* public isDeferred() {{{ */
    /**
     * Tells if the widget should be loaded with AJAX after the dashboard panel
     * view has been sent to the client.
     *
     * @since 7.0.0
     * @access public
     * @return void
     */
    public function isDeferred()
    {
        return $this->deferred;
    }
    /* }}} */
}
