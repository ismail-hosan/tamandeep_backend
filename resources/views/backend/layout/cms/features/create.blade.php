@extends('backend.app')

@section('title', 'Create Features')

@push('style')
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
                        <h4 class="card-title">Create Features</h4>
                        <div class="mt-4">
                            <form class="forms-sample"action="{{route('features.store')}}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group mb-3">
                                    <label class="form-lable required">Title:</label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror"
                                        id="title" name="title" value="{{ old('title') }}">
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label class="form-label required">Description:</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>


                                {{-- Image --}}
                                <div class="form-group mb-3">
                                    <label class="form-label required">Image</label>
                                    <input type="file" class="form-control dropify @error('image') is-invalid @enderror"
                                        id="image" name="image"
                                        data-default-file="{{ isset($banner) && $banner->image ? asset($banner->image) : asset('backend/images/placeholder/image_placeholder.png') }}"
                                        value="{{ old('image', $banner->image ?? '') }}">
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>


                                <div class="form-group mb-3">
                                    <label class="required">Status:</label>
                                    <select name="status"
                                        class="form-control @error('status') is-invalid @enderror select2" required>
                                        @php($status = old('status', isset($data) ? $data->status : ''))
                                        @foreach (['Active', 'Inactive'] as $sts)
                                            <option value="{{ $sts }}" {{ $status == $sts ? 'selected' : '' }}>
                                                {{ $sts }}</option>
                                        @endforeach
                                    </select>
                                    @error('status')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <button type="submit" class="btn btn-primary me-2">Submit</button>
                                <a href="{{ route('features.index') }}" class="btn btn-danger ">Cancel</a>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            ClassicEditor
                .create(document.querySelector('#content'), {
                    height: '500px'
                })
                .catch(error => {
                    console.error(error);
                });
        });
    </script>
@endpush
