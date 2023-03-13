<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\LogActivity;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Repositories\User\UserRepositoryInterface;
class TestJobCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'test cron';
    protected $userRepository;

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
    public function handle()
    {
        $date = Carbon::now()->format('Y-m-d H:i:s');
        $content = "Ting Ting " . $date . "!!! ";
        $check_file = Storage::exists('logs/test_cron.log');
        if ($check_file == false) {
            Storage::disk('local')->append('logs/test_cron.log', $content);
        } else {
            Storage::append('logs/test_cron.log', $content);
        }
    }
}
