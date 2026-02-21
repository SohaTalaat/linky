<?php

namespace App\Enums;

enum LinkStatus: string
{
    case SAVED = 'saved';
    case READING = 'reading';
    case DONE = 'done';
}
