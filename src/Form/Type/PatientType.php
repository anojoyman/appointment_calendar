<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class PatientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Създаваме формата за записване на данните на потребителя
        $builder
            ->add('name', TextType::class)
            ->add('email', TextType::class)
            ->add('phone', TextType::class)
            ->add('save', SubmitType::class)
        ;
    }
}

?>