<?php

namespace App\Services\Webhooks;

use App\Exceptions\Service\Webhook\WebhookEndpointException;

class WebhookEndpointGuard
{
    /**
     * Works out where a delivery to the given endpoint would really go and refuses
     * anything that is not a public address, unless private endpoints are allowed.
     *
     * The address that was checked is returned so that the request can be pinned to it,
     * otherwise a hostname could answer with something else the second time it is asked.
     *
     * @return array{host: string, port: int, address: string}|null
     *
     * @throws WebhookEndpointException
     */
    public function resolve(string $endpoint): ?array
    {
        $parts = parse_url($endpoint);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = trim((string) ($parts['host'] ?? ''), '[]');

        if (! in_array($scheme, ['http', 'https'], true) || $host === '') {
            throw new WebhookEndpointException(trans('admin/webhooks.errors.invalid_endpoint'));
        }

        if (config('webhooks.allow_private_endpoints')) {
            return null;
        }

        $addresses = filter_var($host, FILTER_VALIDATE_IP) !== false ? [$host] : $this->lookup($host);
        if ($addresses === []) {
            throw new WebhookEndpointException(trans('admin/webhooks.errors.unresolvable', ['host' => $host]));
        }

        foreach ($addresses as $address) {
            if (! $this->isAllowed($address)) {
                throw new WebhookEndpointException(trans('admin/webhooks.errors.private_endpoint', ['host' => $host]));
            }
        }

        return [
            'host' => $host,
            'port' => (int) ($parts['port'] ?? ($scheme === 'https' ? 443 : 80)),
            'address' => $addresses[0],
        ];
    }

    /**
     * @return string[]
     */
    protected function lookup(string $host): array
    {
        return array_values(array_unique(array_merge(
            gethostbynamel($host) ?: [],
            array_column(@dns_get_record($host, DNS_AAAA) ?: [], 'ipv6'),
        )));
    }

    protected function isAllowed(string $address): bool
    {
        $packed = @inet_pton($address);
        if ($packed === false) {
            return false;
        }

        if (strlen($packed) === 16 && str_starts_with($packed, str_repeat("\0", 10)."\xff\xff")) {
            $address = (string) inet_ntop(substr($packed, 12));
        }

        return filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_NO_RES_RANGE | FILTER_FLAG_NO_PRIV_RANGE) !== false;
    }
}
