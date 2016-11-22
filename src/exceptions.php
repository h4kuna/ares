<?php

namespace h4kuna\Ares;

abstract class AresException extends \Exception {}

class IdentificationNumberNotFoundException extends AresException {}

class DataOffsetDoesNotExists extends AresException {}