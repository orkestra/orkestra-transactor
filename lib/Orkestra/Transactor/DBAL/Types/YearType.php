<?php

namespace Orkestra\Transactor\DBAL\Types;

use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;

use Orkestra\Transactor\Type\Year;

/**
 * Year Type
 *
 * Provides Doctrine DBAL support for the Year data type
 */
class YearType extends StringType
{
    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        return $value->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        try {
            $val = new Year($value);
        }
        catch (\InvalidArgumentException $e) {
            throw ConversionException::conversionFailed($value, $this->getName());
        }

        return $val;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orkestra.year';
    }
}
