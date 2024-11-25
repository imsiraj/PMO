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

class ReadActualMasterSheet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'read-sheet:actual-master {project_code?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command can reads the data from the excel configured for each project. And if specific project code is given then it reads only for that project sheet.';

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
            if( !empty($projectCode) ){
                $result = $controllerObject->readActualMasterProjectSheet($projectCode,$userId);
                Log::debug("Command read from actual-master with project code executed : ".$projectCode);
                Log::debug($result);
                Log::debug("================================");
                $this->info("Command read from actual-master with project code executed : ".$projectCode);
                $this->info($result);
                $this->info("================================");
            }else{
                $getProjects = Projects::getAllProject();
                if( !empty($getProjects) ){
                    $projects = $getProjects->toArray();
                    foreach($projects as $project){
                        $result = $controllerObject->readActualMasterProjectSheet($project['code'],$userId);
                        Log::debug("Command read from actual-master with project code executed : ".$project['code']);
                        Log::debug($result);
                        Log::debug("================================");
                        $this->info($result);
                        $this->info("================================");
                    }
                }
            }
        }catch(Exception $e){
            Log::error("Excpetion occured while reading actual master sheet using command");
            Log::error($e);
            Log::debug("================END=====================");
        }
    }
}
