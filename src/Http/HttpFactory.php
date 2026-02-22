<?php declare(strict_types = 1);

namespace h4kuna\Ares\Http;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use InvalidArgumentException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use function in_array;
use function sprintf;

/**
 * @deprecated only for guzzle/psr7 < 2.0
 * Copy from see
 * @see https://github.com/guzzle/psr7/blob/2.6/src/HttpFactory.php
 */
final class HttpFactory implements RequestFactoryInterface, StreamFactoryInterface
{

	public function createStream(string $content = ''): StreamInterface
	{
		return Utils::streamFor($content);
	}

	public function createStreamFromFile(
		string $file,
		string $mode = 'r',
	): StreamInterface
	{
		try {
			$resource = Utils::tryFopen($file, $mode);
		} catch (RuntimeException $e) {
			if ($mode === '' || in_array($mode[0], ['r', 'w', 'a', 'x', 'c'], true) === false) {
				throw new InvalidArgumentException(sprintf('Invalid file opening mode "%s"', $mode), 0, $e);
			}

			throw $e;
		}

		return Utils::streamFor($resource);
	}

	public function createStreamFromResource(mixed $resource): StreamInterface
	{
		return Utils::streamFor($resource);
	}

	public function createRequest(
		string $method,
		mixed $uri,
	): RequestInterface
	{
		return new Request($method, $uri);
	}

}
