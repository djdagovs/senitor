<?php namespace Ballen\Senitor;

use Ballen\Senitor\Entities\MessageBag;
use Ballen\Senitor\Entities\Target;
use Ballen\Senitor\Entities\Transmission;
use Ballen\Senitor\Handlers\XmwsRequest;
use InvalidArgumentException;

class Senitor
{

    const SENITOR_VERSION = "1.0.0";

    /**
     * The XMWS user/server credentials object
     * @var Target 
     */
    protected $credentials;
    protected $module;
    protected $endpoint;
    protected $data;
    protected $optional_http_client_headers = [];

    public function __construct($credentials = null)
    {
        if ($credentials instanceof Target) {
            $this->credentials = $credentials;
        }
    }

    /**
     * Set optional HTTP client headers for the Guzzle client
     * @see http://guzzle.readthedocs.org/en/latest/clients.html#request-options
     * @param array $options
     * @return \Ballen\Senitor\Senitor
     */
    public function setHttpOptions(array $options)
    {
        $this->optional_http_client_headers = $options;
        return $this;
    }

    /**
     * Set credentials and server details.
     * @param Target $credentials
     * @return \Ballen\Senitor\Senitor
     */
    public function setCredentials(Target $credentials)
    {
        $this->credentials = $credentials;
        return $this;
    }

    /**
     * Sets the XMWS API module
     * @param string $module
     * @return \Ballen\Senitor\Senitor
     */
    public function setModule($module)
    {
        $this->module = $module;
        return $this;
    }

    /**
     * Sets the XMWS API endpoint (or more specifically, the webservice.ext.php method to call)
     * @param string $endpoint
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    /**
     * Set request data to be sent with the XMWS request.
     * @param MessageBag $data
     * @return Senitor
     * @throws InvalidArgumentException
     */
    public function setRequestData($data = null)
    {
        if ($data instanceof MessageBag) {
            $this->data = $data;
        }
        if (is_array($data)) {
            $this->data = MessageBag::getInstance();
            $this->data->setItems($data);
        }
        return $this;
    }

    /**
     * Dispatch the XMWS request and return the response object.
     */
    public final function send()
    {
        $response = new XmwsRequest(new Transmission(
            $this->credentials, $this->module, $this->endpoint, $this->data), array_merge($this->getClientHeaders(), $this->optional_http_client_headers)
        );
        $response->send();
    }

    /**
     * Set some default HTTP client options.
     * @return array
     */
    private function getClientHeaders()
    {
        return [
            'headers' => [
                'User-Agent' => 'senitor/' . $this->getSenitorVersion(),
                'Accept' => 'application/xml',
            ]
        ];
    }

    /**
     * Return the version of the Senitor Client version (useful for feature checking)
     * @return string
     */
    public final function getSenitorVersion()
    {
        return (double) self::SENITOR_VERSION;
    }
}
