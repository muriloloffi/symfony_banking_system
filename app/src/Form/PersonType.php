<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('name', TextType::class, [
            'label' => "Nome da Pessoa:",
            'attr' => [
                'class' => 'form-control'
            ]
        ])
        ->add('cpf', TextType::class, [
            'label' => "Número do Cadastro de Pessoa Física (CPF):",
            'attr' => [
                'class' => 'form-control'
            ]
        ])
        ->add('addressOne', TextType::class, [
            'label' => "Endereço (rua e número):",
            'attr' => [
                'class' => 'form-control'
            ]
        ])
        ->add('submit', SubmitType::class, [
            'label' => "Salvar",
            'attr' => [
                'class' => 'btn btn-primary'
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
