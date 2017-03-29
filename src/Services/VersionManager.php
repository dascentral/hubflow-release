<?php

namespace Dascentral\HubFlowRelease\Console\Services;

class VersionManager
{
    /**
     * Determine the new version based upon type.
     *
     * @param  string $type
     * @return string
     */
    public function bump($version, $type)
    {
        // Parse the version provided
        list($major, $minor, $patch) = $this->parseVersion($version);

        // Bump the version
        $version = $this->increment($type, $major, $minor, $patch);

        return $version;
    }

    /**
     * Parse the string provided into major, minor, and patch integers.
     *
     * @param  string $version
     * @return array
     */
    protected function parseVersion($version)
    {
        // TODO: This logic assumes a version formatted as X.X.X. Add additional logic to account for variants.
        return explode('.', $version);
    }

    /**
     * Increment the version accordingly.
     *
     * @param  string $type
     * @param  int $major
     * @param  int $minor
     * @param  int $patch
     * @return string
     */
    protected function increment($type, $major, $minor, $patch)
    {
        switch ($type) {
            case 'minor':
                $minor++;
                $patch = 0;
                break;

            case 'major':
                $major++;
                $minor = 0;
                $patch = 0;
                break;

            default:
                $patch++;
                break;
        }

        return $major . '.' . $minor . '.' . $patch;
    }
}