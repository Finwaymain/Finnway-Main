<?php



namespace App\Models;



use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Notifications\Notifiable;

use Laravel\Sanctum\HasApiTokens;



class DriverTransaction extends Authenticatable

{

    use HasApiTokens, HasFactory, Notifiable;



    protected $table = 'tj_conducteur_transaction';

    protected $fillable = [

        'amount',

        'payment_method',

        'id_conducteur',

        'id_ride',

        'id_parcel',

        'planId',

        'note',

        'creer',

        'modifier',

    ];

}

