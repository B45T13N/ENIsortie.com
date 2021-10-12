<?php

namespace App\Form;

use App\Entity\Sortie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreationSortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('date', DateType::class, [
                'html5' => true,
                'widget' => 'single_text',
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée (En minutes)'
            ])
            ->add('dateLimite', DateType::class, [
                'html5' => true,
                'widget' => 'single_text',
            ])
            ->add('nombreInscriptionsMax')
            ->add('description')
            ->add('lieu')
            ->add('etat', ChoiceType::class, [
                'choices' => [
                    'Créée' => 'Créée',
                    'Ouverte' => 'Ouverte',
                    'Clôturée' => 'Clôturée',
                    'En cours' => 'En cours',
                    'Passée' => 'Passée',
                    'Annulée' => 'Annulée'
                ],
                'multiple' => false
            ])
            ->add('campus', ChoiceType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
