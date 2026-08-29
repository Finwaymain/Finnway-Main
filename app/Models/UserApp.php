<?php



namespace App\Models;



use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Notifications\Notifiable;

use Laravel\Sanctum\HasApiTokens;

use Illuminate\Database\Eloquent\SoftDeletes;



/**
 * Class UserApp
 *
 * @property string $id
 * @property string $nom
 * @property string $prenom
 * @property string $email
 * @property string $phone
 * @property string $mdp
 * @property string $login_type
 * @property string $photo
 * @property string $photo_path
 * @property string $photo_nic
 * @property string $photo_nic_path
 * @property string $statut
 * @property string $statut_nic
 * @property string $tonotify
 * @property string $device_id
 * @property string $fcm_id
 * @property string $creer
 * @property string $modifier
 * @property float|string $amount
 * @property string $reset_password_otp
 * @property string $reset_password_otp_modifier
 * @property int $age
 * @property string $gender
 */
class UserApp extends Authenticatable

{

    use HasApiTokens, HasFactory, Notifiable;



    /**

     * The attributes that are mass assignable.

     *

     * @var array<int, string>

     */



    protected $table = 'tj_user_app';

    public $timestamps = false;

    protected $fillable = [

        'nom',

        'email',

        'prenom',

        'email',

        'phone',

        'mdp',

        'login_type',

        'photo',

        'photo_path',

        'photo_nic',

        'photo_nic_path',

        'statut',

        'statut_nic',

        'tonotify',

        'device_id',

        'fcm_id',

        'creer',

        'modifier',

        'amount',

        'reset_password_otp',

        'reset_password_otp_modifier',

        'age',
        'gender',
        'alternate_phone',
        'consumer_plan_id',
        'consumer_plan_expiry_date',
        'consumer_plan',
    ];



    /**

     * The attributes that should be hidden for serialization.

     *

     * @var array<int, string>

     */



    /**

     * The attributes that should be cast.

     *

     * @var array<string, string>

     */

     protected $casts = [

     'id' => 'string',

   ];



}

