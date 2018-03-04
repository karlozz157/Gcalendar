<?php

namespace Gcalendar\Service;

use Gcalendar\Entity\Gevent;
use Gcalendar\Manager\EventManager;
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
     * @param string                $configFile
     * @param string                $appName
     * @param string                $calendarId
     * @param EventManagerInterface $eventManager
     */
    public function __construct($configFile, $appName, $calendarId, EventManagerInterface $eventManager = null)
    {
        parent::__construct($configFile, $appName, $calendarId);

        $this->service = new \Google_Service_Calendar($this->getClient());
        $this->eventManager = $eventManager ? $eventManager : new EventManager();
    }

    /**
     * @param array $params
     *
     * @return array
     */
    protected function getOptionsToListEvents(array $params = [])
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

        return $options;
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

        $events = array_filter($this->all($params), function($event) use ($newEvent) {
            return ($newEvent->getStartDate() <= $event->getEndDate() && $newEvent->getEndDate() >= $event->getStartDate());
        });

        return !(count($events) > 0);
    }

    /**
     * @param array $params
     *
     * @return Gevent[]
     */
    public function all(array $params = [])
    {
        $events  = $this->service->events->listEvents($this->calendarId, $this->getOptionsToListEvents($params));
        $gevents = [];

        foreach ($events->getItems() as $event) {
            $gevents[] = new Gevent($event);
        }

        return $gevents;
    }

    /**
     * @param string $id
     *
     * @return Gevent
     */
    public function get($id)
    {
        $event = $this->service->events->get($this->calendarId, $id);

        return new Gevent($event);
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
            throw new \Exception('The start and/or end date required!');
        }

        if (!$this->isAvailableNewEvent($gevent)) {
            throw new \Exception('The date is not available!');
        }

        $created = $this->service->events->insert($this->calendarId, $gevent->getEvent(), [
            'conferenceDataVersion' => 1,
        ]);
        $this->eventManager->insert($gevent);

        return new Gevent($created);
    }

    /**
     * @param Gevent $gevent
     *
     * @return Gevent
     */
    public function update(Gevent $gevent)
    {
        $this->service->events->update($this->calendarId, $gevent->getId(), $gevent->getEvent());
        $this->eventManager->update($gevent);

        return $gevent;
    }

    /**
     * @param Gevent $gevent
     *
     * @return $this
     */
    public function delete(Gevent $gevent)
    {
        $this->service->events->delete($this->calendarId, $gevent->getId());
        $this->eventManager->delete($gevent);

        return $this;
    }
}
