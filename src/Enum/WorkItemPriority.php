<?php

namespace App\Enum;

enum WorkItemPriority: string
{
    case LOW = 'low';
    case STANDARD = 'standard';
    case HIGH = 'high';
    case CRITICAL = 'critical';

}
