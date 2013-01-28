<?php

namespace h4kuna\Ares;

/**
 * Description of IRequest
 *
 * @author Milan Matějček
 */
interface IRequest {

    /**
     * @param string $in Identification Number
     * @return Data
     */
    public function loadData($in = NULL);

    /**
     * clean last request
     * @void
     */
    public function clean();
}

