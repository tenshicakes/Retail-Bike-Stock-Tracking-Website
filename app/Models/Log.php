<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $table = 'logs';
    protected $primaryKey = 'LogID';
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

    public function account()
    {
        return $this->belongsTo(Account::class, 'UserID', 'UserID');
    }
}
