<?php declare(strict_types=1);

namespace JkaServerStatus\Config;

class ConfigFileService
{
    private const DEFAULT_PATH_TO_CONFIG_FILE = __DIR__ . '/../../config.php';

    /**
     * Gets the path to the config file, if it exists, and is readable.
     * @return string
     * @throws ConfigFileException if the file doesn't exist, or cannot be read.
     */
    public function getConfigFile(string $pathToConfigFile = self::DEFAULT_PATH_TO_CONFIG_FILE): string
    {
        if (!file_exists($pathToConfigFile)) {
            throw new ConfigFileException(
                'Could not find the configuration file ("' . $pathToConfigFile . '").'
            );
        }

        if (!is_file($pathToConfigFile)) {
            throw new ConfigFileException(
                'The configuration file is not a regular file ("' . $pathToConfigFile . '").'
            );
        }

        if (!is_readable($pathToConfigFile)) {
            throw new ConfigFileException(
                'Could not read the configuration file ("' . $pathToConfigFile . '"). '
                . 'Please check the file permissions.'
            );
        }

        return $pathToConfigFile;
    }
}
