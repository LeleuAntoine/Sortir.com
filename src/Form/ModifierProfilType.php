<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Participant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class ModifierProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
            ])
            ->add('username', TextType::class, [
                'label' => 'Pseudo',
            ])
            ->add('telephone', TelType::class, [
                'label' => 'Téléphone',
            ])
            ->add('mail', EmailType::class)
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe ne sont pas identiques',
                'required' => true,
                'first_options'  => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmation mot de passe'],
            ])
            ->add('photo', FileType::class, [
                'label' => 'Photo',

                'mapped' => false,

                'required' => false,

                'constraints' => [
                    new Image([
                        'maxSize' => '5M',
                        'maxSizeMessage' => 'La photo est dépasse la taille maximale autorisée {{limit}}',
                        'mimeTypesMessage' => 'Veuillez choisir un format d\'image valide',
                    ])
                ],
                'attr' => ['::after' => 'Choisir une photo']
            ])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'label' => 'Campus',
                'choice_label' => 'nom',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
