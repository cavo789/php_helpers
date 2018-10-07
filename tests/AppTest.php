<?php

declare(strict_types=1);

namespace cavo789;

define('APP_FOLDER', __DIR__ . '/logs');
define('APP_LOGFILE', APP_FOLDER . '/application.log');

// Autoload files using Composer autoload
require_once dirname(__DIR__) . '/vendor/autoload.php';

use \cavo789\Classes\App as App;
use \PHPUnit\Framework\TestCase;

final class AppTest extends TestCase
{
    public function testDebugModeOn(): void
    {
        // Be sure to have everytime a fresh file
        if (file_exists(APP_LOGFILE)) {
            unlink(APP_LOGFILE);
        }

        // Initialize the debug mode, create the log folder
        // and create the application.log file

        $app = App::getInstance(true, [
            'folder' => APP_FOLDER,
            'trace_deep' => 1,
            'root' => dirname(__DIR__)
        ]);

        // The folder APP_FOLDER should exists
        $this->assertDirectoryExists(APP_FOLDER);

        // The application.log file should exists
        $this->assertFileExists(APP_LOGFILE);

        // .htaccess file should be present too
        $this->assertFileExists(APP_FOLDER . DIRECTORY_SEPARATOR . '.htaccess');

        // Read the file and check that "Debug mode is ON" is there
        $content = file_get_contents(APP_LOGFILE);
        $this->assertContains('[INFO] Debug mode is ON', $content);

        // Mode is ON
        $this->assertTrue($app->getDebugMode());
    }

    public function testDebugModeOff(): void
    {
        $app = App::getInstance();
        $app->setDebugMode(false);
        $content = file_get_contents(APP_LOGFILE);

        $this->assertContains('[INFO] Debug mode is OFF', $content);

        // Mode is OFF
        $this->assertTrue(!($app->getDebugMode()));
    }

    /**
     * Test the log() method with different levels
     *
     * @return void
     */
    public function testLogMsg() : void
    {
        $app = App::getInstance();

        $app->setDebugMode(true);

        $app->log('INFO', 'FAKE-LOG-INFO-INFORMATION-ABOUT-THINGS');
        $content = file_get_contents(APP_LOGFILE);
        $this->assertContains('[INFO] FAKE-LOG-INFO-INFORMATION-ABOUT-THINGS', $content);

        $app->log('ALERT', 'FAKE-ALERT-INFO-INFORMATION-ABOUT-THINGS');
        $content = file_get_contents(APP_LOGFILE);
        $this->assertContains('[ALERT] FAKE-ALERT-INFO-INFORMATION-ABOUT-THINGS', $content);

        $app->log('EMERGENCY', 'FAKE-LOG-EMERGENCY-INFORMATION-ABOUT-THINGS');
        $content = file_get_contents(APP_LOGFILE);
        $this->assertContains('[EMERGENCY] FAKE-LOG-EMERGENCY-INFORMATION-ABOUT-THINGS', $content);
    }

    /**
     * Test the debug() method
     *
     * @return void
     */
    public function testDebugMsg(): void
    {
        $app = App::getInstance();
        $app->setDebugMode(true);

        // Output a fake debug and check that the line is well mentioned in the file
        $app->debug('FAKE-DEBUG-INFORMATION-ABOUT-THINGS');
        $content = file_get_contents(APP_LOGFILE);
        $this->assertContains('[DEBUG] FAKE-DEBUG-INFORMATION-ABOUT-THINGS', $content);

        // No DEBUG message recorded in the file when debug mode is false
        $app->setDebugMode(false);

        $app->debug('DEBUG-NOT-RECORDED');
        $content = file_get_contents(APP_LOGFILE);
        $this->assertNotContains('[DEBUG] DEBUG-NOT-RECORDED', $content);
    }

    /**
     * Test the info() method
     *
     * @return void
     */
    public function testInfoMsg(): void
    {
        $app = App::getInstance();
        $app->setDebugMode(true);

        // Output a fake info and check that the line is well mentioned in the file
        $app->info('FAKE-INFO-INFORMATION-ABOUT-THINGS');
        $content = file_get_contents(APP_LOGFILE);
        $this->assertContains('[INFO] FAKE-INFO-INFORMATION-ABOUT-THINGS', $content);

        // No INFO message recorded in the file when debug mode is false
        $app->setDebugMode(false);

        $app->debug('INFO-NOT-RECORDED');
        $content = file_get_contents(APP_LOGFILE);
        $this->assertNotContains('[INFO] INFO-NOT-RECORDED', $content);
    }

    /**
     * Test the notice() method
     *
     * @return void
     */
    public function testNoticeMsg(): void
    {
        $app = App::getInstance();
        $app->setDebugMode(true);

        // Output a fake notice and check that the line is well mentioned in the file
        $app->notice('FAKE-NOTICE-INFORMATION-ABOUT-THINGS');
        $content = file_get_contents(APP_LOGFILE);
        $this->assertContains('[NOTICE] FAKE-NOTICE-INFORMATION-ABOUT-THINGS', $content);

        // No Notice message recorded in the file when debug mode is false
        $app->setDebugMode(false);

        $app->notice('NOTICE-NOT-RECORDED');
        $content = file_get_contents(APP_LOGFILE);
        $this->assertNotContains('[NOTICE] NOTICE-NOT-RECORDED', $content);
    }

    /**
     * Test the warning() method
     *
     * @return void
     */
    public function testWarningMsg() : void
    {
        $app = App::getInstance();
        $app->setDebugMode(true);

        // Output a fake warning and check that the line is well mentioned in the file
        $app->warning('FAKE-WARNING-INFORMATION-ABOUT-THINGS');
        $content = file_get_contents(APP_LOGFILE);
        $this->assertContains('[WARNING] FAKE-WARNING-INFORMATION-ABOUT-THINGS', $content);

        // No Warning message recorded in the file when debug mode is false
        $app->setDebugMode(false);

        $app->notice('WARNING-NOT-RECORDED');
        $content = file_get_contents(APP_LOGFILE);
        $this->assertNotContains('[WARNING] WARNING-NOT-RECORDED', $content);
    }

    /**
     * Test the error() method
     *
     * @return void
     */
    public function testErrorMsg() : void
    {
        $app = App::getInstance();
        $app->setDebugMode(false);

        // Output a fake error and check that the line is well mentioned in the file
        $app->error('FAKE-ERROR-MESSAGE');
        $content = file_get_contents(APP_LOGFILE);
        $this->assertContains('[ERROR] FAKE-ERROR-MESSAGE', $content);

        // Error message well recorded in the file even when debug mode is false
        $app->setDebugMode(false);

        $app->error('ERROR-WELL-RECORDED');
        $content = file_get_contents(APP_LOGFILE);
        $this->assertContains('[ERROR] ERROR-WELL-RECORDED', $content);
    }

    /**
     * Test the critical() method
     *
     * @return void
     */
    public function testCriticalMsg() : void
    {
        $app = App::getInstance();
        $app->setDebugMode(true);

        // Output a fake critical and check that the line is well mentioned in the file
        $app->critical('FAKE-CRITICAL-MESSAGE');
        $content = file_get_contents(APP_LOGFILE);
        $this->assertContains('[CRITICAL] FAKE-CRITICAL-MESSAGE', $content);

        // Critical message well recorded in the file even when debug mode is false
        $app->setDebugMode(false);

        $app->critical('CRITICAL-WELL-RECORDED');
        $content = file_get_contents(APP_LOGFILE);
        $this->assertContains('[CRITICAL] CRITICAL-WELL-RECORDED', $content);
    }

    /**
     * Test the alert() method
     *
     * @return void
     */
    public function testAlertMsg() : void
    {
        $app = App::getInstance();
        $app->setDebugMode(true);

        // Output a fake alert and check that the line is well mentioned in the file
        $app->alert('FAKE-ALERT-MESSAGE');
        $content = file_get_contents(APP_LOGFILE);
        $this->assertContains('[ALERT] FAKE-ALERT-MESSAGE', $content);

        // Alert message well recorded in the file even when debug mode is false
        $app->setDebugMode(false);

        $app->alert('ALERT-WELL-RECORDED');
        $content = file_get_contents(APP_LOGFILE);
        $this->assertContains('[ALERT] ALERT-WELL-RECORDED', $content);
    }

    /**
     * Test the emergency() method
     *
     * @return void
     */
    public function testEmergencyMsg() : void
    {
        $app = App::getInstance();
        $app->setDebugMode(true);

        // Output a fake emergency and check that the line is well mentioned in the file
        $app->emergency('FAKE-EMERGENCY-MESSAGE');
        $content = file_get_contents(APP_LOGFILE);
        $this->assertContains('[EMERGENCY] FAKE-EMERGENCY-MESSAGE', $content);

        // Emergency message well recorded in the file even when debug mode is false
        $app->setDebugMode(false);

        $app->emergency('EMERGENCY-WELL-RECORDED');
        $content = file_get_contents(APP_LOGFILE);
        $this->assertContains('[EMERGENCY] EMERGENCY-WELL-RECORDED', $content);
    }
}
