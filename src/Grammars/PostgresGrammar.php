<?php

namespace Jaulz\LateralJoins\Grammars;

use Illuminate\Database\Query\Grammars\PostgresGrammar as Base;

class PostgresGrammar extends Base
{
	use CompilesLateralJoins;
}