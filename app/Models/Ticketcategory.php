<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticketcategory extends Model
{
    use HasFactory, SoftDeletes;

    public $table = 'ticketcategories';

    protected $fillable = ['name'];

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'category_id', 'id');
    }
}
