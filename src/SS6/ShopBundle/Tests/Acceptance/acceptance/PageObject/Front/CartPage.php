<?php

namespace SS6\ShopBundle\Tests\Acceptance\acceptance\PageObject\Front;

use Facebook\WebDriver\WebDriverBy;
use SS6\ShopBundle\Tests\Acceptance\acceptance\PageObject\AbstractPage;

class CartPage extends AbstractPage {

	/**
	 * @param string $productName
	 * @param int $quantity
	 */
	public function assertProductQuantity($productName, $quantity) {
		$quantityField = $this->getQuantityFieldByProductName($productName);
		$this->tester->seeInFieldByElement($quantity, $quantityField);
	}

	/**
	 * @param string $productName
	 * @param string $formattedPriceWithCurrency
	 */
	public function assertProductPrice($productName, $formattedPriceWithCurrency) {
		$productPriceCell = $this->getProductPriceCellByName($productName);
		$this->tester->seeInElement($formattedPriceWithCurrency, $productPriceCell);
	}

	/**
	 * @param string $formattedPriceWithCurrency
	 */
	public function assertTotalPriceWithVat($formattedPriceWithCurrency) {
		$orderPriceCell = $this->getTotalProductsPriceCell();
		$this->tester->seeInElement('Celková cena s DPH: ' . $formattedPriceWithCurrency, $orderPriceCell);
	}

	/**
	 * @param string $productName
	 * @param int $quantity
	 */
	public function changeProductQuantity($productName, $quantity) {
		$quantityField = $this->getQuantityFieldByProductName($productName);
		$this->tester->fillFieldByElement($quantityField, $quantity);
		$this->tester->clickByText('Přepočítat');
	}

	/**
	 * @param string $productName
	 * @return \Facebook\WebDriver\WebDriverElement
	 */
	private function getQuantityFieldByProductName($productName) {
		$row = $this->findProductRowInCartByName($productName);

		return $row->findElement(WebDriverBy::cssSelector('input[name^="cart_form[quantities]"]'));
	}

	/**
	 * @param string $productName
	 * @return \Facebook\WebDriver\WebDriverElement
	 */
	private function findProductRowInCartByName($productName) {
		$rows = $this->webDriver->findElements(WebDriverBy::cssSelector('.table-cart tr'));

		foreach ($rows as $row) {
			try {
				$nameCell = $row->findElement(WebDriverBy::cssSelector('.table-cart__title'));

				if ($nameCell->getText() === $productName) {
					return $row;
				}
			} catch (\Facebook\WebDriver\Exception\NoSuchElementException $ex) {
				continue;
			}
		}

		$message = 'Unable to find row containing product "' . $productName . '" in cart.';
		throw new \Facebook\WebDriver\Exception\NoSuchElementException($message);
	}

	/**
	 * @param string $productName
	 * @return \Facebook\WebDriver\WebDriverElement
	 */
	private function getProductPriceCellByName($productName) {
		$row = $this->findProductRowInCartByName($productName);

		return $row->findElement(WebDriverBy::cssSelector('td.table-cart__price-final'));
	}

	/**
	 * @return \Facebook\WebDriver\WebDriverElement
	 */
	private function getTotalProductsPriceCell() {
		return $this->webDriver->findElement(WebDriverBy::cssSelector('.table-cart .table-cart__foot .table-cart__foot__total'));
	}

}
