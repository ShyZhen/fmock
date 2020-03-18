<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnswersFollow extends Model
{
    //
    protected $table = 'answers_follow';

    protected $fillable = [
        'user_id', 'resource_id',
    ];
}
