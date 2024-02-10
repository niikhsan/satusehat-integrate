<?php

namespace Niikhsan\SatusehatIntegrate\FHIR\Enum;

// enum ObservationCategory: string
// {
//     case VitalSigns = 'vital-signs';
// }

class ObservationCategory
{
    private $__value;
    private $__name;

    const VitalSigns = 'vital-signs';

    public function __construct(string $value)
    {
        $myClass = new ReflectionClass ( get_class($this) );
        $constants = $myClass->getConstants();
        foreach($constants as $categoryName=>$categoryValue) {
            if($value === $categoryValue) {
                $this->__category = $categoryName;
                $this->__value = $categoryValue;
                break;
            }
        }
        if(is_null($this->__category)) {
            throw new InvalidArgumentException("Invalid category of the week");
        }
    }

    public function GetValue(): int
    {
        return $this->__value;
    }
}