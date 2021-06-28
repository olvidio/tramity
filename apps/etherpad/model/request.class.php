<?php 
namespace etherpad\model;

use GuzzleHttp\Client as HttpClient;
use Psr\Http\Message\ResponseInterface;
use core\ConfigGlobal;

require_once(ConfigGlobal::$dir_libs.'/vendor/autoload.php');

class Request
{
    /**
     * @var string|null
     */
    private $apiKey = null;
    /**
     * @var string|null
     */
    private $url = null;
    /**
     * @var string
     */
    private $method;
    /**
     * @var array
     */
    private $args;

    /**
     * @param string $url
     * @param string $apiKey
     * @param string $method
     * @param array $args
     */
    public function __construct(string $url, string $apiKey, string $method, $args = [])
    {
        $this->url = $url;
        $this->apiKey = $apiKey;
        $this->method = $method;
        $this->args = $args;
    }

    /**
     * Send the built request url against the etherpad lite instance
     *
     * @return ResponseInterface
     */
    public function send(): ResponseInterface
    {
        $client = new HttpClient(['base_uri' => $this->url]);

        /* Ahora se permite via POST
        return $client->get(
            $this->getUrlPath(),
            [
                'query' => $this->getParams(),
            ]
        );
        */
        return $client->post(
            $this->getUrlPath(),
            [
                'query' => $this->getParams(),
                'verify' => false,
            ]
        );
    }

    /**
     * Returns the path of the request url
     *
     * @return string
     */
    protected function getUrlPath(): string
    {
        $existingPath = parse_url($this->url, PHP_URL_PATH);

        return $existingPath.sprintf(
                '/api/%s/%s',
                Client::API_VERSION,
                $this->method
            );
    }

    /**
     * Maps the given arguments from Client::__call to the parameter of the api method
     *
     * @return array
     */
    public function getParams(): array
    {
        $params = array();
        $args = $this->args;

        $params['apikey'] = $this->apiKey;

        $methods = Client::getMethods();

        foreach ($methods[$this->method] as $key => $paramName) {
            if (isset($args[$key])) {
                $params[$paramName] = $args[$key];
            }
        }

        return $params;
    }
}
