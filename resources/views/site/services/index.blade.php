@extends('layouts.customer')

@section('content')
<section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="mb-8">
        <p class="text-sm font-semibold uppercase tracking-wider text-sky-600">Service Marketplace</p>
        <h1 class="mt-2 customer-section-title">Find Services That Match Your Schedule</h1>
        <p class="mt-3 customer-muted">Filter by category, budget, rating, and availability to get the best match.</p>
    </div>

    <div class="grid gap-6 lg:grid-cols-[18rem,1fr]">
        <x-filter-sidebar
            :categories="$categories"
            :locations="$locations"
            :filters="$filters"
            :sort-options="$sortOptions"
            form-id="serviceFilters"
        />

        <div>
            <div class="customer-surface overflow-hidden p-4 sm:p-5">
                <div class="overflow-x-auto">
                    <table id="servicesTable" class="display w-full text-sm">
                        <thead>
                            <tr>
                                <th>Service</th>
                                <th>Category</th>
                                <th>Provider</th>
                                <th>Location</th>
                                <th>Price</th>
                                <th>Duration</th>
                                <th>Rating</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    $(function () {
        const escapeHtml = function (value) {
            return $('<div>').text(value ?? '').html();
        };

        const filtersForm = $('#serviceFilters');
        const table = $('#servicesTable').DataTable({
            ajax: {
                url: '{{ $servicesDataUrl }}',
                data: function (requestData) {
                    const formData = filtersForm.serializeArray();
                    formData.forEach(function (field) {
                        requestData[field.name] = field.value;
                    });
                },
                dataSrc: 'data'
            },
            processing: true,
            paging: true,
            pagingType: 'simple_numbers',
            lengthChange: true,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            pageLength: 10,
            info: true,
            language: {
                emptyTable: 'No matching services found',
                paginate: {
                    previous: 'Prev',
                    next: 'Next'
                }
            },
            columns: [
                {
                    data: null,
                    render: function (data) {
                        const image = data.image ?? '';
                        const name = escapeHtml(data.name);
                        const description = escapeHtml(data.description);
                        return `<div class="flex items-center gap-3 min-w-[16rem]">
                            <img src="${image}" alt="${name}" class="h-14 w-14 rounded-xl object-cover border border-sky-100">
                            <div>
                                <p class="font-semibold text-sky-900">${name}</p>
                                <p class="text-xs text-sky-600">${description}</p>
                            </div>
                        </div>`;
                    }
                },
                {
                    data: 'category',
                    render: function (data) {
                        return escapeHtml(data);
                    }
                },
                {
                    data: 'provider',
                    render: function (data) {
                        return escapeHtml(data);
                    }
                },
                {
                    data: 'location',
                    render: function (data) {
                        return escapeHtml(data);
                    }
                },
                {
                    data: 'price',
                    render: function (data, type, row) {
                        if (type === 'sort' || type === 'type') {
                            return row.price_value ?? 0;
                        }
                        return `₹${data}`;
                    }
                },
                {
                    data: 'duration',
                    render: function (data, type) {
                        if (type === 'sort' || type === 'type') {
                            return data;
                        }
                        return `${data} min`;
                    }
                },
                {
                    data: null,
                    render: function (data, type) {
                        if (type === 'sort' || type === 'type') {
                            return data.rating ?? 0;
                        }
                        return `<span class="font-semibold text-sky-900">★ ${data.rating_label}</span> <span class="text-xs text-sky-600">(${data.reviews_count})</span>`;
                    }
                },
                {
                    data: 'details_url',
                    orderable: false,
                    searchable: false,
                    render: function (url) {
                        return `<a href="${url}" class="inline-flex rounded-lg bg-sky-600 px-3 py-2 text-xs font-semibold text-white hover:bg-sky-500">View</a>`;
                    }
                }
            ]
        });

        filtersForm.on('submit', function (event) {
            event.preventDefault();
            table.ajax.reload();
        });

        filtersForm.find('select,input').on('change', function () {
            table.ajax.reload();
        });

        $('[data-clear-target="serviceFilters"]').on('click', function () {
            filtersForm[0].reset();
            table.search('').ajax.reload();
        });
    });
</script>
@endpush
