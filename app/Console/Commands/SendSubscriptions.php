<?php

namespace App\Console\Commands;

use App\Services\FormationMessageServices;
use App\Services\SubscriptionService;
use App\Services\TravelPayoutsServices;
use App\Services\VkApi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:subscriptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(SubscriptionService $subscription_service, VkApi $vk_api, FormationMessageServices $formation_message_services, TravelPayoutsServices $travel_payouts_services)
    {
        try {
            $subscription_service->sendSupbscription($vk_api, $formation_message_services, $travel_payouts_services);
        } catch (\Throwable $th) {
            Log::error("Ошибка отправки билетов по подписке: " . $th->getMessage(), $th->getTrace());
        }

        return 0;
    }
}
