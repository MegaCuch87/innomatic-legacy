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
namespace Innomatic\Desktop\Panel;

/**
 * Abstract class for implementing a set of views in a Desktop Panel following
 * the MVC design pattern.
 *
 * @copyright 2000-2012 Innoteam Srl
 * @license http://www.innomatic.io/license/ BSD License
 * @link http://www.innomatic.io
 * @since Class available since Release 5.0
 * @package Desktop
 */
abstract class PanelViews implements \Innomatic\Util\Observer 
{
    /**
     *
     * @deprecated
     *
     */
    protected $_controller;

    protected $controller;

    /**
     *
     * @deprecated
     *
     */
    protected $_helper;

    protected $helper;

    /**
     *
     * @deprecated
     *
     */
    protected $_wuiContainer;

    protected $wuiContainer;
    
    protected $tpl;
    
    protected $wuiPanelContent;

    public function __construct(\Innomatic\Desktop\Panel\PanelController $controller)
    {
        $this->controller = $controller;

        // Deprecated
        $this->_controller = $controller;
        
        // Sets Wui container instance
        $this->wuiContainer = \Innomatic\Wui\Wui::instance('\Innomatic\Wui\Wui');
        
        // Deprecated
        $this->_wuiContainer = $this->wuiContainer;
    }

    abstract public function beginHelper();

    abstract public function endHelper();

    public function execute($view = 'default', $eventData = array())
    {
        // Cheks view method and executes it if exists
        $methodName = 'view' . $view;
        if (! method_exists($this, $methodName)) {
            $methodName = 'viewDefault';
        }
        
        // Check if a template file exists for this view.
        //
        $tplFile = $this->controller->getPanelHome().'templates/'.$view.'.xml.php';
        if (file_exists($tplFile)) {
            $this->tpl = new \Innomatic\Desktop\Panel\PanelTemplate($tplFile);
        }
        // Executes the view.
        //
        $this->$methodName($eventData);

        if (is_a($this->tpl, '\\Innomatic\\Desktop\\Panel\\PanelTemplate')) {
            $this->wuiPanelContent = new \Shared\WuiXml('content', array('definition' => $this->tpl->parse()));
        }
    }

    public function display()
    {
        // Outputs the Wui source
        $this->_wuiContainer->render();
    }

    abstract public function viewDefault($eventData);

    public function update($observable, $arg = '')
    {}

    public function getController()
    {
        return $this->controller;
    }

    public function getWuiContainer()
    {
        return $this->wuiContainer;
    }
}
