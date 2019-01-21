<?php

namespace payla\library;

use Payla;

class Log
{
    private $handle;
    private $filename;

    public function __construct($filename = null){
        if(!is_null($filename))
            $this->filename;
        else
            $this->filename = Payla::app()->request->module.".log";
    }

    public function init($filename)
    {
        $this->handle = fopen(RUNTIME_PATH . 'logs/' . $this->filename, 'a');
    }

    public function write($message)
    {
        fwrite($this->handle, date('Y-m-d G:i:s') . ' - ' . print_r($message, true) . "\n");
    }

    public function __destruct()
    {
        if ($this->handle)
            fclose($this->handle);
    }
}