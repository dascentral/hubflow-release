<?php

namespace Dascentral\Rl\Console;

use Dascentral\Rl\Console\Services\PackageJson;
use Dascentral\Rl\Console\Services\VersionManager;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class AppCommand extends Command
{
    /**
     * The parsed contents of the "package.json" from the current directory.
     *
     * @var Dascentral\Rl\Console\Services\PackageJson
     */
    protected $packageJson;

    /**
     * A class capable of managing versions.
     *
     * @var Dascentral\Rl\Console\Services\VersionManager
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
        $this->setName('app')
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
echo "$version\n";
die();

        // Start the HubFlow release

        // Bump the application version
        $this->packageJson->bump($type);

        // Commit the change

        // Finish the HubFlow release

        // Share output with the user
        $this->outputResult($output, $initial_version);
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
     * Output result to the user.
     *
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @param  string $initial_version
     * @return void
     */
    protected function outputResult($output, $initial_version)
    {
        $message = ($this->packageJson->name()) ? 'Updating <info>' . $this->packageJson->name() . '</info>.' : '<info>Updating application.</info>';
        $message .= ' (<comment>v' . $initial_version . '</comment> => <comment>v' . $this->packageJson->version() . '</comment>)';
        $output->writeln("\n" . $message . "\n");
    }
}