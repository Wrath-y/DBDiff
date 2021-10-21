<?php namespace DBDiff;

use DBDiff\Params\ParamsFactory;
use DBDiff\DB\DiffCalculator;
use DBDiff\SQLGen\SQLGenerator;
use DBDiff\Exceptions\BaseException;
use DBDiff\Logger;
use DBDiff\Templater;


class DBDiff {
    public function setSourceTable($table) {
        $this->source_table = $table;
        return $this;
    }
    public function setTargetTable($table) {
        $this->target_table = $table;
        return $this;
    }
    public function run() {

        // Increase memory limit
        ini_set('memory_limit', '512M');

        try {
            $params = ParamsFactory::get($this->target_table, $this->source_table);

            // Diff
            $diffCalculator = new DiffCalculator;
            $diff = $diffCalculator->getDiff($params);

            // Empty diff
            if (empty($diff['schema']) && empty($diff['data'])) {
                return;
            }
            // SQL
            $sqlGenerator = new SQLGenerator($diff);
            $up =''; $down = '';
            if ($params->include !== 'down') {
                $up = $sqlGenerator->getUp();
            }
            if ($params->include !== 'up') {
                $down = $sqlGenerator->getDown();
            }

            // Generate
            $templater = new Templater($params, $up, $down);
            return $templater->output();
        } catch (\Exception $e) {
            throw $e;
        }

    }
}
