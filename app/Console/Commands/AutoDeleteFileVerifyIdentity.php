<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\VerifyAccountIdentity;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class AutoDeleteFileVerifyIdentity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'verifyIdentity:file';
    protected $user;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete VerifyIdentity verify files after 30 days if admin confirm';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        VerifyAccountIdentity $verifyAccountIdentity
    ) {
        $this->verifyAccountIdentity = $verifyAccountIdentity;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $currentDate = Carbon::now()->toDateString();

        //delete file
        $file_buyer_deletes = $this->verifyAccountIdentity->findBuyDeleteDate($currentDate);
        foreach ($file_buyer_deletes as $file) {
            $path = 'identity/' . $file['account_id'] . '/' . $file->file;
            if (Storage::disk('private')->exists($path)) {
                Storage::disk('private')->delete($path);
            }
        }
        $this->verifyAccountIdentity->deleteFileByDeleteDate($currentDate);
    }
}
