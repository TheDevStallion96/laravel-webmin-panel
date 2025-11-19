<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDatabaseRequest;
use App\Models\Database;
use App\Models\Site;
use App\Services\Database\DatabaseManager;
use App\Actions\Sites\WriteEnv;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;

class DatabasesController extends Controller
{
    public function index(Site $site)
    {
        $this->authorize('update', $site); // manage-site gate previously ensures only admins/developers
        $databases = $site->databases()->orderByDesc('id')->get();
        return view('sites.databases.index', compact('site','databases'));
    }

    public function store(StoreDatabaseRequest $request, Site $site, DatabaseManager $manager, WriteEnv $writer)
    {
        $this->authorize('update', $site);
        $engine = $request->input('engine');
        $name = $request->input('name');
        $username = Str::limit($site->slug.'_'.Str::random(6), 32, '');
        $password = Str::random(20);
        $host = '127.0.0.1';
        $port = $engine === 'mysql' ? 3306 : 5432;

        $driver = $manager->driver($engine);
        $driver->createDatabase($name);
        $driver->createUser($username, $password);
        $driver->grant($username, $name);

        $record = Database::create([
            'site_id' => $site->id,
            'engine' => $engine,
            'name' => $name,
            'username' => $username,
            'host' => $host,
            'port' => $port,
            'password_encrypted' => Crypt::encryptString($password),
        ]);

        // Update site environment to reflect this DB
        $env = $site->environment ?? [];
        $env['db_connection'] = $engine === 'mysql' ? 'mysql' : 'pgsql';
        $env['db_host'] = $host;
        $env['db_port'] = $port;
        $env['db_database'] = $name;
        $env['db_username'] = $username;
        $env['db_password'] = $password;
        $site->environment = $env;
        $site->save();
        $writer->write($site, storage_path('sites/'.$site->slug));

        if (function_exists('activity')) {
            activity()->onSite($site)->action('database.created')->meta([
                'id' => $record->id,
                'engine' => $engine,
                'name' => $name,
            ])->log();
        }

        return redirect()->route('sites.databases.index', $site)->with('status','database-created');
    }

    public function destroy(Site $site, Database $database, DatabaseManager $manager)
    {
        $this->authorize('update', $site);
        if ($database->site_id !== $site->id) {
            abort(404);
        }
        $engine = $database->engine;
        $driver = $manager->driver($engine);
        $driver->revoke($database->username, $database->name);
        $driver->dropDatabase($database->name);
        $database->delete();

        if (function_exists('activity')) {
            activity()->onSite($site)->action('database.deleted')->meta([
                'engine' => $engine,
                'name' => $database->name,
            ])->log();
        }

        return redirect()->route('sites.databases.index', $site)->with('status','database-deleted');
    }
}
