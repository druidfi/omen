<?php declare(strict_types=1);

namespace Druidfi\Omen;

if (!defined('CONFIG_SYNC_DIRECTORY')) {
    define('CONFIG_SYNC_DIRECTORY', 'sync');
}

class DrupalEnvDetector
{
    const DEFAULT_APP_ENV = 'prod';
    const DS = DIRECTORY_SEPARATOR;

    private $app_root = '/app';
    private $config = [];
    private $config_directories = [];
    private $databases = [];
    private $settings = [];

    public function __construct($settings_dir)
    {
        global $config, $config_directories, $databases, $settings;

        $this->config = &$config;
        $this->config_directories = &$config_directories;
        $this->databases = &$databases;
        $this->settings = &$settings;

        $mapping = [];

        // Do the detection!
        if (getenv('AMAZEEIO_SITENAME')) {
            $mapping = $this->getMapping('AmazeeIoLegacy');
        }
        else if (getenv('WODBY_INSTANCE_TYPE')) {
            $mapping = $this->getMapping('Wodby');
        }

        foreach ($mapping as $var => $val) {
            putenv($var . '='. $val);
        }

        // APP_ENV: dev|test|prod
        $APP_ENV = getenv('APP_ENV') ?: self::DEFAULT_APP_ENV;

        // Env specific default values
        $this->setEnvDefaults($APP_ENV);

        // Load/add files (if exist) from sites/default in following order:
        foreach (['all', $APP_ENV, 'local'] as $set) {
            // all.settings.php, dev.settings.php and local.settings.php
            if (file_exists($settings_dir . self::DS . $set . '.settings.php')) {
                include $settings_dir . self::DS . $set . '.settings.php';
            }

            // all.services.yml, dev.services.yml and local.services.yml
            if (file_exists($settings_dir . self::DS . $set . '.services.yml')) {
                $settings['container_yamls'][] = $settings_dir . self::DS . $set . '.services.yml';
            }
        }

        $this->setGlobalDefaults();

        $this->setDatabaseConnection();
    }

    /**
     * Return modified configuration.
     *
     * @return array
     */
    public function getConfiguration() : array {
        return [
            'config' => (array) $this->config,
            'config_directories' => (array) $this->config_directories,
            'databases' => (array) $this->databases,
            'settings' => (array) $this->settings,
        ];
    }

    private function getMapping($system) : array {
        $map_file = __DIR__ . self::DS . 'EnvMapping' . self::DS . $system . '.php';
        $mapping = require $map_file;

        if (is_array($mapping)) {
            return $mapping;
        }
        else {
            echo file_exists($map_file) ? 'JOOO' : 'EII';
            echo '--'. $system .'--' . $map_file . '---';
            var_dump($mapping);
            exit();
        }
    }

    /**
     * Set database connection.
     */
    private function setDatabaseConnection() {
        // DRUPAL_DB_* should be defined at this point.
        $this->databases['default']['default'] = [
            'driver' => getenv('DRUPAL_DB_DRIVER') ?: 'mysql',
            'database' => getenv('DRUPAL_DB_NAME') ?: 'drupal',
            'username' => getenv('DRUPAL_DB_USER') ?: 'drupal',
            'password' => getenv('DRUPAL_DB_PASS') ?: 'drupal',
            'host' => getenv('DRUPAL_DB_HOST') ?: 'db',
            'port' => getenv('DRUPAL_DB_PORT') ?: 3306,
            'prefix' => getenv('DRUPAL_DB_PREFIX') ?: '',
        ];
    }

    /**
     * Set ENV specific default values.
     *
     * @param string $APP_ENV
     */
    private function setEnvDefaults(string $APP_ENV) {
        $defaults = require __DIR__ . self::DS . 'defaults' . self::DS . $APP_ENV . '.php';

        foreach ($defaults as $set => $values) {
            if (!is_array($this->{$set})) {
                $this->{$set} = [];
            }

            $this->{$set} = array_merge($this->{$set}, $values);
        }
    }

    /**
     * Set global values. Same for all environments.
     */
    private function setGlobalDefaults() {
        // Load curated default values for detected ENV
        // Set directory for loading CMI configuration.
        $this->config_directories[CONFIG_SYNC_DIRECTORY] = '../conf/cmi';

        // Hash salt.
        $settings['hash_salt'] = '0000000000000000';

        // Public files path
        $this->settings['file_public_path'] = 'sites/default/files';

        // Private files path
        $this->settings['file_private_path'] = FALSE;

        // Trusted Host Patterns, see https://www.drupal.org/node/2410395 for more information.
        // If your site runs on multiple domains, you need to add these domains here
        $host = str_replace('.', '\.', getenv('HOSTNAME'));
        $this->settings['trusted_host_patterns'][] = '^' . $host . '$';
    }
}
