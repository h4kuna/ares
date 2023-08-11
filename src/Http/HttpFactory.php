<?php

declare(strict_types=1);

namespace h4kuna\Ares\Http;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

/**
 * fallback for guzzlehttp/psr7
 */
final class HttpFactory implements RequestFactoryInterface, StreamFactoryInterface
{

	public function createStream(string $content = ''): StreamInterface
	{
		return Utils::streamFor($content);
	}


	public function createStreamFromFile(string $file, string $mode = 'r'): StreamInterface
	{
		try {
			$resource = Utils::tryFopen($file, $mode);
		} catch (\RuntimeException $e) {
			if ('' === $mode || false === \in_array($mode[0], ['r', 'w', 'a', 'x', 'c'], true)) {
				throw new \InvalidArgumentException(sprintf('Invalid file opening mode "%s"', $mode), 0, $e);
			}

			throw $e;
		}

		return Utils::streamFor($resource);
	}


	public function createStreamFromResource($resource): StreamInterface
	{
		return Utils::streamFor($resource);
	}


	public function createRequest(string $method, $uri): RequestInterface
	{
		return new Request($method, $uri);
	}

}
