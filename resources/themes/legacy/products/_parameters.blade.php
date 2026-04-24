<section class="legacy-section-box p-3 p-lg-4">
    <h2 class="h3 mb-3">Technicke parametry</h2>
    <div class="table-responsive">
        <table class="table table-borderless align-middle mb-0" style="font-size: 1.2rem;">
            <tbody>
                @foreach ($parameterValues as $attributeValue)
                    <tr style="border-bottom:1px solid #d8dde2;">
                        <th scope="row" style="width:34%; font-weight:600; color:#4f5962; background:#f2f5f7;">
                            {{ $attributeValue->attribute->name }}:
                        </th>
                        <td>{{ $attributeValue->display_value }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
