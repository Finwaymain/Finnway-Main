<?php

namespace App\Models\Food;

use Illuminate\Database\Eloquent\Model;

class FoodSetting extends Model
{
    protected $table = 'food_settings';
    protected $fillable = ['key', 'value'];

    public static function getValue(string $key, $default = null)
    {
        $row = static::query()->where('key', $key)->first();
        return $row ? $row->value : $default;
    }

    public static function setValue(string $key, $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => (string) $value]);
    }
}
