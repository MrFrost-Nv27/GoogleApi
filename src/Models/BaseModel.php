<?php

declare (strict_types = 1);

namespace Mrfrost\GoogleApi\Models;

use CodeIgniter\Model;
use Mrfrost\GoogleApi\Config\GapiConfig;

abstract class BaseModel extends Model
{
    /**
     * Auth Table names
     */
    protected array $tables;

    protected GapiConfig $gapiConfig;

    public function __construct()
    {
        $this->gapiConfig = config(GapiConfig::class);

        if ($this->gapiConfig->DBGroup !== null) {
            $this->DBGroup = $this->gapiConfig->DBGroup;
        }

        parent::__construct();
    }

    protected function initialize(): void
    {
        $this->tables = $this->gapiConfig->tables;
    }
}