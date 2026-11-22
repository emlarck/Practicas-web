<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_x_id',
        'player_o_id',
        'winner_id',
        'board',
        'current_turn',
        'status',
        'result',
    ];

    protected function casts(): array
    {
        return [
            'board' => 'array',
        ];
    }

    public function playerX(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player_x_id');
    }

    public function playerO(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player_o_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    public static function emptyBoard(): array
    {
        return array_fill(0, 9, null);
    }
}