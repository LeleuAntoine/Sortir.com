<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Repository\CampusRepository;
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
    private $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class,
                ['label' => 'Nom de la sortie : ',])
            ->add('dateHeureDebut', DateTimeType::class,
                ['label' => 'Date et heure de la sortie : ',
                    'model_timezone' => 'Europe/Paris',
                    'widget'=>'choice',
                    'years'=> range(date('Y'), date('y')+2001)])
            ->add('dateLimiteInscription', DateTimeType::class,
                ['label' => 'Date limite d inscription : ',
                    'model_timezone' => 'Europe/Paris',
                    'widget'=>'choice',
                    'years'=> range(date('Y'), date('y')+2001)])
            ->add('nbInscriptionMax', IntegerType::class,
                ['label' => 'Nombre de places : '])
            ->add('duree', IntegerType::class,
                ['label' => 'Duree : '])
            ->add('infosSortie', TextareaType::class,
                ['label' => 'Description et infos : '])
            ->add('siteOrganisateur', EntityType::class,
                ['class' => Campus::class,
                    'choice_label' => 'nom',
                    'label' => 'Campus : ',
                    'disabled' => true])
            ->add('ville', EntityType::class,
                ['class' => Ville::class,
                    'placeholder' => 'Seletionnez votre ville',
                    'choice_label' => 'nom',
                    'label' => 'Ville : ']);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
        $builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'onPreSubmit'));
    }

    protected function ajoutElement(FormInterface $form, ?Ville $ville, ?Lieu $lieu)
    {
        $form->add('codePostal', EntityType::class, [
            'disabled' => true,
            'class' => Ville::class,
            'placeholder' => $ville ? $ville->getCodePostal() : '',
            'label' => 'Code postal : ',
            'mapped' => false,
            'attr' => array('readonly' => true)])
            ->add('lieu', EntityType::class, [
                'class' => 'App\Entity\Lieu',
                'placeholder' => $ville ? 'Selectionner votre lieu' : 'Selectionnez la ville',
                'required' => false,
                'auto_initialize' => false,
                'choices' => $ville ? $ville->getLieux() : []])
            ->add('rue', EntityType::class, [
                'disabled' => true,
                'class' => Lieu::class,
                'placeholder' => $lieu ? $lieu->getRue() : '',
                'label' => 'Rue : ',
                'mapped' => false,
                'attr' => array('readonly' => true)])
            ->add('latitude', EntityType::class, [
                'disabled' => true,
                'class' => Lieu::class,
                'placeholder' => $lieu ? $lieu->getLatitude() : '',
                'label' => 'Latitude : ',
                'mapped' => false,
                'attr' => array('readonly' => true)])
            ->add('longitude', EntityType::class, [
                'disabled' => true,
                'class' => Lieu::class,
                'placeholder' => $lieu ? $lieu->getLongitude() : '',
                'label' => 'Longitude : ',
                'mapped' => false,
                'attr' => array('readonly' => true)]);
    }

    function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        $ville = $this->em->getRepository('App:Ville')->find($data['ville']);
        $lieu = $this->em->getRepository('App:Lieu')->find($data['lieu']);

        $this->ajoutElement($form, $ville, $lieu);
    }

    function onPreSetData(FormEvent $event)
    {
        $sortie = $event->getData();
        $form = $event->getForm();

        $ville = $sortie->getVille() ? $sortie->getVille() : null;
        $lieu = $sortie->getLieu() ? $sortie->getLieu() : null;

        $this->ajoutElement($form, $ville, $lieu);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
