<?php

namespace App\Entity;


use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class Day
{
    private $dDate;

    private function __construct()
    {
        // Created empty object
        
    }

    public static function createFromDate($date): ?self
    {

        $dayDate = new \DateTime($date);
        if (!$dayDate)
            return false;
        
        $d = new Day();
        $d->dDate = $dayDate;
        return $d;
    }


   public function getDate()
   {
        return $this->dDate;
   }


}