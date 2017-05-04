<?php
class Utility
{

    const SUFFIX_MODE_CLASS = 'CLASS';
    const SUFFIX_MODE_CONFIG = 'CONFIG';

    /**
     * Initialize Utility.
     *
     * @return Utility
     */
    public function __construct()
    {
        set_exception_handler(array($this, 'handleException'));
    }

    /**
     * Post http request
     *
     * @param string $url
     * @param string $request 'param1=a&param2=b&param3=c'
     * @throws Exception
     * @return string $result
     */
    public function postHttpRequest($url, $request)
    {
        $result = null;

        // post the query and get result
        if (function_exists('curl_init')) {
            // Use CURL if installed...
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            if (false === $result) {
                curl_close($ch);
                throw new Exception('Curl exception with errno:' . curl_errno($ch) . ' , error: ' . curl_error($ch));
            }
            curl_close($ch);
        } else {
            // Non-CURL based version...
            $context = array(
                'http' => array(
                    'method' => 'POST',
                    'header' => 'Content-type: application/x-www-form-urlencoded' . "\r\n" .
                    'User-Agent: PHP5 Client 1.1 ' . "\r\n" .
                    'Content-length: ' . strlen($request),
                    'content' => $request
                ),
            );
            $contextId = stream_context_create($context);
            $sock = fopen($url, 'r', false, $contextId);
            if ($sock) {
                $result = '';
                while (!feof($sock)) {
                    $result .= fgets($sock, 4096);
                }
                fclose($sock);
            }
        }

        return $result;
    }

    /**
     * Remove directory with contents.
     *
     * @param string $path
     * @throws Exception
     * @return boolean $result
     */
    public function removeDirectory($path)
    {
        // validate $path is directory
        $dirHandle = null;
        if (is_dir($path)) {
            $dirHandle = opendir($path);
        } else {
            throw new Exception('Target path is not a directory, path: ' . $path);
        }
        // check directory handle
        if (!$dirHandle) {
            throw new Exception('Exception in opening directory, path: ' . $path);
        }
        // loop the directory handle
        while ($file = readdir($dirHandle)) {
            if ($file == '.' || $file == '..') {
                continue; // not file or directory, continue
            }
            if (!is_dir($path . '/' . $file)) { // target is file, delete it
                unlink($path . '/' . $file);
            } else {
                $this->removeDirectory($path . '/' . $file); // recursive execution
            }
        }

        closedir($dirHandle);
        rmdir($path);
    }

    /**
     * Copy contents of the $source to $destination.
     *
     * <pre>
     * Same as:
     * mkdir $destination
     * cp $source/* $destination
     * </pre>
     *
     * @param string $source
     * @param string $destination
     * @throws Exception
     * @return void
     */
    public function copyDirectory($source, $destination)
    {
        // validate path is directory
        if (is_dir($source)) {
            $this->createDirectory($destination);
            $dirHandle = opendir($source);
            // check directory handle
            if (!$dirHandle) {
                throw new Exception('Exception in opening directory, path: ' . $source);
            }
            // loop the directory handle
            while ($file = readdir($dirHandle)) {
                if ($file == '.' || $file == '..') {
                    continue; // not file or directory, continue
                }
                $path = $source . '/' . $file;
                if (is_dir($path)) {
                    $this->copyDirectory($path, $destination . '/' . $file); // recursive execution
                } else {
                    copy($path, $destination . '/' . $file); // copy single file
                }
            }
            closedir($dirHandle);
        } else if (is_file($source)) { // target is file
            copy($source, $destination);
        }
    }

    /**
     * Create directory.
     *
     * @param string $dir
     * @return boolean
     */
    public function createDirectory($dir)
    {
        return @mkdir($dir, 0755, true); // @warning, since target directory may already exist
    }

    /**
     * Check path exists and readable.
     *
     * @param string $path
     * @param boolean $checkWritable default false
     * @throws Exception
     * @return string $path
     */
    public function checkFolder($path, $checkWritable = false)
    {
        if (!file_exists($path)
            || !is_dir($path)
            || !is_readable($path)
        ) {
            throw new Exception("{$path} does not exist, or not readable.");
        }
        if ($checkWritable && !is_writable($path)) {
            throw new Exception("{$path} is not writable.");
        }
        return $path;
    }

    /**
     * Write content into one file.
     *
     * @param string $filePath
     * @param string $content
     * @throws Exception
     * @return void
     */
    public function writeWholeFile($filePath, $content)
    {
        $this->log('-----------------------------------');
        $this->log("Write file| START: {$filePath}");

        if (false === file_put_contents($filePath, $content)) {
            throw new Exception('Failed in writing file: ' . $filePath);
        }

        $this->log("Write file| DONE: {$filePath}");
    }

    /**
     * Write one line data into config file.
     *
     * @param string $filePath
     * @param string $key
     * @param string $value
     * @throws Exception
     * @return void
     */
    public function writeElementIntoConfigFile($filePath, $key, $value)
    {
        $this->log('-----------------------------------');
        $this->log("Write line >> config file| START: {$filePath}");

        // get configurations
        if (!file_exists($filePath)
            || !is_writable($filePath)
        ) {
            throw new Exception('File does not exist or not writable: ' . $filePath);
        }
        $config = include $filePath;

        // add config
        $config[$key] = $value;

        // write config
        $config = $this->exportConfigToString($config);
        if (false === file_put_contents($filePath, $config)) {
            throw new Exception('Failed in writing file: ' . $filePath);
        }

        $this->log("Write line >> config file| DONE: {$filePath}");
    }

    /**
     * Convert configs of array type to string type.
     *
     * @param array $config
     * @return string $config
     */
    public function exportConfigToString($config)
    {
        $config = var_export($config, true);
        $config = <<<TXT
<?php
return {$config};
TXT;
        return $config;
    }

    /**
     * Format the given "underscored_word_group" as a "Human Readable Word Group".
     *
     * @param string $word
     * @return string
     */
    public function wordHumanize($word)
    {
        return ucwords(str_replace('_', ' ', $word));
    }

    /**
     * Format the given "lower_case_and_underscored_word" as a "CamelCased" word.
     *
     * @param $word
     * @param bool $needUcFirst default false
     * @return string
     */
    public function wordCamelize($word, $needUcFirst = false)
    {
        $result = str_replace(' ', '', $this->wordHumanize($word));
        if (!$needUcFirst) {
            $result = lcfirst($result);
        }
        return $result;
    }

    /**
     * Load the pathes of the files under one directory into one array.
     *
     * @param string $path
     * @param string $mode default 'CLASS', refer to Utility::SUFFIX_MODE_*
     * @throws Exception
     * @return array $fileArray
     * <pre>
     * array(
     *     fileNameWithoutSuffix => filePath,
     *     ...
     * )
     * </pre>
     */
    public function loopFolder($path, $mode = 'CLASS')
    {
        $path = rtrim($path, '/');
        $fileArray = array();
        // get target path handle to do operation
        if ($handle = opendir($path)) {
            // loop all elements according to target path handle
            while (($file = readdir($handle)) !== false) {
                if ($file == '.' || $file == '..') {
                    // continue to next loop if element is '.' or '..'
                    continue;
                }
                if (is_dir("{$path}/{$file}")) {
                    // target element is folder, so call loopFolder again
                    $subFileArray = $this->loopFolder("{$path}/{$file}", $mode);
                    if ($subFileArray && is_array($subFileArray)) {
                        foreach ($subFileArray as $fileKey => $filePath) {
                            $fileArray[$fileKey] = $filePath;
                        }
                    }
                } else {
                    // check file is class file or not, if true, get class name and add into array
                    $fileName = $this->suffixFilter($file, $mode);
                    if ($fileName) {
                        $fileArray[$fileName] = "{$path}/{$file}";
                    }
                }
            }
            closedir($handle);
        } else {
            throw new Exception('Cannot get handle of path: ' . $path);
        }

        return $fileArray;
    }

    /**
     * Check file name, if it has '.class.php' or '.config.php', return it's name without the suffix.
     * Otherwise return false.
     *
     * <pre>
     * test.html => false
     * items.config.php => items
     * ItemVo.class.php => ItemVo
     * </pre>
     *
     * @param string $fileName
     * @param string $mode default 'CLASS'
     * @return string|boolean $fileName false means pattern invalid
     */
    protected function suffixFilter($fileName, $mode = 'CLASS')
    {
        // determine suffix mode
        $suffixPos = false;
        if ($mode == self::SUFFIX_MODE_CLASS) {
            $suffixPos = strpos($fileName, '.class.php');
        } else if ($mode == self::SUFFIX_MODE_CONFIG) {
            $suffixPos = strpos($fileName, '.config.php');
        }

        // get file name
        if (false !== $suffixPos) {
            $fileName = substr($fileName, 0, $suffixPos);
        } else {
            $fileName = false;
        }

        return $fileName;
    }

    /**
     * Log message.
     *
     * @param string $msg
     * @return void
     */
    public function log($msg)
    {
        echo $msg . PHP_EOL;
    }

    /**
     * Handle one exception.
     *
     * @param Exception $e
     * @return void
     */
    public function handleException($e)
    {
        $this->log('-----------------------------------');
        $this->log('[Error]: ');
        $this->log('MSG: ' . $e->getMessage());
        $this->log('TRACE: ' . PHP_EOL . $e->getTraceAsString());
        exit(1);
    }

    /**
     * Get current time string as the format of 'YYYY-mm-dd HH:ii:ss'
     *
     * @return string
     */
    public function setTime()
    {
        return date('Y-m-d H:i:s');
    }

}