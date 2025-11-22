<?php

namespace App\Console\Commands;

use App\Http\Controllers\admin\UsersController;
use Illuminate\Console\Command;

class NotificationAlertCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:notification_alert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'description';

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

       // El código que se ejecuta cuando se corre el comando

       $controller = new UsersController();
       $function_test = $controller->test_command();

        echo $function_test;
        return Command::SUCCESS;
    }
}
