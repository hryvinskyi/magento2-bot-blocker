<?php
/**
 * Copyright (c) 2024. MageCloud.  All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

namespace Hryvinskyi\BotBlocker\Model\Validator;

class BotBlockerValidator
{
    /**
     * @var ValidatorInterface[]
     */
    private array $validators;

    public function __construct(array $validators)
    {
        foreach ($validators as $item) {
            if (isset($item['object']) === false) {
                throw new \InvalidArgumentException('The item object empty');
            }

            if (!$item['object'] instanceof ValidatorInterface) {
                throw new \InvalidArgumentException('The item should be implemented of ' . ValidatorInterface::class);
            }
        }

        usort($validators, static function ($a, $b) {
            if (isset($a['sortOrder']) === false || isset($b['sortOrder']) === false) {
                return true;
            }

            return $a['sortOrder'] <=> $b['sortOrder'];
        });

        $this->validators = [];
        foreach ($validators as $item) {
            $this->validators[] = $item['object'];
        }
    }

    /**
     * Validate IP address
     * return false if IP address is not in the blacklist or true otherwise
     *
     * @param string $ipAddress
     * @return bool
     */
    public function validate(string $ipAddress): bool
    {
        foreach ($this->validators as $validator) {
            try {
                if ($validator->validate($ipAddress) === true) {
                    return true;
                }
            } catch (SkipNextValidationsException $e) {
                // Break the loop if for example the IP address is whitelisted
                break;
            }
        }

        return false;
    }
}