<?php



namespace App\Models;



use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Notifications\Notifiable;

use Laravel\Sanctum\HasApiTokens;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


/**
 * @property string $id
 * @property string $nom
 * @property string $email
 * @property string $prenom
 * @property string $phone
 * @property string $mdp
 * @property string $latitude
 * @property string $longitude
 * @property string $statut_vehicule
 * @property string $status_car_image
 * @property string $online
 * @property string $login_type
 * @property string $creer
 * @property string $parcel_delivery
 * @property string $subscriptionPlanId
 * @property string $subscriptionExpiryDate
 * @property string $subscriptionTotalOrders
 * @property array $subscription_plan
 * @property string $statut
 * @property string $driver_on_ride
 * @property string $category_id
 * @property int $is_verified
 */
class Driver extends Authenticatable
{

  use HasApiTokens, HasFactory, Notifiable;

  public $timestamps = false;

  protected $table = 'tj_conducteur';

  protected $fillable = [

    'nom',
    'email',
    'prenom',
    'phone',
    'mdp',
    'latitude',
    'longitude',
    'statut_vehicule',
    'status_car_image',
    'online',
    'login_type',
    'creer',
    'parcel_delivery',
    'subscriptionPlanId',
    'subscriptionExpiryDate',
    'subscriptionTotalOrders',
    'subscription_plan',
    'statut',
    'driver_on_ride',
    'category_id',
    'is_verified',
    'alternate_phone'
  ];



  protected $casts = [

    'id' => 'string',
    'subscription_plan' => 'array',
    'adminCommission' => 'array'

  ];
  public function subscriptionPlan(): BelongsTo
  {
    return $this->belongsTo(SubscriptionPlan::class, 'subscriptionPlanId', 'id');
  }

  public function category(): BelongsTo
  {
    return $this->belongsTo(UserCategory::class, 'category_id');
  }

  public function driverCategories()
  {
    return $this->hasMany(DriverCategory::class, 'driver_id');
  }

  public function getProfessionAttribute()
  {
    $mapped = \Illuminate\Support\Facades\DB::table('tj_conducteur_categories')
        ->where('driver_id', $this->id)
        ->pluck('category_id');
    
    if ($mapped->isEmpty()) {
        return 'No Profession';
    }
    
    $names = \Illuminate\Support\Facades\DB::table('tj_categorie_user')
        ->whereIn('id', $mapped)
        ->pluck('libelle')
        ->toArray();
        
    return implode(', ', $names);
  }
}
