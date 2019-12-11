<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Form\Front\Customer\CustomerUserFormType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\CustomerUserDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\UserFacade;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Security\LoginAsUserFacade;
use Shopsys\FrameworkBundle\Model\Security\Roles;
use Symfony\Component\HttpFoundation\Request;

class CustomerController extends FrontBaseController
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\UserFacade
     */
    private $userFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation
     */
    private $orderItemPriceCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\OrderFacade
     */
    private $orderFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Security\LoginAsUserFacade
     */
    private $loginAsUserFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\CustomerUserDataFactoryInterface
     */
    private $customerUserDataFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\UserFacade $userFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFacade $orderFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation $orderItemPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Security\LoginAsUserFacade $loginAsUserFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerUserDataFactoryInterface $customerUserDataFactory
     */
    public function __construct(
        UserFacade $userFacade,
        OrderFacade $orderFacade,
        Domain $domain,
        OrderItemPriceCalculation $orderItemPriceCalculation,
        LoginAsUserFacade $loginAsUserFacade,
        CustomerUserDataFactoryInterface $customerUserDataFactory
    ) {
        $this->userFacade = $userFacade;
        $this->orderFacade = $orderFacade;
        $this->domain = $domain;
        $this->orderItemPriceCalculation = $orderItemPriceCalculation;
        $this->loginAsUserFacade = $loginAsUserFacade;
        $this->customerUserDataFactory = $customerUserDataFactory;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function editAction(Request $request)
    {
        if (!$this->isGranted(Roles::ROLE_LOGGED_CUSTOMER)) {
            $this->getFlashMessageSender()->addErrorFlash(t('You have to be logged in to enter this page'));
            return $this->redirectToRoute('front_login');
        }

        $user = $this->getUser();
        $customerUserData = $this->customerUserDataFactory->createFromUser($user);

        $form = $this->createForm(CustomerUserFormType::class, $customerUserData, [
            'domain_id' => $this->domain->getId(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $customerUserData = $form->getData();

            $this->userFacade->editByCustomer($user->getId(), $customerUserData);

            $this->getFlashMessageSender()->addSuccessFlash(t('Your data had been successfully updated'));
            return $this->redirectToRoute('front_customer_edit');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->getFlashMessageSender()->addErrorFlash(t('Please check the correctness of all data filled.'));
        }

        return $this->render('Front/Content/Customer/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function ordersAction()
    {
        if (!$this->isGranted(Roles::ROLE_LOGGED_CUSTOMER)) {
            $this->getFlashMessageSender()->addErrorFlash(t('You have to be logged in to enter this page'));
            return $this->redirectToRoute('front_login');
        }

        /** @var \App\Model\Customer\User $user */
        $user = $this->getUser();

        $orders = $this->orderFacade->getCustomerOrderList($user);
        return $this->render('Front/Content/Customer/orders.html.twig', [
            'orders' => $orders,
        ]);
    }

    /**
     * @param string $orderNumber
     */
    public function orderDetailRegisteredAction($orderNumber)
    {
        return $this->orderDetailAction(null, $orderNumber);
    }

    /**
     * @param string $urlHash
     */
    public function orderDetailUnregisteredAction($urlHash)
    {
        return $this->orderDetailAction($urlHash, null);
    }

    /**
     * @param string $urlHash
     * @param string $orderNumber
     */
    private function orderDetailAction($urlHash = null, $orderNumber = null)
    {
        if ($orderNumber !== null) {
            if (!$this->isGranted(Roles::ROLE_LOGGED_CUSTOMER)) {
                $this->getFlashMessageSender()->addErrorFlash(t('You have to be logged in to enter this page'));
                return $this->redirectToRoute('front_login');
            }

            $user = $this->getUser();
            try {
                /** @var \App\Model\Order\Order $order */
                $order = $this->orderFacade->getByOrderNumberAndUser($orderNumber, $user);
            } catch (\Shopsys\FrameworkBundle\Model\Order\Exception\OrderNotFoundException $ex) {
                $this->getFlashMessageSender()->addErrorFlash(t('Order not found'));
                return $this->redirectToRoute('front_customer_orders');
            }
        } else {
            /** @var \App\Model\Order\Order $order */
            $order = $this->orderFacade->getByUrlHashAndDomain($urlHash, $this->domain->getId());
        }

        $orderItemTotalPricesById = $this->orderItemPriceCalculation->calculateTotalPricesIndexedById($order->getItems());

        return $this->render('Front/Content/Customer/orderDetail.html.twig', [
            'order' => $order,
            'orderItemTotalPricesById' => $orderItemTotalPricesById,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loginAsRememberedUserAction(Request $request)
    {
        try {
            $this->loginAsUserFacade->loginAsRememberedUser($request);
        } catch (\Shopsys\FrameworkBundle\Model\Customer\Exception\UserNotFoundException $e) {
            /** @var \Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessageSender $adminFlashMessageSender */
            $adminFlashMessageSender = $this->get('shopsys.shop.component.flash_message.sender.admin');
            $adminFlashMessageSender->addErrorFlash(t('User not found.'));

            return $this->redirectToRoute('admin_customer_list');
        } catch (\Shopsys\FrameworkBundle\Model\Security\Exception\LoginAsRememberedUserException $e) {
            throw $this->createAccessDeniedException('', $e);
        }

        return $this->redirectToRoute('front_homepage');
    }
}
