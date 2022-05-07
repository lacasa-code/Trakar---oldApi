<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Ticket extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, HasFactory, SoftDeletes;

    public $table = 'tickets';

    protected $appends = [
        'attachment',
    ];

    protected $fillable = [
        'user_id', 'lang', 'category_id', 'ticket_no', 'title', 'priority', 
        'message', 'status', 'order_id', 'vendor_id', 'case', 'answer', 'ticketpriority_id', 'product_id'
    ];

    public function ticketCategory()
    {
        return $this->belongsTo(Ticketcategory::class, 'category_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function ticketPriority()
    {
        return $this->belongsTo(Ticketpriority::class, 'ticketpriority_id');
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')->fit('crop', 50, 50);
        $this->addMediaConversion('preview')->fit('crop', 120, 120);
    }

    public function ticketComments()
    {
        return $this->hasMany(Ticketcomment::class, 'ticket_id', 'id');
    }

    public function ticketOrder()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function ticketVendor()
    {
        return $this->belongsTo(AddVendor::class, 'vendor_id');
    }

    public function ticketUser()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getCreatedAtAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->format('Y-m-d H:i:s');
       // return Carbon::createFromFormat('Y-m-d H:i:s', $value->format('Y-m-d H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->format('Y-m-d H:i:s');
       // return Carbon::createFromFormat('Y-m-d H:i:s', $value->format('Y-m-d H:i:s');
    }

    public function getAttachmentAttribute()
    {
        $file = $this->getMedia('attachment')->last();
        $env  = env('Space_URL');
        if ($file) {
            $file->image     = $env.'/tickets/attachments/'.$file['file_name'];
            $file->url       = $file->getUrl();
            $file->fullurl   = $file->getFullUrl();
            $file->thumbnail = $file->getUrl('thumb');
            $file->preview   = $file->getUrl('preview');
        }

        return $file;
    }
}

