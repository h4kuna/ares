# v3.0.0

- podpora nového API ARES2
- v [bin](./bin) jsou spustitelné ukázky, jak se dostat na číselníky a jak na ostatní endpointy
- pro IČO je vyžadován formát \d{8}, pokud je kratší, knihovna sama doplní nuly zleva
- očekávám že se změní url API, pro tento případ je připravená `public static h4kuna\Ares\Ares\Helper::$baseUrl`, kterou
  lze nahradit, bez nutnosti vyčkávat na nový release
- podobně lze doplnit nebo upravit url adresy endpointů
- php 8.0+

### Třidy

- h4kuna\Ares\Ares
    - metoda `loadBasic()` stále vrací [Data](./src/Ares/Core/Data.php)
    - metoda `loadBasicMulti()`
        - nově vrací Generator nikoliv pole
        - vrací jen existující záznamy, třída Error byla smazána, nemá náhradu
        - počet IČO není omezen, interně se rozdělí na dávky po 100, záznamech a ještě před tím se odeberou duplicity,
          při iteraci duplicity zůstanou, jen objekty budou mít stejné reference
        - bylo zachováno pojmenování, vstupem je `['foo' => 123456]`, název `foo` bude jako klíč při iteraci


- h4kuna\Ares\Ares\Core\Data
    - zmizela metoda `psu()` bez náhrady, podobné informace jsou ve vlastnosti `$sources`
    - zmizela metoda `isGroupVat()`, skupinové DPH nelze zjistit. Co se týče správnosti
      DIČ, [chystá se náprava](https://github.com/h4kuna/ares/issues/30#issuecomment-1719170527)
    - odstraněné vlastnosti `$court`, `$file_number`, `$court_all` jsou dostupné na jiném
      endpointu, `Sources::SERVICE_VR`
    - ~~DIČ je nově bez prefixu `CZ`~~, vlastnost `$tin` z důvodu zpětné kompatibility, prefix nese, ~~nová
      vlastnost `$vat_id` prefix nemá~~, vlastnost jsem odebral, `CZ` opět přidali
    - ~~vlastnost `$created` je podle mě momentálně rozbitá,
      pro [Alzu](https://ares.gov.cz/ekonomicke-subjekty-v-be/rest/ekonomicke-subjekty/27082440) datumVzniku
      vrací `2023-09-04`, v registru ekonomických subjektů
      vrací [2003-08-26](https://ares.gov.cz/ekonomicke-subjekty-v-be/rest/ekonomicke-subjekty-res/27082440) (
      nahlášeno)~~ (opraveno)
    - byl změněn namespace pro `h4kuna\Ares\Basic\Data`, ale [aliases.php](./src/aliases.php) zajistí zpětnou
      kompatibilitu a bude hlásit aby jste si třídu přejmnovali, nicméně stará třída bude fungovat
    - už není možnost do metody `toArray()` předat vlastní pole pro úpravu mapování
    - přidané vlastnosti `$country` a `$country_code`

- h4kuna\Ares\Exceptions\ConnectionException
    - nastavena jako deprecated, zpětně funkční
    - nahrazena h4kuna\Ares\Exceptions\ServerResponseException

### Závěrem

`Ares::loadBasic()` pokud si pohlídáte s jakými vlastnostmi pracujete a nebudou tam ty smazané, tak je to zpětně
kompatibilní. Vstup je zpětně kompatibilní.

`Ares::loadBasicMulti()` je potřeba vzít v potaz že to je zpětně nekompatibilní a nově se vrací Generator. Vstup je
zpětně kompatibilní.

Přidal jsem ADIS, služba která ověří zda se jedná o plátce DPH, identifikovanou osobu nebo neplátce DPH. U plátce vrátí,
zda se jedná o spolehlivého plátce DPH. Ukázka je v [bin/adis](./bin/adis).

### Chování validace pomocí ADIS

Třída [Data](./src/Ares/Core/Data.php) a třída vyplněná pomocí [ADIS](./src/Adis/StatusBusinessSubjects/Subject.php).

|                                     | ARES      | ADIS <br> Data::$adis::$exists | Data::$vat_payer | Data::$tin | Spolehlivý plátce DPH <br> Data::$adis::$reliable |
|-------------------------------------|-----------|--------------------------------|------------------|------------|---------------------------------------------------|
| Plátce DPH                          | vrací DIČ | true                           | true             | vyplněno   | true/false                                        |
| Skupinové DPH / již není plátce DPH | vrátí DIČ | false                          | null *           | null       | null                                              |
| Identifikovaná osoba                | vrátí DIČ | true                           | false            | vyplněno   | null                                              |
| Neplátce                            | null      | null                           | false            | null       | null                                              |

> \* Nelze určit, zda se jedná o Skupinové DPH nebo společnost již není plátce DPH

# v2.0.0

- remove support php < 7.4
- serialized date use RFC3339 instead of ISO8601, because ISO is deprecated by php
- removed method Ares::getData()
- update phpstan
- properties Data::$created and Data::$dissolved use \DateTimeImmutable instead of \DateTime
- PSR-7, PSR-17 and PSR-18 ready
- remove dependency on guzzlehttp/guzzle
- prepared AresFactory::create() for instance Ares class
- Data::$tin is null if value is Skupinove_DPH

# v1.4.0

- remove support php < 7.1
- exceptions move to files -> one class is one file and change namespace

# v1.3.0

- remove support for php 5.5
- add Factory provide new instances Guzzle, Data, DataProvider

# v1.2.0

- interface IData was removed
- change data keys:
    - person -> is_person
    - add house_number
    - add city_post
    - add city_district
- all data keys are visible every time (court, file_number)
- class Data extends Messenger
- class Data suggest property

# v1.1.3

- method Ares::loadData throw IdentificationNumberNotFoundException if find nothing
- rename InNotFoundExceptions -> IdentificationNumberNotFoundException
- rename vat_pay -> vat_payer and interface IData
- try download exists item if is faild try download item with non-exists parameter

# v1.1.2

- method Ares::loadData throw InNotFoundExceptions if find nothing
- attribute vat_pay in Data is bool instanceof empty string and string 1
- attribute created in Data is Datetime instanceof ISO date
- API of Ares class and return values are same
- rewritten internal API for better extension
