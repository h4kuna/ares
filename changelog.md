v1.1.3
======
- rename InNotFoundExceptions -> IdentificationNumberNotFoundException
- renane vat_pay -> vat_payer and interface IData
- try download exists item if is faild try download item with non-exists parameter

v1.1.2
======
- attribute vat_pay in Data is bool instanceof empty string and string 1
- attribute created in Data is Datetime instanceof ISO date
- API of Ares class and return values are same
- rewritten internal API for better extension
