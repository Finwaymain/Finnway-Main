<?php



namespace App\Models;



use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Notifications\Notifiable;

use Laravel\Sanctum\HasApiTokens;



/**
 * Class ParcelOrder
 *
 * @property int $id
 * @property int $id_user_app
 * @property string $source
 * @property string $destination
 * @property float $lat_source
 * @property float $lng_source
 * @property float $lat_destination
 * @property float $lng_destination
 * @property string $source_city
 * @property string $destination_city
 * @property string $sender_name
 * @property string $sender_phone
 * @property string $receiver_name
 * @property string $receiver_phone
 * @property float $parcel_weight
 * @property string|null $parcel_image
 * @property string $parcel_type
 * @property string $parcel_dimension
 * @property string|null $note
 * @property string $parcel_date
 * @property string $parcel_time
 * @property string|null $receive_date
 * @property string|null $receive_time
 * @property string $status
 * @property string $payment_status
 * @property int $id_payment_method
 * @property float $tax
 * @property float $discount
 * @property float $admin_commission
 * @property float $amount
 * @property int|null $id_driver
 * @property string|null $rejected_driver_ids
 * @property string|null $otp
 * @property float $distance
 * @property string $distance_unit
 * @property string|null $reason
 * @property string|null $duration
 * @property float $tip
 * @property UserApp $user
 * @property Driver|null $driver
 */
class ParcelOrder extends Authenticatable

{

    use HasApiTokens, HasFactory, Notifiable;



    protected $table = 'parcel_orders';

    protected $fillable = [

        'id_user_app',

        'source',

        'destination',

        'lat_source',

        'lng_source',

        'lat_destination',

        'lng_destination',

        'source_city',

        'destination_city',

        'sender_name',

        'sender_phone',

        'receiver_name',

        'receiver_phone',

        'parcel_weight',

        'parcel_image',

        'parcel_type',

        'parcel_dimension',

        'note',

        'parcel_date',

        'parcel_time',

        'receive_date',

        'receive_time',

        'status',

        'payment_status',

        'id_payment_method',

        'tax',

        'discount',

        'admin_commission',

        'amount', 

        'id_driver',

        'rejected_driver_ids',

        'otp',

        'distance',

        'distance_unit',

        'reason',

        'duration',

        'tip'

    ];

    protected $casts = [

        'id'=>'string',

    ];
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(UserApp::class, 'id_user_app');
    }

    public function driver(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Driver::class, 'id_conducteur');
    }
}

