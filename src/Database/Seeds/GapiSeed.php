<?php

namespace Mrfrost\GoogleApi\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Mrfrost\GoogleApi\Config\GapiConfig;

class GapiSeed extends Seeder
{
    public function run()
    {
        $config = config(GapiConfig::class);

        $data = [
            [
                'name' => 'oauth',
            ],
            [
                'name' => 'gmail',
            ],
            [
                'name' => 'drive',
            ],
        ];
        $this->db->table($config->tables['services'])->emptyTable();
        $this->db->table($config->tables['services'])->insertBatch($data);
    }
}
