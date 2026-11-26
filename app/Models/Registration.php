<?php

namespace App\Models;

use App\Enums\GuardianRelationship;
use App\Enums\RegistrationStatus;
use App\Enums\RegistrationType;
use Database\Factories\RegistrationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A registration sent by a parent or guardian: a free training, or the monthly
 * or annual plan, for one or more players. Contact phones and the street address
 * are encrypted at rest. `reference` is the public id (never the numeric one).
 *
 * @property string $reference
 * @property RegistrationType $type
 * @property RegistrationStatus $status
 * @property GuardianRelationship $guardian_relationship
 */
class Registration extends Model
{
    /** @use HasFactory<RegistrationFactory> */
    use HasFactory;

    /**
     * Everything is set by RegisterFamily from validated data.
     *
     * @var list<string>
     */
    protected $guarded = ['id'];

    /**
     * @var list<string>
     */
    protected $hidden = ['guardian_phone', 'address_street', 'second_guardian_phone', 'emergency_contact_phone', 'signed_ip', 'signed_user_agent'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => RegistrationType::class,
            'status' => RegistrationStatus::class,
            'guardian_relationship' => GuardianRelationship::class,
            'guardian_phone' => 'encrypted',
            'address_street' => 'encrypted',
            'second_guardian_phone' => 'encrypted',
            'emergency_contact_phone' => 'encrypted',
            'preferred_training_date' => 'date',
            'media_release' => 'boolean',
            'marketing_opt_in' => 'boolean',
            'liability_waiver_accepted_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
            'refund_policy_accepted_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<RegistrationPlayer, $this>
     */
    public function players(): HasMany
    {
        return $this->hasMany(RegistrationPlayer::class);
    }

    public function guardianName(): string
    {
        return "{$this->guardian_first_name} {$this->guardian_last_name}";
    }
}
