<?php

namespace App\Service;

use Cocur\Slugify\Slugify;

class SlugifyService
{
    private Slugify $slugify;

    public function __construct()
    {
        $this->slugify = new Slugify();
    }

    public function slugify(string $text): string
    {
        return $this->slugify->slugify($text);
    }
}
