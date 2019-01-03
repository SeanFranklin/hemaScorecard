<?php

namespace Scorecard\Infrastructure;


use Scorecard\Config\PdoConnection;

abstract class AbstractPdoRepository
{
    protected $handle;

    public function __construct(\PDO $PDO = null)
    {
        $this->handle = isset($PDO) ? $PDO : PdoConnection::getConnection();
    }
}