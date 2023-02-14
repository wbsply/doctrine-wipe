<?php

namespace WebSupply\DoctrineWipe\Command;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Table;
use Neos\Flow\Annotations as Flow;
use Doctrine\DBAL\Connection;
use Neos\Flow\Cli\CommandController;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

#[Flow\Scope("singleton")]
final class DoctrineCommandController extends CommandController
{
    #[Flow\Inject]
    protected Connection $connection;

    /**
     * Truncate data from one or all tables.
     *
     * Given either the `--table <table>` or `--all` parameter, will truncate the
     * data from the tables.
     *
     * Apply the `--dry-run` parameter, wil output the statements, but not execute them
     *
     * @param string|null $table
     * @param bool $all
     * @param bool $dryRun
     */
    public function truncateCommand(?string $table = null, bool $all = false, bool $dryRun = false)
    {
        $this->outputDryRunNotification($dryRun);
        $this->ensureCorrectCommandArgumentsCombination($table, $all);
        $tableSchemas = $this->determineTablesFromCommandArguments($table, $all);

        /**
         * We leave out the flow_doctrine_migrationstatus table
         */
        $tableSchemas = array_filter($tableSchemas, fn(Table $tableSchema): bool => $tableSchema->getName() !== 'flow_doctrine_migrationstatus');

        $statements[] = 'SET foreign_key_checks = 0';
        foreach ($tableSchemas as $tableSchema) {
            $statements[] = $this->connection->getDatabasePlatform()->getTruncateTableSQL($tableSchema->getName());
        }
        $statements[] = 'SET foreign_key_checks = 1';

        $this->executeStatements($statements, $dryRun);
    }

    /**
     * Drop one or all tables.
     *
     * Given either the `--table <table>` or `--all` parameter, will drop the tables.
     *
     * Apply the `--dry-run` parameter, wil output the statements, but not execute them
     *
     * @param string|null $table
     * @param bool $all
     * @param bool $dryRun
     */
    public function dropCommand(?string $table = null, bool $all = false, bool $dryRun = false)
    {
        $this->outputDryRunNotification($dryRun);
        $this->ensureCorrectCommandArgumentsCombination($table, $all);
        $tableSchemas = $this->determineTablesFromCommandArguments($table, $all);

        $statements[] = 'SET foreign_key_checks = 0';
        foreach ($tableSchemas as $tableSchema) {
            $statements[] = $this->connection->getDatabasePlatform()->getDropTableSQL($tableSchema->getName());
        }
        $statements[] = 'SET foreign_key_checks = 1';

        $this->executeStatements($statements, $dryRun);
    }

    protected function ensureCorrectCommandArgumentsCombination($table, $all)
    {
        if ($table !== null && $all === true)
        {
            $this->outputLine('<error>Both "--table" and "--all" can not be set at the same time</error>');
            $this->quit();
        }

        if ($table === null && $all === false)
        {
            $this->outputLine('<error>Either "--table" must be a string or "--all"  must be passed. None was given</error>');
            $this->quit();
        }
    }

    /**
     * @param string|null $table
     * @param bool $all
     * @return Schema[]
     */
    protected function determineTablesFromCommandArguments(?string $table, bool $all): array
    {
        $tableSchemas = $this->connection->getSchemaManager()->listTables();
        return array_filter($tableSchemas, function(Table $tableSchema) use ($table, $all) {
            return $all ? true : ($tableSchema->getName() === $table);
        });
    }

    protected function executeStatements(array $statements, bool $dryRun): void
    {
        foreach ($statements as $statement) {
            $this->outputLine('<info>[QUERY]:</info> "%s"', [$statement]);
            if ($dryRun !== true) {
                try {
                    $this->connection->executeStatement($statement);
                } catch (Exception $exception) {
                    $this->outputLine('<error>[ERROR]:</error> "%s"', [$exception->getMessage()]);
                }
            }
        }
    }

    protected function outputDryRunNotification(bool $dryRun): void
    {
        if ($dryRun) {
            $this->output->getOutput()->getFormatter()->setStyle('notification', new OutputFormatterStyle('white', 'blue', ['bold']));
            $this->outputLine('<notification>--------------------------------------</notification>');
            $this->outputLine('<notification> Dry run - no statements are executed </notification>');
            $this->outputLine('<notification>--------------------------------------</notification>');
            $this->outputLine();
        }

    }
}
