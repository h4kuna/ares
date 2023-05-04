<?php declare(strict_types = 1);

// keep up to v3.0
namespace h4kuna\Ares\Data {
    if (false) {
        /** @deprecated use h4kuna\Ares\Basic\Data */
        class Data
        {
        }

        /** @deprecated use h4kuna\Ares\Basic\SubjectFlag */
        class SubjectFlag
        {
        }
    }
}

namespace {
    use h4kuna\Ares;
    class_alias(Ares\Basic\Data::class, 'h4kuna\Ares\Data\Data');
    class_alias(Ares\Basic\SubjectFlag::class, 'h4kuna\Ares\Data\SubjectFlag');
}
// --
