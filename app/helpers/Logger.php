<?php

class Logger
{
    private static string $logFile = __DIR__ . '/../../logs/app.log'; // Ubicación del log

    /**
     * Escribe un mensaje en el archivo de log.
     *
     * @param string $level Nivel del log (INFO, WARNING, ERROR).
     * @param string $message Mensaje a registrar.
     */
    public static function log(string $level, string $message): void
    {
        // Asegurar que el directorio de logs existe
        $logDir = dirname(self::$logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        // Formato del mensaje: [fecha] [NIVEL] mensaje
        $logMessage = sprintf("[%s] [%s] %s\n", date("Y-m-d H:i:s"), strtoupper($level), $message);

        // Escribir en el archivo
        file_put_contents(self::$logFile, $logMessage, FILE_APPEND);
    }

    /**
     * Atajo para escribir logs de INFO.
     */
    public static function info(string $message): void
    {
        self::log("INFO", $message);
    }

    /**
     * Atajo para escribir logs de WARNING.
     */
    public static function warning(string $message): void
    {
        self::log("WARNING", $message);
    }

    /**
     * Atajo para escribir logs de ERROR.
     */
    public static function error(string $message): void
    {
        self::log("ERROR", $message);
    }
}
