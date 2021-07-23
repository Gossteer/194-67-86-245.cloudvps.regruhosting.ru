<?php

namespace App\Services;

use App\Models\UserData;
use Illuminate\Database\Eloquent\Model;

class StaticDataServise
{
    public function changeUserStatic(string $column, $value, UserData $user_data): bool
    {
        $user_data_value = $user_data->value;

        if (!$user_data_value) {
            $user_data_value = [];
        }

        $user_data_value[$column] = json_encode($value);
        $user_data->value = $user_data_value;

        return $user_data->save();
    }

    public function getUserStatic(int $user_id): UserData
    {
        $user_data = UserData::where('user_id', $user_id)->where('key', 'static_data')->first();

        if (!$user_data) {
            $user_data = UserData::create([
                'key' => 'static_data',
                'user_id' => $user_id
            ]);
        }

        return $user_data;
    }

    public function getStaticDataBetwinDate(string $model, string $date_old): int
    {

        return $model::where('created_at', '>=', $this->getDateFormatForMySql($date_old))->count();
    }

    public function getStaticDataBetwinDateForUser(string $model, string $date_old, int $user_id): int
    {
        return $model::where('user_id', $user_id)->where('created_at', '>=', $this->getDateFormatForMySql($date_old))->count();
    }

    public function getStaticDataForUser(string $model, int $user_id): int
    {
        return $model::where('user_id', $user_id)->count();
    }

    private function getDateFormatForMySql(string $date): ?string
    {
        return date('Y-m-d H:i:s', strtotime($date));
    }
}
