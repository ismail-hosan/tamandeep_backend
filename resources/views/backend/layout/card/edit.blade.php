@extends('backend.app')

@section('title', 'Edit Plan Package')

@push('style')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <style>
        .ck-editor__editable[role="textbox"] {
            min-height: 150px;
        }
    </style>
@endpush

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Edit Card Package</h4>
                        <div class="mt-4">
                            <div class="mt-4">
                                <form class="forms-sample" action="{{ route('card.update', $card->id) }}" method="POST"
                                    enctype="multipart/form-data">
                                    @csrf

                                    <div class="form-group mb-3">
                                        <label class="form-lable required">Name:</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                            id="name" name="name" value="{{ old('name', $card->name ?? '') }}">
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Price Field -->
                                    <div class="form-group mb-3">
                                        <label class="form-label required">Price:</label>
                                        <input type="text" class="form-control @error('price') is-invalid @enderror"
                                            id="price" name="price" value="{{ old('price', $card->price ?? '') }}">
                                        @error('price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Color Field -->
                                    <div class="form-group mb-3">
                                        <label class="form-label required">Color:</label>
                                        <select name="color[]" id="color" class="form-control select2" multiple>
                                            <option value="red"
                                                {{ in_array('red', old('color', $card->colors->pluck('name')->toArray())) ? 'selected' : '' }}>
                                                Red</option>
                                            <option value="blue"
                                                {{ in_array('blue', old('color', $card->colors->pluck('name')->toArray())) ? 'selected' : '' }}>
                                                Blue</option>
                                            <option value="green"
                                                {{ in_array('green', old('color', $card->colors->pluck('name')->toArray())) ? 'selected' : '' }}>
                                                Green</option>
                                        </select>
                                        @error('color')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Image Field -->
                                    <div class="form-group mb-3">
                                        <label class="form-label required">Image</label>
                                        <input type="file"
                                            class="form-control dropify @error('image') is-invalid @enderror" id="image"
                                            name="image"
                                            data-default-file="{{ isset($card->image) && $card->image ? asset($card->image) : asset('backend/images/placeholder/image_placeholder.png') }}"
                                            value="{{ old('image', $card->image ?? '') }}">
                                        @error('image')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Status Field -->
                                    <div class="form-group mb-3">
                                        <label class="required">Status:</label>
                                        <select name="status"
                                            class="form-control @error('status') is-invalid @enderror select2" required>
                                            @php($status = old('status', $card->status ?? ''))
                                            @foreach (['Active', 'Inactive'] as $sts)
                                                <option value="{{ $sts }}"
                                                    {{ $status == $sts ? 'selected' : '' }}>
                                                    {{ $sts }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('status')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Submit Button -->
                                    <button type="submit" class="btn btn-primary me-2">Submit</button>
                                    <a href="{{ route('card.index') }}" class="btn btn-danger">Cancel</a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>
    <script type="text/javascript" src="https://jeremyfagis.github.io/dropify/dist/js/dropify.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.dropify').dropify();
        });
    </script>
@endpush
