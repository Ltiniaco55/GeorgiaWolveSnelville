<?php

namespace App\Enums;

enum GuardianRelationship: string
{
    case Mother = 'mother';
    case Father = 'father';
    case LegalGuardian = 'legal_guardian';
    case Other = 'other';
}
