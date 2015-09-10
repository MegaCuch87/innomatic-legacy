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
namespace Innomatic\Datatransfer;

/**
 * Classe che implementa un meccanismo per trasferire dati
 * tramite operazioni di copia/taglia/incolla.
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 * @since 1.0
 */
class Clipboard
{
    protected $type;
    protected $customType;
    protected $unit;
    protected $application;
    protected $domain;
    protected $user;
    protected $fileName;
    const TYPE_TEXT = 'text';
    const TYPE_RAW = 'raw';
    const TYPE_FILE = 'file';
    const TYPE_ARRAY = 'array';
    const TYPE_OBJECT = 'object';
    const TYPE_CUSTOM = 'custom';

    /**
     * Costruisce la classe della clipboard.
     * @param string $type tipo di dato da trattare.
     * @param string $customType tipo utente di dato da trattare se $type � impostato a Clipboard::TYPE_CUSTOM
     * @param integer $unit unit� identificativa della clipboard da utilizzare a partire da 0
     * @param string $application nome del modulo.
     * @param string $domain nome del sito.
     * @param string $user nome dell'utente.
     */
    public function __construct(
        $type,
        $customType = '',
        $unit = 0,
        $application = '',
        $domain = '',
        $user = ''
    )
    {
        $this->type = $type;
        if ($this->type == Clipboard::TYPE_CUSTOM) {
            $this->customType = $customType;
        }
        $this->unit = $unit;
        $this->application = $application;
        $this->domain = $domain;
        $this->user = $user;
        $this->fileName = \Innomatic\Core\InnomaticContainer::instance(
            '\Innomatic\Core\InnomaticContainer'
        )->getHome() . 'core/temp/clipboard/'
        . $this->type . '_' . $this->customType . '_' . $this->unit
        . '_' . $this->application . '_' . $this->domain
        . '_' . $this->user . '.clipboard';
    }

    /**
     * Controlla se la clipboard contiene dati validi.
     * @return bool
     * @access public
     */
    public function isValid()
    {
        clearstatcache();
        return file_exists($this->fileName);
    }

    /**
     * Immagazzina un dato nella clipboard.
     * @param mixed $item dato da salvare.
     * @return bool
     * @access public
     * @see Clipboard::Retrieve()
     */
    public function store(&$item)
    {
        $result = false;
        $sem = new \Innomatic\Process\Semaphore('clipboard', $this->fileName);
        $sem->waitGreen();
        $sem->setRed();

        $fh = fopen($this->fileName, 'wb');
        if ($fh) {
            switch ($this->type) {
                case Clipboard::TYPE_TEXT :
                case Clipboard::TYPE_RAW :
                    fwrite($fh, $item);
                    $result = true;
                    break;

                case Clipboard::TYPE_FILE :
                    fwrite(
                        $fh,
                        serialize(
                            array(
                                'filename' => $item,
                                'content' => file_get_contents($item)
                            )
                        )
                    );
                    $result = true;
                    break;

                case Clipboard::TYPE_OBJECT :
                case Clipboard::TYPE_ARRAY :
                case Clipboard::TYPE_CUSTOM :
                    fwrite($fh, serialize($item));
                    $result = true;
                    break;
            }
            fclose($fh);
            $sem->setGreen();
        }
        return $result;
    }

    /**
     * Estrae il contenuto della clipboard.
     * @return mixed
     * @access public
     * @see Clipboard::Store()
     */
    public function retrieve()
    {
        $result = '';
        $sem = new \Innomatic\Process\Semaphore('clipboard', $this->fileName);
        $sem->waitGreen();

        if ($this->IsValid()) {
            $sem->setRed();
            if (file_exists($this->fileName)) {
                switch ($this->type) {
                    case Clipboard::TYPE_TEXT :
                        // this break was intentionally left blank
                    case Clipboard::TYPE_RAW :
                        $result = file_get_contents($this->fileName);
                        break;

                    case Clipboard::TYPE_FILE :
                        // this break was intentionally left blank
                    case Clipboard::TYPE_OBJECT :
                        // this break was intentionally left blank
                    case Clipboard::TYPE_ARRAY :
                        // this break was intentionally left blank
                    case Clipboard::TYPE_CUSTOM :
                        $result = unserialize(
                            file_get_contents($this->fileName)
                        );
                        break;
                }
                $sem->setGreen();
            }
        }
        return $result;
    }

    /**
     * Svuota il contenuto della clipboard.
     * @return bool
     * @access public
     */
    public function erase()
    {
        $result = false;
        if ($this->IsValid()) {
            $sem = new \Innomatic\Process\Semaphore('clipboard', $this->fileName);
            $sem->waitGreen();
            $sem->setRed();
            $result = unlink($this->fileName);
            $sem->setGreen();
        } else
            $result = true;
        return $result;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getCustomType()
    {
        return $this->customType;
    }

    public function getUnit()
    {
        return $this->unit;
    }

    public function getApplication()
    {
        return $this->application;
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function getFileName()
    {
        return $this->fileName;
    }
}
