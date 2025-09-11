<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Participant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom : ',
                'label_attr' => ['class' => 'inline-block w-40 font-bold text-gray-700 text-right pr-4'],
                'row_attr' => ['class' => 'mb-6 flex items-center'],
                'attr' => [
                    'class' => 'w-64 rounded-md bg-white px-3 py-2 text-base text-gray-900 border border-gray-300 
                                placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 
                                outline-none transition-colors'
                ]
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom : ',
                'label_attr' => ['class' => 'inline-block w-40 font-bold text-gray-700 text-right pr-4'],
                'row_attr' => ['class' => 'mb-6 flex items-center'],
                'attr' => [
                    'class' => 'w-64 rounded-md bg-white px-3 py-2 text-base text-gray-900 border border-gray-300 
                                placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 
                                outline-none transition-colors'
                ]
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Téléphone : ',
                'label_attr' => ['class' => 'inline-block w-40 font-bold text-gray-700 text-right pr-4'],
                'row_attr' => ['class' => 'mb-6 flex items-center'],
                'required' => false,
                'attr' => [
                    'class' => 'w-64 rounded-md bg-white px-3 py-2 text-base text-gray-900 border border-gray-300 
                                placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 
                                outline-none transition-colors'
                ]
            ])
            ->add('mail', EmailType::class, [
                'label' => 'Email : ',
                'label_attr' => ['class' => 'inline-block w-40 font-bold text-gray-700 text-right pr-4'],
                'row_attr' => ['class' => 'mb-6 flex items-center'],
                'disabled' => true,
                'attr' => [
                    'class' => 'w-64 rounded-md bg-gray-50 px-3 py-2 text-base text-gray-500 border border-gray-300 
                                placeholder:text-gray-400 cursor-not-allowed outline-none'
                ]
            ])
            ->add('motPasse', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'Mot de passe : ',
                    'label_attr' => ['class' => 'inline-block w-40 font-bold text-gray-700 text-right pr-4'],
                    'row_attr' => ['class' => 'mb-6 flex items-center'],
                    'attr' => [
                        'class' => 'w-64 rounded-md bg-white px-3 py-2 text-base text-gray-900 border border-gray-300 
                                    placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 
                                    outline-none transition-colors'
                    ]
                ],
                'second_options' => [
                    'label' => 'Confirmer le mot de passe : ',
                    'label_attr' => ['class' => 'inline-block w-40 font-bold text-gray-700 text-right pr-4'],
                    'row_attr' => ['class' => 'mb-6 flex items-center'],
                    'attr' => [
                        'class' => 'w-64 rounded-md bg-white px-3 py-2 text-base text-gray-900 border border-gray-300 
                                    placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 
                                    outline-none transition-colors'
                    ]
                ],
                'invalid_message' => 'Les mots de passe doivent correspondre.',
                'required' => false,
                'mapped' => false,
            ])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom',
                'label' => 'Campus : ',
                'label_attr' => ['class' => 'inline-block w-40 font-bold text-gray-700 text-right pr-4'],
                'row_attr' => ['class' => 'mb-8 flex items-center'],
                'attr' => [
                    'class' => 'w-64 rounded-md bg-white px-3 py-2 text-base text-gray-900 border border-gray-300 
                                focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 outline-none transition-colors'
                ]
            ])
            ->add('avatar', FileType::class, [
                'label' => 'Avatar (fichier image) :',
                'mapped' => false,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}