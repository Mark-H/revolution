<?php

namespace MODX\Revolution\Transport;

use MODX\Revolution\modX;
use MODX\Revolution\Rest\modRest;
use MODX\Revolution\Rest\RestClientResponse;
use xPDO\Om\xPDOSimpleObject;
use xPDO\Transport\xPDOTransport;
use xPDO\xPDO;

/**
 * Class modTransportProvider
 *
 * @property string  $name
 * @property string  $description
 * @property string  $service_url
 * @property string  $username
 * @property string  $api_key
 * @property string  $created
 * @property string  $updated
 * @property boolean $active
 * @property integer $priority
 * @property array   $properties
 *
 * @package MODX\Revolution\Transport
 */
class modTransportProvider extends xPDOSimpleObject
{
    /** @var xPDO|modX */
    public $xpdo;

    /** @var modRest  */
    protected $client;

    /**
     * Return a list repositories from this Provider.
     *
     * @return array A list of repositories in this Provider.
     */
    public function repositories()
    {
        $response = $this->request('repository');
        if ($response->responseError) {
            return $this->xpdo->lexicon('provider_err_connect', ['error' => $response->responseError]);
        }
        $repositories = $response->process();

        $list = [];
        foreach ($repositories['repository'] as $repository) {
            $list[] = [
                'id' => 'n_repository_' . $repository['id'],
                'text' => $repository['name'],
                'leaf' => false,
                'data' => $repository,
                'type' => 'repository',
                'iconCls' => 'icon icon-folder',
            ];
        }

        return $list;
    }

    public function categories($node)
    {
        $this->xpdo->getVersionData();
        $productVersion = $this->xpdo->version['code_name'] . '-' . $this->xpdo->version['full_version'];

        $response = $this->request('repository/' . $node, 'GET', [
            'supports' => $productVersion,
        ]);
        if ($response->responseError) {
            return $this->xpdo->lexicon('provider_err_connect', ['error' => $response->responseError]);
        }
        $data = $response->process();

        $list = [];

        // The tags are either provided in <tag> elements, or in separate <repository> elements due to lack of a formal spec
        // Pre-3.0, both worked because it iterated over all xml children(), now we have to be a bit more specific in accessing the data
        $tags = array_key_exists('tag', $data) ? $data['tag'] : $data['repository'];
        foreach ($tags as $tag) {
            if (empty($tag['name'])) {
                continue;
            }
            $list[] = [
                'id' => 'n_tag_' . $tag['id'] . '_' . $node,
                'text' => $tag['name'],
                'leaf' => true,
                'data' => $tag,
                'type' => 'tag',
                'iconCls' => 'icon icon-tag',
            ];
        }

        return $list;
    }

    /**
     * Return statistical data about this Provider
     *
     * @param array $args Additional arguments to pass to the provider service
     *
     * @return array An array of statistics
     */
    public function stats(array $args = [])
    {
        $stats = [
            'packages' => 0,
            'downloads' => 0,
            'topdownloaded' => [],
            'newest' => [],
        ];
        $response = $this->request('home', 'GET', $args);

        if ($response->responseError) {
            $this->xpdo->log(xPDO::LOG_LEVEL_ERROR, $response->getError(), '', __METHOD__, __FILE__, __LINE__);
            return $stats;
        }

        $data = $response->process();

        $stats['packages'] = number_format((int)$data['packages']);
        $stats['downloads'] = number_format((int)$data['downloads']);

        foreach ($data['topdownloaded'] as $package) {
            $stats['topdownloaded'][] = [
                'url' => (string)$data['url'],
                'id' => (string)$package['id'],
                'name' => (string)$package['name'],
                'downloads' => number_format((int)$package['downloads'], 0),
            ];
        }

        foreach ($data['newest'] as $package) {
            $stats['newest'][] = [
                'url' => (string)$data['url'],
                'id' => (string)$package['id'],
                'name' => (string)$package['name'],
                'package_name' => (string)$package['package_name'],
                'releasedon' => strftime('%b %d, %Y', strtotime($package['releasedon'])),
            ];
        }

        return $stats;
    }

    public function info($identifier, array $args = [])
    {
        if (strpos($identifier, '-') > 0) {
            $response = $this->request('package', 'GET', ['signature' => $identifier]);
            if (!$response->responseError) {
                return $response->process();
            }
        }

        /* TODO: implement package info by package name */
        $this->xpdo->log(xPDO::LOG_LEVEL_ERROR,'Could not load package info from name for ' . $identifier . ', not yet implemented.');

        return [];
    }

    public function latest($identifier, $constraint = '*', array $args = [])
    {
        $latest = [];
        if (strpos($identifier, '-') === false) {
            $response = $this->request(
                'package/versions',
                'GET',
                array_merge(
                    [
                        'package' => $identifier,
                        'constraint' => $constraint,
                    ],
                    $args
                )
            );
            if ($response->responseError) {
                $this->xpdo->log(xPDO::LOG_LEVEL_ERROR, $response->responseError, '', __METHOD__, __FILE__, __LINE__);
            } else {
                $opts = $response->process();
                if (isset($opts['package']['id'])) {
                    $opts['package'] = [$opts['package']];
                }
                foreach ($opts['package'] as $package) {
                    if (xPDOTransport::satisfies((string)$package['version'], $constraint)) {
                        $latest[] = $package;
                    }
                }
            }
            return $latest;
        }

        $response = $this->request(
            'package/update',
            'GET',
            array_merge(
                [
                    'signature' => $identifier,
                    'constraint' => $constraint,
                ],
                $args
            )
        );
        if ($response->responseError) {
            $this->xpdo->log(xPDO::LOG_LEVEL_ERROR, $response->responseError, '', __METHOD__, __FILE__, __LINE__);
        }
        else {
            $opts = $response->process();
            if (isset($opts['package']['id'])) {
                $opts['package'] = [$opts['package']];
            }
            foreach ($opts['package'] as $package) {
                if (xPDOTransport::satisfies($package['version'], $constraint)) {
                    $latest[] = $package;
                }
            }
        }

        return $latest;
    }

    public function transfer($signature, $target = null, array $args = [])
    {
        $result = false;
        $metadata = $this->info($signature);
        if (!empty($metadata)) {
            /** @var modTransportPackage $package */
            $package = $this->xpdo->newObject(modTransportPackage::class);
            $package->set('signature', $signature);
            $package->set('state', 1);
            $package->set('workspace', 1);
            $package->set('created', strftime('%Y-%m-%d %H:%M:%S'));
            $package->set('provider', $this->get('id'));
            $package->set('metadata', $metadata);
            $package->set('package_name', $metadata['name']);

            $package->parseSignature();
            $package->setPackageVersionData();

            $locationArgs = (isset($metadata['file'])) ? array_merge($metadata['file'], $args) : $args;
            $url = $this->downloadUrl($signature, $this->arg('location', $locationArgs), $args);
            if (!empty($url)) {
                if (empty($target)) {
                    $target = $this->xpdo->getOption('core_path', $args, MODX_CORE_PATH) . 'packages/';
                }
                if ($package->transferPackage($url, $target)) {
                    if ($package->save()) {
                        $package->getTransport();
                        $result = $package;
                    }
                }
            }
        }

        return $result;
    }

    public function find(array $search = [], array $args = [])
    {
        $results = [];

        $where = array_merge(
            [
                'query' => false,
                'tag' => false,
                'sorter' => false,
                'start' => 0,
                'limit' => 10,
                'dateFormat' => '%b %d, %Y',
                'supportsSeparator' => ', ',
            ],
            $search
        );
        $where['page'] = !empty($where['start']) ? round($where['start'] / $where['limit']) : 0;

        $response = $this->request('package', 'GET', $where);
        if ($response->responseError) {
            $this->xpdo->log(xPDO::LOG_LEVEL_ERROR, $response->responseError, '', __METHOD__, __FILE__, __LINE__);

            return $results;
        }
        $data = $response->process();

        // If an ID is set, that means a single package was returned. In that case we'll wrap it in an array to avoid further processing.
        if (isset($data['package']['id'])) {
            $data['package'] = [$data['package']];
        }

        foreach ($data['package'] as $package) {
            $installed = $this->xpdo->getObject(modTransportPackage::class, (string)$package['signature']);

            $versionCompiled = rtrim($package['version'] . '-' . $package['release'], '-');
            $releasedon = strftime($this->arg('dateFormat', $where), strtotime($package['releasedon']));

            $support = is_array($package['supports']) ? implode($this->arg('supportsSeparator', $where), $package['supports']) : $package['supports'];
            $results[] = [
                'id' => (string)$package['id'],
                'version' => (string)$package['version'],
                'release' => (string)$package['release'],
                'signature' => (string)$package['signature'],
                'author' => (string)$package['author'],
                'description' => (string)$package['description'],
                'instructions' => (string)$package['instructions'],
                'changelog' => (string)$package['changelog'],
                'createdon' => (string)$package['createdon'],
                'editedon' => (string)$package['editedon'],
                'name' => (string)$package['name'],
                'downloads' => number_format((integer)$package['downloads'], 0),
                'releasedon' => $releasedon,
                'screenshot' => (string)$package['screenshot'],
                'thumbnail' => !empty($package['thumbnail']) ? (string)$package['thumbnail'] : (string)$package['screenshot'],
                'license' => (string)$package['license'],
                'minimum_supports' => (string)$package['minimum_supports'],
                'breaks_at' => (integer)$package['breaks_at'] != 10000000 ? (string)$package['breaks_at'] : '',
                'supports_db' => (string)$package['supports_db'],
                'location' => (string)$package['location'],
                'version-compiled' => $versionCompiled,
                'downloaded' => !empty($installed),
                'featured' => ((string)$package['featured'] == 'true'),
                'audited' => ((string)$package['audited'] == 'true'),
                'dlaction-icon' => $installed ? 'package-installed' : 'package-download',
                'dlaction-text' => $installed ? $this->xpdo->lexicon('downloaded') : $this->xpdo->lexicon('download'),
            ];
        }

        return [(int)$data['total'], $results];
    }

    protected function downloadUrl($signature, $location, array $args = [])
    {
        $client = new modRest($this->xpdo, [
            'suppressSuffix' => true,
            'format' => 'text',
        ]);


        $url = false;

        $response = $client->get($location, [
            'revolution_version' => $this->arg('revolution_version', $this->args($args)),
            'getUrl' => true,
        ]);
        if (empty($response) || empty($response->responseBody)) {
            $this->xpdo->log(xPDO::LOG_LEVEL_ERROR,
                "Could not get download url for package {$signature} using location {$location}");
        }
        elseif ($response->responseError) {
            $this->xpdo->log(xPDO::LOG_LEVEL_ERROR,"Could not get download url for package {$signature} using location {$location}: {$response->responseError}");
        }
        else {
            $url = (string)$response->responseBody;
        }

        return $url;
    }

    protected function arg($key, array $args = [], $default = null)
    {
        $arg = $default;
        if (array_key_exists($key, $this->args($args))) {
            $arg = $args[$key];
        }

        return $arg;
    }

    protected function args(array $args = [])
    {
        if (!is_array($this->xpdo->version)) {
            $this->xpdo->getVersionData();
        }
        $baseArgs = [
            'api_key' => $this->get('api_key'),
            'username' => $this->get('username'),
            'uuid' => $this->xpdo->uuid,
            'database' => $this->xpdo->config['dbtype'],
            'revolution_version' => $this->xpdo->version['code_name'] . '-' . $this->xpdo->version['full_version'],
            'supports' => $this->xpdo->version['code_name'] . '-' . $this->xpdo->version['full_version'],
            'http_host' => $this->xpdo->getOption('http_host'),
            'php_version' => PHP_VERSION,
            'language' => $this->xpdo->getOption('manager_language', $_SESSION,
                $this->xpdo->getOption('cultureKey', null, 'en')),
        ];

        return array_merge($baseArgs, $args);
    }

    /**
     * Sends a REST request to the provider
     *
     * @param string $path   The path of the request
     * @param string $method The method of the request (GET/POST)
     * @param array  $params An array of parameters to send to the REST request
     *
     * @return RestClientResponse The response from the REST request, or false
     */
    public function request($path, $method = 'GET', $params = [])
    {
        $client = $this->getClient();
        return $client->request($method, $path, $this->args($params));
    }

    /**
     * Get the client responsible for communicating with the provider.
     *
     * @return modRest A modRest client instance
     */
    public function getClient()
    {
        if (!$this->client) {
            $this->client = new modRest($this->xpdo, [
                'baseUrl' => $this->get('service_url'),
                'suppressSuffix' => true,
                'format' => 'xml', // Note: for POST requests, this also affects the **request** parameters
            ]);
        }

        return $this->client;
    }

    /**
     * Verifies the authenticity of the provider
     *
     * @return boolean True if verified, xml if failed
     */
    public function verify()
    {
        $response = $this->request('verify', 'GET');
        if ($response->responseError) {
            $message = $response->responseError;
            if ($this->xpdo->lexicon && $this->xpdo->lexicon->exists('provider_err_' . $message)) {
                $message = $this->xpdo->lexicon('provider_err_' . $message);
            }

            return $message;
        }
        $status = $response->process();

        return (bool)$status['verified'];
    }

    /**
     * Overrides xPDOObject::save to set the created date.
     *
     * @param boolean $cacheFlag
     *
     * @return boolean True if successful
     */
    public function save($cacheFlag = null)
    {
        if ($this->isNew() && !$this->get('created')) {
            $this->set('created', strftime('%Y-%m-%d %H:%M:%S'));
        }
        $saved = parent:: save($cacheFlag);

        return $saved;
    }
}
