<?php

namespace App\Form;

use App\Entity\Campus;
use App\Form\Models\SortieSearch;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieFilterSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            //changer le type pour entity type
            ->add('campus', EntityType::class, [
                'label' => 'Campus :',
                'required' => false,
                'placeholder' => 'Sélectionnez un campus',
                'class' => Campus::class,
                'attr' => [
                    'class' => 'block w-48 rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 
                                -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 
                                focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6'
                ]
            ])
            ->add('sortieNom', TextType::class, [
                'label' => 'Nom de la sortie :',
                'required' => false,
                'attr' => [
                    'class' => 'block w-48 rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 
                                -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 
                                focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6',
                    'placeholder' => 'Rechercher par nom...'
                ]
            ])
            ->add('premiereDate', DateTimeType::class, [
                'label' => 'Date de début :',
                'required' => false,
                'widget' => 'single_text',
                'html5' => true,
                'attr' => [
                    'class' => 'block w-48 rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 
                                -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 
                                focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6'
                ]
            ])
            ->add('derniereDate', DateTimeType::class, [
                'label' => 'Date de fin :',
                'required' => false,
                'widget' => 'single_text',
                'html5' => true,
                'attr' => [
                    'class' => 'block w-48 rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 
                                -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 
                                focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6'
                ]
            ])
            ->add('sortiesOrganisees', CheckboxType::class, [
                'label' => 'Sorties dont je suis l\'organisateur/trice',
                'required' => false,
                'attr' => [
                    'class' => 'block  bg-white px-3 py-1.5 text-base text-gray-900 outline-1 
                                -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 
                                focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6'
                ]
            ])
            ->add('sortiesInscrites', CheckboxType::class, [
                'label' => 'Sorties auxquelles je suis inscrit/e',
                'required' => false,
                'attr' => [
                    'name'=>'insciption',
                    'class' => 'block  bg-white px-3 py-1.5 text-base text-gray-900 outline-1 
                                -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 
                                focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6'
                ]
            ])
            ->add('sortiesNonInscrites', CheckboxType::class, [
                'label' => 'Sorties auxquelles je ne suis pas inscrit/e',
                'required' => false,
                'attr' => [
                    'name'=>'insciption',
                    'class' => 'block  bg-white px-3 py-1.5 text-base text-gray-900 outline-1 
                                -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 
                                focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6'
                ]
            ])
            ->add('sortiesPassees', CheckboxType::class, [
                'label' => 'Sorties passées',
                'required' => false,
                'attr' => [
                    'class' => 'block  bg-white px-3 py-1.5 text-base text-gray-900 outline-1 
                                -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 
                                focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6'
                ]
            ])
            ->add('rechercher', SubmitType::class, [
                'label' => 'Rechercher',
                'attr' => [
                    'class' => 'w-32 h-15 bg-slate-300/75 hover:bg-slate-400/50 rounded-md'
                ]
            ])
            ->add('reinitialiser', ResetType::class, [
                'label' => 'Réinitialiser',
                'attr' => [
                    'class' => 'w-32 h-15 bg-slate-300/75 hover:bg-slate-400/50 rounded-md'
                ]
            ])
            ->
            add('creer', ButtonType::class, [
                'label' => 'Créer une sortie',
                'attr' => [
                    'class' => 'w-40 h-15 bg-indigo-200/75 hover:bg-indigo-300/50 rounded-md mb-10']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SortieSearch::class
        ]);
    }
}