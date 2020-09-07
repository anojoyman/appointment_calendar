<?php

namespace App\Repository;

use App\Entity\Appointment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Appointment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Appointment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Appointment[]    findAll()
 * @method Appointment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AppointmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Appointment::class);
    }

    /**
    * @return Appointment[] Returns an array of Appointment objects
    */
    
    // Търсим записани стойности за часове от БД за даден период - начална и крайна дата
    public function findByRange($firstDate, $lastDate)
    {
        $criteria = Criteria::create()
            ->orderBy(['appointment.for_date' => 'ASC', 'appointment.for_hour' => 'ASC']);
            
        $qb = $this->createQueryBuilder('appointment')
            ->andWhere('appointment.for_date >= :firstDate')
            ->setParameter('firstDate', $firstDate)
            ->andWhere('appointment.for_date <= :lastDate')
            ->setParameter('lastDate', $lastDate);

        $qb->addCriteria($criteria);
        return $qb->getQuery()
            ->getArrayResult()
        ;
    }
    // Търсим записаните стойности за часове в БД за месец и ги групираме в малко по-добре структуриран масив подредени по ден/час
    public function getAppointmentsForMonth(string $monthFirstDate, string $monthLastDate)
    {
        
        $appointments = $this->findByRange($monthFirstDate, $monthLastDate);
        $currentDate = $monthFirstDate;
        $result = [];
        foreach($appointments as $appointment)
        {
            $tempArray = [];
            if (!array_key_exists($appointment['for_date'], $result))
            {
                $result[$appointment['for_date']] = [];
                if(!array_key_exists($appointment['for_hour'], $result[$appointment['for_date']]))
                {
                    $result[$appointment['for_date']][$appointment['for_hour']] = $appointment;
                }
            } else
            {
                if(!array_key_exists($appointment['for_hour'], $result[$appointment['for_date']]))
                {
                    $result[$appointment['for_date']][$appointment['for_hour']] = $appointment;
                }
            }
        }
        
        return $result;

    }

}
