<?php

namespace Esyede\BCA\Exceptions;

class BCAException extends \Exception
{
    public function toArray()
    {
        return [
            'message' => $this->getMessage(),
            'previous' => $this->getPrevious(),
            'code' => $this->getCode(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'trace' => $this->getTrace(),
            'traces' => $this->getTraceAsString(),
        ];
    }

    public function toJson()
    {
        return json_encode($this->toArray());
    }
}