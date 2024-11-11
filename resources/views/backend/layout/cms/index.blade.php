@extends('backend.app')

@section('title', 'Landing Page Cms')

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
                        <h4 class="card-title">Top Banner</h4>
                        <div class="mt-4">
                            <form class="forms-sample" action="{{ route('cms.banner') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf

                                {{-- Title --}}
                                <div class="form-group mb-3">
                                    <label class="form-label required">Title</label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror"
                                        id="title" name="title" value="{{ old('title', $banner->title ?? '') }}"
                                        placeholder="Title...">
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Highlight Title --}}
                                <div class="form-group mb-3">
                                    <label class="form-label required">Highlight Title</label>
                                    <input type="text" class="form-control @error('hilight_title') is-invalid @enderror"
                                        id="hilight_title" name="hilight_title"
                                        value="{{ old('hilight_title', $banner->hilight_title ?? '') }}"
                                        placeholder="Highlight title...">
                                    @error('hilight_title')
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

                                {{-- Description --}}
                                <div class="form-group mb-3">
                                    <label class="form-label required">Short Description</label>
                                    <input type="text" class="form-control @error('description') is-invalid @enderror"
                                        id="description" name="description"
                                        value="{{ old('description', $banner->descriptions ?? '') }}"
                                        placeholder="Short Descriptions...">
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <button type="submit" class="btn btn-primary me-2">Submit</button>
                            </form>

                        </div>
                    </div>
                </div>
                {{-- Second --}}
                <div class="card mt-4">
                    <div class="card-body">
                        <h4 class="card-title">Second Section</h4>
                        <div class="mt-4">
                            <form class="forms-sample" action="{{ route('cms.second_section') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                {{-- Title --}}
                                <div class="form-group mb-3">
                                    <label class="form-label required">Title</label>
                                    <input type="text" class="form-control @error('f_title') is-invalid @enderror"
                                        id="f_title" name="f_title"
                                        value="{{ old('f_title', $second_section->title ?? '') }}" placeholder="Title...">
                                    @error('f_title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                {{-- Higlight Title --}}
                                <div class="form-group mb-3">
                                    <label class="form-label required">Highlight Title</label>
                                    <input type="text"
                                        class="form-control @error('f_hilight_title') is-invalid @enderror"
                                        id="f_hilight_title" name="f_hilight_title"
                                        value="{{ old('f_hilight_title', $second_section->hilight_title ?? '') }}"
                                        placeholder="Highlight title...">
                                    @error('f_hilight_title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                {{-- Description --}}
                                <div class="form-group mb-3">
                                    <label class="form-label required">Description</label>
                                    <textarea name="f_description" id="f_description" class="form-control @error('f_description') is-invalid @enderror"
                                        placeholder="Description...">{{ old('f_description', $second_section->descriptions ?? '') }}</textarea>
                                    @error('f_description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                {{-- Image --}}
                                <div class="form-group mb-3">
                                    <label class="form-label required">Image</label>
                                    <input type="file"
                                        class="form-control dropify @error('f_image') is-invalid @enderror" id="f_image"
                                        name="f_image"
                                        data-default-file="{{ isset($second_section) && $second_section->image ? asset($second_section->image) : asset('backend/images/placeholder/image_placeholder.png') }}"
                                        value="{{ old('image', $second_section->image ?? '') }}">
                                    @error('f_image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                {{-- First Title Section --}}
                                <div class="form-group mb-3"
                                    style="border: 1px solid #bfbcbc; padding: 15px;border-radius: 13px;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label required">First Title</label>
                                            <input type="text"
                                                class="form-control @error('f_first_title') is-invalid @enderror"
                                                id="f_first_title" name="f_first_title"
                                                value="{{ old('f_first_title', $second_section->first_title ?? '') }}"
                                                placeholder="First Title...">
                                            @error('f_first_title')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label required">First Description</label>
                                            <textarea name="f_first_desc" id="f_first_desc" class="form-control @error('f_first_desc') is-invalid @enderror"
                                                placeholder="First Description...">{{ old('f_first_desc', $second_section->first_desc ?? '') }}</textarea>
                                            @error('f_first_desc')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                {{-- Second Title Section --}}
                                <div class="form-group mb-3"
                                    style="border: 1px solid #bfbcbc; padding: 15px;border-radius: 13px;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label required">Second Title</label>
                                            <input type="text"
                                                class="form-control @error('f_second_title') is-invalid @enderror"
                                                id="f_second_title" name="f_second_title"
                                                value="{{ old('f_second_title', $second_section->second_title ?? '') }}"
                                                placeholder="Second Title...">
                                            @error('f_second_title')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label required">Second Description</label>
                                            <textarea name="f_second_desc" id="f_second_desc" class="form-control @error('f_second_desc') is-invalid @enderror"
                                                placeholder="Second Description...">{{ old('f_second_desc', $second_section->second_desc ?? '') }}</textarea>
                                            @error('f_second_desc')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                {{-- Third Title Section --}}
                                <div class="form-group mb-3"
                                    style="border: 1px solid #bfbcbc; padding: 15px;border-radius: 13px;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label required">Third Title</label>
                                            <input type="text"
                                                class="form-control @error('f_third_title') is-invalid @enderror"
                                                id="f_third_title" name="f_third_title"
                                                value="{{ old('f_third_title', $second_section->third_title ?? '') }}"
                                                placeholder="Third Title...">
                                            @error('f_third_title')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label required">Third Description</label>
                                            <textarea name="f_third_desc" id="f_third_desc" class="form-control @error('f_third_desc') is-invalid @enderror"
                                                placeholder="Third Description...">{{ old('f_third_desc', $second_section->third_desc ?? '') }}</textarea>
                                            @error('f_third_desc')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary me-2">Submit</button>
                                {{-- <a href="{{ route('admin.offers.index') }}" class="btn btn-danger ">Cancel</a> --}}
                            </form>

                        </div>
                    </div>
                </div>
                <div class="card mt-4">
                    <div class="card-body">
                        <h4 class="card-title">Third Section</h4>
                        <div class="mt-4">
                            <form class="forms-sample" action="{{ route('cms.third_section') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                {{-- Title --}}
                                <div class="form-group mb-3">
                                    <label class="form-label required">Title</label>
                                    <input type="text" class="form-control @error('s_title') is-invalid @enderror"
                                        id="s_title" name="s_title"
                                        value="{{ old('s_title', $third_section->title ?? '') }}" placeholder="Title...">
                                    @error('s_title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                {{-- Higlight Title --}}
                                <div class="form-group mb-3">
                                    <label class="form-label required">Highlight Title</label>
                                    <input type="text"
                                        class="form-control @error('s_hilight_title') is-invalid @enderror"
                                        id="s_hilight_title" name="s_hilight_title"
                                        value="{{ old('s_hilight_title', $third_section->hilight_title ?? '') }}"
                                        placeholder="Highlight title...">
                                    @error('s_hilight_title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                {{-- Description --}}
                                <div class="form-group mb-3">
                                    <label class="form-label required">Description</label>
                                    <textarea name="s_description" id="s_description" class="form-control @error('s_description') is-invalid @enderror"
                                        placeholder="Description...">{{ old('s_description', $third_section->descriptions ?? '') }}</textarea>
                                    @error('s_description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                {{-- Image --}}
                                <div class="form-group mb-3">
                                    <label class="form-label required">Image</label>
                                    <input type="file"
                                        class="form-control dropify @error('s_image') is-invalid @enderror"
                                        id="s_image" name="s_image"
                                        data-default-file="{{ isset($third_section) && $third_section->image ? asset($third_section->image) : asset('backend/images/placeholder/image_placeholder.png') }}"
                                        value="{{ old('s_image', $third_section->image ?? '') }}">
                                    @error('s_image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                {{-- First Title Section --}}
                                <div class="form-group mb-3"
                                    style="border: 1px solid #bfbcbc; padding: 15px;border-radius: 13px;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label required">First Title</label>
                                            <input type="text"
                                                class="form-control @error('s_first_title') is-invalid @enderror"
                                                id="s_first_title" name="s_first_title"
                                                value="{{ old('s_first_title', $third_section->first_title ?? '') }}"
                                                placeholder="First Title...">
                                            @error('s_first_title')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label required">First Description</label>
                                            <textarea name="s_first_desc" id="s_first_desc" class="form-control @error('s_first_desc') is-invalid @enderror"
                                                placeholder="First Description...">{{ old('s_first_desc', $third_section->first_desc ?? '') }}</textarea>
                                            @error('s_first_desc')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                {{-- Second Title Section --}}
                                <div class="form-group mb-3"
                                    style="border: 1px solid #bfbcbc; padding: 15px;border-radius: 13px;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label required">Second Title</label>
                                            <input type="text"
                                                class="form-control @error('s_second_title') is-invalid @enderror"
                                                id="s_second_title" name="s_second_title"
                                                value="{{ old('s_second_title', $third_section->second_title ?? '') }}"
                                                placeholder="Second Title...">
                                            @error('s_second_title')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label required">Second Description</label>
                                            <textarea name="s_second_desc" id="s_second_desc" class="form-control @error('s_second_desc') is-invalid @enderror"
                                                placeholder="Second Description...">{{ old('f_second_desc', $third_section->second_desc ?? '') }}</textarea>
                                            @error('s_second_desc')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                {{-- Third Title Section --}}
                                <div class="form-group mb-3"
                                    style="border: 1px solid #bfbcbc; padding: 15px;border-radius: 13px;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label required">Third Title</label>
                                            <input type="text"
                                                class="form-control @error('s_third_title') is-invalid @enderror"
                                                id="s_third_title" name="s_third_title"
                                                value="{{ old('s_third_title', $third_section->third_title ?? '') }}"
                                                placeholder="Third Title...">
                                            @error('s_third_title')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label required">Third Description</label>
                                            <textarea name="s_third_desc" id="s_third_desc" class="form-control @error('s_third_desc') is-invalid @enderror"
                                                placeholder="Third Description...">{{ old('f_third_desc', $third_section->third_desc ?? '') }}</textarea>
                                            @error('s_third_desc')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary me-2">Submit</button>
                                {{-- <a href="{{ route('admin.offers.index') }}" class="btn btn-danger ">Cancel</a> --}}
                            </form>

                        </div>
                    </div>
                </div>
                <div class="card mt-4">
                    <div class="card-body">
                        <h4 class="card-title">Four Or Works</h4>
                        <div class="mt-4">
                            <form class="forms-sample" action="{{ route('cms.four_section') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                {{-- Title --}}
                                <div class="form-group mb-3">
                                    <label class="form-label required">Title</label>
                                    <input type="text" class="form-control @error('t_title') is-invalid @enderror"
                                        id="t_title" name="t_title"
                                        value="{{ old('t_title', $four_section->title ?? '') }}" placeholder="Title...">
                                    @error('t_title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                {{-- Higlight Title --}}
                                <div class="form-group mb-3">
                                    <label class="form-label required">Highlight Title</label>
                                    <input type="text"
                                        class="form-control @error('t_hilight_title') is-invalid @enderror"
                                        id="t_hilight_title" name="t_hilight_title"
                                        value="{{ old('t_hilight_title', $four_section->title ?? '') }}"
                                        placeholder="Highlight title...">
                                    @error('t_hilight_title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                {{-- First Title Section --}}
                                <div class="form-group mb-3"
                                    style="border: 1px solid #bfbcbc; padding: 15px;border-radius: 13px;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label required">First Title</label>
                                            <input type="text"
                                                class="form-control @error('t_first_title') is-invalid @enderror"
                                                id="t_first_title" name="t_first_title"
                                                value="{{ old('t_first_title', $four_section->title ?? '') }}"
                                                placeholder="First Title...">
                                            @error('t_first_title')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label required">First Description</label>
                                            <textarea name="t_first_desc" id="t_first_desc" class="form-control @error('t_first_desc') is-invalid @enderror"
                                                placeholder="First Description...">{{ old('first_desc', $four_section->first_desc ?? '') }}</textarea>
                                            @error('t_first_desc')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    {{-- Image --}}
                                    <div class="form-group mb-3">
                                        <label class="form-label required">First Image</label>
                                        <input type="file"
                                            class="form-control dropify @error('t_first_image') is-invalid @enderror"
                                            id="t_first_image" name="t_first_image"
                                            data-default-file="{{ isset($four_section) && $four_section->first_image ? asset($four_section->first_image) : asset('backend/images/placeholder/image_placeholder.png') }}"
                                            value="{{ old('t_first_image', $four_section->first_image ?? '') }}">
                                        @error('t_first_image')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                {{-- Second Title Section --}}
                                <div class="form-group mb-3"
                                    style="border: 1px solid #bfbcbc; padding: 15px;border-radius: 13px;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label required">Second Title</label>
                                            <input type="text"
                                                class="form-control @error('t_second_title') is-invalid @enderror"
                                                id="t_second_title" name="t_second_title"
                                                value="{{ old('t_second_title', $four_section->title ?? '') }}"
                                                placeholder="Second Title...">
                                            @error('t_second_title')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label required">Second Description</label>
                                            <textarea name="t_second_desc" id="t_second_desc" class="form-control @error('t_second_desc') is-invalid @enderror"
                                                placeholder="Second Description...">{{ old('t_second_desc', $four_section->second_desc ?? '') }}</textarea>
                                            @error('t_second_desc')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    {{-- Image --}}
                                    <div class="form-group mb-3">
                                        <label class="form-label required">Second Image</label>
                                        <input type="file"
                                            class="form-control dropify @error('t_second_image') is-invalid @enderror"
                                            id="t_second_image" name="t_second_image"
                                            data-default-file="{{ isset($four_section) && $four_section->second_image ? asset($four_section->second_image) : asset('backend/images/placeholder/image_placeholder.png') }}"
                                            value="{{ old('t_second_image', $four_section->second_image ?? '') }}">
                                        @error('t_second_image')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                {{-- Third Title Section --}}
                                <div class="form-group mb-3"
                                    style="border: 1px solid #bfbcbc; padding: 15px;border-radius: 13px;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label required">Third Title</label>
                                            <input type="text"
                                                class="form-control @error('t_third_title') is-invalid @enderror"
                                                id="t_third_title" name="t_third_title"
                                                value="{{ old('t_third_title', $four_section->title ?? '') }}"
                                                placeholder="Third Title...">
                                            @error('t_third_title')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label required">Third Description</label>
                                            <textarea name="t_third_desc" id="t_third_desc" class="form-control @error('t_third_desc') is-invalid @enderror"
                                                placeholder="Third Description...">{{ old('t_third_desc', $four_section->third_desc ?? '') }}</textarea>
                                            @error('t_third_desc')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    {{-- Image --}}
                                    <div class="form-group mb-3">
                                        <label class="form-label required">Third Image</label>
                                        <input type="file"
                                            class="form-control dropify @error('t_third_image') is-invalid @enderror"
                                            id="t_third_image" name="t_third_image"
                                            data-default-file="{{ isset($four_section) && $four_section->third_image ? asset($four_section->third_image) : asset('backend/images/placeholder/image_placeholder.png') }}"
                                            value="{{ old('t_third_image', $four_section->third_image ?? '') }}">
                                        @error('t_third_image')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary me-2">Submit</button>
                                {{-- <a href="{{ route('admin.offers.index') }}" class="btn btn-danger ">Cancel</a> --}}
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card mt-4">
                    <div class="card-body">
                        <h4 class="card-title">Features Section</h4>
                        <div class="mt-4">
                            <form class="forms-sample" action="{{route('cms.features')}}" method="POST">
                                @csrf
                                {{-- Title --}}
                                <div class="form-group mb-3">
                                    <label class="form-label required">Title</label>
                                    <input type="text" class="form-control @error('p_title') is-invalid @enderror"
                                        id="p_title" name="p_title" value="{{ old('p_title', $feature->title ?? '') }}"
                                        placeholder="Title...">
                                    @error('p_title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                {{-- Higlight Title --}}
                                <div class="form-group mb-3">
                                    <label class="form-label required">Highlight Title</label>
                                    <input type="text"
                                        class="form-control @error('p_hilight_title') is-invalid @enderror"
                                        id="p_hilight_title" name="p_hilight_title"
                                        value="{{ old('p_hilight_title', $feature->hilight_title ?? '') }}"
                                        placeholder="Highlight title...">
                                    @error('p_hilight_title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                {{-- Description --}}
                                <div class="form-group mb-3">
                                    <label class="form-label required">Description</label>
                                    <textarea name="p_description" id="p_description" class="form-control @error('p_description') is-invalid @enderror"
                                        placeholder="Description...">{{ old('p_description', $feature->descriptions ?? '') }}</textarea>
                                    @error('p_description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-primary me-2">Submit</button>
                                {{-- <a href="{{ route('admin.offers.index') }}" class="btn btn-danger ">Cancel</a> --}}
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card mt-4">
                    <div class="card-body">
                        <h4 class="card-title">Footer Section</h4>
                        <div class="mt-4">
                            <form class="forms-sample" action="{{route('cms.footer')}}" method="POST">
                                @csrf
                                {{-- Description --}}
                                <div class="form-group mb-3">
                                    <label class="form-label required">Description</label>
                                    <textarea name="footer_description" id="footer_description" class="form-control @error('footer_description') is-invalid @enderror"
                                        placeholder="Description...">{{ old('footer_description', $footer->descriptions ?? '') }}</textarea>
                                    @error('footer_description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-primary me-2">Submit</button>
                                {{-- <a href="{{ route('admin.offers.index') }}" class="btn btn-danger ">Cancel</a> --}}
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
