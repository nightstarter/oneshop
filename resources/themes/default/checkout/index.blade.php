@extends('theme::layouts.app')

@section('title', __('checkout.title') . ' - ' . config('app.name'))

@section('content')
    <h1 class="h3 mb-4"><i class="bi bi-receipt me-2"></i>{{ __('checkout.complete_order') }}</h1>

    @if ($shippingMethods->isEmpty())
        <div class="alert alert-warning">{{ __('messages.no_shipping_available') }}</div>
    @endif

    <form action="{{ route('checkout.store') }}" method="POST" id="checkoutForm">
        @csrf
        <div class="row g-4">
            <div class="col-lg-7">
                @guest
                    <div class="card shadow-sm mb-3">
                        <div class="card-header fw-bold">{{ __('checkout.contact') }}</div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">{{ __('forms.email') }} <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required>
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                @endguest

                <div class="card shadow-sm mb-3">
                    <div class="card-header fw-bold">{{ __('checkout.shipping_and_payment') }}</div>
                    <div class="card-body row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('checkout.shipping') }} <span class="text-danger">*</span></label>
                            <select name="shipping_method_id" id="shipping_method_id" class="form-select @error('shipping_method_id') is-invalid @enderror" required>
                                @foreach($shippingMethods as $method)
                                    <option
                                        value="{{ $method->id }}"
                                        data-provider="{{ $method->provider_code }}"
                                        data-price-net="{{ (float) $method->price_net }}"
                                        data-price-gross="{{ (float) $method->price_gross }}"
                                        @selected((int) old('shipping_method_id', $selectedShipping?->id) === $method->id)
                                    >
                                        {{ $method->name }} ({{ number_format((float) $method->price_gross, 2, ',', ' ') }} {{ config('shop.currency') }})
                                    </option>
                                @endforeach
                            </select>
                            @error('shipping_method_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ __('checkout.payment') }} <span class="text-danger">*</span></label>
                            <select name="payment_method_id" id="payment_method_id" class="form-select @error('payment_method_id') is-invalid @enderror" required>
                                @foreach($paymentMethods as $method)
                                    <option
                                        value="{{ $method->id }}"
                                        data-type="{{ $method->type }}"
                                        data-price-net="{{ (float) $method->price_net }}"
                                        data-price-gross="{{ (float) $method->price_gross }}"
                                        @selected((int) old('payment_method_id') === $method->id)
                                    >
                                        {{ $method->name }} ({{ number_format((float) $method->price_gross, 2, ',', ' ') }} {{ config('shop.currency') }})
                                    </option>
                                @endforeach
                            </select>
                            @error('payment_method_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12" id="pickup-point-block" style="display:none;">
                            <div class="border rounded p-3 bg-light">
                                <h6 class="mb-3">{{ __('checkout.pickup_point') }}</h6>

                                <input type="hidden" name="pickup_point_id" id="pickup_point_id" value="{{ old('pickup_point_id') }}">
                                <input type="hidden" name="pickup_point_name" id="pickup_point_name" value="{{ old('pickup_point_name') }}">
                                <input type="hidden" name="pickup_point_address" id="pickup_point_address" value="{{ old('pickup_point_address') }}">

                                <button type="button" class="btn btn-outline-primary" id="pickup-point-select-btn">
                                    Vybrat výdejní místo
                                </button>

                                <div id="pickup-point-selected" class="mt-3" style="display:none;">
                                    <div class="small text-muted mb-1">{{ __('checkout.pickup_point') }}</div>
                                    <div class="fw-semibold" id="pickup-point-selected-name"></div>
                                    <div class="text-muted" id="pickup-point-selected-address"></div>
                                </div>

                                @error('pickup_point_id')
                                    <div class="text-danger small mt-2">{{ $message }}</div>
                                @enderror
                                @error('pickup_point_name')
                                    <div class="text-danger small mt-2">{{ $message }}</div>
                                @enderror
                                @error('pickup_point_address')
                                    <div class="text-danger small mt-2">{{ $message }}</div>
                                @enderror

                                <div class="form-text">{{ __('checkout.pickup_required') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-3">
                    <div class="card-header fw-bold">{{ __('checkout.billing_address') }}</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('forms.first_name') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('billing_first_name') is-invalid @enderror" name="billing_first_name" value="{{ old('billing_first_name', $customer?->first_name) }}" required>
                                @error('billing_first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('forms.last_name') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('billing_last_name') is-invalid @enderror" name="billing_last_name" value="{{ old('billing_last_name', $customer?->last_name) }}" required>
                                @error('billing_last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">{{ __('forms.street') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('billing_street') is-invalid @enderror" name="billing_street" value="{{ old('billing_street') }}" required>
                                @error('billing_street') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">{{ __('forms.city') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('billing_city') is-invalid @enderror" name="billing_city" value="{{ old('billing_city') }}" required>
                                @error('billing_city') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ __('forms.zip') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('billing_zip') is-invalid @enderror" name="billing_zip" value="{{ old('billing_zip') }}" required>
                                @error('billing_zip') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">{{ __('forms.country') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('billing_country') is-invalid @enderror" name="billing_country" required>
                                    <option value="CZ" {{ old('billing_country', 'CZ') === 'CZ' ? 'selected' : '' }}>Ceska republika</option>
                                    <option value="SK" {{ old('billing_country') === 'SK' ? 'selected' : '' }}>Slovensko</option>
                                    <option value="DE" {{ old('billing_country') === 'DE' ? 'selected' : '' }}>Nemecko</option>
                                    <option value="AT" {{ old('billing_country') === 'AT' ? 'selected' : '' }}>Rakousko</option>
                                    <option value="PL" {{ old('billing_country') === 'PL' ? 'selected' : '' }}>Polsko</option>
                                </select>
                                @error('billing_country') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="use_billing_as_shipping" id="useBillingAsShipping" value="1" {{ old('use_billing_as_shipping', '1') ? 'checked' : '' }}>
                    <label class="form-check-label" for="useBillingAsShipping">{{ __('checkout.same_as_billing') }}</label>
                </div>

                <div class="card shadow-sm mb-3" id="shippingBlock" style="display:none !important">
                    <div class="card-header fw-bold">{{ __('checkout.shipping_address') }}</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('forms.first_name') }}</label>
                                <input type="text" class="form-control @error('shipping_first_name') is-invalid @enderror" name="shipping_first_name" value="{{ old('shipping_first_name') }}">
                                @error('shipping_first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('forms.last_name') }}</label>
                                <input type="text" class="form-control @error('shipping_last_name') is-invalid @enderror" name="shipping_last_name" value="{{ old('shipping_last_name') }}">
                                @error('shipping_last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">{{ __('forms.street') }}</label>
                                <input type="text" class="form-control @error('shipping_street') is-invalid @enderror" name="shipping_street" value="{{ old('shipping_street') }}">
                                @error('shipping_street') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">{{ __('forms.city') }}</label>
                                <input type="text" class="form-control @error('shipping_city') is-invalid @enderror" name="shipping_city" value="{{ old('shipping_city') }}">
                                @error('shipping_city') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ __('forms.zip') }}</label>
                                <input type="text" class="form-control @error('shipping_zip') is-invalid @enderror" name="shipping_zip" value="{{ old('shipping_zip') }}">
                                @error('shipping_zip') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">{{ __('forms.country') }}</label>
                                <select class="form-select @error('shipping_country') is-invalid @enderror" name="shipping_country">
                                    <option value="CZ" {{ old('shipping_country', 'CZ') === 'CZ' ? 'selected' : '' }}>Ceska republika</option>
                                    <option value="SK" {{ old('shipping_country') === 'SK' ? 'selected' : '' }}>Slovensko</option>
                                    <option value="DE" {{ old('shipping_country') === 'DE' ? 'selected' : '' }}>Nemecko</option>
                                    <option value="AT" {{ old('shipping_country') === 'AT' ? 'selected' : '' }}>Rakousko</option>
                                    <option value="PL" {{ old('shipping_country') === 'PL' ? 'selected' : '' }}>Polsko</option>
                                </select>
                                @error('shipping_country') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __('checkout.order_note') }}</label>
                    <textarea class="form-control" name="note" rows="2">{{ old('note') }}</textarea>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card shadow-sm sticky-top" style="top:80px">
                    <div class="card-header fw-bold">{{ __('checkout.cart_summary') }}</div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @foreach ($items as $item)
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="fw-semibold small">{{ $item->product->name }}</div>
                                        <div class="text-muted small">{{ $item->quantity }} x {{ number_format($item->unit_gross, 2, ',', ' ') }} {{ config('shop.currency') }}</div>
                                    </div>
                                    <span class="fw-bold small">{{ number_format($item->total_gross, 2, ',', ' ') }} {{ config('shop.currency') }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-between text-muted small mb-1">
                            <span>{{ __('checkout.summary_items_net') }}</span>
                            <span id="summary-items-net">{{ number_format($totals['net'], 2, ',', ' ') }} {{ config('shop.currency') }}</span>
                        </div>
                        <div class="d-flex justify-content-between text-muted small mb-1">
                            <span>{{ __('checkout.summary_shipping') }}</span>
                            <span id="summary-shipping-gross">0,00 {{ config('shop.currency') }}</span>
                        </div>
                        <div class="d-flex justify-content-between text-muted small mb-2">
                            <span>{{ __('checkout.summary_payment') }}</span>
                            <span id="summary-payment-gross">0,00 {{ config('shop.currency') }}</span>
                        </div>
                        <div class="d-flex justify-content-between text-muted small mb-2">
                            <span>{{ __('checkout.summary_total_vat') }}</span>
                            <span id="summary-total-vat">{{ number_format($totals['vat'], 2, ',', ' ') }} {{ config('shop.currency') }}</span>
                        </div>
                        <div class="d-flex justify-content-between fw-bold fs-5 mb-3">
                            <span>{{ __('checkout.summary_total') }}</span>
                            <span id="summary-total-gross" class="text-danger">{{ number_format($totals['gross'], 2, ',', ' ') }} {{ config('shop.currency') }}</span>
                        </div>
                        <button type="submit" class="btn btn-success w-100 btn-lg" @disabled($shippingMethods->isEmpty())>
                            <i class="bi bi-check-circle me-1"></i>{{ __('buttons.submit_order') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script src="https://widget.packeta.com/v6/www/js/library.js"></script>
<script>
    const shippingAddressToggle = document.getElementById('useBillingAsShipping');
    const shippingAddressBlock  = document.getElementById('shippingBlock');
    const shippingSelect = document.getElementById('shipping_method_id');
    const paymentSelect = document.getElementById('payment_method_id');

    const pickupBlock = document.getElementById('pickup-point-block');
    const pickupId = document.getElementById('pickup_point_id');
    const pickupName = document.getElementById('pickup_point_name');
    const pickupAddress = document.getElementById('pickup_point_address');
    const pickupSelectButton = document.getElementById('pickup-point-select-btn');
    const pickupSelectedBox = document.getElementById('pickup-point-selected');
    const pickupSelectedName = document.getElementById('pickup-point-selected-name');
    const pickupSelectedAddress = document.getElementById('pickup-point-selected-address');
    const packetaApiKey = @json(config('services.packeta.api_key') ?? config('services.zasilkovna.api_key') ?? env('PACKETA_API_KEY') ?? env('ZASILKOVNA_API_KEY') ?? '');

    const baseNet = {{ json_encode((float) $totals['net']) }};
    const baseGross = {{ json_encode((float) $totals['gross']) }};
    const baseVat = {{ json_encode((float) $totals['vat']) }};

    function formatCurrency(value) {
        return value.toFixed(2).replace('.', ',') + ' {{ config('shop.currency') }}';
    }

    function syncShippingAddress() {
        shippingAddressBlock.style.setProperty('display', shippingAddressToggle.checked ? 'none' : 'block', 'important');
    }

    function selectedShippingProvider() {
        if (!shippingSelect || shippingSelect.selectedIndex < 0) {
            return '';
        }

        return shippingSelect.options[shippingSelect.selectedIndex].dataset.provider || '';
    }

    function renderPickupPointInfo() {
        const hasPickupPoint = pickupName.value.trim() !== '' && pickupAddress.value.trim() !== '';

        pickupSelectedBox.style.display = hasPickupPoint ? 'block' : 'none';
        pickupSelectedName.textContent = pickupName.value;
        pickupSelectedAddress.textContent = pickupAddress.value;
    }

    function syncPickupBlock() {
        const provider = selectedShippingProvider();
        const requiresPickup = provider === 'zasilkovna_box';

        pickupBlock.style.display = requiresPickup ? 'block' : 'none';

        pickupId.required = requiresPickup;
        pickupName.required = requiresPickup;
        pickupAddress.required = requiresPickup;

        if (requiresPickup) {
            renderPickupPointInfo();
        }
    }

    function onPickupPointSelected(point) {
        if (!point) {
            return;
        }

        const street = (point.street || '').trim();
        const city = (point.city || '').trim();
        const address = [street, city].filter(Boolean).join(', ');

        pickupId.value = point.id || '';
        pickupName.value = point.name || '';
        pickupAddress.value = address;

        renderPickupPointInfo();
    }

    function openPickupPointPicker() {
        if (!packetaApiKey) {
            alert('Chybí Packeta API key. Nastavte PACKETA_API_KEY nebo ZASILKOVNA_API_KEY.');
            return;
        }

        if (!window.Packeta || !window.Packeta.Widget || typeof window.Packeta.Widget.pick !== 'function') {
            alert('Packeta widget není dostupný. Zkuste to prosím znovu za chvíli.');
            return;
        }

        window.Packeta.Widget.pick(packetaApiKey, onPickupPointSelected);
    }

    function selectedOptionFloat(selectEl, dataAttr) {
        if (!selectEl || selectEl.selectedIndex < 0) {
            return 0;
        }

        return parseFloat(selectEl.options[selectEl.selectedIndex].dataset[dataAttr] || '0');
    }

    function updateTotals() {
        const shippingNet = selectedOptionFloat(shippingSelect, 'priceNet');
        const shippingGross = selectedOptionFloat(shippingSelect, 'priceGross');
        const paymentNet = selectedOptionFloat(paymentSelect, 'priceNet');
        const paymentGross = selectedOptionFloat(paymentSelect, 'priceGross');

        const totalNet = baseNet + shippingNet + paymentNet;
        const totalGross = baseGross + shippingGross + paymentGross;
        const totalVat = baseVat + (shippingGross - shippingNet) + (paymentGross - paymentNet);

        document.getElementById('summary-shipping-gross').textContent = formatCurrency(shippingGross);
        document.getElementById('summary-payment-gross').textContent = formatCurrency(paymentGross);
        document.getElementById('summary-total-vat').textContent = formatCurrency(totalVat);
        document.getElementById('summary-total-gross').textContent = formatCurrency(totalGross);
    }

    async function reloadPaymentMethods() {
        if (!shippingSelect) {
            return;
        }

        const shippingMethodId = shippingSelect.value;

        const response = await fetch(`{{ route('checkout.payment-methods') }}?shipping_method_id=${encodeURIComponent(shippingMethodId)}`);
        const paymentMethods = await response.json();

        paymentSelect.innerHTML = '';

        for (const method of paymentMethods) {
            const option = document.createElement('option');
            option.value = method.id;
            option.dataset.type = method.type;
            option.dataset.priceNet = method.price_net;
            option.dataset.priceGross = method.price_gross;
            option.textContent = `${method.name} (${Number(method.price_gross).toFixed(2).replace('.', ',')} {{ config('shop.currency') }})`;
            paymentSelect.appendChild(option);
        }

        updateTotals();
    }

    shippingAddressToggle?.addEventListener('change', syncShippingAddress);
    shippingSelect?.addEventListener('change', async () => {
        syncPickupBlock();
        await reloadPaymentMethods();
        updateTotals();
    });
    paymentSelect?.addEventListener('change', updateTotals);
    pickupSelectButton?.addEventListener('click', openPickupPointPicker);

    syncShippingAddress();
    syncPickupBlock();
    renderPickupPointInfo();
    updateTotals();
</script>
@endpush