<?php declare(strict_types=1);

namespace h4kuna\Ares;

class BodyFactory
{


	/**
	 * @param array|string[] $identificationNumbers
	 * @return string
	 * @throws \Exception
	 */
	public function createBodyContent(array $identificationNumbers): string
	{
		$date = new \DateTime();
		$content = '
		<are:Ares_dotazy 
		xmlns:are="http://wwwinfo.mfcr.cz/ares/xml_doc/schemas/ares/ares_request_orrg/v_1.0.0" 
		xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
		xsi:schemaLocation="http://wwwinfo.mfcr.cz/ares/xml_doc/schemas/ares/ares_request_orrg/v_1.0.0 http://wwwinfo.mfcr.cz/ares/xml_doc/schemas/ares/ares_request_orrg/v_1.0.0/ares_request_orrg.xsd" 
		dotaz_datum_cas="' . $date->format('Y-m-dTH:i:s') . '" 
		dotaz_pocet="' . count($identificationNumbers) . '" 
		dotaz_typ="Basic" 
		vystup_format="XML" 
		validation_XSLT="http://wwwinfo.mfcr.cz/ares/xml_doc/schemas/ares/ares_answer/v_1.0.0/ares_answer.xsl" 
		answerNamespaceRequired="http://wwwinfo.mfcr.cz/ares/xml_doc/schemas/ares/ares_answer_basic/v_1.0.3"
		Id="Ares_dotaz">
		';

		foreach ($identificationNumbers as $key => $in) {
			$content .=  '<Dotaz><Pomocne_ID>' . $key . '</Pomocne_ID><ICO>' . $in . '</ICO></Dotaz>';
		}

		$content .= '</are:Ares_dotazy>';
		return $content;
	}

}
