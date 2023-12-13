<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Pricing\Group;

use App\DataFixtures\Demo\PricingGroupDataFixture;
use App\Model\Customer\BillingAddressDataFactory;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupData;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade;
use Tests\App\Test\TransactionFunctionalTestCase;

class PricingGroupFacadeTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private PricingGroupFacade $pricingGroupFacade;

    /**
     * @inject
     */
    private CustomerUserFacade $customerUserFacade;

    /**
     * @inject
     */
    private CustomerUserDataFactoryInterface $customerUserDataFactory;

    /**
     * @inject
     */
    private CustomerUserUpdateDataFactoryInterface $customerUserUpdateDataFactory;

    /**
     * @inject
     */
    private BillingAddressDataFactory $billingAddressDataFactory;

    public function testDeleteAndReplace()
    {
        $pricingGroupData = new PricingGroupData();
        $pricingGroupData->name = 'name';
        $pricingGroupToDelete = $this->pricingGroupFacade->create($pricingGroupData, Domain::FIRST_DOMAIN_ID);
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroupToReplaceWith */
        $pricingGroupToReplaceWith = $this->getReferenceForDomain(
            PricingGroupDataFixture::PRICING_GROUP_ORDINARY,
            Domain::FIRST_DOMAIN_ID,
        );
        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $this->customerUserFacade->getCustomerUserById(1);

        $customerUserData = $this->customerUserDataFactory->createFromCustomerUser($customerUser);
        $customerUserData->pricingGroup = $pricingGroupToDelete;

        /** @var \App\Model\Customer\BillingAddress $billingAddress */
        $billingAddress = $customerUser->getCustomer()->getBillingAddress();
        $billingAddressData = $this->billingAddressDataFactory->createFromBillingAddress($billingAddress);

        $customerUserUpdateData = $this->customerUserUpdateDataFactory->create();
        $customerUserUpdateData->customerUserData = $customerUserData;
        $customerUserUpdateData->billingAddressData = $billingAddressData;

        $this->customerUserFacade->editByAdmin($customerUser->getId(), $customerUserUpdateData);

        $this->pricingGroupFacade->delete($pricingGroupToDelete->getId(), $pricingGroupToReplaceWith->getId());

        $this->em->refresh($customerUser);

        $this->assertEquals($pricingGroupToReplaceWith, $customerUser->getPricingGroup());
    }
}
