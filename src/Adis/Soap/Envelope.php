<?php declare(strict_types=1);

namespace h4kuna\Ares\Adis\Soap;

final class Envelope
{
	public static function seznamNespolehlivyPlatce(): string
	{
		return self::soap('<SeznamNespolehlivyPlatceRequest xmlns="http://adis.mfcr.cz/rozhraniCRPDPH/"></SeznamNespolehlivyPlatceRequest>');
	}

	public static function statusNespolehlivyPlatce(string ...$tin): string
	{
		$dic = implode('</dic><dic>', $tin);
		return self::soap(<<<XML
		<StatusNespolehlivyPlatceRequest xmlns="http://adis.mfcr.cz/rozhraniCRPDPH/">
			<dic>$dic</dic>
		</StatusNespolehlivyPlatceRequest>
		XML);
	}


	public static function StatusNespolehlivyPlatceRozsireny(string ...$tin): string
	{
		$dic = implode('</roz:dic><roz:dic>', $tin);
		return self::soapExtends(<<<XML
		<roz:StatusNespolehlivyPlatceRequest xmlns="http://adis.mfcr.cz/rozhraniCRPDPH/">
			<roz:dic>$dic</roz:dic>
		</roz:StatusNespolehlivyPlatceRequest>
		XML);
	}


	public static function StatusNespolehlivySubjektRozsireny(string ...$tin): string
	{
		$dic = implode('</roz:dic><roz:dic>', $tin);
		return self::soapExtends(<<<XML
		<roz:StatusNespolehlivySubjektRozsirenyRequest xmlns="http://adis.mfcr.cz/rozhraniCRPDPH/">
			<roz:dic>$dic</roz:dic>
		</roz:StatusNespolehlivySubjektRozsirenyRequest>
		XML);
	}

	private static function soap(string $body): string
	{
		return <<<XML
		<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
			<soapenv:Body>
				$body
			</soapenv:Body>
		</soapenv:Envelope>';
		XML;
	}


	private static function soapExtends(string $body): string
	{
		return <<<XML
		<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"  xmlns:roz="http://adis.mfcr.cz/rozhraniCRPDPH/">
			<soapenv:Header/>
			<soapenv:Body>
				$body
			</soapenv:Body>
		</soapenv:Envelope>
		XML;
	}
}
