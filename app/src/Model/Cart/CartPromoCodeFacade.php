<?php

declare(strict_types=1);

namespace App\Model\Cart;

use App\Model\Order\PromoCode\CurrentPromoCodeFacade;
use App\Model\Order\PromoCode\Exception\AlreadyAppliedPromoCodeException;
use App\Model\Order\PromoCode\PromoCode;
use Doctrine\ORM\EntityManagerInterface;

class CartPromoCodeFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * @var \App\Model\Order\PromoCode\CurrentPromoCodeFacade
     */
    private CurrentPromoCodeFacade $currentPromoCodeFacade;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
     */
    public function __construct(
        EntityManagerInterface $em,
        CurrentPromoCodeFacade $currentPromoCodeFacade
    ) {
        $this->em = $em;
        $this->currentPromoCodeFacade = $currentPromoCodeFacade;
    }

    /**
     * @param \App\Model\Cart\Cart $cart
     * @param string $promoCodeCode
     */
    public function applyPromoCodeByCode(Cart $cart, string $promoCodeCode): void
    {
        if ($cart->isPromoCodeApplied($promoCodeCode)) {
            throw new AlreadyAppliedPromoCodeException($promoCodeCode);
        }

        $promoCode = $this->currentPromoCodeFacade->getValidatedPromoCode($promoCodeCode, $cart);

        $cart->applyPromoCode($promoCode);

        $this->em->flush();
    }

    /**
     * @param \App\Model\Cart\Cart $cart
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     */
    public function removePromoCode(Cart $cart, PromoCode $promoCode): void
    {
        $cart->removePromoCodeById($promoCode->getId());

        $this->em->flush();
    }
}
