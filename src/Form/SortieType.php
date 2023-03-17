<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom',TextType::class,[
                'label'=> 'Nom de la sortie  '
            ])
            ->add('campus', EntityType::class, [
                'label'=> 'campus  ',
                'class' => Campus::class,
                'choice_label' => 'nom'
            ])
            ->add('ville', EntityType::class,[
            'label' => 'Ville ',
            'class' => Ville::class,
            'choice_label' => 'nom',
                'attr' => [
                    'id' => 'sortie_ville',

    ]
            ])
            ->add('lieu', EntityType::class,[
                'label' => 'Lieu  ',
                'class' => Lieu::class,
                'attr' => [
                    'id' => 'sortie_lieu'],
                'choice_label' => 'nom'

            ])
            ->add('dateHeureDebut',DateTimeType::class,[
                'label' => 'Dâte et heure de la sortie  ',
                'date_widget'=> 'single_text',
                'time_widget'=> 'single_text',
                'html5' => true
            ])
            ->add('duree', NumberType::class,[
                'label'=> 'Durée de la sortie : (en minutes)'
            ])
            ->add('dateLimiteInscription', DateTimeType::class,[
                'label' => 'Dâte limite d\'inscription  ',
                'date_widget'=> 'single_text',
                'time_widget'=> 'single_text',
                'html5' => true
            ])
            ->add('nbInscriptionMax', NumberType::class,[
                'label' => 'Nombre de places  '
            ])
            ->add('infosSortie', TextareaType::class,[
                'label'=> 'Descriptions et infos  '
            ])
            ->add('inscriptionAuto', CheckboxType::class, [
                'label' => 'Voulez-vous vous inscrire à la sortie ? ',
                'mapped'=> false,
                'required' => false
            ]);
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}