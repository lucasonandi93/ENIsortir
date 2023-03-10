<?php

namespace App\Form;

use App\Entity\Campus;
use App\Form\modele\ModeleFiltres;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FiltreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('campus',EntityType::class,[
                'required' => false,
                'class'=> Campus::class,
                'choice_label' => 'nom'
            ])
            ->add('nom',SearchType::class,[
                'required' => false,
                'label'=> 'Le nom de la sortie contient : ',
                'attr' => [ 'placeholder'=> 'Recherche'
                ]
            ])
            ->add('dateSortie', DateType::class,[
                'required' => false,
                'label'=> 'Entre le ',
                'widget' => 'single_text',
                'html5' => true
            ])
            ->add('dateCloture', DateType::class,[
                'required' => false,
                'label'=> 'et le ',
                'widget' => 'single_text',
                'html5' => true
            ])
            ->add('sortieOrganisateur', CheckboxType::class,[
                'required' => false,
                'label' => 'Sorties dont je suis l\'organisateur.rice'
            ])
            ->add('sortieInscrit', CheckboxType::class,[
                'required' => false,
                'label' => 'Sorties auxquelles je suis inscrit.e'
            ])
            ->add('sortiePasInscrit', CheckboxType::class,[
                'required' => false,
                'label' => 'Sorties auxquelles je ne suis pas inscrit.e'
            ])
            ->add('sortiePasses', CheckboxType::class,[
                'required' => false,
                'label' => 'Sorties passées'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class"=> ModeleFiltres::class
        ]);
    }
}