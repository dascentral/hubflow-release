<?php

namespace Dascentral\HubFlowRelease\Console\Services;

class PackageJson
{
    /**
     * The file that contains the content.
     *
     * @var mixed
     */
    protected $file = './package.json';

    /**
     * The parsed contents of "package.json".
     *
     * @var mixed
     */
    protected $packageJson;

    /**
     * Create a new instance of this class.
     *
     * @return void
     */
    public function __construct()
    {
        $this->packageJson = (file_exists($this->file)) ? json_decode(file_get_contents($this->file)) : null;
    }

    /**
     * Getter for the full package.json contents.
     *
     * @return mixed
     */
    public function contents()
    {
        return $this->packageJson;
    }

    /**
     * Getter for the name within the package.json.
     *
     * @return string
     */
    public function name()
    {
        return isset($this->packageJson->name) ? $this->packageJson->name : null;
    }

    /**
     * Getter for the version within the package.json.
     *
     * @return string
     */
    public function version()
    {
        return isset($this->packageJson->version) ? $this->packageJson->version : null;
    }

    /**
     * Save a new "package.json" with the provided version.
     *
     * @param  string $version
     * @return void
     */
    public function saveVersion($version)
    {
        $this->packageJson->version = $version;
        $this->save();
    }

    /**
     * Bump the version accordingly.
     *
     * @param  string $type
     * @return void
     */
    public function bump($type)
    {
        $this->updateVersion($type);
        $this->save();
    }

    /**
     * Determine the new version based upon type.
     *
     * @param  string $type
     * @return void
     */
    protected function updateVersion($type)
    {
        list($version_major, $version_minor, $version_patch) = explode('.', $this->packageJson->version);
        switch ($type) {
            case 'minor':
                $version_minor++;
                $version_patch = 0;
                break;

            case 'major':
                $version_major++;
                $version_minor = 0;
                $version_patch = 0;
                break;

            default:
                $version_patch++;
                break;
        }

        $this->packageJson->version = $version_major . '.' . $version_minor . '.' . $version_patch;
    }

    /**
     * Save the contents of the current object to disk.
     *
     * @return void
     */
    protected function save()
    {
        if ($fh = fopen($this->file, 'w')) {
            $json = json_encode($this->packageJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            fwrite($fh, $json);
        }
    }
}