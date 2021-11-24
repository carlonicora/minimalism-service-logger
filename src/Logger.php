<?php
namespace CarloNicora\Minimalism\Services\Logger;

use CarloNicora\Minimalism\Abstracts\AbstractService;
use CarloNicora\Minimalism\Enums\LogLevel;
use CarloNicora\Minimalism\Interfaces\LoggerInterface;
use CarloNicora\Minimalism\Services\Logger\Objects\MinimalismLog;
use CarloNicora\Minimalism\Services\Path;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\Handler;
use Monolog\Handler\StreamHandler;

class Logger extends AbstractService
{
    /** @var array  */
    protected array $extra=[];

    /** @var array  */
    private array $handlers=[];

    /** @var array|MinimalismLog[]  */
    private array $logs=[];

    /**
     * Logger constructor.
     * @param Path $path
     * @param int $MINIMALISM_LOG_LEVEL
     */
    public function __construct(
        private Path $path,
        private int $MINIMALISM_LOG_LEVEL= \Monolog\Logger::WARNING
    )
    {
        parent::__construct();

        $this->handlers[] = [$this, 'getStreamHandler'];
        $this->initialise();
    }

    /**
     * @return string|null
     */
    public static function getBaseInterface(
    ): ?string
    {
        return LoggerInterface::class;
    }

    /**
     * @param callable $handler
     */
    protected function addHandler(
        callable $handler,
    ): void
    {
        $this->handlers[] = $handler;
    }

    /**
     * @param string $name
     * @param string|int $value
     */
    public function addExtraInformation(
        string $name,
        string|int $value,
    ): void
    {
        $this->extra[$name] = $value;
    }

    /**
     * @param string $message
     * @param string|null $domain
     * @param array $context
     */
    public function debug(
        string $message,
        ?string $domain=null,
        array $context = []
    ): void
    {
        $this->logs[] = new MinimalismLog(
            LogLevel::Debug,
            $domain,
            $message,
            $context
        );
    }

    /**
     * @param string $message
     * @param string|null $domain
     * @param array $context
     */
    public function info(
        string $message,
        ?string $domain=null,
        array $context = []
    ): void
    {
        $this->logs[] = new MinimalismLog(
            LogLevel::Info,
            $domain,
            $message,
            $context
        );
    }

    /**
     * @param string $message
     * @param string|null $domain
     * @param array $context
     */
    public function notice(
        string $message,
        ?string $domain=null,
        array $context = []
    ): void
    {
        $this->logs[] = new MinimalismLog(
            LogLevel::Notice,
            $domain,
            $message,
            $context
        );
    }

    /**
     * @param string $message
     * @param string|null $domain
     * @param array $context
     */
    public function warning(
        string $message,
        ?string $domain=null,
        array $context = []
    ): void
    {
        $this->logs[] = new MinimalismLog(
            LogLevel::Warning,
            $domain,
            $message,
            $context
        );
    }

    /**
     * @param string $message
     * @param string|null $domain
     * @param array $context
     */
    public function error(
        string $message,
        ?string $domain=null,
        array $context = []
    ): void
    {
        $this->logs[] = new MinimalismLog(
            LogLevel::Error,
            $domain,
            $message,
            $context
        );
    }

    /**
     * @param string $message
     * @param string|null $domain
     * @param array $context
     */
    public function critical(
        string $message,
        ?string $domain=null,
        array $context = []
    ): void
    {
        $this->logs[] = new MinimalismLog(
            LogLevel::Critical,
            $domain,
            $message,
            $context
        );
    }

    /**
     * @param string $message
     * @param string|null $domain
     * @param array $context
     */
    public function alert(
        string $message,
        ?string $domain=null,
        array $context = []
    ): void
    {
        $this->logs[] = new MinimalismLog(
            LogLevel::Alert,
            $domain,
            $message,
            $context
        );
    }

    /**
     * @param string $message
     * @param string|null $domain
     * @param array $context
     */
    public function emergency(
        string $message,
        ?string $domain=null,
        array $context = []
    ): void
    {
        $this->logs[] = new MinimalismLog(
            LogLevel::Emergency,
            $domain,
            $message,
            $context
        );
    }

    /**
     * @param string|null $domain
     * @return \Monolog\Logger
     */
    protected function getLogger(?string $domain=null): \Monolog\Logger
    {
        $response = new \Monolog\Logger($domain??'minimalism');
        $this->setHandlers($response);

        $response->pushProcessor(function($record){
            foreach ($this->extra as $name=>$value){
                $record['extra'][$name] = $value;
            }
            return $record;
        });

        return $response;
    }

    /**
     * @param \Monolog\Logger $logger
     */
    protected function setHandlers(\Monolog\Logger $logger): void
    {
        foreach ($this->handlers ?? [] as $handler){
            $loggerHandler = $handler();
            if ($loggerHandler !== null) {
                $logger->pushHandler(
                    $loggerHandler
                );
            }
        }
    }

    /**
     * @return Handler
     */
    protected function getStreamHandler(): Handler
    {
        $response = new StreamHandler(
            $this->getLogsFolder()
            . date('Ymd') . '.log',
            $this->MINIMALISM_LOG_LEVEL
        );
        /** @noinspection UnusedFunctionResultInspection */
        $response->setFormatter(new JsonFormatter());

        return $response;
    }

    /**
     * @return string
     */
    protected function getLogsFolder(): string
    {
        return $this->path->getRoot() . DIRECTORY_SEPARATOR
            . 'logs' . DIRECTORY_SEPARATOR;
    }

    /**
     *
     */
    public function flush(): void
    {
        foreach ($this->logs ?? [] as $log){
            if ($this->path->getUrl() !== null){
                $log->addUriToContext(
                    $this->path->getUri()
                );
            }

            switch ($log->getLevel()){
                case LogLevel::Info:
                    $this->getLogger($log->getDomain())->info($log->getMessage(), $log->getContext());
                    break;
                case LogLevel::Notice:
                    $this->getLogger($log->getDomain())->notice($log->getMessage(), $log->getContext());
                    break;
                case LogLevel::Warning:
                    $this->getLogger($log->getDomain())->warning($log->getMessage(), $log->getContext());
                    break;
                case LogLevel::Error:
                    $this->getLogger($log->getDomain())->error($log->getMessage(), $log->getContext());
                    break;
                case LogLevel::Critical:
                    $this->getLogger($log->getDomain())->critical($log->getMessage(), $log->getContext());
                    break;
                case LogLevel::Alert:
                    $this->getLogger($log->getDomain())->alert($log->getMessage(), $log->getContext());
                    break;
                case LogLevel::Emergency:
                    $this->getLogger($log->getDomain())->emergency($log->getMessage(), $log->getContext());
                    break;
                case LogLevel::Debug:
                    $this->getLogger($log->getDomain())->debug($log->getMessage(), $log->getContext());
                    break;
            }
        }

        $this->logs = [];
    }

    /**
     *
     */
    public function initialise(
    ): void
    {
    }

    /**
     *
     */
    public function destroy(
    ): void
    {
        $this->flush();
    }

    /**
     * @return LogLevel
     */
    public function getLogLevel(
    ): LogLevel
    {
        return LogLevel::from($this->MINIMALISM_LOG_LEVEL);
    }
}