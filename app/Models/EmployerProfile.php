<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'industry',
        'company_size',
        'website',
        'location',
        'about',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}