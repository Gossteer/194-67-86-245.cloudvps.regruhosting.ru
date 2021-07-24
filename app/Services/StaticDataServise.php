<?php

namespace App\Services;

use App\Models\UserData;

class StaticDataServise
{
    /**
     * Метод по изменению данных статистики пользователя
     *
     * @param  string $column
     * @param  mix $value
     * @param  UserData $user_data
     * @return bool
     */
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

    /**
     * Метод по получению/созданию данных статистики пользователя
     *
     * @param  int $user_id
     * @return UserData
     */
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

    /**
     * Метод по получению статистики в промежутке времени
     *
     * @param  string $model
     * @param  string $date_old
     * @return int
     */
    public function getStaticDataBetwinDate(string $model, string $date_old): int
    {

        return $model::where('created_at', '>=', $this->getDateFormatForMySql($date_old))->count();
    }

    /**
     * Метод по получению статистики в промежутке времени определённого пользователя
     *
     * @param  string $model
     * @param  string $date_old
     * @param  int user_id
     * @return int
     */
    public function getStaticDataBetwinDateForUser(string $model, string $date_old, int $user_id): int
    {
        return $model::where('user_id', $user_id)->where('created_at', '>=', $this->getDateFormatForMySql($date_old))->count();
    }

    /**
     * Метод по получению статистики  определённого пользователя
     *
     * @param  string $model
     * @param  int $user_id
     * @return int
     */
    public function getStaticDataForUser(string $model, int $user_id): int
    {
        return $model::where('user_id', $user_id)->count();
    }

    /**
     * Форматирование даты в необходимом формате
     *
     * @param  string $date
     * @return string|null
     */
    private function getDateFormatForMySql(string $date): ?string
    {
        return date('Y-m-d H:i:s', strtotime($date));
    }
}
