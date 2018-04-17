<?php


namespace Mapbender\CoreBundle\Component\Cache;


use Mapbender\CoreBundle\Component\Exception\NotCachable;
use Mapbender\CoreBundle\Entity\Application;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Caching service for assorted Mapbender Application-specific raw string data values.
 * Storage backend separately pluggable via container. First implementation defaults to a filesystem backend.
 *
 * Instance registered in container as mapbender.presenter.application.cache (see services.xml)
 */
class ApplicationDataService
{
    /** @var LoggerInterface */
    protected $logger;

    /** @var Backend\File */
    protected $backend;

    /** @var float */
    protected $containerTimestamp;

    /**
     * ApplicationDataService constructor.
     * @param LoggerInterface $logger
     * @param Backend\File $backend
     * @param float $containerTimestamp
     */
    public function __construct(LoggerInterface $logger, $backend, $containerTimestamp)
    {
        $this->logger = $logger;
        $this->backend = $backend;
        $this->containerTimestamp = $containerTimestamp;
    }

    /**
     * Returns cached (and non-stale) data as a single string. If no reusable data is found, returns false.
     *
     * @param Application $application
     * @param string[] $keyPath
     * @return string|false false if no cache data available.
     */
    public function getValue(Application $application, $keyPath)
    {
        try {
            $mark = $this->getMark($application);
            $fullKeyPath = array_merge(array($application->getSlug()), $keyPath);
            return $this->backend->get($fullKeyPath, $mark);
        } catch (NotCachable $e) {
            return false;
        }
    }

    /**
     * Returns cached (and non-stale) data baked into a Response with the desired $mimeType. If no
     * reusable data is found, returns false.
     *
     * @param Application $application
     * @param string[] $keyPath
     * @param string $mimeType
     * @return Response|false
     */
    public function getResponse(Application $application, $keyPath, $mimeType)
    {
        try {
            $mark = $this->getMark($application);
            $fullKeyPath = array_merge(array($application->getSlug()), $keyPath);
            $content = $this->backend->get($fullKeyPath, $mark);
            if ($content === false) {
                return false;
            }
            $response = new Response($content, 200, array(
                'Content-Type' => $mimeType,
            ));
            // @todo: add etag etc
            return $response;
        } catch (NotCachable $e) {
            return false;
        }
    }

    /**
     * @param Application $application
     * @param string[] $keyPath
     * @param string $value
     */
    public function putValue(Application $application, $keyPath, $value)
    {
        try {
            $mark = $this->getMark($application);
            $fullKeyPath = array_merge(array($application->getSlug()), $keyPath);
            $this->backend->put($fullKeyPath, $value, $mark);
        } catch (NotCachable $e) {
            // do nothing
        }
    }

    /**
     * Generates the reusability mark for application data.
     * Considers application "updated" column plus container compilation time, so any change to the application
     * or any configuration file makes the cache stale.
     *
     * @param Application $application
     * @return array
     */
    protected function getMark(Application $application)
    {
        $parts = array();
        if ($this->containerTimestamp !== null) {
            $parts[] = $this->containerTimestamp;
        }
        $updated = $application->getUpdated();
        if ($updated) {
            // NOTE: $updated is only available on DB applications, always NULL for yaml applications.
            //       Including the container compilation time makes caching safe even for yaml applications though,
            //       because any edit to any application's yml triggers a container re-compilation, which is reflected
            //       in the container timestamp.
            $parts[] = $updated->format('U-u');
        }
        if (!$parts) {
            throw new NotCachable();
        }
        return $parts;
    }
}
