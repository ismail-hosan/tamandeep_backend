@extends('backend.app')

@section('title', 'Show User')

@section('content')

    <div class="content-wrapper">
        <div class="row">
            <!-- User Details Section -->
            <div class="col-lg-4 col-md-5 col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">{{ $user->name ?? 'User' }} Details</h4>
                    </div>
                    <div class="card-body">
                        <div class="media">
                            {{-- <img src="{{ asset($user->avater) }}" class="mr-3 rounded-circle" alt="Avatar" width="80"> --}}
                            <div class="media-body">
                                {{-- <h5 class="mt-0">Name: {{ $user->name }}</h5> --}}
                                <p><strong>Name:</strong> {{ $user->name ?? '' }}</p>
                                {{-- <p><strong>Username:</strong> {{ $user->name ??'' }}</p> --}}
                                <p><strong>Email:</strong> {{ $user->email ?? '' }}</p>
                                {{-- <p><strong>Phone:</strong> {{ $user->phone }}</p> --}}
                                <p>
                                    <strong>Status:</strong> <span
                                        style="color: {{ $user->status == 1 ? 'green' : 'red' }}">{{ $user->status == 1 ? 'active' : 'inactive' }}</span>
                                </p>
                                <p><strong>Role:</strong> {{ ucfirst($user->role) }}</p>
                                <p><strong>Subscription:</strong> {{ ucfirst($user->subscription->plan ?? 'N/A') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Videos Section -->
            <div class="col-lg-8 col-md-7 col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">{{ $user->name ?? 'User' }} All Orders</h4>
                    </div>
                    <div class="card-body">
                        @if ($user->orders->isEmpty())
                            <p class="text-center">No Orders yet.</p>
                        @else
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Unique Code</th>
                                        <th>Qr Code</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($user->orders as $order)
                                        @foreach ($order->items as $items)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $items->unique_code ?? 'N/A' }}</td>
                                                <td>
                                                    <img src="{{ asset('storage/app/public/' . $items->qr_code) }}"
                                                        alt="" width="80px">
                                                </td>
                                                <td>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" role="switch"
                                                            id="flexSwitchCheckDefault"
                                                            {{ $items->status == 1 ? 'checked' : '' }}
                                                            onclick="deleteAlert()">
                                                    </div>
                                                </td>
                                                <td>
                                                    {{-- <a href="" class="btn btn-primary btn-sm" title="Edit Order">
                                                        <i class="bi bi-pencil"></i> Edit
                                                    </a> --}}
                                                    <a href="#" onclick="showDeleteConfirm()"
                                                        class="btn btn-danger btn-sm" title="Delete Order">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>

        </div>

        <!-- Friends Section -->
        {{-- <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">User's Friends</h4>
                </div>
                <div class="card-body">
                    @if ($user->friends->isEmpty())
                        <p class="text-center">No friends found.</p>
                    @else
                        <ul class="list-group">
                            @foreach ($user->friends as $friend)
                                <li class="list-group-item">
                                    <strong>{{ $friend->name }}</strong> ({{ $friend->username }})
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div> --}}

        <!-- Subscribers Section -->
        {{-- <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">User's Subscribers</h4>
                </div>
                <div class="card-body">
                    @if ($user->subscribers->isEmpty())
                        <p class="text-center">No subscribers found.</p>
                    @else
                        <ul class="list-group">
                            @foreach ($user->subscribers as $subscriber)
                                <li class="list-group-item">
                                    <strong>{{ $subscriber->name }}</strong> ({{ $subscriber->username }})
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div> --}}
    </div>

@endsection
@push('script')
    <script src="{{ asset('backend/vendors/sweetalert/sweetalert2@11.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        // delete Confirm
        function showDeleteConfirm(id) {
            event.preventDefault();
            Swal.fire({
                title: 'Are you sure you want to delete this record?',
                text: 'If you delete this, it will be gone forever.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!',
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteItem(id);
                }
            });
        };
        // Sweet alert Delete confirm
        const deleteAlert = (id) => {
            Swal.fire({
                title: "Are you sure?",
                text: "You want to update user status!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, Upgrade status!"
            }).then((result) => {
                if (result.isConfirmed) {
                    updatedStatus(id);
                }
            });
        }


        // deleting an auction
        const updatedStatus = (id) => {
            try {
                let url = ``;
                let csrfToken = `{{ csrf_token() }}`;
                $.ajax({
                    type: "GET",
                    url: url.replace(':id', id),
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: (response) => {
                        console.log(response);
                        $('#data-table').DataTable().ajax.reload();
                        if (response.success === true) {
                            Swal.fire({
                                title: "Deleted!",
                                text: "Auction has been deleted.",
                                icon: "success"
                            });
                        } else if (response.errors) {
                            console.log(response.errors[0])
                            errorAlert()
                        } else {
                            console.log(response.message);
                            errorAlert()
                        }
                    },
                    error: (error) => {
                        console.log(error.message);
                        errorAlert()
                    }
                })
            } catch (e) {
                console.log(e)
            }
        }
    </script>
@endpush
