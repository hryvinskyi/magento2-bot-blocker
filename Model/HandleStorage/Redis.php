<?php
/**
 * Copyright (c) 2023. MageCloud.  All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

namespace Hryvinskyi\BotBlocker\Model\HandleStorage;

use Hryvinskyi\BotBlocker\Model\HandlerInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;

class Redis implements HandlerInterface
{
    private CacheInterface $cache;
    private SerializerInterface $serializer;

    public function __construct(
        CacheInterface $cache,
        SerializerInterface $serializer
    ) {
        $this->cache = $cache;
        $this->serializer = $serializer;
    }

    /**
     * @inheritDoc
     */
    public function execute(string $ip, int $threshold, int $timeframe): int
    {
        $key = 'ip_' . md5($ip);
        $data = $this->cache->load($key);

        if ($data) {
            $data = $this->serializer->unserialize($data);
        } else {
            $data = [
                'count' => 0,
                'first_request_time' => time(),
            ];
        }

        $data['count']++;

        $elapsedTime = time() - $data['first_request_time'];
        if ($elapsedTime > $timeframe) {
            $data['count'] = 1;
            $data['first_request_time'] = time();
        }

        $this->cache->save($this->serializer->serialize($data), $key, ['ip'], $timeframe);

        return (int)$data['count'];
    }
}