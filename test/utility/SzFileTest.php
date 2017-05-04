<?php
require_once dirname(dirname(__DIR__)) . '/test/SzTestAbstract.class.php';

class SzFileTest extends SzTestAbstract
{
    /**
     * @var SzFile
     */
    protected static $instance;
    protected static $content = 'Hello World!';
    protected static $csvData = array (
        array('column1_1', 'column1_2'),
        array('column2_1', 'column2_2'),
    );

    public function setUp()
    {
        self::$instance = new SzFile();
    }

    /**
     * @see SzFile::openFile
     * @see SzFile::writeFile
     * @see SzFile::closeFile
     */
    public function test_WriteFile()
    {
        $path = dirname(dirname(__DIR__)) . '/test/utility/mock/';
        $name = 'SzFileMock.class.php';

        $SzFile = new SzFile();

        // Test SzFile::openFile
        $this->assertTrue(is_resource($SzFile->openFile($path, $name, SzFile::MODE_WRITE)));
        // Test SzFile::writeFile
        $this->assertEquals(SzUtility::strLen(self::$content), $SzFile->writeFile(self::$content));
        // Test SzFile::closeFile
        $SzFile->closeFile();

        $this->assertFalse(is_resource($this->getPropertyValue('SzFile', self::$instance, 'handle')));

        // Test SzFile::readLineOfFile
        $this->assertTrue(is_resource($SzFile->openFile($path, $name, SzFile::MODE_READ)));
        $this->assertEquals(self::$content, $SzFile->readLineOfFile());
    }

    /**
     * @see SzFile::openFile
     *
     * @expectedException       SzException
     * @expectedExceptionCode   10204
     */
    public function test_OpenFile_Error_10204()
    {
        $path = dirname(dirname(__DIR__)) . '/test/utility/mock/';
        $name = 'SzFileMockError.class.php'; // empty file as reading place holder

        self::$instance->openFile($path, $name, 'Error'); // test wrong open mode
    }

    /**
     * @see SzFile::writeFile
     *
     * @expectedException       SzException
     * @expectedExceptionCode   10206
     */
    public function test_WriteFile_Error_10206()
    {
        self::$instance->writeFile(self::$content);
    }

    /**
     * @see SzFile::closeFile
     *
     * @expectedException       SzException
     * @expectedExceptionCode   10206
     */
    public function test_WriteCsvFile_Error_10206()
    {
        self::$instance->writeCsvFile(self::$csvData);
    }

    /**
     * @see SzFile::readCsvFile
     *
     * @expectedException       SzException
     * @expectedExceptionCode   10206
     */
    public function test_ReadCsvFile_Error_10206()
    {
        self::$instance->readCsvFile();
    }

    /**
     * Test SzFile::openFile
     * Test SzFile::writeCsvFile
     * Test SzFile::closeFile
     * Test SzFile::readCsvFile
     */
    public function test_WriteCsvFile()
    {
        $path = dirname(dirname(__DIR__)) . '/test/utility/mock/';
        $name = 'unit_test_data.csv';

        $SzFile = new SzFile();

        // Test SzFile::openFile
        $this->assertTrue(is_resource($SzFile->openFile($path, $name, SzFile::MODE_WRITE)));
        // Test SzFile::writeCsvFile
        $SzFile->writeCsvFile(self::$csvData);
        // Test SzFile::closeFile
        $SzFile->closeFile();

        // Test SzFile::readCsvFile
        $this->assertTrue(is_resource($SzFile->openFile($path, $name, SzFile::MODE_READ)));
        $this->assertEquals(array_shift(self::$csvData), $SzFile->readCsvFile());
    }

    /**
     * @see SzFile::setCsvDelm
     */
    public function test_SetCsvDelm()
    {
        $SzFile = new SzFile();
        $SzFile->setCsvDelm('-');
        $this->assertEquals('-', $this->getPropertyValue('SzFile', $SzFile, 'csvDelm'));
    }

    /**
     * @see SzFile::setCsvLineEnd
     */
    public function test_SetCsvLineEnd()
    {
        $SzFile = new SzFile();
        $SzFile->setCsvLineEnd('//n');
        $this->assertEquals('//n', $this->getPropertyValue('SzFile', $SzFile, 'csvLineEnd'));
    }

    /**
     * @see SzFile::checkUploadedFile
     */
    public function test_CheckUploadedFile()
    {
        $_FILES['file'] = array(
            'name'      => 'name',
            'type'      => 'image/jpeg',
            'size'      => 1024,
            'tmp_name'  => 'tmp_name',
            'error'     => false,
        );

        $SzFile = new SzFile();
        $this->assertEquals(array('tmp_name', 'name'),  $SzFile->checkUploadedFile('image/jpeg'));
    }

    /**
     * @see SzFile::checkUploadedFile
     *
     * @expectedException       SzException
     * @expectedExceptionCode   10209
     */
    public function test_CheckUploadedFile_Error_10209()
    {
        $_FILES['file'] = array(
            'name'      => 'name',
            'type'      => 'image/jpeg',
            'size'      => 1024,
            'tmp_name'  => 'tmp_name',
            'error'     => true,
        );

        $SzFile = new SzFile();
        $SzFile->checkUploadedFile('image/jpeg');
    }

    /**
     * @see SzFile::checkUploadedFile
     *
     * @expectedException       SzException
     * @expectedExceptionCode   10210
     */
    public function test_CheckUploadedFile_Error_10210()
    {
        $_FILES['file'] = array(
            'name'      => 'name',
            'type'      => 'image/jpeg',
            'size'      => 0,
            'tmp_name'  => 'tmp_name',
            'error'     => false,
        );

        $SzFile = new SzFile();
        $SzFile->checkUploadedFile('image/jpeg');
    }

    /**
     * @see SzFile::checkUploadedFile
     *
     * @expectedException       SzException
     * @expectedExceptionCode   10211
     */
    public function test_CheckUploadedFile_Error_10211()
    {
        $_FILES['file'] = array(
            'name'      => 'name',
            'type'      => 'image/png',
            'size'      => 1024,
            'tmp_name'  => 'tmp_name',
            'error'     => false,
        );

        $SzFile = new SzFile();
        $SzFile->checkUploadedFile('image/jpeg');
    }

    /**
     * Test SzFile::checkFolder
     */
    public function test_CheckFolder()
    {
        $path = dirname(dirname(__DIR__)) . '/test/utility/mock/';

        $SzFile = new SzFile();
        $this->assertEquals($path,  $SzFile->checkFolder($path, true));
    }

    /**
     * Test SzFile::checkFolder
     *
     * @expectedException       SzException
     * @expectedExceptionCode   10212
     */
    public function test_CheckFolder_Error_10212()
    {
        $path = dirname(dirname(__DIR__)) . '/test/utility/mock_Error/';

        $SzFile = new SzFile();
        $SzFile->checkFolder($path, true);
    }

    /**
     * Test SzFile::readWholeFile
     */
    public function test_ReadWholeFile()
    {
        $path = dirname(dirname(__DIR__)) . '/test/utility/mock/';
        $name = 'SzFileMock.class.php';

        $SzFile = new SzFile();
        $this->assertEquals(self::$content,  $SzFile->readWholeFile($path . $name));
    }

    /**
     * Test SzFile::exportConfigToString
     */
    public function test_ExportConfigToString()
    {
        $configs = array('10001' => array('id' => 10001, 'name' => 'configName'));
        $path = dirname(dirname(__DIR__)) . '/test/utility/mock/';
        $name = 'SzFileMock.class.php';

        $SzFile = new SzFile();
        $configsContent = $SzFile->exportConfigToString($configs);

        $SzFile->openFile($path, $name, SzFile::MODE_WRITE);
        $SzFile->writeFile($configsContent);
        $SzFile->closeFile();

        $this->assertEquals($configs,  include $path . $name);
    }
}