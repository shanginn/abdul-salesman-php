<?php

declare(strict_types=1);

namespace Shanginn\AbdulSalesman\Anthropic;

use Http\Adapter\React\Client;
use Http\Client\Exception\HttpException;
use Http\Discovery\Psr17FactoryDiscovery;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final readonly class AnthropicClient implements AnthropicClientInterface
{
    private const ANTHROPIC_VERSION = '2023-06-01';
    private ClientInterface $httpClient;
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;

    public function __construct(
        private string $apiKey,
        private string $apiUrl = 'https://api.anthropic.com/v1',
    ) {
        $this->httpClient     = new Client();
        $this->requestFactory = Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory  = Psr17FactoryDiscovery::findStreamFactory();
    }

    /**
     * @param string $method
     * @param string $json
     *
     * @throws HttpException
     * @throws ClientExceptionInterface
     *
     * @return string
     */
    public function sendRequest(string $method, string $json): string
    {
        $url = "{$this->apiUrl}/{$method}";

        dump($url, $json);

        $request = $this->requestFactory
            ->createRequest('POST', $url)
            ->withHeader('x-api-key', "{$this->apiKey}")
            ->withHeader('content-type', 'application/json')
            ->withHeader('anthropic-version', self::ANTHROPIC_VERSION)
            ->withBody(
                $this->streamFactory->createStream($json)
            );

        $response = $this->httpClient->sendRequest($request);

        dump($response);

        if ($response->getStatusCode() !== 200) {
            throw new HttpException(
                message: "Request failed with status code {$response->getStatusCode()} ({$response->getReasonPhrase()}): {$response->getBody()->getContents()}",
                request: $request,
                response: $response,
            );
        }

        return $response->getBody()->getContents();
    }
}
