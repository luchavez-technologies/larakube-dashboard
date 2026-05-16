<?php

use App\Mcp\Servers\LaraKubeConsoleServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('mcp', LaraKubeConsoleServer::class);
