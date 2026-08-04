<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to create legacy tables.
     */
    public function up(): void
    {
        // 1. tj_currency
        if (!Schema::hasTable('tj_currency')) {
        Schema::create('tj_currency', function (Blueprint $table) {
            $table->id();
            $table->string('libelle')->nullable();
            $table->string('symbole')->nullable();
            $table->string('statut')->nullable();
            $table->string('symbol_at_right')->nullable();
            $table->integer('decimal_digit')->nullable();
            $table->dateTime('creer')->nullable();
            $table->dateTime('modifier')->nullable();
            $table->softDeletes();
        });
        }

        // 2. tj_type_vehicule
        if (!Schema::hasTable('tj_type_vehicule')) {
        Schema::create('tj_type_vehicule', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('libelle')->nullable();
            $table->string('prix')->nullable();
            $table->string('image')->nullable();
            $table->string('selected_image')->nullable();
            $table->string('status')->nullable();
            $table->dateTime('creer')->nullable();
            $table->dateTime('modifier')->nullable();
            $table->softDeletes();
        });
        }

        // 3. tj_settings
        if (!Schema::hasTable('tj_settings')) {
        Schema::create('tj_settings', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('footer')->nullable();
            $table->string('email')->nullable();
            $table->string('delivery_distance')->nullable();
            $table->decimal('minimum_deposit_amount', 10, 2)->nullable();
            $table->decimal('minimum_withdrawal_amount', 10, 2)->nullable();
            $table->decimal('referral_amount', 10, 2)->nullable();
            $table->string('parcel_active')->nullable();
            $table->decimal('delivery_charge_parcel', 10, 2)->nullable();
            $table->string('subscription_model')->nullable();
            $table->string('commission_model')->nullable();
            $table->dateTime('creer')->nullable();
            $table->dateTime('modifier')->nullable();
        });
        }

        // 4. tj_conducteur
        if (!Schema::hasTable('tj_conducteur')) {
        Schema::create('tj_conducteur', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('nom')->nullable();
            $table->string('email')->nullable();
            $table->string('prenom')->nullable();
            $table->string('phone')->nullable();
            $table->string('mdp')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('statut_vehicule')->nullable();
            $table->string('status_car_image')->nullable();
            $table->string('online')->nullable();
            $table->string('login_type')->nullable();
            $table->string('creer')->nullable();
            $table->string('parcel_delivery')->nullable();
            $table->string('subscriptionPlanId')->nullable();
            $table->string('subscriptionExpiryDate')->nullable();
            $table->string('subscriptionTotalOrders')->nullable();
            $table->text('subscription_plan')->nullable();
            $table->string('statut')->nullable();
            $table->string('driver_on_ride')->nullable();
            $table->integer('is_verified')->nullable();
            $table->string('alternate_phone')->nullable();
        });
        }

        // 5. tj_vehicule
        if (!Schema::hasTable('tj_vehicule')) {
        Schema::create('tj_vehicule', function (Blueprint $table) {
            $table->id();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('color')->nullable();
            $table->string('numberplate')->nullable();
            $table->string('car_make')->nullable();
            $table->string('milage')->nullable();
            $table->string('km')->nullable();
            $table->integer('passenger')->nullable();
            $table->integer('id_conducteur')->nullable();
            $table->integer('id_type_vehicule')->nullable();
            $table->string('statut')->nullable();
            $table->dateTime('creer')->nullable();
            $table->dateTime('modifier')->nullable();
        });
        }

        // 6. tj_payment_method
        if (!Schema::hasTable('tj_payment_method')) {
        Schema::create('tj_payment_method', function (Blueprint $table) {
            $table->id();
            $table->string('libelle')->nullable();
            $table->string('image')->nullable();
            $table->string('statut')->nullable();
            $table->dateTime('creer')->nullable();
            $table->dateTime('modifier')->nullable();
        });
        }

        // 7. tj_user_app
        if (!Schema::hasTable('tj_user_app')) {
        Schema::create('tj_user_app', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->nullable();
            $table->string('prenom')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('mdp')->nullable();
            $table->string('login_type')->nullable();
            $table->string('photo')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('photo_nic')->nullable();
            $table->string('photo_nic_path')->nullable();
            $table->string('statut')->nullable();
            $table->string('statut_nic')->nullable();
            $table->string('tonotify')->nullable();
            $table->string('device_id')->nullable();
            $table->string('fcm_id')->nullable();
            $table->dateTime('creer')->nullable();
            $table->dateTime('modifier')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('reset_password_otp')->nullable();
            $table->dateTime('reset_password_otp_modifier')->nullable();
            $table->integer('age')->nullable();
            $table->string('gender')->nullable();
            $table->string('alternate_phone')->nullable();
        });
        }

        // 8. tj_requete
        if (!Schema::hasTable('tj_requete')) {
        Schema::create('tj_requete', function (Blueprint $table) {
            $table->id();
            $table->string('id_user_app')->nullable();
            $table->string('depart_name')->nullable();
            $table->string('destination_name')->nullable();
            $table->string('latitude_depart')->nullable();
            $table->string('longitude_depart')->nullable();
            $table->string('latitude_arrivee')->nullable();
            $table->string('longitude_arrivee')->nullable();
            $table->integer('place')->nullable();
            $table->integer('number_poeple')->nullable();
            $table->decimal('distance', 10, 2)->nullable();
            $table->string('duree')->nullable();
            $table->decimal('montant', 10, 2)->nullable();
            $table->decimal('tip_amount', 10, 2)->nullable();
            $table->string('trajet')->nullable();
            $table->string('statut')->nullable();
            $table->string('statut_paiement')->nullable();
            $table->string('id_conducteur')->nullable();
            $table->string('id_payment_method')->nullable();
            $table->dateTime('creer')->nullable();
            $table->dateTime('modifier')->nullable();
            $table->string('date_retour')->nullable();
            $table->string('heure_retour')->nullable();
            $table->string('statut_round')->nullable();
            $table->string('statut_course')->nullable();
            $table->string('id_conducteur_accepter')->nullable();
            $table->string('trip_objective')->nullable();
            $table->string('trip_category')->nullable();
            $table->integer('age_children1')->nullable();
            $table->integer('age_children2')->nullable();
            $table->integer('age_children3')->nullable();
            $table->string('feel_safe')->nullable();
            $table->string('feel_safe_driver')->nullable();
            $table->string('car_driver_confirmed')->nullable();
            $table->decimal('admin_commission', 10, 2)->nullable();
            $table->string('rejected_driver_id')->nullable();
            $table->string('otp')->nullable();
            $table->dateTime('otp_created')->nullable();
            $table->softDeletes();
        });
        }

        // 9. brands
        if (!Schema::hasTable('brands')) {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('status')->nullable();
            $table->dateTime('modifier')->nullable();
            $table->timestamps();
        });
        }

        // 10. car_model
        if (!Schema::hasTable('car_model')) {
        Schema::create('car_model', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->integer('vehicle_type_id')->nullable();
            $table->string('status')->nullable();
            $table->dateTime('modifier')->nullable();
            $table->timestamps();

            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('cascade');
            $table->foreign('vehicle_type_id')->references('id')->on('tj_type_vehicule')->onDelete('cascade');
        });
        }

        // 11. users_access
        if (!Schema::hasTable('users_access')) {
        Schema::create('users_access', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable();
            $table->string('accesstoken')->nullable();
            $table->string('user_type')->nullable();
            $table->dateTime('creer')->nullable();
            $table->dateTime('modifier')->nullable();
            $table->timestamps();
        });
        }

        // 12. admin_notification
        if (!Schema::hasTable('admin_notification')) {
        Schema::create('admin_notification', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('message')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
        }

        // 13. banners
        if (!Schema::hasTable('banners')) {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('image')->nullable();
            $table->string('status')->nullable();
            $table->string('position')->nullable();
            $table->dateTime('creer')->nullable();
            $table->dateTime('modifier')->nullable();
            $table->timestamps();
        });
        }

        // 14. dispatcher_booking
        if (!Schema::hasTable('dispatcher_booking')) {
        Schema::create('dispatcher_booking', function (Blueprint $table) {
            $table->id();
            $table->string('id_user_app')->nullable();
            $table->string('id_conducteur')->nullable();
            $table->string('statut')->nullable();
            $table->dateTime('creer')->nullable();
            $table->dateTime('modifier')->nullable();
            $table->timestamps();
        });
        }

        // 15. tj_cms
        if (!Schema::hasTable('tj_cms')) {
        Schema::create('tj_cms', function (Blueprint $table) {
            $table->id();
            $table->string('page_name')->nullable();
            $table->string('page_slug')->nullable();
            $table->text('page_description')->nullable();
            $table->string('statut')->nullable();
            $table->dateTime('creer')->nullable();
            $table->dateTime('modifier')->nullable();
        });
        }

        // 16. tj_commission
        if (!Schema::hasTable('tj_commission')) {
        Schema::create('tj_commission', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('value')->nullable();
            $table->string('type')->nullable();
            $table->string('statut')->nullable();
            $table->dateTime('creer')->nullable();
            $table->dateTime('modifier')->nullable();
        });
        }

        // 17. tj_complaints
        if (!Schema::hasTable('tj_complaints')) {
        Schema::create('tj_complaints', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('id_user_app')->nullable();
            $table->string('id_conducteur')->nullable();
            $table->string('statut')->nullable();
            $table->dateTime('creer')->nullable();
            $table->dateTime('modifier')->nullable();
        });
        }

        // 18. tj_country
        if (!Schema::hasTable('tj_country')) {
        Schema::create('tj_country', function (Blueprint $table) {
            $table->id();
            $table->string('libelle')->nullable();
            $table->string('code')->nullable();
            $table->string('statut')->nullable();
            $table->dateTime('creer')->nullable();
            $table->dateTime('modifier')->nullable();
        });
        }

        // 19. delivery_charges
        if (!Schema::hasTable('delivery_charges')) {
        Schema::create('delivery_charges', function (Blueprint $table) {
            $table->id();
            $table->string('from_distance')->nullable();
            $table->string('to_distance')->nullable();
            $table->string('charges')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
        }

        // 20. tj_discount
        if (!Schema::hasTable('tj_discount')) {
        Schema::create('tj_discount', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('type')->nullable();
            $table->string('value')->nullable();
            $table->string('statut')->nullable();
            $table->dateTime('creer')->nullable();
            $table->dateTime('modifier')->nullable();
        });
        }

        // 21. dispatcher_user
        if (!Schema::hasTable('dispatcher_user')) {
        Schema::create('dispatcher_user', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('phone')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
        }

        // 22. admin_documents
        if (!Schema::hasTable('admin_documents')) {
        Schema::create('admin_documents', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('is_required')->nullable();
            $table->string('is_enabled')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
        }

        // 23. tj_conducteur_transaction
        if (!Schema::hasTable('tj_conducteur_transaction')) {
        Schema::create('tj_conducteur_transaction', function (Blueprint $table) {
            $table->id();
            $table->string('id_conducteur')->nullable();
            $table->string('amount')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('statut')->nullable();
            $table->dateTime('creer')->nullable();
            $table->dateTime('modifier')->nullable();
        });
        }

        // 24. driver_document
        if (!Schema::hasTable('driver_document')) {
        Schema::create('driver_document', function (Blueprint $table) {
            $table->id();
            $table->string('driver_id')->nullable();
            $table->string('document_id')->nullable();
            $table->string('document_path')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
        }

        // 25. email_template
        if (!Schema::hasTable('email_template')) {
        Schema::create('email_template', function (Blueprint $table) {
            $table->id();
            $table->string('subject')->nullable();
            $table->text('body')->nullable();
            $table->string('type')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
        }

        // 26. tj_favorite_ride
        if (!Schema::hasTable('tj_favorite_ride')) {
        Schema::create('tj_favorite_ride', function (Blueprint $table) {
            $table->id();
            $table->string('id_user_app')->nullable();
            $table->string('lat')->nullable();
            $table->string('lng')->nullable();
            $table->string('address')->nullable();
            $table->dateTime('creer')->nullable();
            $table->dateTime('modifier')->nullable();
        });
        }

        // 27. home_services
        if (!Schema::hasTable('home_services')) {
        Schema::create('home_services', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('image')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
        }

        // 28. landing_page_templates
        if (!Schema::hasTable('landing_page_templates')) {
        Schema::create('landing_page_templates', function (Blueprint $table) {
            $table->id();
            $table->string('section_name')->nullable();
            $table->text('content')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
        }

        // 29. tj_message
        if (!Schema::hasTable('tj_message')) {
        Schema::create('tj_message', function (Blueprint $table) {
            $table->id();
            $table->text('message')->nullable();
            $table->string('id_requete')->nullable();
            $table->string('id_user_app')->nullable();
            $table->string('id_conducteur')->nullable();
            $table->string('user_cat')->nullable();
            $table->dateTime('creer')->nullable();
            $table->dateTime('modifier')->nullable();
        });
        }

        // 30. tj_note
        if (!Schema::hasTable('tj_note')) {
        Schema::create('tj_note', function (Blueprint $table) {
            $table->id();
            $table->string('niveau')->nullable();
            $table->string('id_conducteur')->nullable();
            $table->string('id_user_app')->nullable();
            $table->string('statut')->nullable();
            $table->text('comment')->nullable();
            $table->dateTime('creer')->nullable();
            $table->dateTime('modifier')->nullable();
            $table->string('ride_id')->nullable();
            $table->string('parcel_id')->nullable();
        });
        }

        // 31. tj_notification
        if (!Schema::hasTable('tj_notification')) {
        Schema::create('tj_notification', function (Blueprint $table) {
            $table->id();
            $table->string('to_id')->nullable();
            $table->string('from_id')->nullable();
            $table->string('titre')->nullable();
            $table->text('message')->nullable();
            $table->string('statut')->nullable();
            $table->string('type')->nullable();
            $table->dateTime('creer')->nullable();
            $table->dateTime('modifier')->nullable();
        });
        }

        // 32. on_boardings
        if (!Schema::hasTable('on_boardings')) {
        Schema::create('on_boardings', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('type')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
        });
        }

        // 33. parcel_category
        if (!Schema::hasTable('parcel_category')) {
        Schema::create('parcel_category', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('image')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
        }

        // 34. parcel_orders
        if (!Schema::hasTable('parcel_orders')) {
        Schema::create('parcel_orders', function (Blueprint $table) {
            $table->id();
            $table->string('id_user_app')->nullable();
            $table->string('source')->nullable();
            $table->string('destination')->nullable();
            $table->string('lat_source')->nullable();
            $table->string('lng_source')->nullable();
            $table->string('lat_destination')->nullable();
            $table->string('lng_destination')->nullable();
            $table->string('source_city')->nullable();
            $table->string('destination_city')->nullable();
            $table->string('sender_name')->nullable();
            $table->string('sender_phone')->nullable();
            $table->string('receiver_name')->nullable();
            $table->string('receiver_phone')->nullable();
            $table->string('parcel_weight')->nullable();
            $table->string('parcel_image')->nullable();
            $table->string('parcel_type')->nullable();
            $table->string('parcel_dimension')->nullable();
            $table->text('note')->nullable();
            $table->string('parcel_date')->nullable();
            $table->string('parcel_time')->nullable();
            $table->string('receive_date')->nullable();
            $table->string('receive_time')->nullable();
            $table->string('status')->nullable();
            $table->string('payment_status')->nullable();
            $table->string('id_payment_method')->nullable();
            $table->string('tax')->nullable();
            $table->string('discount')->nullable();
            $table->string('admin_commission')->nullable();
            $table->string('amount')->nullable();
            $table->string('id_driver')->nullable();
            $table->string('id_conducteur')->nullable();
            $table->text('rejected_driver_ids')->nullable();
            $table->string('otp')->nullable();
            $table->string('distance')->nullable();
            $table->string('distance_unit')->nullable();
            $table->string('reason')->nullable();
            $table->string('duration')->nullable();
            $table->string('tip')->nullable();
            $table->timestamps();
        });
        }

        // 35. payment_settings
        if (!Schema::hasTable('payment_settings')) {
        Schema::create('payment_settings', function (Blueprint $table) {
            $table->id();
            $table->dateTime('creer')->nullable();
            $table->dateTime('modifier')->nullable();
        });
        }

        // 36. tj_recu
        if (!Schema::hasTable('tj_recu')) {
        Schema::create('tj_recu', function (Blueprint $table) {
            $table->id();
            $table->string('image')->nullable();
            $table->string('image_name')->nullable();
            $table->string('montant')->nullable();
            $table->string('duree')->nullable();
            $table->string('distance')->nullable();
            $table->string('id_course')->nullable();
            $table->string('id_conducteur')->nullable();
            $table->string('id_user_app')->nullable();
            $table->dateTime('creer')->nullable();
            $table->dateTime('modifier')->nullable();
        });
        }

        // 37. referral
        if (!Schema::hasTable('referral')) {
        Schema::create('referral', function (Blueprint $table) {
            $table->id();
            $table->string('referral_by_id')->nullable();
            $table->string('referral_code')->nullable();
            $table->dateTime('creer')->nullable();
        });
        }

        // 38. tj_type_vehicule_rental
        if (!Schema::hasTable('tj_type_vehicule_rental')) {
        Schema::create('tj_type_vehicule_rental', function (Blueprint $table) {
            $table->id();
            $table->string('libelle')->nullable();
            $table->string('prix')->nullable();
            $table->string('image')->nullable();
            $table->dateTime('creer')->nullable();
            $table->dateTime('modifier')->nullable();
        });
        }

        // 39. tj_requete_book
        if (!Schema::hasTable('tj_requete_book')) {
        Schema::create('tj_requete_book', function (Blueprint $table) {
            $table->id();
            $table->string('id_user_app')->nullable();
            $table->string('depart_name')->nullable();
            $table->string('destination_name')->nullable();
            $table->string('latitude_depart')->nullable();
            $table->string('longitude_depart')->nullable();
            $table->string('latitude_arrivee')->nullable();
            $table->string('longitude_arrivee')->nullable();
            $table->integer('place')->nullable();
            $table->integer('number_poeple')->nullable();
            $table->decimal('distance', 10, 2)->nullable();
            $table->string('duree')->nullable();
            $table->decimal('montant', 10, 2)->nullable();
            $table->string('trajet')->nullable();
            $table->string('statut')->nullable();
            $table->string('statut_paiement')->nullable();
            $table->string('id_conducteur')->nullable();
            $table->string('id_payment_method')->nullable();
            $table->string('date_book')->nullable();
            $table->string('nb_day')->nullable();
            $table->string('heure_depart')->nullable();
            $table->string('cu')->nullable();
            $table->dateTime('creer')->nullable();
            $table->dateTime('modifier')->nullable();
            $table->string('statut_round')->nullable();
            $table->string('heure_retour')->nullable();
        });
        }

        // 40. tj_sos
        if (!Schema::hasTable('tj_sos')) {
        Schema::create('tj_sos', function (Blueprint $table) {
            $table->id();
            $table->string('ride_id')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->timestamps();
        });
        }

        // 41. subscription_history
        if (!Schema::hasTable('subscription_history')) {
        Schema::create('subscription_history', function (Blueprint $table) {
            $table->id();
            $table->string('expiry_date')->nullable();
            $table->string('payment_type')->nullable();
            $table->text('subscription_plan')->nullable();
            $table->string('user_id')->nullable();
            $table->string('subscriptionPlanId')->nullable();
            $table->timestamps();
        });
        }

        // 42. subscription_plans
        if (!Schema::hasTable('subscription_plans')) {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->integer('bookingLimit')->nullable();
            $table->text('description')->nullable();
            $table->integer('expiryDay')->nullable();
            $table->string('image')->nullable();
            $table->string('isEnable')->nullable();
            $table->string('name')->nullable();
            $table->string('place')->nullable();
            $table->string('plan_points')->nullable();
            $table->string('price')->nullable();
            $table->string('type')->nullable();
            $table->timestamps();
        });
        }

        // 43. tj_tax
        if (!Schema::hasTable('tj_tax')) {
        Schema::create('tj_tax', function (Blueprint $table) {
            $table->id();
            $table->string('libelle')->nullable();
            $table->string('value')->nullable();
            $table->string('type')->nullable();
            $table->string('statut')->nullable();
            $table->string('country')->nullable();
            $table->dateTime('creer')->nullable();
            $table->dateTime('modifier')->nullable();
        });
        }

        // 44. tj_terms_and_conditions
        if (!Schema::hasTable('tj_terms_and_conditions')) {
        Schema::create('tj_terms_and_conditions', function (Blueprint $table) {
            $table->id();
            $table->text('terms')->nullable();
            $table->dateTime('creer')->nullable();
            $table->dateTime('modifier')->nullable();
        });
        }

        // 45. tj_transaction
        if (!Schema::hasTable('tj_transaction')) {
        Schema::create('tj_transaction', function (Blueprint $table) {
            $table->id();
            $table->string('amount')->nullable();
            $table->string('id_user_app')->nullable();
            $table->dateTime('creer')->nullable();
            $table->dateTime('modifier')->nullable();
        });
        }

        // 46. tj_user_note
        if (!Schema::hasTable('tj_user_note')) {
        Schema::create('tj_user_note', function (Blueprint $table) {
            $table->id();
            $table->string('niveau_driver')->nullable();
            $table->string('id_conducteur')->nullable();
            $table->string('id_user_app')->nullable();
            $table->string('statut')->nullable();
            $table->dateTime('creer')->nullable();
            $table->dateTime('modifier')->nullable();
            $table->text('comment')->nullable();
        });
        }

        // 47. tj_user
        if (!Schema::hasTable('tj_user')) {
        Schema::create('tj_user', function (Blueprint $table) {
            $table->id();
            $table->string('nom_prenom')->nullable();
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->string('mdp')->nullable();
            $table->string('statut')->nullable();
            $table->dateTime('creer')->nullable();
            $table->dateTime('modifier')->nullable();
        });
        }

        // 48. tj_location_vehicule
        if (!Schema::hasTable('tj_location_vehicule')) {
        Schema::create('tj_location_vehicule', function (Blueprint $table) {
            $table->id();
            $table->string('nb_jour')->nullable();
            $table->string('date_debut')->nullable();
            $table->string('date_fin')->nullable();
            $table->string('contact')->nullable();
            $table->string('longitude_arrivee')->nullable();
            $table->string('statut')->nullable();
            $table->string('id_vehicule_rental')->nullable();
            $table->string('id_user_app')->nullable();
            $table->dateTime('creer')->nullable();
            $table->dateTime('modifier')->nullable();
        });
        }

        // 49. tj_vehicule_rental
        if (!Schema::hasTable('tj_vehicule_rental')) {
        Schema::create('tj_vehicule_rental', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->nullable();
            $table->string('statut')->nullable();
            $table->string('prix')->nullable();
            $table->string('nb_place')->nullable();
            $table->string('image')->nullable();
            $table->string('id_type_vehicule_rental')->nullable();
            $table->dateTime('creer')->nullable();
            $table->dateTime('modifier')->nullable();
        });
        }

        // 50. tj_vehicule_service_book
        if (!Schema::hasTable('tj_vehicule_service_book')) {
        Schema::create('tj_vehicule_service_book', function (Blueprint $table) {
            $table->id();
            $table->string('id_conducteur')->nullable();
            $table->string('km')->nullable();
            $table->string('photo_car_service_book')->nullable();
            $table->string('photo_car_service_book_path')->nullable();
            $table->string('file_name')->nullable();
            $table->dateTime('creer')->nullable();
            $table->dateTime('modifier')->nullable();
        });
        }

        // 51. withdrawals
        if (!Schema::hasTable('withdrawals')) {
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->string('amount')->nullable();
            $table->string('statut')->nullable();
            $table->string('id_conducteur')->nullable();
            $table->dateTime('creer')->nullable();
            $table->dateTime('modifier')->nullable();
            $table->timestamps();
        });
        }

        // 52. language
        if (!Schema::hasTable('language')) {
        Schema::create('language', function (Blueprint $table) {
            $table->id();
            $table->string('language')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
        }

        // 53. tj_privacy_policy
        if (!Schema::hasTable('tj_privacy_policy')) {
        Schema::create('tj_privacy_policy', function (Blueprint $table) {
            $table->id();
            $table->text('privacy_policy')->nullable();
            $table->dateTime('creer')->nullable();
            $table->dateTime('modifier')->nullable();
        });
        }

        // 54. tj_vehicle_images
        if (!Schema::hasTable('tj_vehicle_images')) {
        Schema::create('tj_vehicle_images', function (Blueprint $table) {
            $table->id();
            $table->string('id_vehicle')->nullable();
            $table->string('id_driver')->nullable();
            $table->string('image')->nullable();
            $table->string('image_path')->nullable();
            $table->dateTime('creer')->nullable();
            $table->dateTime('modifier')->nullable();
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tj_vehicle_images');
        Schema::dropIfExists('tj_privacy_policy');
        Schema::dropIfExists('language');
        Schema::dropIfExists('withdrawals');
        Schema::dropIfExists('tj_vehicule_service_book');
        Schema::dropIfExists('tj_vehicule_rental');
        Schema::dropIfExists('tj_location_vehicule');
        Schema::dropIfExists('tj_user');
        Schema::dropIfExists('tj_user_note');
        Schema::dropIfExists('tj_transaction');
        Schema::dropIfExists('tj_terms_and_conditions');
        Schema::dropIfExists('tj_tax');
        Schema::dropIfExists('subscription_plans');
        Schema::dropIfExists('subscription_history');
        Schema::dropIfExists('tj_sos');
        Schema::dropIfExists('tj_requete_book');
        Schema::dropIfExists('tj_type_vehicule_rental');
        Schema::dropIfExists('referral');
        Schema::dropIfExists('tj_recu');
        Schema::dropIfExists('payment_settings');
        Schema::dropIfExists('parcel_orders');
        Schema::dropIfExists('parcel_category');
        Schema::dropIfExists('on_boardings');
        Schema::dropIfExists('tj_notification');
        Schema::dropIfExists('tj_note');
        Schema::dropIfExists('tj_message');
        Schema::dropIfExists('landing_page_templates');
        Schema::dropIfExists('home_services');
        Schema::dropIfExists('tj_favorite_ride');
        Schema::dropIfExists('email_template');
        Schema::dropIfExists('driver_document');
        Schema::dropIfExists('tj_conducteur_transaction');
        Schema::dropIfExists('admin_documents');
        Schema::dropIfExists('dispatcher_user');
        Schema::dropIfExists('tj_discount');
        Schema::dropIfExists('delivery_charges');
        Schema::dropIfExists('tj_country');
        Schema::dropIfExists('tj_complaints');
        Schema::dropIfExists('tj_commission');
        Schema::dropIfExists('tj_cms');
        Schema::dropIfExists('dispatcher_booking');
        Schema::dropIfExists('banners');
        Schema::dropIfExists('admin_notification');
        Schema::dropIfExists('users_access');
        Schema::dropIfExists('car_model');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('tj_requete');
        Schema::dropIfExists('tj_user_app');
        Schema::dropIfExists('tj_payment_method');
        Schema::dropIfExists('tj_conducteur');
        Schema::dropIfExists('tj_vehicule');
        Schema::dropIfExists('tj_settings');
        Schema::dropIfExists('tj_type_vehicule');
        Schema::dropIfExists('tj_currency');
    }
};
