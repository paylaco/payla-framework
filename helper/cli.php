<?php
class CliHelper {

    protected $registry;

    public function __construct($registry) {
        if (!$this->isActive()) {
            $this->printWarning("Invalid request!");
        }
        $this->registry = $registry;
    }

    public function router() {
        global $argv;
        if (empty($argv[1]) || $argv[1] == 'cli/router') {
            $route = DEFAULT_ROUTE;
        } else {
            $route = str_replace('../', '', (string)$argv[1]);
        }
        $output = null;

        // Any output needs to be another Action object.
        $params = array_slice($argv, 2);

        // We dont want to use the loader class as it would make any controller callable.
        $action = new Action($route, $params);

        $this->registry->route_info = $this->route_info($route);

        $output = $action->execute($this->registry); 

        if($output === false)
            $this->printError("Route not found!");
    }

    protected function isActive() {
        return defined('PAYLA_CLI_MODE') && PAYLA_CLI_MODE === TRUE;
    }

    protected function route_info($route) {
        list($class, $func) = array_pad(explode('/',$route), 2, '');
        return [
            'class' => $class,
            'method' => $func
        ];
    }

    protected function printSuccess($msg = '')
    {
        cli_output("\e[32m" . $msg . "\e[0m", 1);
    }

    protected function printInfo($msg = '')
    {
        cli_output("\e[36m" . $msg . "\e[0m", 1);
    }

    protected function printWarning($msg = '')
    {
        cli_output("\e[33m" . $msg . "\e[0m", 1);
    }

    protected function printError($msg = '')
    {
        cli_output("\e[31m" . $msg . "\e[0m", 1);
    }

}