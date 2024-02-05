<?php

namespace Niikhsan\SatusehatIntegrate\FHIR\Exception;

use Exception;

class FHIRException extends Exception
{
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        $message = 'FHIR Exception: '.$message;

        parent::__construct($message, $code, $previous);
    }
}
