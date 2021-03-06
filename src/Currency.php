<?php

namespace SSD\Currency;

use BadMethodCallException;
use SSD\Currency\Providers\BaseProvider;

/**
 * Class Currency
 *
 * @package SSD\Currency
 *
 * @method string get()
 * @method void set(string $currency)
 * @method bool is(string $currency)
 */
class Currency
{
    /**
     * @var \SSD\Currency\Providers\BaseProvider
     */
    private $provider;

    /**
     * Currency constructor.
     *
     * @param  \SSD\Currency\Providers\BaseProvider $provider
     */
    public function __construct(BaseProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Convert value to decimal.
     *
     * @param  string|array $values
     * @param  string|null $currency
     * @param  int $decimal_points
     * @return string
     */
    public function decimal($values, string $currency = null, int $decimal_points = 2): string
    {
        [$className, $value] = $this->classAndValue($values, $currency);

        return (new $className)->decimal($value, $decimal_points);
    }

    /**
     * Convert value to integer.
     *
     * @param  string|array $values
     * @param  string|null $currency
     * @return int
     */
    public function integer($values, string $currency = null): int
    {
        [$className, $value] = $this->classAndValue($values, $currency, false);

        return (new $className)->integer($value);
    }

    /**
     * Display value as decimal with currency symbol.
     *
     * @param  string|array $values
     * @param  string|null $currency
     * @param  int|null $decimal_points
     * @return string
     */
    public function withSymbol($values, string $currency = null, int $decimal_points = null): string
    {
        [$className, $value] = $this->classAndValue($values, $currency);

        return (new $className)->withSymbol($value, $decimal_points);
    }

    /**
     * Display value as decimal with currency label.
     *
     * @param  string|array $values
     * @param  string|null $currency
     * @param  int|null $decimal_points
     * @return string
     */
    public function withCode($values, string $currency = null, int $decimal_points = null): string
    {
        [$className, $value] = $this->classAndValue($values, $currency);

        return (new $className)->withCode($value, $decimal_points);
    }

    /**
     * Display value as decimal with currency symbol and label.
     *
     * @param  string|array $values
     * @param  string|null $currency
     * @param  int|null $decimal_points
     * @return string
     */
    public function withSymbolAndCode($values, string $currency = null, int $decimal_points = null): string
    {
        [$className, $value] = $this->classAndValue($values, $currency);

        return (new $className)->withSymbolAndCode($value, $decimal_points);
    }

    /**
     * Get class and value.
     *
     * @param  string|array $values
     * @param  string|null $currency
     * @param  bool $asFloat
     * @return array
     */
    private function classAndValue($values, string $currency = null, bool $asFloat = true): array
    {
        $className = $this->getClass($currency);

        $value = is_array($values) ? $this->getValue($values, $currency) : $values;

        if ($asFloat && $this->provider->config->value_as_integer) {
            $value = $value / 100;
        }

        return [$className, $value];
    }

    /**
     * Get class name.
     *
     * @param  string|null $currency
     * @return string
     */
    private function getClass(string $currency = null): string
    {
        return $this->provider->config->getClass(
            strtoupper($currency ?? $this->provider->get())
        );
    }

    /**
     * Get value.
     *
     * @param  array $values
     * @param  string|null $currency
     * @return string
     */
    private function getValue(array $values, string $currency = null): string
    {
        return $values[strtoupper($currency ?? $this->provider->get())];
    }

    /**
     * Get a an array of available currencies.
     *
     * @return array
     */
    public function options(): array
    {
        $currencies = $this->provider->config->get('currencies');

        $options = [];

        foreach ($currencies as $key => $currency) {

            $options[] = new Option($key, new $currency);

        }

        return $options;
    }

    /**
     * Selected attribute for currency select.
     *
     * @param  string $currency
     * @return string
     */
    public function selected(string $currency): string
    {
        if (!$this->provider->is($currency)) {
            return '';
        }

        return ' selected="selected"';
    }

    /**
     * Override call to the methods on the provider.
     *
     * @param  string $name
     * @param  array $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        if (!in_array($name, ['get', 'set', 'is'])) {
            throw new BadMethodCallException('Method {$name} does not exist in '.__FILE__);
        }

        return call_user_func_array([$this->provider, $name], $arguments);
    }
}
