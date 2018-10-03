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
