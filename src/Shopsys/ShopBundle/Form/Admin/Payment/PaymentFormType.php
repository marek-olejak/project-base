<?php

namespace Shopsys\ShopBundle\Form\Admin\Payment;

use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Shopsys\ShopBundle\Form\DomainsType;
use Shopsys\ShopBundle\Form\FileUploadType;
use Shopsys\ShopBundle\Form\Locale\LocalizedType;
use Shopsys\ShopBundle\Form\YesNoType;
use Shopsys\ShopBundle\Model\Payment\PaymentData;
use Shopsys\ShopBundle\Model\Pricing\Vat\VatFacade;
use Shopsys\ShopBundle\Model\Transport\TransportFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class PaymentFormType extends AbstractType
{
    /**
     * @var \Shopsys\ShopBundle\Model\Transport\TransportFacade
     */
    private $transportFacade;

    /**
     * @var \Shopsys\ShopBundle\Model\Pricing\Vat\VatFacade
     */
    private $vatFacade;

    public function __construct(TransportFacade $transportFacade, VatFacade $vatFacade)
    {
        $this->transportFacade = $transportFacade;
        $this->vatFacade = $vatFacade;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', LocalizedType::class, [
                'main_constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter name']),
                ],
                'options' => [
                    'constraints' => [
                        new Constraints\Length(['max' => 255, 'maxMessage' => 'Name cannot be longer than {{ limit }} characters']),
                    ],
                ],
            ])
            ->add('domains', DomainsType::class, [
                'required' => false,
            ])
            ->add('hidden', YesNoType::class, ['required' => false])
            ->add('czkRounding', YesNoType::class, ['required' => false])
            ->add('transports', ChoiceType::class, [
                'choice_list' => new ObjectChoiceList($this->transportFacade->getAll(), 'name', [], null, 'id'),
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ])
            ->add('vat', ChoiceType::class, [
                'required' => true,
                'choice_list' => new ObjectChoiceList($this->vatFacade->getAll(), 'name', [], null, 'id'),
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter VAT rate']),
                ],
            ])
            ->add('description', LocalizedType::class, [
                'required' => false,
                'type' => TextareaType::class,
            ])
            ->add('instructions', LocalizedType::class, [
                'required' => false,
                'type' => CKEditorType::class,
            ])
            ->add('image', FileUploadType::class, [
                'required' => false,
                'file_constraints' => [
                    new Constraints\Image([
                        'mimeTypes' => ['image/png', 'image/jpg', 'image/jpeg', 'image/gif'],
                        'mimeTypesMessage' => 'Image can be only in JPG, GIF or PNG format',
                        'maxSize' => '2M',
                        'maxSizeMessage' => 'Uploaded image is to large ({{ size }} {{ suffix }}). '
                            . 'Maximum size of an image is {{ limit }} {{ suffix }}.',
                    ]),
                ],
            ]);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PaymentData::class,
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
