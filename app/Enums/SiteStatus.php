<?php

namespace App\Enums;

enum SiteStatus: string
{
    case Active = 'active';
    case Paused = 'paused';
    case Error = 'error';
}
