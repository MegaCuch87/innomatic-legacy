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
namespace Shared\Wui;

/**
 * @package WUI
 */
class WuiHorizgroup extends \Innomatic\Wui\Widgets\WuiContainerWidget
{
    /*
    public $mAlign;
    public $mGroupAlign;
    public $mGroupValign;
    public $mWidth;
    */

    public function __construct(
        $elemName,
        $elemArgs = '',
        $elemTheme = '',
        $dispEvents = ''
    )
    {
        parent::__construct(
            $elemName,
            $elemArgs,
            $elemTheme,
            $dispEvents
        );

            if (isset($this->mArgs['align'])) {
                switch ($this->mArgs['align']) {
                    case 'top' :
                    case 'middle' :
                    case 'bottom':
                        break;
                    default :
                        $this->mArgs['align'] = 'top';
                }
            } else
                $this->mArgs['align'] = 'top';

            if (isset($this->mArgs['groupalign'])) {
                switch ($this->mArgs['groupalign']) {
                    case 'left' :
                    case 'center' :
                    case 'right':
                        break;
                    default :
                        $this->mArgs['groupalign'] = 'left';
                }
            } else
                $this->mArgs['groupalign'] = 'left';

            if (isset($this->mArgs['groupvalign'])) {
                switch ($this->mArgs['groupvalign']) {
                    case 'top' :
                    case 'middle' :
                    case 'bottom':
                        break;
                    default :
                        $this->mArgs['groupvalign'] = 'middle';
                }
            } else
                $this->mArgs['groupvalign'] = 'middle';

            if (!isset($this->mArgs['width'])) {
                $this->mArgs['width'] = "100%";
            }

            if (isset($this->mArgs['scrollable'])) {
                switch ($this->mArgs['scrollable']) {
                    case 'true':
                    case 'false':
                        break;
                    default:
                        $this->mArgs['scrollable'] = 'false';
                }
            } else {
                $this->mArgs['scrollable'] = 'false';
            }
    }

    protected function generateSourceBegin()
    {
        return ( $this->mComments ? '<!-- begin '.$this->mName." horizgroup -->\n" : '' ).
            ($this->mArgs['scrollable'] == 'true' ? '<div style="'.(isset($this->mArgs['height']) ? 'height: '.$this->mArgs['height'].'px; ' : '' ).(isset($this->mArgs['width']) ? 'width: '.$this->mArgs['width'].'px; ' : '').'overflow: auto">' : '').
            '<table border="0" cellspacing="1" cellpadding="0" height="100%"'.
            ( strlen( $this->mArgs['groupalign' ]) ? ' align="'.$this->mArgs['groupalign'].'"' : '' ).
            ( strlen( $this->mArgs['groupvalign'] ) ? ' valign="'.$this->mArgs['groupvalign'].'"' : '' ).
            ( (isset($this->mArgs['width']) and strlen($this->mArgs['width'])) ? ' width="'.$this->mArgs['width'].'"' : '' ).
            "><tr>\n";
    }

    protected function generateSourceEnd()
    {
        return "</tr></table>\n" .
            ($this->mArgs['scrollable'] == 'true' ? '</div>' : '')
            . ($this->mComments ? '<!-- end ' . $this->mName . " horizgroup -->\n" : '');
    }

    protected function generateSourceBlockBegin()
    {
        return '<td' .
            ($this->mArgs['align'] ? ' valign="' . $this->mArgs['align'] .'"' : '')
            . '>';
    }

    protected function generateSourceBlockEnd()
    {
        return "</td>\n";
    }
}
