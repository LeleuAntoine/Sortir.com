<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class,
                ['label' => 'Nom de la sortie : '])
            ->add('dateHeureDebut', DateTimeType::class,
                ['label' => 'Date et heure de la sortie : '])
            ->add('dateLimiteInscription', DateTimeType::class,
                ['label' => 'Date limite d inscription : '])
            ->add('nbInscriptionMax', IntegerType::class,
                ['label' => 'Nombre de places : '])
            ->add('duree', IntegerType::class,
                ['label' => 'Duree : '])
            ->add('infosSortie', TextareaType::class,
                ['label' => 'Description et infos : '])
            ->add('campus', EntityType::class,
                ['class' => Campus::class,
                    'choice_label' => 'nom',
                    'label' => 'Campus : ',
                    'mapped' => false])
            ->add('ville', EntityType::class,
                ['class' => Ville::class,
                    'placeholder' => 'Seletionnez votre ville',
                    'choice_label' => 'nom',
                    'label' => 'Ville : ',
                    'mapped' => false]);

        $builder->get('ville')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();
                $this->ajoutLieu($form->getParent(), $form->getData());
            }
        );

        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            function (FormEvent $event) {
                $data = $event->getData();

                /* @var $lieu Lieu */
                $lieu = $data ? $data->getId() : null;
                $form = $event->getForm();
                if ($lieu) {
                    $ville = $lieu->getVille();
                    $this->ajoutLieu($form, $ville);
                    $this->ajoutAutresChamps($form, $lieu);
                    $form->get('ville')->setData($ville);
                    $form->get('lieu')->setData($lieu);
                } else {
                    $this->ajoutLieu($form, null);
                    $this->ajoutAutresChamps($form, null);
                }
            }
        );
    }

    /**
     * Rajoute un champ lieu au formulaire
     * @param FormInterface $form
     * @param Ville $ville
     */
    private function ajoutLieu(FormInterface $form, ?Ville $ville)
    {
        $builder = $form->getConfig()->getFormFactory()->createNamedBuilder(
            'lieu',
            EntityType::class,
            null,
            [
                'class' => 'App\Entity\Lieu',
                'placeholder' => $ville ? 'Selectionner votre lieux' : 'Selectionnez la ville',
                'mapped' => false,
                'required' => false,
                'auto_initialize' => false,
                'choices' => $ville ? $ville->getLieux() : []
            ]
        );
        $form->add('codePostal', EntityType::class, ['disabled' => true,
            'class' => Ville::class,
            'placeholder' => $ville ? $ville->getCodePostal() : '',
            'label' => 'Code postal : ']);

        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();
                $this->ajoutAutresChamps($form->getParent(), $form->getData());
            }
        );
        $form->add($builder->getForm());
    }

    private function ajoutAutresChamps(FormInterface $form, ?Lieu $lieu)
    {
        $form->add('rue', EntityType::class, [
            'disabled' => true,
            'class' => Lieu::class,
            'placeholder' => $lieu ? $lieu->getRue() : '',
            'label' => 'Rue : '])
            ->add('latitude', EntityType::class, [
                'disabled' => true,
                'class' => Lieu::class,
                'placeholder' =>  $lieu ? $lieu->getLatitude() : '',
                'label' => 'Latitude : '])
            ->add('longitude', EntityType::class, [
                'disabled' => true,
                'class' => Lieu::class,
                'placeholder' => $lieu ?  $lieu->getLongitude() : '',
                'label' => 'Longitude : ']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
