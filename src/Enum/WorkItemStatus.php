<?php

namespace App\Enum;

enum WorkItemStatus: string
{
    case TODO = 'todo';
    case IN_PROGRESS = 'inProgress';
    case DONE = 'done';

}
