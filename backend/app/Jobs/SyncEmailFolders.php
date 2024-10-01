<?php

namespace App\Jobs;

use App\Facades\ImapDataParser;
use App\Models\Elastic\EmailFolder;
use App\Models\OauthAccount;
use App\Services\EmailUpdateService;
use App\Services\MailService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncEmailFolders implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $oauthAccount;
    public $esEmailFolderModel;

    /**
     * Create a new job instance.
     */
    public function __construct(OauthAccount $oauthAccount)
    {
        $this->oauthAccount = $oauthAccount;
    }

         /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return $this->oauthAccount->id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Syncing folders for account: " . $this->oauthAccount->id);

        try {
            $this->esEmailFolderModel = new EmailFolder();

            $mailServiceInstance = new MailService($this->oauthAccount);
            $mailServiceClient = $mailServiceInstance->getClient();

            $folders = $mailServiceClient->getFolders();

            $this->esEmailFolderModel->deleteAcountFolders($this->oauthAccount->id);

            foreach ($folders as $folder) {

                Log::info("Syncing account: " . $this->oauthAccount->id . " and folder: " . $folder->name);

                $folderData = ImapDataParser::parseFolderData($folder);

                $this->esEmailFolderModel->add($this->oauthAccount, $folderData);

                SyncFolderMessages::dispatch($this->oauthAccount, $folder->name);

                // Setting Idle job in separate queue to scale separately
                IdleEmailFolder::dispatch($this->oauthAccount, $folder->name)->onQueue('imap_idle');

                Log::info("Synced folders for account: " . $this->oauthAccount->id . " and folder: " . $folder->name);
            }
        } catch (Exception $e) {
            Log::error("Exception in account: " . $this->oauthAccount->id);
            Log::error($e->getMessage());

            $this->fail($e);
        }
        
        EmailUpdateService::sendEmailUpdate($this->oauthAccount->id, $this->oauthAccount->provier, 'sync_folders');

        Log::info("Synced folders for account: " . $this->oauthAccount->id);
    }
}
