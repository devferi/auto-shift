<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappApiLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'method', 'url', 'request_body', 'response_status', 'response_body'
    ];
}

