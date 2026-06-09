<?php

namespace App\Payments;

use App\Payments\Contracts\PaymentGateway;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

/**
 * Resolves and lists payment gateways from config/payments.php.
 *
 * Resolved instances are memoised. Enablement is delegated to each driver's
 * isEnabled() (config/env driven), so the registry can list everything while
 * only the switched-on gateways are offered to users.
 */
class PaymentManager
{
    /** @var array<string,PaymentGateway> */
    protected array $resolved = [];

    public function __construct(protected Container $container) {}

    /** Resolve a single gateway by key. */
    public function gateway(string $key): PaymentGateway
    {
        if (isset($this->resolved[$key])) {
            return $this->resolved[$key];
        }

        $class = config("payments.gateways.{$key}");

        if (! $class) {
            throw new InvalidArgumentException("Unknown payment gateway [{$key}].");
        }

        $instance = $this->container->make($class);

        if (! $instance instanceof PaymentGateway) {
            throw new InvalidArgumentException("[{$class}] must implement PaymentGateway.");
        }

        return $this->resolved[$key] = $instance;
    }

    /** @return array<string,PaymentGateway> All registered gateways, keyed. */
    public function all(): array
    {
        $out = [];
        foreach (array_keys((array) config('payments.gateways', [])) as $key) {
            $out[$key] = $this->gateway($key);
        }

        return $out;
    }

    /** @return array<string,PaymentGateway> Only the enabled gateways. */
    public function enabled(): array
    {
        return array_filter($this->all(), fn (PaymentGateway $g) => $g->isEnabled());
    }

    /** @return array<string,PaymentGateway> Enabled gateways an admin confirms by hand (cash, UPI). */
    public function enabledManual(): array
    {
        return array_filter($this->enabled(), fn (PaymentGateway $g) => $g->isManual());
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, (array) config('payments.gateways', []));
    }
}
