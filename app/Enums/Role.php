<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Developer = 'developer';
    case Viewer = 'viewer';
}
