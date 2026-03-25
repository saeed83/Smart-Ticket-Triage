<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // 1. IMPORT THIS
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory; // 2. USE IT INSIDE THE CLASS

    protected $table = 'tickets';

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'category',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function replies()
    {
        return $this->hasMany(Ticket_Replies::class);
    }
}
