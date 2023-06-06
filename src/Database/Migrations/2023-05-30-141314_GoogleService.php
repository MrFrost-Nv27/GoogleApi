<?php

namespace Mrfrost\GoogleApi\Database\Migrations;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Forge;
use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;
use Config\Database;
use Mrfrost\GoogleApi\Config\GapiConfig;
use Mrfrost\GoogleApi\Database\Seeds\GapiSeed;

class GoogleService extends Migration
{
    private array $tables;

    private array $attributes;

    public function __construct( ? Forge $forge = null)
    {
        $gapiConfig = config(GapiConfig::class);

        if ($gapiConfig->DBGroup !== null) {
            $this->DBGroup = $gapiConfig->DBGroup;
        }

        parent::__construct($forge);

        $this->tables = $gapiConfig->tables;
        $this->attributes = ($this->db->getPlatform() === 'MySQLi') ? ['ENGINE' => 'InnoDB'] : [];
    }

    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'varchar', 'constraint' => 64, 'null' => false],
            'access_token' => ['type' => 'text', 'null' => true],
            'expires_in' => ['type' => 'text', 'null' => true],
            'scope' => ['type' => 'text', 'null' => true],
            'token_type' => ['type' => 'text', 'null' => true],
            'id_token' => ['type' => 'text', 'null' => true],
            'created' => ['type' => 'text', 'null' => true],
            'refresh_token' => ['type' => 'text', 'null' => true],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
            'deleted_at' => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('name');
        $this->createTable($this->tables['services']);

        $seeder = Database::seeder();
        $seeder->call(GapiSeed::class);
    }

    public function down()
    {
        /** @var BaseConnection $db */
        $db = $this->db;
        $db->disableForeignKeyChecks();

        $this->forge->dropTable($this->tables['services'], true);

        $db->enableForeignKeyChecks();
    }

    private function createTable(string $tableName) : void
    {
        $this->forge->createTable($tableName, false, $this->attributes);
    }
}
