<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $table = 'logs';
    protected $primaryKey = 'LogID';
    
    // disable standard Laravel timestamps because of custom 'LogDate'
    public $timestamps = false; 

    protected $fillable = [
        'UserID',
        'ProductID',
        'ActionType',
        'Quantity',
        'UnitPrice',
        'TotalPrice',
        'LogDate',
        'LogDescription',
        'BatchID'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'ProductID', 'ProductID');
    }

    // A log belongs to the account that executed it
    public function account()
    {
        return $this->belongsTo(Account::class, 'UserID', 'UserID');
    }
}
