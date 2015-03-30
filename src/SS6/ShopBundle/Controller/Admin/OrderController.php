<?php

namespace SS6\ShopBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SS6\ShopBundle\Form\Admin\Order\OrderFormType;
use SS6\ShopBundle\Form\Admin\Order\QuickSearchFormType;
use SS6\ShopBundle\Model\AdminNavigation\MenuItem;
use SS6\ShopBundle\Model\Grid\DataSourceInterface;
use SS6\ShopBundle\Model\Grid\QueryBuilderWithRowManipulatorDataSource;
use SS6\ShopBundle\Model\Order\OrderData;
use SS6\ShopBundle\Model\Order\OrderFacade;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class OrderController extends Controller {

	/**
	 * @var \SS6\ShopBundle\Model\Order\OrderFacade
	 */
	private $orderFacade;

	public function __construct(OrderFacade $orderFacade) {
		$this->orderFacade = $orderFacade;
	}

	/**
	 * @Route("/order/edit/{id}", requirements={"id" = "\d+"})
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param int $id
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	public function editAction(Request $request, $id) {
		$flashMessageSender = $this->get('ss6.shop.flash_message.sender.admin');
		/* @var $flashMessageSender \SS6\ShopBundle\Model\FlashMessage\FlashMessageSender */
		$orderStatusRepository = $this->get('ss6.shop.order.order_status_repository');
		/* @var $orderStatusRepository \SS6\ShopBundle\Model\Order\Status\OrderStatusRepository */
		$orderRepository = $this->get('ss6.shop.order.order_repository');
		/* @var $orderRepository \SS6\ShopBundle\Model\Order\OrderRepository */
		$orderItemPriceCalculation = $this->get('ss6.shop.order.item.order_item_price_calculation');
		/* @var $orderItemPriceCalculation \SS6\ShopBundle\Model\Order\Item\OrderItemPriceCalculation */

		$order = $orderRepository->getById($id);
		$allOrderStatuses = $orderStatusRepository->findAll();
		$form = $this->createForm(new OrderFormType($allOrderStatuses));

		try {
			$orderData = new OrderData();

			if (!$form->isSubmitted()) {
				$orderData->setFromEntity($order);
			}
			$form->setData($orderData);
			$form->handleRequest($request);

			if ($form->isValid()) {
				$orderFacade = $this->get('ss6.shop.order.order_facade');
				/* @var $orderFacade \SS6\ShopBundle\Model\Order\OrderFacade */

				$order = $orderFacade->edit($id, $orderData);

				$flashMessageSender->addSuccessFlashTwig('Byla upravena objednávka č.'
						. ' <strong><a href="{{ url }}">{{ number }}</a></strong>', [
					'number' => $order->getNumber(),
					'url' => $this->generateUrl('admin_order_edit', ['id' => $order->getId()]),
				]);
				return $this->redirect($this->generateUrl('admin_order_list'));
			}
		} catch (\SS6\ShopBundle\Model\Order\Status\Exception\OrderStatusNotFoundException $e) {
			$flashMessageSender->addErrorFlash('Zadaný stav objednávky nebyl nalezen, prosím překontrolujte zadané údaje');
		} catch (\SS6\ShopBundle\Model\Customer\Exception\UserNotFoundException $e) {
			$flashMessageSender->addErrorFlash('Zadaný zákazník nebyl nalezen, prosím překontrolujte zadané údaje');
		} catch (\SS6\ShopBundle\Model\Order\Mail\Exception\SendMailFailedException $e) {
			$flashMessageSender->addErrorFlash('Nepodařilo se odeslat aktualizační email');
		}

		if ($form->isSubmitted() && !$form->isValid()) {
			$flashMessageSender->addErrorFlash('Prosím zkontrolujte si správnost vyplnění všech údajů');
		}

		$breadcrumb = $this->get('ss6.shop.admin_navigation.breadcrumb');
		/* @var $breadcrumb \SS6\ShopBundle\Model\AdminNavigation\Breadcrumb */
		$breadcrumb->replaceLastItem(new MenuItem('Editace objednávky - č. ' . $order->getNumber()));

		$orderItemTotalPricesById = $orderItemPriceCalculation->calculateTotalPricesIndexedById($order->getItems());

		return $this->render('@SS6Shop/Admin/Content/Order/edit.html.twig', [
			'form' => $form->createView(),
			'order' => $order,
			'orderItemTotalPricesById' => $orderItemTotalPricesById,
		]);
	}

	/**
	 * @Route("/order/list/")
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 */
	public function listAction(Request $request) {
		$administratorGridFacade = $this->get('ss6.shop.administrator.administrator_grid_facade');
		/* @var $administratorGridFacade \SS6\ShopBundle\Model\Administrator\AdministratorGridFacade */
		$administrator = $this->getUser();
		/* @var $administrator \SS6\ShopBundle\Model\Administrator\Administrator */
		$gridFactory = $this->get('ss6.shop.grid.factory');
		/* @var $gridFactory \SS6\ShopBundle\Model\Grid\GridFactory */

		$quickSearchForm = $this->createForm(new QuickSearchFormType());
		$quickSearchForm->handleRequest($request);
		$quickSearchData = $quickSearchForm->getData();

		if ($quickSearchForm->isSubmitted()) {
			$queryBuilder = $this->orderFacade->getOrderListQueryBuilderByQuickSearchData($quickSearchData);
		} else {
			$queryBuilder = $this->orderFacade->getOrdersListQueryBuilder();
		}

		$dataSource = new QueryBuilderWithRowManipulatorDataSource(
			$queryBuilder, 'o.id',
			function ($row) {
				return $this->addOrderEntityToDataSource($row);
			}
		);

		$grid = $gridFactory->create('orderList', $dataSource);
		$grid->allowPaging();
		$grid->setDefaultOrder('created_at', DataSourceInterface::ORDER_DESC);

		$grid->addColumn('number', 'o.number', 'Č. objednávky', true);
		$grid->addColumn('created_at', 'o.createdAt', 'Vytvořena', true);
		$grid->addColumn('customer_name', 'customerName', 'Zákazník', true);
		$grid->addColumn('domain_id', 'o.domainId', 'Doména', true);
		$grid->addColumn('status_name', 'statusName', 'Stav', true);
		$grid->addColumn('total_price', 'o.totalPriceWithVat', 'Celková cena', false)->setClassAttribute('text-right text-nowrap');

		$grid->setActionColumnClassAttribute('table-col table-col-10');
		$grid->addActionColumn('edit', 'Upravit', 'admin_order_edit', ['id' => 'id']);
		$grid->addActionColumn('delete', 'Smazat', 'admin_order_delete', ['id' => 'id'])
			->setConfirmMessage('Opravdu si přejete objednávku smazat?');

		$grid->setTheme('@SS6Shop/Admin/Content/Order/listGrid.html.twig');

		$administratorGridFacade->restoreAndRememberGridLimit($administrator, $grid);

		return $this->render('@SS6Shop/Admin/Content/Order/list.html.twig', [
			'gridView' => $grid->createView(),
			'quickSearchForm' => $quickSearchForm->createView(),
		]);
	}

	/**
	 * @param array $row
	 * @return array
	 */
	private function addOrderEntityToDataSource(array $row) {
		$orderFacade = $this->get('ss6.shop.order.order_facade');
		/* @var $orderFacade \SS6\ShopBundle\Model\Order\OrderFacade */

		$row['order'] = $orderFacade->getById($row['id']);

		return $row;
	}

	/**
	 * @Route("/order/delete/{id}", requirements={"id" = "\d+"})
	 * @param int $id
	 */
	public function deleteAction($id) {
		$flashMessageSender = $this->get('ss6.shop.flash_message.sender.admin');
		/* @var $flashMessageSender \SS6\ShopBundle\Model\FlashMessage\FlashMessageSender */
		$orderRepository = $this->get('ss6.shop.order.order_repository');
		/* @var $orderRepository \SS6\ShopBundle\Model\Order\OrderRepository */

		try {
			$orderNumber = $orderRepository->getById($id)->getNumber();
			$orderFacade = $this->get('ss6.shop.order.order_facade');
			/* @var $orderFacade \SS6\ShopBundle\Model\Order\OrderFacade */
			$orderFacade->deleteById($id);

			$flashMessageSender->addSuccessFlashTwig('Objednávka č. <strong>{{ number }}</strong> byla smazána', [
				'number' => $orderNumber,
			]);
		} catch (\SS6\ShopBundle\Model\Order\Exception\OrderNotFoundException $ex) {
			$flashMessageSender->addErrorFlash('Zvolená objednávka neexistuje');
		}

		return $this->redirect($this->generateUrl('admin_order_list'));
	}
}
