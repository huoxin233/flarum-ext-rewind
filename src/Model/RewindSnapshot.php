<?php

namespace HuseyinFiliz\Rewind\Model;

use Flarum\Database\AbstractModel;
use Flarum\User\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RewindSnapshot extends AbstractModel
{
    protected $table = 'rw_snaps';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'data' => 'array',
        'is_public' => 'boolean',
        'generated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
