<?php

namespace App\Form\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ContractorAllDataValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) take care of that
        if (null === $value || '' === $value) {
            return;
        }

        if (
                !$value->getName() || 
                !$value->getFullName() || 
                !$value->getAddressCity() || 
                !$value->getAddressStreet() || 
                !$value->getAddressPostCode() || 
                !$value->getNip()
        ) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}