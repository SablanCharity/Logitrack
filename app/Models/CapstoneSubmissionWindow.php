<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CapstoneSubmissionWindow extends Model
{
    protected $fillable = ['is_open', 'deadline_on'];

    protected function casts(): array
    {
        return [
            'is_open' => 'boolean',
            'deadline_on' => 'date',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate(['id' => 1]);
    }

    public function isCurrentlyOpen(): bool
    {
        return $this->is_open
            && $this->deadline_on !== null
            && now()->toDateString() <= $this->deadline_on->toDateString();
    }
}
