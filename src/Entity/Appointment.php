<?php

namespace App\Entity;

use App\Repository\AppointmentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AppointmentRepository::class)
 */
class Appointment
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id_appointment;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $for_date;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $for_hour;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $patient_info;

    /**
     * @ORM\Column(type="text")
     */
    private $created_at;


    public function getIdAppointment(): ?int
    {
        return $this->id_appointment;
    }

    public function setIdAppointment(int $id_appointment): self
    {
        $this->id_appointment = $id_appointment;

        return $this;
    }

    public function getForDate(): ?string
    {
        return $this->for_date;
    }

    public function setForDate(string $for_date): self
    {
        $this->for_date = $for_date;

        return $this;
    }

    public function getForHour(): ?string
    {
        return $this->for_hour;
    }

    public function setForHour(string $for_hour): self
    {
        $this->for_hour = $for_hour;

        return $this;
    }

    public function getPatientInfo(): ?string
    {
        return $this->patient_info;
    }

    public function setPatientInfo(string $patient_info): self
    {
        $this->patient_info = $patient_info;

        return $this;
    }

    public function getCreatedAt(): ?string
    {
        return $this->created_at;
    }

    public function setCreatedAt(string $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

}
