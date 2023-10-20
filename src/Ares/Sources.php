<?php declare(strict_types=1);

namespace h4kuna\Ares\Ares;

/**
 * become enum, php 8.1+
 */
final class Sources
{
	// Záznamy Veřejných rejstříků
	public const SERVICE_VR = 'stavZdrojeVr';
	// Záznamy Registru ekonomických subjektů
	public const SERVICE_RES = 'stavZdrojeRes';
	// Záznamy Registru živnostenského podnikání
	public const SERVICE_RZP = 'stavZdrojeRzp';
	// Záznamy Národního registru poskytovatelů zdravotnických služeb
	public const SERVICE_NRPZS = 'stavZdrojeNrpzs';
	// Záznamy Registru církví a náboženských společností
	public const SERVICE_RCNS = 'stavZdrojeRcns';
	// Záznamy Registru politických stran a hnutí
	public const SERVICE_RPSH = 'stavZdrojeRpsh';
	// Záznamy Registru škol
	public const SERVICE_RS = 'stavZdrojeRs';
	// Záznamy Zemědělského registru
	public const SERVICE_SZR = 'stavZdrojeSzr';
	// Záznamy Centrální evidence úpadců
	public const SERVICE_CEU = 'stavZdrojeCeu';
	// Data ekonomického subjektu z Jádra ARES
	public const CORE = 'ares';

	// not supported but note
	// https://adisspr.mfcr.cz/pmd/dokumentace/webove-sluzby-spolehlivost-platcu
	public const SER_NO_DPH = 'stavZdrojeDph';

	// https://isir.justice.cz/isir/common/index.do
	public const SER_NO_IR = 'stavZdrojeIr';

	// https://opendata.mfcr.cz/topics/dotace
	public const SER_NO_RED = 'stavZdrojeRed';

	// Spotřební daň a eko daň
	// https://www.celnisprava.cz/cz/aplikace/Stranky/SpdInternet.aspx?act=findspd
	public const SER_NO_SD = 'stavZdrojeSd';
	public const DIAL = 'ciselnikyNazevniky';

}
