<?php
declare(strict_types=1);

namespace App\Service;

abstract class Apa
{

    const MAX_REQUEST_SIZE          = 10;
    const STANDARD_RESPONSE_TYPES   = [
            'ItemAttributes',
            'Images',
            'Large',
            'AlternateVersions',
            'EditorialReview'
        ];

}
