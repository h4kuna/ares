<?php declare(strict_types = 1);

use h4kuna\Ares\Exception\IdentificationNumberNotFoundException;
use h4kuna\Ares\Exception\ResultException;
use h4kuna\Ares\Exception\ServerResponseException;

class_alias(IdentificationNumberNotFoundException::class, 'h4kuna\Ares\Exceptions\IdentificationNumberNotFoundException');
class_alias(ResultException::class, 'h4kuna\Ares\Exceptions\ResultException');
class_alias(ServerResponseException::class, 'h4kuna\Ares\Exceptions\ServerResponseException');
