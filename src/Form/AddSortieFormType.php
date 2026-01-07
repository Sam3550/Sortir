<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddSortieFormType extends AbstractType
{
    private const INPUT_CLASS = 'block w-48 rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 
                -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 
                focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, [
                'label' => 'Nom de la sortie :',
                'attr' => ['class' => self::INPUT_CLASS]
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'label' => 'Date & heure de la sortie :',
                'widget' => 'single_text',
                'html5' => true,
                'attr' => [
                    'id' => 'dateHeureDebut',
                    'class' => self::INPUT_CLASS
                ]
            ])
            ->add('dateLimiteInscription', DateType::class, [
                'label' => 'Date limite d\'inscription :',
                'widget' => 'single_text',
                'attr' => [
                    'id' => 'dateLimiteInscription',
                    'class' => self::INPUT_CLASS
                ]
            ])
            ->add('nbInscriptionMax', IntegerType::class, [
                'label' => 'Nombre de places :',
                'attr' => [
                    'min' => 1,
                    'max' => 100,
                    'class' => 'block w-24 rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 
                                -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 
                                focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6'
                ]
            ])
            ->add('duree', null, [
                'label' => 'Durée (en minutes) :',
                'attr' => [
                    'class' => 'block w-24 rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 
                                -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 
                                focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6'
                ]
            ])
            ->add('infosSortie', TextareaType::class, [
                'label' => 'Infos sortie :',
                'attr' => [
                    'class' => 'block w-96 h-28 rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 
                                -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 
                                focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6'
                ]
            ])
            // ========================================
            // CHOIX DU TYPE DE LIEU
            // ========================================
            ->add('lieuType', ChoiceType::class, [
                'label' => 'Type de lieu :',
                'mapped' => false,
                'expanded' => true,
                'choices' => [
                    'Lieu existant' => 'existant',
                    'Nouvelle adresse' => 'adresse',
                    'Coordonnées GPS' => 'gps',
                ],
                'data' => 'existant',
                'attr' => ['class' => 'flex gap-4']
            ])
            // ========================================
            // OPTION 1 : LIEU EXISTANT
            // ========================================
            ->add('ville', EntityType::class, [
                'label' => 'Ville :',
                'class' => Ville::class,
                'mapped' => false,
                'required' => false,
                'choice_label' => 'nom',
                'placeholder' => '-- Sélectionner une ville --',
                'attr' => ['class' => self::INPUT_CLASS, 'id' => 'lieu-ville']
            ])
            ->add('lieu', EntityType::class, [
                'label' => 'Lieu :',
                'class' => Lieu::class,
                'required' => false,
                'choice_label' => 'nom',
                'placeholder' => '-- Sélectionner un lieu --',
                'attr' => ['class' => self::INPUT_CLASS, 'id' => 'lieu-existant']
            ])
            // ========================================
            // OPTION 2 : NOUVELLE ADRESSE
            // ========================================
            ->add('nouveauLieuNom', TextType::class, [
                'label' => 'Nom du lieu :',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => self::INPUT_CLASS, 'placeholder' => 'Ex: Restaurant Le Provençal']
            ])
            ->add('nouveauLieuNumero', TextType::class, [
                'label' => 'Numéro :',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'block w-20 rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 
                                -outline-offset-1 outline-gray-300 sm:text-sm/6', 'placeholder' => 'Ex: 12']
            ])
            ->add('nouveauLieuRue', TextType::class, [
                'label' => 'Rue :',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'block w-64 rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 
                                -outline-offset-1 outline-gray-300 sm:text-sm/6', 'placeholder' => 'Ex: Rue de la Paix']
            ])
            ->add('nouveauLieuCodePostal', TextType::class, [
                'label' => 'Code postal :',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'block w-28 rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 
                                -outline-offset-1 outline-gray-300 sm:text-sm/6', 'placeholder' => 'Ex: 44000']
            ])
            ->add('nouveauLieuVille', TextType::class, [
                'label' => 'Ville :',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => self::INPUT_CLASS, 'placeholder' => 'Ex: Nantes']
            ])
            // ========================================
            // OPTION 3 : COORDONNÉES GPS
            // ========================================
            ->add('gpsNom', TextType::class, [
                'label' => 'Nom du lieu :',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => self::INPUT_CLASS, 'placeholder' => 'Ex: Point de rendez-vous']
            ])
            ->add('gpsLatitude', NumberType::class, [
                'label' => 'Latitude :',
                'mapped' => false,
                'required' => false,
                'scale' => 6,
                'attr' => ['class' => self::INPUT_CLASS, 'placeholder' => 'Ex: 47.218371', 'step' => '0.000001']
            ])
            ->add('gpsLongitude', NumberType::class, [
                'label' => 'Longitude :',
                'mapped' => false,
                'required' => false,
                'scale' => 6,
                'attr' => ['class' => self::INPUT_CLASS, 'placeholder' => 'Ex: -1.553621', 'step' => '0.000001']
            ])
            ->add('gpsVille', EntityType::class, [
                'label' => 'Ville associée :',
                'class' => Ville::class,
                'mapped' => false,
                'required' => false,
                'choice_label' => 'nom',
                'placeholder' => '-- Sélectionner une ville --',
                'attr' => ['class' => self::INPUT_CLASS]
            ])
            // ========================================
            // BOUTONS
            // ========================================
            ->add('enregistrer', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => ['class' => 'w-32 h-15 bg-indigo-200/75 hover:bg-indigo-300/50 rounded-md']
            ])
            ->add('publier', SubmitType::class, [
                'label' => 'Publier la sortie',
                'attr' => ['class' => 'w-40 h-15 bg-emerald-300/75 hover:bg-emerald-400/50 rounded-md']
            ])
            ->add('annuler', SubmitType::class, [
                'label' => 'Annuler',
                'attr' => ['class' => 'w-32 h-15 bg-slate-300/75 hover:bg-slate-400/50 rounded-md']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
