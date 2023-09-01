<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class,[
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => 'E-Mail'

            ])
            ->add('firstname', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => 'Prénom'
            ])
            ->add('last_name', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => 'Nom'
            ])
            ->add('phone_number', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => 'N° de téléphone'
            ])
            ->add('address', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => 'Adresse'
            ])
            ->add('rgpdConsent', CheckboxType::class, [
                'mapped' => false,
                'attr' => [
                    'class' => 'mt-3'
                ],
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
                'label' => 'En m\'inscrivant à ce site j\'accepte...'
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => [
                    'autocomplete' => 'new-password',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
                'label' => 'Mot de passe'
            ])
            // ->add('nomtitulaire', TextType::class, [
            //     'attr' => [
            //         'class' => 'form-control'
            //     ],
            //     'constraints' => [
            //         new NotBlank([
            //             'message' => 'Please enter a name',
            //         ]),
            //     ],
            //     'label' => 'Nom du titulaire'
            // ])
            // ->add('cartnumber', TextType::class, [
            //     'attr' => [
            //         'class' => 'form-control'
            //     ],'constraints' => [
            //         new NotBlank([
            //             'message' => 'Please enter a cart number',
            //         ]),
            //     ],
            //     'label' => 'Numéro de la carte'
            // ])
            // ->add('expirationdate', TextType::class, [
            //     'attr' => [
            //         'class' => 'form-control'
            //     ],
            //     'constraints' => [
            //         new NotBlank([
            //             'message' => 'Please enter expiration date',
            //         ]),
            //     ],
            //     'label' => 'Date d\' Expiration'
            // ])
            // ->add('numbercvc', TextType::class, [
            //     'attr' => [
            //         'class' => 'form-control'
            //     ],
            //     'constraints' => [
            //         new NotBlank([
            //             'message' => 'Please enter CVC',
            //         ]),
            //         new Length([
            //             'min' => 3,
            //             'minMessage' => 'Your password should be at least {{ limit }} characters',
            //             // max length allowed by Symfony for security reasons
            //             'max' => 3,
            //         ]),
            //     ],
            //     'label' => 'Numéro de CVC'
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
