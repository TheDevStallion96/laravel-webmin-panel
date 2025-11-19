<?php

namespace App\Enums;

enum DeployStrategy: string
{
    case Basic = 'basic';
    case ZeroDowntime = 'zero_downtime';
}
