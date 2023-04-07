<?php

namespace Modules\Contacts\Form;

use Modules\Contacts\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class,
                [
                    'label' => 'Jméno',
                    'required' => true,
                    'trim' => true,
                    'attr' => ['placeholder' => 'Jiří'],
                ])
            ->add('lastName', TextType::class,
                [
                    'label' => 'Příjmení',
                    'required' => true,
                    'trim' => true,
                    'attr' => ['placeholder' => 'Novák'],
                ])
            ->add('phone', TextType::class,
                [
                    'label' => 'Telefonní číslo',
                    'required' => false,
                    'trim' => true,
                    'attr' => ['placeholder' => '+420 777 666 555'],
                ])
            ->add('email', EmailType::class,
                [
                    'label' => 'Email',
                    'required' => true,
                    'trim' => true,
                    'attr' => ['placeholder' => 'email@bůhvíkam.cz'],
                ])
            ->add('note', TextareaType::class,
                [
                    'label' => 'Poznámka',
                    'required' => false,
                ])
            ->add('submit', SubmitType::class, ['label' => 'Přidat'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
