<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Avatar extends Model
{
    use HasFactory;

    protected $fillable = ['model_id', 'model_type', 'created_by', 'image_name', 'image_size', 'image_path', 'mime_type', 'created_by_id', 'created_by_name'];
}
