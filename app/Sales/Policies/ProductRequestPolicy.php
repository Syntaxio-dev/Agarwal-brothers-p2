<?php

namespace App\Sales\Policies;

/** The reps' requests for a note: the same managers answer them. */
class ProductRequestPolicy extends ProductPolicy
{
    protected string $key = 'sales-product-requests';
}
