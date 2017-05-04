<?php
class SzFile
{

    const MODE_READ   = 'r';
    const MODE_WRITE  = 'w';
    const MODE_APPEND = 'a';

    const TYPE_CSV    = 'text/csv';

    private $csvDelm     = "\t";   // the default delimiter of csv file
    private $csvLineEnd  = "\r\n"; // the default enclosure of csv file, chr(0)

    private $handle; // file resource handle, close it before function return

    /**
     * Open one file, if mode is write or append, folder writeable will be checked.
     *
     * @param string $path file path, possible to be full file name with path in it
     * @param string $name file name, possible to be ''
     * @param string $mode default null => read
     * @throws SzException 10204
     * @return resource
     */
    public function openFile($path, $name, $mode = null)
    {
        if ($name) {
            $target = rtrim($path, '/') . '/' . $name;
        } else {
            $target = $path;
        }

        if (is_null($mode)) { // default mode read
            $mode = self::MODE_READ;
        } else {
            if ($mode == self::MODE_WRITE
                || $mode == self::MODE_APPEND
            ) { // mode write & append
                self::checkFolder($path, true);
            }
        }

        if (isset($_SERVER['HTTP_USER_AGENT'])
                && false !== strpos($_SERVER['HTTP_USER_AGENT'], 'Mac')) { // if operation system is Mac OSX
            ini_set('auto_detect_line_endings', true);
        }
        $this->handle = @fopen($target, $mode);
        if (false === $this->handle) {
            throw new SzException(10204, $target);
        }

        return $this->handle;
    }

    /**
     * Close the file handle opened, if it exists.
     *
     * @throws SzException
     * @return void
     */
    public function closeFile()
    {
        if ($this->handle && is_resource($this->handle)) {
            if (false === fclose($this->handle)) {
                throw new SzException(10205);
            }
        }
    }

    /**
     * Write $string into current opened file.
     *
     * @param string $string
     * @throws SzException 10206
     * @return int
     */
    public function writeFile($string)
    {
        if (!$this->handle) {
            throw new SzException(10206);
        } else if ($this->handle && !is_resource($this->handle)) {
            /**
             * SzFileLogger will register close file event when system start to shutdown,
             * and it's possible there is some log event after file is closed,
             * it will cause warning, since file handle exists but already closed.
             *
             * In this case, we just need to return 0, and do not actually write something.
             *
             * @see SzFileLogger::__construct
             *     SzSystem::registerShutdownHandler($this, 'closeLog');
             */
            return 0;
        } else {
            return fwrite($this->handle, $string);
        }
    }

    /**
     * Write data into csv file.
     *
     * @param array $datas
     * <pre>
     * array(
     *     array('column', 'column', ...), // one line
     *     array('column', 'column', ...), // one line
     * );
     * </pre>
     *
     * @throws SzException 10206, 10207
     * @return void
     */
    public function writeCsvFile($datas)
    {
        if (!$this->handle) {
            throw new SzException(10206);
        }
        if ($datas && is_array($datas)) {
            foreach ($datas as $data) { // handle line of data
                $line = '';
                if ($data && is_array($data)) {
                    $line = implode($this->csvDelm, $data);
                }
                $line .= $this->csvLineEnd;
                if (false === fwrite($this->handle, $line, strlen($line))) {
                    throw new SzException(10207);
                }
            }
        }
    }

    /**
     * Read line of csv file data.
     *
     * @throws SzException 10206
     * @return array $line
     */
    public function readCsvFile()
    {
        if (!$this->handle) {
            throw new SzException(10206);
        }
        $line = array();
        if ($line = fgets($this->handle)) {
            $line = trim($line, $this->csvLineEnd); // remove tailing line end mark
            $line = SzUtility::explodeWithTrim($this->csvDelm, $line);
        } else {
            $line = false; // eof
        }
        return $line;
    }

    /**
     * Read line of file data.
     *
     * @throws SzException 10206
     * @return string $line false returned when eof
     */
    public function readLineOfFile()
    {
        if (!$this->handle) {
            throw new SzException(10206);
        }
        $line = '';
        if ($line = fgets($this->handle)) {
            // do nothing
        } else {
            $line = false; // eof
        }
        return $line;
    }

    /**
     * Set csv file delimiter.
     *
     * @param string $delm
     * @return void
     */
    public function setCsvDelm($delm)
    {
        $this->csvDelm = $delm;
    }

    /**
     * Set csv file line end.
     *
     * @param string $lineEnd
     * @return void
     */
    public function setCsvLineEnd($lineEnd)
    {
        $this->csvLineEnd = $lineEnd;
    }

    /**
     * Check the the uploaded file, also check it's type. Then return the tmp path of the file.
     *
     * @param string $type default null, means no need to check type
     * @throws SzException 10209, 10210, 10211
     * @return array
     * <pre>
     * array($filePath, $fileName) <br/>
     *
     * <b>NOTE:</b>
     * The $filePath here is not a directory, but the temporarily file name with path!
     * </pre>
     */
    public static function checkUploadedFile($type = null)
    {
        if ($_FILES['file']['error']) { // error in uploading
            throw new SzException(10209, $_FILES['file']['error']);
        } else {
            if ($_FILES['file']['size'] == 0) { // empty size file uploaded
                throw new SzException(10210);
            } else {
                if (!is_null($type) && $type != $_FILES['file']['type']) { // file type invalid
                    throw new SzException(10211, array($type, $_FILES['file']['type']));
                }
            }
        }
        return array($_FILES['file']['tmp_name'], $_FILES['file']['name']);
    }

    /**
     * Check path exists and readable.
     *
     * @param string $path
     * @param boolean $checkWritable
     * @throws SzException 10212, 10213
     * @return string $path
     */
    public static function checkFolder($path, $checkWritable = false)
    {
        if (!file_exists($path)
            || !is_readable($path)
        ) {
            throw new SzException(10212, $path);
        }
        if ($checkWritable && !is_writable($path)) {
            throw new SzException(10213, $path);
        }
        return $path;
    }

    /**
     * Read the content of the given file path.
     *
     * @param string $filePath
     * @return string $content false returned if file not exist, or not readable
     */
    public static function readWholeFile($filePath)
    {
        return @file_get_contents($filePath);
    }

    /**
     * Write content into file.
     *
     * @param string $filePath
     * @param array $content
     * @throws SzException 10214
     * @return void
     */
    public static function writeWholeFile($filePath, $content)
    {
        if (false === file_put_contents($filePath, $content)) {
            throw new SzException(10214, $filePath);
        }
    }

    /**
     * Convert configs of array type to string type.
     *
     * @param array $config
     * @return string $config
     */
    public static function exportConfigToString($config)
    {
        $config = var_export($config, true);
        $config = <<<TXT
<?php
return {$config};
TXT;
        return $config;
    }

}