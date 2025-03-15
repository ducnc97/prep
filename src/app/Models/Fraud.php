<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fraud extends Model
{
    const TYPE_LOCATION = 0;
    const TYPE_FINGERPRINTS_USER_AGENT = 1;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'evidence' => 'array',
        ];
    }
}
