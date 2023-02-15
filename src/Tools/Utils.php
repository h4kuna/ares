<?php declare(strict_types=1);

namespace h4kuna\Ares\Tools;

use Nette\Utils\Strings;

final class Utils
{

	private static function toArray(?\stdClass $content, string $property): bool
	{
		if ($content !== null && property_exists($content, $property)) {
			if ($content->$property === null) {
				$content->$property = [];
			} elseif (is_array($content->$property) === false) {
				$content->$property = [$content->$property];
			}

			return true;
		}

		return false;
	}


	public static function nameAddress(?\stdClass $content, string $property): void
	{
		if (self::toArray($content, $property) === false) {
			return;
		} elseif (isset($content->$property) && is_array($content->$property) === true) {
			foreach ($content->$property as $item) {
				if (isset($item->C->FO)) {
					self::person($item->C->FO);
				}
				if (isset($item->FO)) {
					self::person($item->FO);
				}
				if (isset($item->C->PO)) {
					self::company($item->C->PO);
				}
				if (isset($item->PO)) {
					self::company($item->PO);
				}
				if (isset($item->C->VF->DZA)) {
					$item->C->VF->DZA = self::toDate($item->C->VF->DZA);
				}
				if (isset($item->C->CLE->DZA)) {
					$item->C->CLE->DZA = self::toDate($item->C->CLE->DZA);
				}
			}
		}
	}


	private static function person(\stdClass $person): void
	{
		if (isset($person->DN)) {
			$person->DN = self::toDate($person->DN);
		}

		if (isset($person->J)) {
			$person->J = self::capitalize($person->J);
		}

		if (isset($person->P)) {
			$person->P = self::capitalize($person->P);
		}

		if (isset($person->B)) {
			self::address($person->B);
		}
	}


	private static function toDate(string $date): \DateTimeImmutable
	{
		$date = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
		assert($date !== false);

		return $date;
	}


	private static function capitalize(string $string): string
	{
		return Strings::capitalize($string);
	}


	public static function address(\stdClass $address): void
	{
		$addr = new \stdClass();
		$addr->zip = $address->PSC ?? '';
		$addr->ks = $address->KS;
		$addr->country = $address->NS;

		$cd = $address->CD ?? null;
		$nu = $address->NU ?? '';
		$nco = $address->NCO ?? null;
		if ($cd === null && $nu !== '') { // číslo domu je obsaženo v ulici
			$match = Strings::match($nu, '/(?<CD>\d+(?:\/[\d\w]+)?)$/');
			$cd = $match['CD'] ?? '';
			$nu = trim(str_replace($cd, '', $nu));
		}

		if ($nu === '' && $nco !== null) {
			$nu = $nco;
			$nco = null;
		}

		$addr->street = $nu === '' ? $nco : $nu;
		$addr->house = isset($address->CO) ? trim($cd . '/' . $address->CO, '/') : $cd;
		$addr->city = isset($nco) ? $address->N . ' - ' . $nco : $address->N;

		$address->optional = $addr;
	}


	private static function company(\stdClass $company): void
	{
		self::address($company->SI);
	}

}
