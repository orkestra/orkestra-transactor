<?php

namespace Orkestra\Transactor\DBAL\Types;

use Doctrine\DBAL\Types\StringType,
    Doctrine\DBAL\Platforms\AbstractPlatform,
    Doctrine\DBAL\Types\ConversionException;
    
use Orkestra\Common\Type\Month;

/**
 * Year Type
 *
 * Provides Doctrine DBAL support for the Month data type
 */
class MonthType extends StringType
{
    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }
        
        return $value->getShortMonth();
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
            $val = new Month($value);
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
        return 'orkestra.month';
    }
}