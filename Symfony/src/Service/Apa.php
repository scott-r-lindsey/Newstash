<?php
declare(strict_types=1);

namespace App\Service;

abstract class Apa
{

    const NODE_ROOT     = 283155;
    const NODE_BOOKS    = 1000;
    const NODE_KINDLE   = 154606011;

    const MAX_REQUEST_SIZE          = 10;
    const STANDARD_RESPONSE_TYPES   = [
            'ItemAttributes',
            'Images',
            'Large',
            'AlternateVersions',
            'EditorialReview'
        ];

}
