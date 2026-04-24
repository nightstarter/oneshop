<div class="mb-4">
    <h2 class="h5 mb-3">{{ __('shop.parameters') }}</h2>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <tbody>
                @foreach ($parameterValues as $attributeValue)
                    <tr>
                        <th scope="row" class="text-muted fw-semibold" style="width: 40%;">{{ $attributeValue->attribute->name }}</th>
                        <td>{{ $attributeValue->display_value }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>