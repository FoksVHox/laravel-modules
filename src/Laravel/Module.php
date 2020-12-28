<?php

declare(strict_types=1);

namespace Nwidart\Modules\Laravel;

use Illuminate\Cache\CacheManager;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Foundation\ProviderRepository;
use Illuminate\Support\Str;
use Illuminate\Translation\Translator;
use Nwidart\Modules\Contracts\ActivatorInterface;
use Nwidart\Modules\Module as BaseModule;

class Module extends BaseModule
{
    /**
     * The laravel|lumen application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application|\Laravel\Lumen\Application
     */
    protected $app;

    /**
     * The module name.
     *
     * @var
     */
    public $name;

    /**
     * The module path.
     *
     * @var string
     */
    protected $path;

    /**
     * @var array of cached Json objects, keyed by filename
     */
    protected $moduleJson = [];

    /**
     * @var CacheManager
     */
    private $cache;

    /**
     * @var Filesystem
     */
    private $files;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var ActivatorInterface
     */
    private $activator;

    private $id;

    private $attributes;

    /**
     * The constructor.
     *
     * @param Container $app
     * @param string    $name
     * @param           $path
     * @param array      $attributes
     */
    public function __construct(Container $app, string $name, $path, $attributes = [])
    {
        parent::__construct($app, $name, $path);
        $this->attributes = $attributes;
        $this->id = isset($this->attributes['id']) ? $this->attributes['id'] : false;
    }

    /**
     * {@inheritdoc}
     */
    public function getCachedServicesPath(): string
    {
        return Str::replaceLast('services.php', $this->getSnakeName() . '_module.php', $this->app->getCachedServicesPath());
    }

    /**
     * {@inheritdoc}
     */
    public function registerProviders(): void
    {
        (new ProviderRepository($this->app, new Filesystem(), $this->getCachedServicesPath()))
            ->load($this->get('providers', []));
    }

    /**
     * {@inheritdoc}
     */
    public function registerAliases(): void
    {
        $loader = AliasLoader::getInstance();
        foreach ($this->get('aliases', []) as $aliasName => $aliasClass) {
            $loader->alias($aliasName, $aliasClass);
        }
    }

    public function getLaravel()
    {
        // TODO: Implement getLaravel() method.
    }

    public function enabled(): bool
    {
        return $this->attributes['is_active'];
    }

    public function disabled(): bool
    {
        return !$this->attributes['is_active'];
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Get a specific data from json file by given the key.
     *
     * @param string $key
     * @param null $default
     *
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        // Assume this Module is loaded from database.
        if ($this->getId()) {
            return isset($this->attributes[$key]) ? $this->attributes[$key] : $default;
        }
        return $this->json('module.json')->get($key, $default);
    }
}
