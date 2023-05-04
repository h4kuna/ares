v2.0.0
======
- remove support php < 7.4
- serialized date use RFC3339 instead of ISO8601, because ISO is deprecated by php
- removed method Ares::getData()
- update phpstan
- properties Data::$created and Data::$dissolved use \DateTimeImmutable instead of \DateTime
- PSR-7, PSR-17 and PSR-18 ready
- remove dependency on guzzlehttp/guzzle
- prepared AresFactory::create() for instance Ares class
- Data::$tin is null if value is Skupinove_DPH

v1.4.0
======
- remove support php < 7.1
- exceptions move to files -> one class is one file and change namespace


v1.3.0
======
- remove support for php 5.5
- add Factory provide new instances Guzzle, Data, DataProvider

v1.2.0
======
- interface IData was removed
- change data keys:
   - person -> is_person 
   - add house_number
   - add city_post
   - add city_district
- all data keys are visible every time (court, file_number)
- class Data extends Messenger
- class Data suggest property

v1.1.3
======
- method Ares::loadData throw IdentificationNumberNotFoundException if find nothing
- rename InNotFoundExceptions -> IdentificationNumberNotFoundException
- rename vat_pay -> vat_payer and interface IData
- try download exists item if is faild try download item with non-exists parameter

v1.1.2
======
- method Ares::loadData throw InNotFoundExceptions if find nothing
- attribute vat_pay in Data is bool instanceof empty string and string 1
- attribute created in Data is Datetime instanceof ISO date
- API of Ares class and return values are same
- rewritten internal API for better extension
