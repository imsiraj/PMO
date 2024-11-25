<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\GoogleSheetsController;
use Illuminate\Support\Facades\Log;
use App\Services\GoogleSheetsService;
use App\Services\LoggingService;
use App\Models\Projects;
use Users;
use Exception;

class ClearChangeLogSheet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:change-log-sheet {project_code?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command clears the data in the change log sheet. At specific time or when executed manually.';

    /**
     * Create a new command instance.
     *
     * @return void
     */

    protected $sheetsService;
    protected $loggingService;
    public function __construct(GoogleSheetsService $sheetsService,LoggingService $loggingService)
    {
        $this->sheetsService = $sheetsService;
        $this->loggingService = $loggingService;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try{
            $projectCode = $this->argument('project_code');
            $controllerObject = new GoogleSheetsController($this->sheetsService,$this->loggingService);
            $userId = Users::SUPER_ADMIN;
            $revisionId = time();
            if( !empty($projectCode) ){
                $result = $controllerObject->clearChangeLogSheetData($projectCode,$userId,$revisionId);
                Log::debug("Command clear change logs sheet with project code executed : ".$projectCode);
                Log::debug(json_encode($result));
                Log::debug("================================");
                $this->info("Command clear change logs sheet with project code executed : ".$projectCode);
                $this->info(json_encode($result));
                $this->info("================================");
            }else{
                $getProjects = Projects::getAllProject();
                if( !empty($getProjects) ){
                    $projects = $getProjects->toArray();
                    foreach($projects as $project){
                        $result = $controllerObject->clearChangeLogSheetData($project['code'],$userId,$revisionId);
                        Log::debug("Command read from actual-master with project code executed : ".$project['code']);
                        Log::debug(json_encode($result));
                        Log::debug("================================");
                        $this->info("Command clear change logs sheet with project code executed : ".$project['code']);
                        $this->info(json_encode($result));
                        $this->info("================================");
                    }
                }
            }
        }catch(Exception $e){
            Log::error("Excpetion occured while clear change log sheet using command");
            Log::error($e);
            Log::debug("================END=====================");
        }
    }
}
