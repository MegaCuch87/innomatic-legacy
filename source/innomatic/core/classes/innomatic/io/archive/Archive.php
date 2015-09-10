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
namespace Innomatic\Io\Archive;

class Archive
{
    /*! @var mFile string - Full path of archive file. */
    protected $mFile;
    /*! @var mFormat string - Archive format. */
    protected $mFormat;
    const FORMAT_TAR = 'tar';
    const FORMAT_TGZ = 'tgz';
    const FORMAT_ZIP = 'zip';

    /*!
     @function Archive

     @abstract Class constructor.

     @param arcFile string - Full path of archive file.
     @param arcFormat string - Archive format.
     */
    public function __construct($arcFile, $arcFormat)
    {
        $this->mFile = $arcFile;
        $this->mFormat = $arcFormat;
    }

    /*!
     @function Extract

     @abstract Extracts the archive.

     @param destinationDir string - Full path of the destination dir for the extracted files.

     @result true if the archive has been successfully extracted.
     */
    public function Extract($destinationDir)
    {
        $result = false;

        if (file_exists($destinationDir)) {
            $old_dir = getcwd();

            if (@chdir($destinationDir)) {
                switch ($this->mFormat) {
                    case self::FORMAT_TAR :
                    case self::FORMAT_TGZ :
                        $result = true;
                        $tar = new \Innomatic\Io\Archive\Archivers\Tar();
                        if ($tar->openTar($this->mFile)) {
                            if ($tar->numDirectories > 0) {
                                foreach ($tar->directories as $id => $information) {
                                    // Fix for a PHP 5 bug under Windows
                                    if (isset($_ENV['OS']) and strpos(strtolower($_ENV['OS']), 'windows') !== false) {
                                        $information['name'] = str_replace('/', '\\', $information['name']);
                                    }
                                    if (!file_exists($information['name'])) {
                                        @mkdir($destinationDir.'/'.$information['name'], 0755, true);
                                    }
                                }
                            }

                            if ($tar->numFiles > 0) {
                                foreach ($tar->files as $id => $information) {
                                    if (!file_exists($information['name'])) {
                                        if ($fp = @fopen($information['name'], 'wb')) {
                                            @fwrite($fp, $information['file']);
                                            @fclose($fp);
                                            $mode = substr($information['mode'], -5);
                                            @chmod($information['name'], octdec($mode));
                                        } else
                                            $result = false;
                                    }
                                }
                            }
                        }

                        break;

                    case self::FORMAT_ZIP :
                        $result = true;
                        $zip = new \Innomatic\Io\Archive\Archivers\PclZip($this->mFile);
                        $list = $zip->extract(PCLZIP_OPT_PATH, $destinationDir);
                        break;
                }
                @chdir($old_dir);
            }
        }

        return $result;
    }
}
