<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticketpriority extends Model
{
    use HasFactory, SoftDeletes;

    public $table = 'ticketpriorities';

    protected $fillable = ['name', 'status', 'lang'];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
