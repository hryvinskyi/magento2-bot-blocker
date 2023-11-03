<?php
/**
 * Copyright (c) 2023. MageCloud.  All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

namespace Hryvinskyi\BotBlocker\Model;

class IpStorage implements IpStorageInterface
{
    public function pack($ip)
    {
        // already compatible with MySQLs function INET6_NTOA() for unpacking
        return inet_pton($ip);
    }

    public function unpack($packedIp)
    {
        $return = $this->inet6Ntop($packedIp);
        if ($return === false) {
            throw new \Exception($packedIp. ' IP not a valid packed IP');
        }
        return $return;
    }

    /**
     * Convert a MySQL binary v4 (4-byte) or v6 (16-byte) IP address to a printable string.
     *
     * @param string $ip A binary string containing an IP address, as returned from MySQL's INET6_ATON function
     * @return string|false Empty if not valid.
     */
    private function inet6Ntop(string $ip) {
        $l = strlen($ip);
        if ($l == 4 || $l == 16) {
            return inet_ntop(pack('A' . $l, $ip));
        }
        return false;
    }
}
