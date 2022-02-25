<?php

namespace Jaulz\LateralJoins;

use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\DatabaseTransactionsManager;
use Illuminate\Database\Schema\Grammars\PostgresGrammar as SchemaGrammar;
use Jaulz\LateralJoins\Connection;
use Jaulz\LateralJoins\Grammars\PostgresGrammar;

class DatabaseServiceProvider extends \Illuminate\Database\DatabaseServiceProvider
{
    /**
     * Register the primary database bindings.
     *
     * @return void
     */
    protected function registerConnectionServices()
    {
        // The connection factory is used to create the actual connection instances on
        // the database. We will inject the factory into the manager so that it may
        // make the connections while they are actually needed and not of before.
        $this->app->singleton('db.factory', function ($app) {
            return new ConnectionFactory($app);
        });

        // The database manager is used to resolve various connections, since multiple
        // connections might be managed. It also implements the connection resolver
        // interface which may be used by other components requiring connections.
        $this->app->singleton('db', function ($app) {
            // Load the default DatabaseManager
            $dbm = new DatabaseManager($app, $app['db.factory']);

            // Extend to include the custom connection (Postgres only for now)
            $dbm->extend('pgsql', function($config, $name) use ($app) {
                // Create default connection from factory
                $connection = $app['db.factory']->make($config, $name);
                
                // Instantiate our connection with the default connection data
                $newConnection = new Connection(
                    $connection->getPdo(),
                    $connection->getDatabaseName(),
                    $connection->getTablePrefix(),
                    $config + [
                        'name' => $name,
                    ]
                );

                // Set the appropriate grammar object
                $newConnection->setQueryGrammar(new PostgresGrammar());
                $newConnection->setSchemaGrammar(new SchemaGrammar());
                
                return $newConnection;
            });
            
            return $dbm;
        });

        $this->app->bind('db.connection', function ($app) {
            return $app['db']->connection();
        });

        $this->app->bind('db.schema', function ($app) {
            return $app['db']->connection()->getSchemaBuilder();
        });

        $this->app->singleton('db.transactions', function ($app) {
            return new DatabaseTransactionsManager;
        });
    }
}
