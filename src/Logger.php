<?php
namespace App;
use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
class Logger
{
    private static $log;
    public static function getLogger()
    {
        if (self::$log === null) {
            self::$log = new MonologLogger('API_DEBUG');

            // Jalur log file relatif dari root proyek, sekarang ../logs/app.log
            // Karena Logger.php di src/, dan logs/ di root proyek
            $logFilePath = __DIR__ . '/../logs/app.log';

            if (!file_exists(dirname($logFilePath))) {
                mkdir(dirname($logFilePath), 0777, true);
            }
            $handler = new StreamHandler($logFilePath, MonologLogger::DEBUG);
            $formatter = new LineFormatter(
                "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
                "Y-m-d H:i:s",
                true,
                true
            );
            $handler->setFormatter($formatter);
            self::$log->pushHandler($handler);
        }
        return self::$log;
    }
}