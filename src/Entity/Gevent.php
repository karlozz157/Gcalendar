<?php

namespace Gcalendar\Entity;

class Gevent
{
    /**
     * @var array $attendees
     */
    protected $attendees = [];

    /**
     * @var \Google_Service_Calendar_Event $event
     */
    protected $event;

    /**
     * @param \Google_Service_Calendar_Event $event
     */
    public function __construct(\Google_Service_Calendar_Event $event = null)
    {
        if (!$event) {
            $event = new \Google_Service_Calendar_Event();
        }

        $this->event = $event;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->event->getId();
    }

    /**
     * @return string
     */
    public function getHangoutLink()
    {
        return $this->event->getHangoutLink();
    }

    /**
     * @param string $colorId
     *
     * @return $this
     */
    public function setColorId($colorId)
    {
        $this->event->setColorId($colorId);

        return $this;
    }

    /**
     * @return string
     */
    public function getColorId()
    {
        return $this->event->getColorId();
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->event->setSummary($title);

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->event->getSummary();
    }

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->event->setDescription($description);

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->event->getDescription();
    }

    /**
     * @param string $location
     *
     * @return $this
     */
    public function setLocation($location)
    {
        $this->event->setLocation($location);

        return $this;
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->event->getLocation();
    }

    /**
    * @param string $email
    */
    public function addAttendees($email)
    {
        if (!filter_var($email, \FILTER_VALIDATE_EMAIL)) {
            throw new \Exception(sprintf('The %s is not valid email!', $email));
        }

        $attendee = new \Google_Service_Calendar_EventAttendee();
        $attendee->setEmail($email);

        $this->attendees[] = $attendee;
        $this->event->attendees = $this->attendees;

        return $this;
    }

    /**
    * @return Google_Service_Calendar_EventAttendee
    */
    public function getAttendees()
    {
        return $this->event->getAttendees();
    }

    /**
     * @return Google_Service_Calendar_Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        $start = $this->event->start->dateTime;
        $start = (!$start) ? $this->event->start->date : $start;

        return new \DateTime($start);
    }

    /**
     * @param \DateTime $date
     *
     * @return $this
     */
    public function setStartDate(\DateTime $date)
    {
        $this->event->setStart($this->getCalendarEvent($date));

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        $end = $this->event->end->dateTime;
        $end = (!$end) ? $this->event->end->date : $end;

        return new \DateTime($end);
    }

    /**
     * @param \DateTime $date
     *
     * @return $this
     */
    public function setEndDate(\DateTime $date)
    {
        $this->event->setEnd($this->getCalendarEvent($date));

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id'           => $this->getId(),
            'title'        => $this->getTitle(),
            'description'  => $this->getDescription(),
            'location'     => $this->getLocation(),
            'start_date'   => $this->getStartDate()->format('c'),
            'end_date'     => $this->getEndDate()->format('c'),
            'hangout_link' => $this->getHangoutLink(),
            'color_id'     => $this->getColorId(),
        ];
    }

    /**
     * @param \DateTime $date
     *
     * @return Google_Service_Calendar_EventDateTime
     */
    private function getCalendarEvent(\DateTime $date)
    {
        $calendarEvent = new \Google_Service_Calendar_EventDateTime();
        $calendarEvent->setDateTime($this->getFormattedDate($date));
        $calendarEvent->setTimeZone($date->getTimezone()->getName());

        return $calendarEvent;
    }

    /**
     * @param \DateTime $date
     *
     * @return string
     */
    private function getFormattedDate(\DateTime $date)
    {
        return $date->format('c');
    }
}
