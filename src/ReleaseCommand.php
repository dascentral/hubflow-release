<?php

namespace Dascentral\HubFlowRelease\Console;

use Dascentral\HubFlowRelease\Console\Services\PackageJson;
use Dascentral\HubFlowRelease\Console\Services\VersionManager;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ReleaseCommand extends Command
{
    /**
     * The parsed contents of the "package.json" from the current directory.
     *
     * @var Dascentral\HubFlowRelease\Console\Services\PackageJson
     */
    protected $packageJson;

    /**
     * A class capable of managing versions.
     *
     * @var Dascentral\HubFlowRelease\Console\Services\VersionManager
     */
    protected $versionManager;

    /**
     * Create a new instance of this class.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->packageJson = new PackageJson();
        $this->versionManager = new VersionManager();
    }

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('release')
             ->setDescription('Perform a full app release via HubFlow.')
             ->addArgument('type', InputArgument::OPTIONAL);
    }

    /**
     * Execute the command.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $type = ($input->getArgument('type')) ? strtolower($input->getArgument('type')) : 'patch';

        // Get the current application version
        $initial_version = $this->getCurrentVersion();

        // Determine the next version
        $version = $this->versionManager->bump($initial_version, $type);

        // Start the HubFlow release
        $this->startRelease($version, $output);

        // Save the new application version
        $this->packageJson->saveVersion($version);

        // Commit the change
        $this->commitChange($version, $output);

        // Finish the HubFlow release
        $this->finishRelease($version, $output);

        // Share output with the user
        $this->outputResult($initial_version, $output);
    }

    /**
     * Determine the current version of the "package.json".
     *
     * @return void
     */
    protected function getCurrentVersion()
    {
        // Confirm that the "package.json" file exists
        if (!$this->packageJson->contents()) {
            $output->writeln("\n" . '<error>No package.json in current directory.</error>' . "\n");
            exit(1);
        }

        // Confirm version information exists within the "package.json"
        if (!$initial_version = $this->packageJson->version()) {
            $output->writeln("\n" . '<error>No version information found within the package.json.</error>' . "\n");
            exit(1);
        }

        return $initial_version;
    }

    /**
     * Begin the HubFlow release.
     *
     * @param  string $version
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    protected function startRelease($version, $output)
    {
        if ($output->isVerbose()) {
            $output->writeln("\n" . '<comment>Starting the release</comment>');
        }

        $process = new Process("git hf release start $version");
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    /**
     * Commit the change to the "package.json".
     *
     * @param  string $version
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    protected function commitChange($version, $output)
    {
        if ($output->isVerbose()) {
            $output->writeln('<comment>Committing the new "package.json"</comment>');
        }

        $process = new Process("git add package.json");
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $process = new Process("git commit -m \"Version $version\"");
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    /**
     * Finish the HubFlow release.
     *
     * @param  string $version
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    protected function finishRelease($version, $output)
    {
        if ($output->isVerbose()) {
            $output->writeln('<comment>Finishing the release</comment>');
        }

        $process = new Process("git hf release finish $version");
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    /**
     * Output result to the user.
     *
     * @param  string $initial_version
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    protected function outputResult($initial_version, $output)
    {
        $message = ($this->packageJson->name()) ? '<info>' . $this->packageJson->name() . '</info> updated.' : '<info>Application updated.</info>';
        $message .= ' (<comment>v' . $initial_version . '</comment> => <comment>v' . $this->packageJson->version() . '</comment>)';
        $output->writeln("\n" . $message . "\n");
    }
}