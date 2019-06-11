<?php

namespace App\Form\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ContractorAllData extends Constraint {
    public $message = 'Kontrahent dla pozycji typu przychód musi mieć uzupełnione pełne dane wraz z adresem i numerem NIP!';
}