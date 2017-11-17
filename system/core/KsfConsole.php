<?php

/**
 * User: kasiss
 * Date: 6/12/16
 * Time: 23:05
 */
class KsfConsole
{

    protected $maxRuntime;

    protected $startTime;

    protected $endTime;

    public function __construct()
    {
        if (php_sapi_name() !== 'cli')
            throw new Exception("NEED CLI ENV!");
        
        $this->startTime = time();
        if (method_exists($this, "init"))
            $this->init();
    }

    public function setMemory($size)
    {
        ini_set('memory_limit', $size);
    }

    public function setMaxTime($time)
    {
        set_time_limit($time + 60);
        $this->maxRuntime = $time;
    }

    public function checkRunTime()
    {
        return (time() - $this->startTime) > $this->maxRuntime ? true : false;
    }
}