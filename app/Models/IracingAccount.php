<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IracingAccount extends Model
{

    protected $fillable = [
    'user_id',
    'iracing_customer_id',
    'display_name',
    'access_token',
    'refresh_token',
    'token_expires_at',
];

}
