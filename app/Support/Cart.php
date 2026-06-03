<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Cart
{
    protected static function key(): string
    {
        return 'cart.items';
    }

    protected static function items(): array
    {
        return session()->get(static::key(), []);
    }

    protected static function store(array $items): void
    {
        session()->put(static::key(), $items);
    }

    public static function add($id, $name, $price, $quantity = 1, array $attributes = []): object
    {
        $items = static::items();

        $items[(string) $id] = [
            'id' => $id,
            'name' => $name,
            'price' => (float) $price,
            'quantity' => (int) $quantity,
            'attributes' => $attributes,
            'model' => null,
        ];

        static::store($items);

        return new class ((string) $id) {
            public function __construct(public string $id)
            {
            }

            public function associate(string $modelClass): self
            {
                $items = session()->get('cart.items', []);

                if (isset($items[$this->id])) {
                    $items[$this->id]['model'] = $modelClass;
                    session()->put('cart.items', $items);
                }

                return $this;
            }
        };
    }

    public static function get($id): mixed
    {
        $item = static::items()[(string) $id] ?? null;

        if (! $item) {
            return null;
        }

        return static::hydrateItem($item);
    }

    public static function update($id, array $data): void
    {
        $items = static::items();
        $id = (string) $id;

        if (! isset($items[$id])) {
            return;
        }

        $quantity = $data['quantity']['value'] ?? $items[$id]['quantity'];

        $items[$id]['quantity'] = max(1, (int) $quantity);
        static::store($items);
    }

    public static function remove($id): void
    {
        $items = static::items();
        unset($items[(string) $id]);
        static::store($items);
    }

    public static function clear(): void
    {
        session()->forget(static::key());
    }

    public static function getContent(): Collection
    {
        return collect(static::items())->map(fn (array $item) => static::hydrateItem($item));
    }

    public static function getSubTotal(): float
    {
        return (float) static::getContent()->sum(fn ($item) => $item->price * $item->quantity);
    }

    public static function getTotalQuantity(): int
    {
        return (int) static::getContent()->sum(fn ($item) => (int) $item->quantity);
    }

    public static function isEmpty(): bool
    {
        return static::getContent()->isEmpty();
    }

    public static function getTotal(): float
    {
        $subtotal = static::getSubTotal();
        $discount = (float) session()->get('discount', 0);

        return max(0, $subtotal - $discount);
    }

    protected static function hydrateItem(array $item): object
    {
        $modelClass = $item['model'] ?? null;
        $model = $modelClass && class_exists($modelClass) ? $modelClass::find($item['id']) : null;

        return (object) [
            'id' => $item['id'],
            'name' => $item['name'],
            'price' => (float) $item['price'],
            'quantity' => (int) $item['quantity'],
            'attributes' => collect($item['attributes']),
            'model' => $model,
        ];
    }
}