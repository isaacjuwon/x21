<?php

namespace App\Managers;

use App\Integrations\Contracts\Providers\AccountProvider;
use App\Integrations\Contracts\Providers\PaymentProvider;
use App\Integrations\Contracts\Providers\VtuProvider;
use App\Integrations\Epins\EpinsConnector;
use App\Integrations\Epins\EpinsProvider;
use App\Integrations\Monnify\MonnifyProvider;
use App\Integrations\Paystack\PaystackConnector;
use App\Integrations\Paystack\PaystackProvider;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\MultipleInstanceManager;
use LogicException;

class ApiManager extends MultipleInstanceManager
{
    use Concerns\InteractsWithFakeApi;

    /**
     * The key name of the "driver" equivalent configuration option.
     *
     * @var string
     */
    protected $driverKey = 'driver';

    /**
     * Get a payment provider instance by name.
     *
     * @throws LogicException
     */
    public function paymentProvider(?string $name = null): PaymentProvider
    {
        return tap($this->instance($name), function ($instance) {
            if (! $instance instanceof PaymentProvider) {
                throw new LogicException('Provider ['.get_class($instance).'] does not support payments.');
            }
        });
    }

    /**
     * Get a fakeable payment provider instance.
     */
    public function fakeablePaymentProvider(?string $name = null): PaymentProvider
    {
        $provider = $this->paymentProvider($name);

        return $this->paymentsAreFaked()
            ? (clone $provider)->useGateway($this->fakePaymentGateway())
            : $provider;
    }

    /**
     * Get a VTU provider instance by name.
     *
     * @throws LogicException
     */
    public function vtuProvider(?string $name = null): VtuProvider
    {
        return tap($this->instance($name), function ($instance) {
            if (! $instance instanceof VtuProvider) {
                throw new LogicException('Provider ['.get_class($instance).'] does not support VTU.');
            }
        });
    }

    /**
     * Get a fakeable VTU provider instance.
     */
    public function fakeableVtuProvider(?string $name = null): VtuProvider
    {
        $provider = $this->vtuProvider($name);

        return $this->vtuAreFaked()
            ? (clone $provider)->useGateway($this->fakeVtuGateway())
            : $provider;
    }

    /**
     * Get an account provider instance by name.
     *
     * @throws LogicException
     */
    public function accountProvider(?string $name = null): AccountProvider
    {
        return tap($this->instance($name), function ($instance) {
            if (! $instance instanceof AccountProvider) {
                throw new LogicException('Provider ['.get_class($instance).'] does not support accounts.');
            }
        });
    }

    /**
     * Get a fakeable account provider instance.
     */
    public function fakeableAccountProvider(?string $name = null): AccountProvider
    {
        $provider = $this->accountProvider($name);

        return $this->accountsAreFaked()
            ? (clone $provider)->useGateway($this->fakeAccountGateway())
            : $provider;
    }

    /**
     * Create a Paystack powered instance.
     */
    public function createPaystackDriver(array $config): PaystackProvider
    {
        return new PaystackProvider(
            $config,
            $this->app->make(Dispatcher::class),
            $this->app->make(PaystackConnector::class)
        );
    }

    /**
     * Create an Epins powered instance.
     */
    public function createEpinsDriver(array $config): EpinsProvider
    {
        return new EpinsProvider(
            $this->app->make(EpinsConnector::class)
        );
    }

    /**
     * Create a Monnify powered instance.
     */
    public function createMonnifyDriver(array $config): MonnifyProvider
    {
        return new MonnifyProvider(
            $config,
            $this->app->make(Dispatcher::class)
        );
    }

    /**
     * Get the default instance name.
     *
     * @return string
     */
    public function getDefaultInstance(): string
    {
        return $this->app['config']['api.default'];
    }

    /**
     * Set the default instance name.
     *
     * @param  string  $name
     * @return void
     */
    public function setDefaultInstance($name): void
    {
        $this->app['config']['api.default'] = $name;
    }

    /**
     * Get the instance specific configuration.
     *
     * @param  string  $name
     * @return array
     */
    public function getInstanceConfig($name): array
    {
        $config = $this->app['config']->get(
            'api.providers.'.$name, ['driver' => $name],
        );

        $config['name'] = $name;

        return $config;
    }

    /**
     * Fake the payment gateway.
     */
    protected function fakePaymentGateway(): string
    {
        return 'fake';
    }

    /**
     * Fake the VTU gateway.
     */
    protected function fakeVtuGateway(): string
    {
        return 'fake';
    }

    /**
     * Fake the account gateway.
     */
    protected function fakeAccountGateway(): string
    {
        return 'fake';
    }
}
