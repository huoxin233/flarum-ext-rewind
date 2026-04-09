<?php

namespace HuseyinFiliz\Rewind\Model;

use Flarum\Database\AbstractModel;

class CommunitySnapshot extends AbstractModel
{
    protected $table = 'rw_community';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'data' => 'array',
        'generated_at' => 'datetime',
    ];
}
