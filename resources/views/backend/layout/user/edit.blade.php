@extends('backend.app')

@section('title', 'Edit Customer')

@push('style')
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/css/dropify.css">
    <style>
        .ck-editor__editable[role="textbox"] {
            min-height: 150px;
        }
    </style>
@endpush

@section('content')

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Edit Customer</h4>
                        <div class="mt-4">
                            <form class="forms-sample"action="{{route('user.update',$customer->id)}}" method="POST"
                                enctype="multipart/form-data">
                                @csrf

                                <input type="hidden" name="role" id="role" value="user">
                                <div class="mb-3 form-group">
                                    <phonelabel class="form-label required">Name</phonelabel>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        id="name" name="name" value="{{ $customer->name ??'' }}">
                                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                </div>
                                <div class="mb-3 form-group">
                                    <label class="form-label required">Email</label>
                                    <input type="email" class="form-control  @error('email') is-invalid @enderror"
                                        name="email" required value="{{ $customer->email ??'' }}" />
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                </div>
                                <div class="mb-3 form-group">
                                    <label class="form-label">Password</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                        name="password" value="{{ old('password') }}" />
                                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                </div>
                                <div class="mb-3 form-group">
                                    <label class="form-label">Confirm Password</label>
                                    <input type="password"
                                        class="form-control @error('password_confirmation')  is-invalid @enderror"
                                        name="password_confirmation" value="{{ old('password_confirmation') }}" />
                                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                                </div>
                                <button type="submit" class="btn btn-primary me-2">Submit</button>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script')
    <script src="https://cdn.ckeditor.com/ckeditor5/41.2.0/classic/ckeditor.js"></script>
    <script type="text/javascript" src="https://jeremyfagis.github.io/dropify/dist/js/dropify.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.select2').select2();
            $('.dropify').dropify(); // Initialize dropify for any existing input fields
        });
    </script>
@endpush
