<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use \DateTimeInterface;

class AddVendor extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia, Auditable, HasFactory;

    public $table = 'add_vendors';

    protected $appends = [
        'images', 'commercialDocs', 'taxCardDocs', 'wholesaleDocs', 'bankDocs', 'vendorStatus'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    const TYPE_RADIO = [
        '1' => 'vendor',
        '2' => 'hot sale',
        '3' => 'Both',
    ];

    protected $fillable = [
        'serial',
        'vendor_name',
        'lang',
        'email',
        'type',
        'userid_id',
        // new added 26 may 2021
        'commercial_no',
        'commercial_doc',
        'tax_card_no',
        'tax_card_doc',
        'bank_account',
        // new added 26 may 2021
        'approved',
        'created_at',
        'updated_at',
        'deleted_at',
        'complete',
        // june 15 2021
        'declined',
        'rejected',
        'company_name',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')->fit('crop', 50, 50);
        $this->addMediaConversion('preview')->fit('crop', 120, 120);
    }

    public function userid()
    {
        return $this->belongsTo(User::class, 'userid_id');
    }

    public function vendor_type()
    {
        return $this->belongsTo(Vendortype::class, 'type');
    }

    public function rejectedreason()
    {
        return $this->belongsToMany(Rejectedreason::class)->withPivot('reason')->withTimestamps();
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'vendor_id', 'id');
    }

    public function orderDetails()
    {
        return $this->hasMany(Orderdetail::class, 'vendor_id', 'id');
    }

    public function stores()
    {
        return $this->hasMany(Store::class, 'vendor_id', 'id');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'vendor_id', 'id');
    }

    public function getImagesAttribute()
    {
        $file = $this->getMedia('images')->last();
        $env  = env('Space_URL');
        if ($file) {
            $file->image     = $env.'/add-vendors/'.$file['file_name'];
            $file->url       = $file->getUrl();
            $file->fullurl   = $file->getFullUrl();
            $file->thumbnail = $file->getUrl('thumb');
            $file->preview   = $file->getUrl('preview');
        }

        return $file;
    }

    public function getCommercialDocsAttribute()
    {
        $file = $this->getMedia('commercialDocs')->last();
        $env  = env('Space_URL');
        if ($file) {
            $file->image     = $env.'/vendor-documents/commercial-documents/'.$file['file_name'];
            $file->url       = $file->getUrl();
            $file->fullurl   = $file->getFullUrl();
            $file->thumbnail = $file->getUrl('thumb');
            $file->preview   = $file->getUrl('preview');
        }

        return $file;
    }

    public function getTaxCardDocsAttribute()
    {
        $file = $this->getMedia('taxCardDocs')->last();
        $env  = env('Space_URL');
        if ($file) {
            $file->image     = $env.'/vendor-documents/tax-documents/'.$file['file_name'];
            $file->url       = $file->getUrl();
            $file->fullurl   = $file->getFullUrl();
            $file->thumbnail = $file->getUrl('thumb');
            $file->preview   = $file->getUrl('preview');
        }

        return $file;
    }

    public function getWholesaleDocsAttribute()
    {
      //  if ($this->type != 1) 
       // {
            $file = $this->getMedia('wholesaleDocs')->last();
            $env  = env('Space_URL');
                if ($file) 
                {
                    $file->image     = $env.'/vendor-documents/wholesale-documents/'.$file['file_name'];
                    $file->url       = $file->getUrl();
                    $file->fullurl   = $file->getFullUrl();
                    $file->thumbnail = $file->getUrl('thumb');
                    $file->preview   = $file->getUrl('preview');
                }
       /* }
        else{
            $file = null;
        }*/
        return $file;
    }

    public function getBankDocsAttribute()
    {
        $file = $this->getMedia('bankDocs')->last();
        $env  = env('Space_URL');
        if ($file) {
            $file->image     = $env.'/vendor-documents/bank-documents/'.$file['file_name'];
            $file->url       = $file->getUrl();
            $file->fullurl   = $file->getFullUrl();
            $file->thumbnail = $file->getUrl('thumb');
            $file->preview   = $file->getUrl('preview');
        }

        return $file;
    }

    public function getVendorStatusAttribute()
    {
        if($this->complete == 1 && $this->rejected != 1 && $this->declined != 1)
        {
            $status = 'pending';
        }
        if($this->complete == 1 && $this->rejected == 1 && $this->declined != 1)
        {
            $status = 'invalid info';
        }
        if($this->complete == 1 && $this->rejected == 1 && $this->declined == 1)
        {
            $status = 'declined';
        }
        if($this->complete == 1 && $this->rejected != 1 && $this->declined == 1)
        {
            $status = 'declined';
        }
        if($this->complete != 1 && $this->rejected != 1 && $this->declined != 1)
        {
            $status = 'incomplete';
        }
        if($this->approved == 1 && $this->complete == 1)
        {
            $status = 'approved';
        }
        if($this->complete == 0 && $this->rejected == 0 && $this->declined == 0)
        {
            $status = 'incomplete';
        }
        return $status;
    }
}
