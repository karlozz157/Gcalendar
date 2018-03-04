<?php

namespace Gcalendar\Service;

use Gcalendar\Entity\Gevent;
use Gcalendar\Manager\EventManagerInterface;

class GeventService extends Gservice
{
    /**
     * @var EventManagerInterface $eventManager
     */
    protected $eventManager;

    /**
     * @var \Google_Service_Calendar $service
     */
    protected $service;

    /**
     * @param string $configFile
     * @param string $appName
     * @param string $calendarId
     */
    public function __construct($configFile, $appName, $calendarId)
    {
        parent::__construct($configFile, $appName, $calendarId);

        $this->service = new \Google_Service_Calendar($this->getClient());
    }

    /**
     * @param Gevent $newEvent
     *
     * @return boolean
     */
    protected function isAvailableNewEvent(Gevent $newEvent)
    {
        $params = [
            'timeMin' => $newEvent->getStartDate(),
            'timeMax' => $newEvent->getEndDate(),
        ];

        $events = array_filter($this->getEvents($params), function($event) use ($newEvent) {
            return ($newEvent->getStartDate() <= $event->getEndDate() && $newEvent->getEndDate() >= $event->getStartDate());
        });

        if (count($events) > 0) {
            throw new \Exception('The date is not available!');
        }

        return true;
    }

    /**
     * @param Gevent $gevent
     *
     * @return Gevent
     */
    public function insert(Gevent $gevent)
    {
        $event = $gevent->getEvent();

        if (!$event->start || !$event->end) {
            throw new \Exception('The start and/or end date is required!');
        }

        $this->isAvailableNewEvent($gevent);
        $created = $this->service->events->insert($this->calendarId, $gevent->getEvent());

        if ($this->eventManager) {
            $this->eventManager->insert($gevent);
        }

        return new Gevent($created);
    }

    /**
     * @param array $params
     *
     * @return Gevent[]
     */
    public function getEvents(array $params = [])
    {
        $timeMin = (isset($params['timeMin']) && $params['timeMin'] instanceof \DateTime) ? $params['timeMin'] :  new \DateTime('now');

        $options = [
            'orderBy'      => 'startTime',
            'singleEvents' => true,
            'timeMin'      => $timeMin->format('c'),
        ];

        if (isset($params['timeMax']) && $params['timeMax'] instanceof \DateTime) {
            $options['timeMax'] = $params['timeMax']->format('c');
        }

        $results = $this->service->events->listEvents($this->calendarId, $options);

        $gevents = [];
        foreach ($results->getItems() as $result) {
            $gevents[] = new Gevent($result);
        }

        return $gevents;
    }

    /**
     * @param EventManagerInterface $eventManager
     */
    public function eventManager(EventManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
    }
}
