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
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType; // ✅ Bon import
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddSortieFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, [
                'label' => 'Nom de la sortie :',
                'attr' => [
                    'class' => 'block w-48 rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 
                                -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 
                                focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6'
                ]
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'label' => 'Date & heure de la sortie :',
                'widget' => 'single_text',
                'html5' => true,
                'attr' => [
                    'class' => 'block w-48 rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 
                                -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 
                                focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6'
                ]
            ])
            ->add('dateLimiteInscription', DateType::class, [
                'label' => 'Date limite d\'inscription :',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'block w-48 rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 
                                -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 
                                focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6'
                ]
            ])
            ->add('nbInscriptionMax', IntegerType::class, [
                'label' => 'Nombre de places :',
                'attr' => [
                    'min' => 1,
                    'max' => 20,
                    'class' => 'block w-24 rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 
                                -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 
                                focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6'
                ]
            ])
            ->add('duree', null, [
                'label' => 'Durée de la sortie :',
                'attr' => [
                    'class' => 'block w-24 rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 
                                -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 
                                focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6'
                ]
            ] )
            ->add('infosSortie', TextareaType::class, [
                'label' => 'Infos sortie :',
                'attr' => [
                    'class' => 'block w-96 h-28 rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 
                                -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 
                                focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6'
                ]
            ])
            ->add('ville', EntityType::class, [
                'label' => 'Ville de la sortie :',
                'class' => Ville::class,
                'mapped' => false,
                'choice_label' => 'nom',
                'attr' => [
                    'class' => 'block w-48 rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 
                                -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 
                                focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6'
                ]
            ])
            ->add('lieu', EntityType::class, [
                'label' => 'Lieu de la sortie :',
                'class' => Lieu::class,
                'choice_label' => 'nom',
                'attr' => [
                    'class' => 'block w-48 rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 
                                -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 
                                focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6'
                ]
            ])
            /*->add('latitude', EntityType::class, [
                'label' => 'Latitude :',
                'class' => Lieu::class,
                'choice_label' => 'latitude',
                'attr' => [
                    'class' => 'block w-48 rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 
                                -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 
                                focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6'
                ]
            ])
            ->add('longitude', EntityType::class, [
                'label' => 'Longitude :',
                'class' => Lieu::class,
                'choice_label' => 'longitude',
                'attr' => [
                    'class' => 'block w-48 rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 
                                -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 
                                focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6'
                ]
            ])*/
            ->add('enregistrer', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => [
                    'class' => 'w-32 h-15 bg-indigo-200/75 hover:bg-indigo-300/50 rounded-md'
                ]
            ])
            ->add('publier', SubmitType::class, [
                'label' => 'Publier la sortie',
                'attr' => [
                    'class' => 'w-40 h-15 bg-emerald-300/75 hover:bg-emerald-400/50 rounded-md'
                ]
            ])
            ->add('annuler', SubmitType::class, [
                'label' => 'Annuler',
                'attr' => [
                    'class' => 'w-32 h-15 bg-slate-300/75 hover:bg-slate-400/50 rounded-md'
                ]
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
