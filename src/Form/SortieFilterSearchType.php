<?php
namespace App\Form;
use App\Entity\Campus;
use App\Form\Models\SortieSearch;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
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
                'label' => 'Campus',
                'required' => false,
                'placeholder' => 'Sélectionnez un campus',
                'class' => Campus::class,
                'attr' => [
                    'class' => 'form-select'
                ]
            ])
            ->add('sortieNom', TextType::class, [
                'label' => 'Nom de la sortie',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Rechercher par nom...'
                ]
            ])
            ->add('premiereDate', DateTimeType::class, [
                'label' => 'Date de début',
                'required' => false,
                'widget' => 'single_text',
                'html5' => true,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('derniereDate', DateTimeType::class, [
                'label' => 'Date de fin',
                'required' => false,
                'widget' => 'single_text',
                'html5' => true,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('sortiesOrganisees', CheckboxType::class, [
                'label' => 'Sorties dont je suis l\'organisateur/trice',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ])
            ->add('sortiesInscrites', CheckboxType::class, [
                'label' => 'Sorties auxquelles je suis inscrit/e',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ])
            ->add('sortiesNonInscrites', CheckboxType::class, [
                'label' => 'Sorties auxquelles je ne suis pas inscrit/e',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ])
            ->add('sortiesPassees', CheckboxType::class, [
                'label' => 'Sorties passées',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ])
            ->add('rechercher', SubmitType::class, [
                'label' => 'Rechercher',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ])
            ->add('reinitialiser', ResetType::class, [
                'label' => 'Réinitialiser',
                'attr' => [
                    'class' => 'btn btn-secondary'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SortieSearch::class
        ]);
    }
}