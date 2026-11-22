<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'student_name',
        'absences',
        'grade',
        'grade_equivalence',
    ];

    protected function casts(): array
    {
        return [
            'absences' => 'integer',
            'grade' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calcula automáticamente la equivalencia alfabética según la calificación.
     * Escala 0-100: NA (<70), SA (70-79.99), DE (80-89.99), AU (90-100).
     */
    public static function calculateEquivalence(float $grade): string
    {
        if ($grade < 70) {
            return 'NA';
        }

        if ($grade < 80) {
            return 'SA';
        }

        if ($grade < 90) {
            return 'DE';
        }

        return 'AU';
    }
}