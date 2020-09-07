<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use libphonenumber\PhoneNumberUtil;

class Patient
{
    private $name;
    private $email;
    private $phone;

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        // Задаваме метода на валидиране на полетата на потребителя в базата данни 
        $metadata->addPropertyConstraint('name', new Assert\NotBlank());
        $metadata->addPropertyConstraint(
            'name',
            new Assert\Length([
                'min' => 3,
                'minMessage' => 'Името трябва да съдържа минимум 3 знака'])
        );
        $metadata->addPropertyConstraint(
            'email',
            new Assert\Email([
                'message' => 'Моля въведете валиден имейл'
            ]));
        $metadata->addPropertyConstraint(
            'email',
            new Assert\Length(['min' => 3])
        );
        $metadata->addPropertyConstraint('phone', new Assert\NotBlank());
        $metadata->addPropertyConstraint(
            'phone',
            new Assert\Length([
                'min' => 6,
                'minMessage' => 'Телефона трябва да съдържа минимум 6 цифри']),
            
        );
        $metadata->addGetterConstraint(
            'phoneValid', 
            new Assert\IsTrue([
                'message' => 'Телефонния номер е невалиден'
        ]));
        
        
    }
    

    public function isPhoneValid()
    {
        $isValid = false;
        // Използваме библиотеката libphonenumber за валидиране на телефонни номера от България(в случая)
        $phoneUtil = PhoneNumberUtil::getInstance();
        
        $phone = $phoneUtil->parse($this->phone, 'BG');
        $isValid = $phoneUtil->isValidNumber($phone);
        
        
        return $isValid;
    }
    
    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

}