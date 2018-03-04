<?php

namespace Gcalendar\Service;

abstract class Gservice
{
    /**
     * @var string $appName
     */
    protected $appName;

    /**
     * @var string $calendarId
     */
    protected $calendarId;

    /**
     * @var Google_Client $client
     */
    protected $client;

    /**
     * @var string $configFile
     */
    protected $configFile;

    /**
     * @var array $scopes
     */
    protected $scopes = [
        \Google_Service_Calendar::CALENDAR
    ];

    /**
     * @param string $configFile
     * @param string $appName
     * @param string $calendarId
     */
    public function __construct($configFile, $appName, $calendarId)
    {
        if (!file_exists($configFile)) {
            throw new \Exception('The config file doesn\'t exists!');
        }

        $this->appName    = $appName;
        $this->calendarId = $calendarId;
        $this->configFile = $configFile;

        $this->getClient();
    }

    /**
     * @return Google_Client
     */
    protected function getClient()
    {
        if ($this->client) {
            return $this->client;
        }

        $client = new \Google_Client();
        $client->setScopes(implode(' ', $this->scopes));
        $client->setApplicationName($this->appName);
        $client->setAuthConfigFile($this->configFile);
        $this->client = $client;

        return $this->client;
    }
}
