<?php

namespace App\Entity;

use DateTime;

class Month
{
    protected $days = [];
    protected $info = [];

    private function __construct()
    {
        // Създаваме празен обект, целта е да може да се направи проверка, при инициализация за валидна дата, а това е невъзможно с __construct()
    }

    public static function createFromDate($date)
    {
        // Проверка за валидна дата, в противен случай връщаме False
        $dayDate = new DateTime($date);
        if (!$dayDate)
            return false;
        
        // Създаваме празен обект
        $m = new Month();

        // Инициализираме необходимите данни за месеца - начален ден от първа седмица, първи ден от месеца, последен ден от месеца, последен ден от последна седмица
        $firstDayOfMonth = date('Y-m-01', $dayDate->format('U'));
        $info['firstDay'] = $firstDayOfMonth;
        $lastDayOfMonth = date('Y-m-t', strtotime($firstDayOfMonth));
        $info['lastDay'] = $lastDayOfMonth;
        $lastDayOfLastWeek = date('Y-m-d', strtotime('next sunday', strtotime($lastDayOfMonth)));
        $firstDayOfMonthWeekDay = date('w', strtotime($firstDayOfMonth));
        
        // Ако първия ден на месеца не е понеделник, то значи в пътвата седмица имаме дни от предишния месец и търсим 'предишен' понеделник
        if ($firstDayOfMonthWeekDay != 1)
        {
            $firstDayOfFirstWeek = date('Y-m-d', strtotime('last monday', strtotime($firstDayOfMonth)));
        } else
        {
            $firstDayOfFirstWeek = $firstDayOfMonth;
        }
        $info['firstDayOfFirstWeek'] = $firstDayOfFirstWeek;
        $info['lastDayOfLastWeek'] = $lastDayOfLastWeek;
        $currentDate = $firstDayOfFirstWeek;

        // Обхождаме дните от седмиците на месеца и ги добавяме към масива $days
        while ($currentDate <= $lastDayOfLastWeek)
        {
            $m->days[] = $currentDate;
            $currentDate = date('Y-m-d', strtotime($currentDate.' + 1 day'));
            
        }
        $m->info = $info;
        return $m;
    }

    
    public function getDays(): ?array
    {
        return $this->days;
    }

    public function getInfo(): ?array
    {
        return $this->info;
    }
}