<?php namespace App\Http\Controllers;

use App\FieldValuePreset;
use App\Timer;
use App\User;
use App\Version;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class InstallController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Install Controller
	|--------------------------------------------------------------------------
	|
	| This controller handles generating the .env file and running the artisan
	| migration so the rest of the controllers can function. It also creates the
	| first user. And sets the application key, and creates needed folders
	*/

    /**
     * @var string - The version that will be assigned when you install kora.
     */
	const INSTALLED_VERSION = '3.0.0';

    /**
     * @var array - Directories that will be created upon installation.
     */
    public $DIRECTORIES = [
        "app/exodus",
        "app/exodus/assocData/",
        "app/exodus/kidConversions",
        "app/exports",
        "app/files",
        "app/profiles",
		"app/tmpFiles",
	];

    /**
     * Install kora - Creates the database, and adds any defaults needed
     *
     * @param  string $password - The admin password to create
     * @param  array $request - Optional DB values
     * @return bool - Whether things were successful or not
     */
	public function install($password, $request = null) {
        //Build the App Key
        try {
            echo "Generating app key...\n";
            Artisan::call("key:generate", array('--force' => true));
            echo "App Key generated!\n";
        } catch (\Exception $e) {
            Log::info($e);
            echo "Failed to add App Key to ENV! Review the logs for more error information.\n";
            $this->resetInstall();
            return false;
        }

        //Test out the DB connection
        $dbc = null;
        $dbHost = (!is_null($request) && isset($request->db_host)) ? $request->db_host : config('database.connections.mysql.host');
        $dbDatabase = (!is_null($request) && isset($request->db_database)) ? $request->db_database : config('database.connections.mysql.database');
        $dbUser = (!is_null($request) && isset($request->db_username)) ? $request->db_username : config('database.connections.mysql.username');
        $dbPassword = (!is_null($request) && isset($request->db_password)) ? $request->db_password : config('database.connections.mysql.password');
        try{
            echo "Testing database connection...\n";
            $dbc = new \PDO('mysql:host='.$dbHost.';dbname='.$dbDatabase, $dbUser, $dbPassword);
            echo "Database connection successful!\n";
        } catch(\Exception $e) {
            Log::info($e);
            echo "Failed to connect to database! Check your database credentials or review the logs for more error information.\n";
            $this->resetInstall();
            return false;
        }

        //Install database tables
        $shellRes = null;
        try {
            echo "Installing kora tables...\n";
            $shellRes = Artisan::call('migrate', array('--force' => true));
            echo "Kora tables installed!\n";
        } catch(\Exception $e) {
            Log::info($e);
            Log::info($shellRes);
            echo "Failed to install database tables! Review the logs for more error information.\n";
            $this->resetInstall($dbc);
            return false;
        }

        //Set the version number for this Kora 3 install
        try {
            echo "Setting kora version number...\n";
            $v = new Version();
            $v->version = InstallController::INSTALLED_VERSION;
            $v->save();
            echo "Version number set!\n";
        } catch(\Exception $e) {
            Log::info($e);
            echo "Failed to set version number! Review the logs for more error information.\n";
            $this->resetInstall($dbc);
            return false;
        }

				//Set the global timers for this Kora 3 install
        try {
            echo "Setting global timers...\n";
						foreach(Timer::$globalTimers as $tName) {
		            $timer = new Timer();
						    $timer->timestamps = false;
		            $timer->name = $tName;
								$timer->interval = Carbon::now();
		            $timer->save();
					  }
            echo "Global timers set!\n";
        } catch(\Exception $e) {
            Log::info($e);
            echo "Failed to set global timers! Review the logs for more error information.\n";
            $this->resetInstall($dbc);
            return false;
        }

        //Create all the needed directories for storage
        try {
            echo "Creating local storage directories...\n";
            $this->createDirectories();
            echo "Storage directories created!\n";
        } catch(\Exception $e) {
            Log::info($e);
            echo "Failed to create storage directories! Check user permissions for writing files to the kora directory.\n";
            $this->resetInstall($dbc);
            return false;
        }

        //Create admin user
        try {
            echo "Creating the admin user...\n";
            $this->makeAdmin($password);
            echo "Admin user created!\n";
        } catch(\Exception $e) {
            Log::info($e);
            echo "Failed to create the admin user! Review the logs for more error information.\n";
            $this->resetInstall($dbc);
            return false;
        }

        //Add the default field value presets
        try {
            echo "Adding global field value presets...\n";
            $this->createStockPresets();
            echo "Global field value presets created!\n";
        } catch(\Exception $e) {
            Log::info($e);
            echo "Failed to add global field value presets! Review the logs for more error information.\n";
            $this->resetInstall($dbc);
            return false;
        }

        //CLOSE THE CONNECTION
        $dbc = null;
        return true;
    }

    /**
     * Creates all the directories for the installation process.
     *
     * @return bool - Directories created
     */
    private function createDirectories() {
        foreach($this->DIRECTORIES as $dir) {
            if(!file_exists(storage_path($dir))) {
                try {
                    mkdir(storage_path($dir), 0775); //Notice the permission that is set and if it's OK!
                } catch(\Exception $e) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Create the admin user for the installation.
     *
     * @param  string $password - The admin password to create
     */
    private function makeAdmin($password) {
        $newuser = User::create([
            'username' => 'admin',
            'email' => 'root@localhost.com',
            'password' => bcrypt($password)
        ]);

        $preferences = array();
        $preferences['first_name'] = 'Kora';
        $preferences['last_name'] = 'Admin';
        $preferences['organization'] = 'Kora User';
        $preferences['language'] = 'en';
        $preferences['profile_pic'] = '';
        $preferences['use_dashboard'] = 1;
        $preferences['logo_target'] = 2;
        $preferences['proj_tab_selection'] = 2;
        $preferences['form_tab_selection'] = 2;
        $preferences['onboarding'] = 1;

        $newuser->preferences = $preferences;
        $newuser->active = 1;
        $newuser->admin = 1;
        $newuser->save();
    }

    /**
     * Create the stock field value presets for the installation.
     */
    private function createStockPresets() {
        foreach(FieldValuePreset::$STOCKPRESETS as $info) {
            $project_id = null;
            $shared = 0;
            $preset = $info;
            $created_at = $updated_at = Carbon::now();

            FieldValuePreset::create(compact("project_id","shared","preset","created_at","updated_at"));
        }
    }

    /**
     * Rolls back the install process so it can be rerun.
     *
     * @param  \PDO $dbc - Connection to the DB
     */
    private function resetInstall(\PDO $dbc = null) {
        //Empty the Database
        if(!is_null($dbc)) {
            if($result = $dbc->query("SHOW TABLES")) {
                while($row = $result->fetch(\PDO::FETCH_NUM)) {
                    $dbc->query('DROP TABLE IF EXISTS ' . $row[0]);
                }
                echo "Database reset!\n";
            }
        }

        //Close the connection
        $dbc = null;

        echo "Resolve issues and please try again!\n";
    }

    /**
     * Edits recaptcha and mail options in the ENV configuration file.
     *
     * @return View
     */
    public function editEnvConfigs() {
        if(!Auth::check())
            return redirect("/");

        if(!Auth::user()->admin)
            return redirect("/");

        $configs = array(
            ['title'=>'Allow Public Registration?',   'slug'=>'public_registration',  'value'=>config('auth.public_registration'), 'boolean'=>true],
            ['title'=>'Recaptcha Public Key',  'slug'=>'recaptcha_public',     'value'=>config('auth.recap_public'), 'boolean'=>false],
            ['title'=>'Recaptcha Private Key', 'slug'=>'recaptcha_private',    'value'=>config('auth.recap_private'), 'boolean'=>false],
            ['title'=>'Gitlab Client',         'slug'=>'gitlab_client',        'value'=>config('services.gitlab.host'), 'boolean'=>false],
            ['title'=>'Gitlab Client ID',      'slug'=>'gitlab_client_id',     'value'=>config('services.gitlab.client_id'), 'boolean'=>false],
            ['title'=>'Gitlab Client Secret',  'slug'=>'gitlab_client_secret', 'value'=>config('services.gitlab.client_secret'), 'boolean'=>false],
        );

        return view('admin.config',compact('configs'));
    }

    /**
     * Updates recaptcha and mail options in the ENV configuration file.
     *
     * @param  Request $request
     * @return Redirect
     */
    public function updateEnvConfigs(Request $request) {
        if(!Auth::user()->admin)
            return redirect('projects')->with('k3_global_error', 'not_admin');

        if(config('app.debug'))
            $debug = 'true';
        else
            $debug = 'false';

        //ENV values with spaces need to be surrounded in quotes
        //Whether or not a particular ENV value should have a space is another issue, but those errors are more manageable
        //ENV errors break everything!
        $public_registration = (isset($request->public_registration) && $request->public_registration) ? 'true' : 'false';
        $recaptcha_public = (strpos($request->recaptcha_public, ' ') !== false) ? '"'.$request->recaptcha_public.'"' : $request->recaptcha_public;
        $recaptcha_private = (strpos($request->recaptcha_private, ' ') !== false) ? '"'.$request->recaptcha_private.'"' : $request->recaptcha_private;
        $gitlab_client = (strpos($request->gitlab_client, ' ') !== false) ? '"'.$request->gitlab_client.'"' : $request->gitlab_client;
        $gitlab_client_id = (strpos($request->gitlab_client_id, ' ') !== false) ? '"'.$request->gitlab_client_id.'"' : $request->gitlab_client_id;
        $gitlab_client_secret = (strpos($request->gitlab_client_secret, ' ') !== false) ? '"'.$request->gitlab_client_secret.'"' : $request->gitlab_client_secret;

        $layout = "APP_ENV=" . config('app.env') . "\n".
            "APP_DEBUG=" . $debug . "\n".
            "APP_KEY=" . config('app.key') . "\n\n".

            "DB_HOST=" . config('database.connections.mysql.host') . "\n" .
            "DB_DATABASE=" . config('database.connections.mysql.database') . "\n" .
            "DB_USERNAME=" . config('database.connections.mysql.username') . "\n" .
            "DB_PASSWORD=" . config('database.connections.mysql.password') . "\n" .
            "DB_DEFAULT=" . config('database.default') . "\n" .
            "DB_PREFIX=" . config('database.connections.mysql.prefix') . "\n\n" .

            "CACHE_DRIVER=" . config('cache.default') . "\n".
            "SESSION_DRIVER=" . config('session.driver') . "\n" .
            "STORAGE_TYPE=" . config('filesystems.kora_storage') . "\n\n" .

            "PUBLIC_REGISTRATION=" . $public_registration . "\n" .
            "RECAPTCHA_PUBLIC_KEY=" . $recaptcha_public . "\n" .
            "RECAPTCHA_PRIVATE_KEY=" . $recaptcha_private . "\n\n" .

            "GITLAB_CLIENT=" . $gitlab_client . "\n" .
            "GITLAB_CLIENT_ID=" . $gitlab_client_id . "\n" .
            "GITLAB_CLIENT_SECRET=" . $gitlab_client_secret;

        try {
            Log::info("Beginning ENV Write");
            $envfile = fopen(base_path(".env"), "w");

            fwrite($envfile, $layout);

            fclose($envfile);
            Log::info("Ending ENV Write");
        } catch(\Exception $e) { //Most likely if the file is owned by another user or PHP doesn't have permission
            Log::info($e);
            return redirect('admin/config')->with('k3_global_error', 'env_cant_write');
        }

        return redirect('admin/config')->with('k3_global_success', 'kora_config_updated');
    }
}
