<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * Unified cart service.
 *
 * Guest:  cart stored in session under key 'cart' as [product_id => quantity].
 * User:   cart stored in DB (carts + cart_items tables).
 *
 * On login MergeGuestCartOnLogin listener calls mergeGuestCartIntoUserCart().
 */
class CartService
{
    private const SESSION_KEY = 'cart';

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    public function add(int $productId, int $quantity = 1): void
    {
        $quantity = max(1, $quantity);

        if (Auth::check()) {
            $this->dbAdd(Auth::user(), $productId, $quantity);
        } else {
            $this->sessionAdd($productId, $quantity);
        }
    }

    public function update(int $productId, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->remove($productId);
            return;
        }

        if (Auth::check()) {
            $this->dbUpdate(Auth::user(), $productId, $quantity);
        } else {
            $this->sessionUpdate($productId, $quantity);
        }
    }

    public function remove(int $productId): void
    {
        if (Auth::check()) {
            $this->dbRemove(Auth::user(), $productId);
        } else {
            $this->sessionRemove($productId);
        }
    }

    public function clear(): void
    {
        if (Auth::check()) {
            $cart = $this->findOrCreateUserCart(Auth::user());
            $cart->items()->delete();
        } else {
            Session::forget(self::SESSION_KEY);
        }
    }

    /**
     * Returns a Collection of stdClass objects with:
     *   product, quantity, unit_gross, total_gross, unit_net, total_net, vat_rate
     */
    public function items(): Collection
    {
        if (Auth::check()) {
            return $this->dbItems(Auth::user());
        }

        return $this->sessionItems();
    }

    /** Total number of product lines in cart. */
    public function count(): int
    {
        return $this->items()->count();
    }

    /** Total quantity across all lines. */
    public function totalQuantity(): int
    {
        return $this->items()->sum('quantity');
    }

    /** Aggregated price totals [net, vat, gross]. */
    public function totals(): array
    {
        $items = $this->items();

        return [
            'net'   => round($items->sum('total_net'), 2),
            'vat'   => round($items->sum('total_vat'), 2),
            'gross' => round($items->sum('total_gross'), 2),
        ];
    }

    /**
     * Called after login: copy session cart into user's DB cart, then clear session.
     */
    public function mergeGuestCartIntoUserCart(User $user): void
    {
        $sessionCart = Session::get(self::SESSION_KEY, []);

        if (empty($sessionCart)) {
            return;
        }

        $cart = $this->findOrCreateUserCart($user);

        foreach ($sessionCart as $productId => $qty) {
            $existing = $cart->items()->where('product_id', $productId)->first();

            if ($existing) {
                $existing->update(['quantity' => $existing->quantity + $qty]);
            } else {
                $cart->items()->create(['product_id' => $productId, 'quantity' => $qty]);
            }
        }

        Session::forget(self::SESSION_KEY);
    }

    // -------------------------------------------------------------------------
    // Session helpers (guest)
    // -------------------------------------------------------------------------

    private function sessionAdd(int $productId, int $quantity): void
    {
        $cart = Session::get(self::SESSION_KEY, []);
        $cart[$productId] = ($cart[$productId] ?? 0) + $quantity;
        Session::put(self::SESSION_KEY, $cart);
    }

    private function sessionUpdate(int $productId, int $quantity): void
    {
        $cart = Session::get(self::SESSION_KEY, []);
        $cart[$productId] = $quantity;
        Session::put(self::SESSION_KEY, $cart);
    }

    private function sessionRemove(int $productId): void
    {
        $cart = Session::get(self::SESSION_KEY, []);
        unset($cart[$productId]);
        Session::put(self::SESSION_KEY, $cart);
    }

    private function sessionItems(): Collection
    {
        $cart = Session::get(self::SESSION_KEY, []);

        if (empty($cart)) {
            return collect();
        }

        $products = Product::with('categories')
            ->whereIn('id', array_keys($cart))
            ->activeForSale()
            ->get()
            ->keyBy('id');

        $calculator = app(PriceCalculator::class);

        return collect($cart)
            ->filter(fn ($qty, $id) => $products->has($id))
            ->map(function (int $qty, int $id) use ($products, $calculator) {
                $product = $products[$id];
                $price = $calculator->calculate($product, null, $qty);
                return $this->makeItem($product, $qty, $price);
            })
            ->values();
    }

    // -------------------------------------------------------------------------
    // DB helpers (authenticated user)
    // -------------------------------------------------------------------------

    private function findOrCreateUserCart(User $user): Cart
    {
        return Cart::firstOrCreate(['user_id' => $user->id]);
    }

    private function dbAdd(User $user, int $productId, int $quantity): void
    {
        $cart = $this->findOrCreateUserCart($user);
        $item = $cart->items()->where('product_id', $productId)->first();

        if ($item) {
            $item->increment('quantity', $quantity);
        } else {
            $cart->items()->create(['product_id' => $productId, 'quantity' => $quantity]);
        }
    }

    private function dbUpdate(User $user, int $productId, int $quantity): void
    {
        $cart = $this->findOrCreateUserCart($user);
        $cart->items()->where('product_id', $productId)->update(['quantity' => $quantity]);
    }

    private function dbRemove(User $user, int $productId): void
    {
        $cart = $this->findOrCreateUserCart($user);
        $cart->items()->where('product_id', $productId)->delete();
    }

    private function dbItems(User $user): Collection
    {
        $cart = $this->findOrCreateUserCart($user);
        $customer = $user->customer;
        $calculator = app(PriceCalculator::class);

        return $cart->items()
            ->with('product.categories')
            ->get()
            ->filter(fn (CartItem $item) => $item->product?->isActiveForSale())
            ->map(function (CartItem $item) use ($customer, $calculator) {
                $price = $calculator->calculate($item->product, $customer, $item->quantity);
                return $this->makeItem($item->product, $item->quantity, $price);
            })
            ->values();
    }

    // -------------------------------------------------------------------------
    // Shared factory
    // -------------------------------------------------------------------------

    private function makeItem(Product $product, int $quantity, array $price): object
    {
        return (object) [
            'product'     => $product,
            'quantity'    => $quantity,
            'unit_net'    => $price['unit_net'],
            'unit_gross'  => $price['unit_gross'],
            'total_net'   => $price['total_net'],
            'total_vat'   => $price['total_vat'],
            'total_gross' => $price['total_gross'],
            'vat_rate'    => $price['vat_rate'],
        ];
    }
}
