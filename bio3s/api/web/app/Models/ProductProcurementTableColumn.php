<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class productProcurementTableColumn extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $table = 'productProcurementTableColumn';

    protected function serializeDate(\DateTimeInterface $date) {
        return $date->format('Y-m-d H:i:s');
    }
}
