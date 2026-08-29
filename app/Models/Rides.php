<?php



namespace App\Models;



use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Notifications\Notifiable;

use Laravel\Sanctum\HasApiTokens;

use Illuminate\Database\Eloquent\SoftDeletes;



class Rides extends Authenticatable

{

    protected $table = 'tj_requete';

    public $timestamps = false;

    protected $fillable = [

        'statut'

    ];




    public function user()
    {
        return $this->belongsTo(UserApp::class, 'id_user_app');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'id_conducteur');
    }
}

