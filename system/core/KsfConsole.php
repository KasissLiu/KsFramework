<?php
/**
 * User: kasiss
 * Date: 6/12/16
 * Time: 23:05
 */


class KsfConsole
{
    protected $max_runtime;
    protected $start_time;
    protected $end_time;


    public function __construct()
    {
        if(php_sapi_name() !== 'cli')
            throw new Exception ("NEED CLI ENV!");

        $this->start_time = time();
        if(method_exists($this,"init"))
            $this->init();

    }

    public function setMemory($size)
    {
        ini_set('memory_limit',$size);
    }
    public function setMaxTime($time)
    {
        set_time_limit($time+60);
        $this->max_runtime = $time;
    }
    public function checkRunTime()
    {
        return (time()-$this->start_time) > $this->max_runtime ? true : false;
    }



}