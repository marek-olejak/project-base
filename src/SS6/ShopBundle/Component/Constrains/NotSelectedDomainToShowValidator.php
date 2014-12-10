<?php

namespace SS6\ShopBundle\Component\Constrains;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use SS6\ShopBundle\Model\Domain\Domain;

class NotSelectedDomainToShowValidator extends ConstraintValidator {

	/**
	 * @var \SS6\ShopBundle\Model\Domain\Domain $domain
	 */
	private $domain;

	public function __construct(Domain $domain) {
		$this->domain = $domain;
	}

	/**
	 * @param array $values
	 * @param \Symfony\Component\Validator\Constraint $constraint
	 */
	public function validate($values, Constraint $constraint) {
		if (!$constraint instanceof NotSelectedDomainToShow) {
			throw new \Symfony\Component\Validator\Exception\UnexpectedTypeException($constraint, NotSelectedDomainToShow::class);
		}

		$allDomains = $this->domain->getAll();

		if (count($allDomains) === count($values)) {
			$this->context->addViolation($constraint->message);
			return;
		}
	}
}
