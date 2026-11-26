<?php

namespace App\Models;

use Database\Factories\RegistrationPlayerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A child in a registration. The date of birth and the medical notes are
 * encrypted at rest; `category` (U6…U14) is worked out from the date of birth
 * when the registration is stored (AgeGroup).
 *
 * @property string $date_of_birth Y-m-d
 * @property string $category
 * @property string|null $medical_notes
 */
class RegistrationPlayer extends Model
{
    /** @use HasFactory<RegistrationPlayerFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $guarded = ['id'];

    /**
     * @var list<string>
     */
    protected $hidden = ['date_of_birth', 'medical_notes'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'encrypted',
            'medical_notes' => 'encrypted',
            'needs_uniform' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Registration, $this>
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }
}
