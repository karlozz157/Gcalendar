<?php

namespace Gcalendar\Manager;

use Gcalendar\Entity\Gevent;

interface EventManagerInterface
{
    /**
     * @param Gevent $gevent
     */
    public function insert(Gevent $gevent);

    /**
     * @param Gevent $gevent
     */
    public function update(Gevent $gevent);

    /**
     * @param Gevent $gevent
     */
    public function delete(Gevent $gevent);
}
