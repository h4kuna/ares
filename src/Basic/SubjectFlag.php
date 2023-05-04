<?php declare(strict_types=1);

namespace h4kuna\Ares\Basic;

/**
 * Indexation is from 0 for substr()
 * @see https://wwwinfo.mfcr.cz/ares/ares_xml_standard.html.cz
 */
final class SubjectFlag
{
	public const VR_2 = 1;
	public const RES_3 = 2;
	public const RZP_4 = 3;
	public const NRPZS_5 = 4;
	public const RPDPH_6 = 5;
	public const RPSD_7 = 6;
	public const CEU_K_9 = 8;
	public const CEU_V_10 = 9;
	public const CEDR_11 = 10;
	public const ARIS_12 = 11;
	public const RCNS_14 = 13;
	public const SPSH_15 = 14;
	public const ZR_21 = 20;
	public const IR_22 = 21;
	public const RSSZ_23 = 22;
	public const RO_25 = 24;

}
