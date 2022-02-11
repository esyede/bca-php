<?php

namespace Esyede\BCA;

class BcaRequestException extends \Exception
{
    public function toArray()
    {
        return [
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'trace' => $this->getTrace(),
            'previous' => $this->getPrevious(),
            'trace_as_string' => $this->getTraceAsString(),
        ];
    }


    public function toJson()
    {
        return json_encode($this->toArray());
    }
}