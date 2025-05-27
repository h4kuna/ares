<?php declare(strict_types=1);

namespace h4kuna\Ares\Adis;

use Generator;
use h4kuna\Ares\Adis\StatusBusinessSubjects\StatusBusinessSubjectsTransformer;
use h4kuna\Ares\Adis\StatusBusinessSubjects\Subject;
use h4kuna\Ares\Ares\Helper;
use h4kuna\Ares\Exception\LogicException;
use h4kuna\Ares\Exception\ServerResponseException;
use h4kuna\Ares\Tool\Batch;

final class ContentProvider
{
	public function __construct(private Client $client, private StatusBusinessSubjectsTransformer $stdClassTransformer)
	{
	}


	/**
	 * @throws ServerResponseException
	 */
	public function statusBusinessSubject(string $tin): Subject
	{
		foreach ($this->statusBusinessSubjects([$tin => $tin]) as $subject) {
			return $subject;
		}

		throw new LogicException('ADIS must return anything.');
	}


	/**
	 * @param array<string, string> $tin
	 * @return Generator<string, Subject>
	 *
	 * @throws ServerResponseException
	 */
	public function statusBusinessSubjects(array $tin): Generator
	{
		$duplicity = Batch::checkDuplicities($tin, static fn (string $tin) => Helper::normalizeTIN($tin));
		$chunks = Batch::chunk($duplicity, 100);

		foreach ($chunks as $chunk) {
			$responseData = $this->client->statusBusinessSubjects($chunk);

			foreach ($responseData as $item) {
				$subject = $this->stdClassTransformer->transform($item);
				foreach ($duplicity[$subject->tin] as $name) {
					yield $name => $subject;
				}
			}
		}
	}

}
