<?php

namespace Shopsys\ShopBundle\Controller\Admin;

use Shopsys\ShopBundle\Component\Controller\AdminBaseController;
use Shopsys\ShopBundle\Model\AdminNavigation\Menu;
use Shopsys\ShopBundle\Model\AdminNavigation\MenuFactory;

class MenuController extends AdminBaseController {

	/**
	 * @var \Shopsys\ShopBundle\Model\AdminNavigation\MenuFactory
	 */
	private $menuFactory;

	public function __construct(MenuFactory $menuFactory) {
		$this->menuFactory = $menuFactory;
	}

	public function menuAction($route, array $parameters = null) {
		$menu = $this->menuFactory->createMenuWithVisibleItems();
		$activePath = $menu->getMenuPath($route, $parameters);

		return $this->render('@ShopsysShop/Admin/Inline/Menu/menu.html.twig', [
			'menu' => $menu,
			'activePath' => $activePath,
		]);
	}

	public function panelAction($route, array $parameters = null) {
		$menu = $this->menuFactory->createMenuWithVisibleItems();
		$activePath = $menu->getMenuPath($route, $parameters);

		if (isset($activePath[1]) && $menu->isRouteMatchingDescendantOfSettings($route, $parameters)) {
			$panelItems = $activePath[1]->getItems();
		} elseif (isset($activePath[0])) {
			$panelItems = $activePath[0]->getItems();
		} else {
			$panelItems = null;
		}

		return $this->render('@ShopsysShop/Admin/Inline/Menu/panel.html.twig', [
			'items' => $panelItems,
			'activePath' => $activePath,
		]);
	}
}