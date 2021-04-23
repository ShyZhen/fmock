<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    //
    protected $table = 'reports';

    protected $fillable = [
        'reason', 'user_id', 'resource_id', 'type',
    ];
}
