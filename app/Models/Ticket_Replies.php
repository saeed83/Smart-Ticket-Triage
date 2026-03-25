<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket_Replies extends Model
{
    protected $table = 'ticket_replies';

    protected $fillable = [
        'ticket_id',
        'user_id',
        'reply',
        'created_by',
        'updated_by',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
