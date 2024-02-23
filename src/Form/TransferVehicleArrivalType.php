<?php

namespace App\Form;

use App\Entity\Area;
use App\Entity\TransferVehicleArrival;
use App\Entity\TransportCompany;
use App\Repository\AreaRepository;
use App\Repository\TransportCompanyRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class TransferVehicleArrivalType extends AbstractType
{

    // 2. Declare a locally accesible variable
    public $translator;
    private $transportCompanyRepository;
    private $areaRepository;

    // 3. Autowire the translator interface and update the local value with the injected one
    public function __construct(TranslatorInterface $translator, TransportCompanyRepository $transportCompanyRepository, AreaRepository $areaRepository )
    {
        $this->translator = $translator;
        $this->transportCompanyRepository = $transportCompanyRepository;
        $this->areaRepository = $areaRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $dqlCompanies = $this->transportCompanyRepository->findBy([], ['name' => "ASC"]);
/*         $companies = [];
        foreach ($dqlCompanies as $company) {
            $companies[] = $company
        } */
        /* dd($dqlCompanies[0]); */

        $builder
            ->add('vehicleNumber', null, [
                'label' => $this->translator->trans('Vehicle Number'),
            ])
 
            ->add('vehicleType', null, [
                'label' => $this->translator->trans('Vehicle Type'),
            ])
            ->add('isCollective', null, [
                'label' => $this->translator->trans('Collective'),
            ])
            ->add('date', null, [
                'label' => $this->translator->trans('Transfer date'),
                'date_widget' => 'single_text',
                'time_widget' => 'single_text'
            ])
            ->add('voucherNumber', null, [
                'label' => $this->translator->trans('Voucher Number'),
            ])
            ->add('area')
            ->add('adultsNumber', null, [
                'label' => $this->translator->trans('Adults Number'),
            ])
            ->add('childrenNumber', null, [
                'label' => $this->translator->trans('Children Number'),
            ])
            ->add('babiesNumber', null, [
                'label' => $this->translator->trans('Babies Number'),
            ])
            ->add('transportCompany', EntityType::class, [
                'class' => TransportCompany::class,
                'choices' => $dqlCompanies,
                'label' => $this->translator->trans('Transport Company'),
            ])
/*             ->add('staff', EntityType::class, [
                'label' => "Reps",
                'class' => User::class,
                'autocomplete' =>false,
                'choices' => $dqlCompanies,
                'data' => $repList[0]
             
            ] ) */

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TransferVehicleArrival::class,
        ]);
    }
}
