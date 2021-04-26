<?php

namespace Jaulz\LateralJoins;

use Illuminate\Database\PostgresConnection;
use Jaulz\LateralJoins\Query\Builder;

class Connection extends PostgresConnection {
    //@Override
    public function query() {
        return new Builder(
            $this,
            $this->getQueryGrammar(),
            $this->getPostProcessor()
        );
    }
}