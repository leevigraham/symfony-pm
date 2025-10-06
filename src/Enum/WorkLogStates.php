<?php

namespace App\Enum;

enum WorkLogStates: string
{
    case STOPPED = 'stopped';
    case STARTED = 'started';
    case SUBMITTED = 'submitted';
}
