<?php

declare (strict_types = 1);

namespace Mrfrost\GoogleApi\Models;

use Mrfrost\GoogleApi\ApiService\GapiException;
use Mrfrost\GoogleApi\Entities\GapiStore;
use Mrfrost\GoogleApi\GapiService;

/**
 * @phpstan-consistent-constructor
 */
class GapiModel extends BaseModel
{
    protected $primaryKey = 'id';
    protected $returnType = GapiStore::class;
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'name',
        'access_token', 'expires_in', 'scope', 'token_type', 'id_token', 'created', 'refresh_token',
    ];

    // Dates
    protected $useTimestamps = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    protected function initialize(): void
    {
        parent::initialize();

        $this->table = $this->tables['services'];
    }

    public function findByService(GapiService | String $service):  ? GapiStore
    {
        $query = "";
        if ($service instanceof GapiService) {
            $query = $service->getServiceName();
        } elseif (is_string($service)) {
            if (!array_key_exists($service, $this->gapiConfig->services)) {
                throw GapiException::forUnknownService($service);
            }
            $query = $service;
        } else {
            return null;
        }
        return $this->where('name', $query)->first();
    }
}